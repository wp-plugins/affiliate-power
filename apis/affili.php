<?php
if (!defined('ABSPATH')) die; //no direct access

class Affiliate_Power_Api_Affili {


	static public function checkLogin($username, $password) {
		define ("WSDL_LOGON", "https://api.affili.net/V2.0/Logon.svc?wsdl");
		define ("WSDL_STATS",  "https://api.affili.net/V2.0/PublisherStatistics.svc?wsdl");
		$SOAP_LOGON = new SoapClient(WSDL_LOGON);
		
		try {
			$Token      = $SOAP_LOGON->Logon(array(
				'Username'  => $username,
				'Password'  => $password,
				'WebServiceType' => 'Publisher'
				));
		}
		catch (Exception $e) {
			return false;
		}
		return true;	
	}
	
	
	
	
	static public function downloadTransactions($username, $password, $prefix_filter, $subid_prefix, $fromTS, $tillTS) {
		define ("WSDL_LOGON", "https://api.affili.net/V2.0/Logon.svc?wsdl");
		define ("WSDL_STATS",  "https://api.affili.net/V2.0/PublisherStatistics.svc?wsdl");
		
		$SOAP_LOGON = new SoapClient(WSDL_LOGON);
		
		try {
			$Token      = $SOAP_LOGON->Logon(array(
				 'Username'  => $username,
				 'Password'  => $password,
				 'WebServiceType' => 'Publisher'
				 ));
		}
		catch (Expection $e) {
			//todo: error handling, mail to admin etc.
			return array();
		}
		
		$SOAP_REQUEST = new SoapClient(WSDL_STATS);
		
		$params = array(
			'StartDate' => $fromTS,
			'EndDate' => $tillTS,
			'ProgramIds' => array(),
			'ProgramTypes' => 'All',
			'MaximumRecords' => '0',
			'TransactionStatus' => 'All',
			'ValuationType' => 'DateOfRegistration'
			);
				
		$output_transactions = array();
		$page=0;
		
		do
		{
			$page++;
			$page_params = array (
				'CurrentPage' => $page,
				'PageSize' => 100
				);

			$req = $SOAP_REQUEST->GetTransactions(array(
				'CredentialToken' => $Token,
				'TransactionQuery' => $params,
				'PageSettings' => $page_params
				));
				
			//print_r($req);	
				
			//if we only have one transaction, we just get one object. if we have multiplate transactions, we get an array of objects
			$transactions = $req->TransactionCollection->Transaction;
			if (is_object($transactions)) $arr_transactions = array($transactions);
			if (is_array($transactions)) $arr_transactions = $transactions;
			
				
			if (is_array($arr_transactions))
			{
				foreach($arr_transactions as $obj_transaction)
				{
					$date = $obj_transaction->RegistrationDate;
					$number = $obj_transaction->TransactionId;
					$sub_id = $obj_transaction->SubId;
					$shop_id = $obj_transaction->ProgramId;
					$shop_name = $obj_transaction->ProgramTitle;
					$price = $obj_transaction->NetPrice;
					$commission = $obj_transaction->PublisherCommission;
					$status = $obj_transaction->TransactionStatus;
					$checkdate = $obj_transaction->CheckDate;
					
					if ($prefix_filter) {
						$current_sub_id_prefix = substr($sub_id, 0, 3);
						if ($current_sub_id_prefix != $subid_prefix) { echo 'wrong prefix'; continue; }
					}
					if (!is_numeric($sub_id)) $sub_id = substr($sub_id, 3);
					
					if (is_object($obj_transaction->RateInfo)) $transaction_fulltext = $obj_transaction->RateInfo->RateMode;
					else $transaction_fulltext = 'PayPerLead';
					
					$datetime_db = str_replace("T", " ", $date);
					$checkdatetime_db = str_replace("T", " ", $checkdate);
					
					if ($status == 'Confirmed') $confirmed = $commission;
					else $confirmed = 0;
					
					if ($status == 'Open') $checkdatetime_db = '0001-01-01 00:00:00'; //neue API uebermittelt bei offenen Transaktionen gar kein Checkdate, altes Schema beibehalten
					
					if ($transaction_fulltext == 'PayPerSale') $transaction_type = 'S';
					else $transaction_type = 'L';

					$output_transactions[] = array(
						'network' => 'affili', 
						'number' => $number,
						'datetime_db' => $datetime_db,
						'sub_id' => $sub_id,
						'shop_id' => $shop_id,
						'shop_name' => $shop_name,
						'transaction_type' => $transaction_type,
						'price' => $price,
						'commission' => $commission,
						'confirmed' => $confirmed,
						'checkdatetime_db' => $checkdatetime_db,
						'status' => $status
					);
					
				}
			}
		}
		while ($page*100 < $req->TotalRecords);	
		
		return $output_transactions;
	}
	


}

?>