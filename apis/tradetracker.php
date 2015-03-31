<?php
if (!defined('ABSPATH')) die; //no direct access

class Affiliate_Power_Api_Tradetracker {


	static public function checkLogin($username, $password, $siteid) {

		$soap = new SoapClient('http://ws.tradetracker.com/soap/affiliate?wsdl', array('compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP));
		
		try { 
			$soap->authenticate($username, $password); 
		}
		catch (Exception $e) { 
			return false; 
		}

		$options = array (
			'registrationDateFrom' => date('Y-m-d', time()-86400),
			'registrationDateTo' => date('Y-m-d')
		);
		
		try { 
			$soap->getConversionTransactions($siteid, $options); 
		}
		catch (Exception $e) { 
			return false; 
		}
		
		return true;
	}
	
	
	static public function downloadTransactions($username, $password, $siteid, $fromTS, $tillTS) {
	
		$StartDate = date('Y-m-d', $fromTS);
		$EndDate = date('Y-m-d', $tillTS);
	
		$soap = new SoapClient('http://ws.tradetracker.com/soap/affiliate?wsdl', array('compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP));
		
		try { 
			$soap->authenticate($username, $password); 
		}
		catch (Exception $e) { 
			//todo: error handling, mail to admin etc.
			return array();
		}
		
		
		$options = array (
			'registrationDateFrom' => $StartDate,
			'registrationDateTo' => $EndDate
		);
		
		try { 
			$result = $soap->getConversionTransactions($siteid, $options); 
		}
		catch (Exception $e) { 
			//todo: error handling, mail to admin etc.
			return array();
		}

		//print_r($result);
		
		$output_transactions = array();
		
		foreach ($result as $transaction) {
		
			$number = $transaction->ID;
			$datetime = $transaction->registrationDate;
			$sub_id = $transaction->reference;
			$shop_id = $transaction->campaign->ID;
			$shop_name = $transaction->campaign->name;
			$transaction_type_tradetracker = $transaction->TransactionType;
			$price = $transaction->orderAmount;
			$commission = $transaction->commission;
			$checkdatetime = $transaction->assessmentDate;
			$status_tradetracker = $transaction->transactionStatus;
			
			$datetime_db = str_replace('T', ' ', $datetime);
			$datetime_db = substr($datetime_db, 0, 19);
			
			if ($checkdatetime == '') $checkdatetime = $datetime;
			$checkdatetime_db = str_replace('T', ' ', $checkdatetime);
			$checkdatetime_db = substr($checkdatetime_db, 0, 19);
			
			if ($status_tradetracker == 'pending') $status = 'Open';
			elseif ($status_tradetracker == 'accepted') $status = 'Confirmed';
			elseif ($status_tradetracker == 'rejected') $status = 'Cancelled';
		
			if ($transaction_type_tradetracker == 'sale') $transaction_type = 'S';
			else $transaction_type = 'L';
			
			if ($status == 'Confirmed') $confirmed = $commission;
			else $confirmed = 0;
		
			$output_transactions[] = array(
				'network' => 'tradetracker', 
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
		return $output_transactions;
	}
	
	
	
}