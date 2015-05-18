<?php
if (!defined('ABSPATH')) die; //no direct access

class Affiliate_Power_Api_Webgains {


	static public function checkLogin($username, $password, $campaignid) {
	
		$StartDate = date('Y-m-d', time()-86400).' 00:00:00';
		$EndDate = date('Y-m-d H:i:s');
		
		$soap = new SoapClient (NULL, 
			array ( 
				"location"   => "http://ws.webgains.com/aws.php",
				"uri"        => "urn:http://ws.webgains.com/aws.php",
				"style"      => SOAP_RPC,
				"use"        => SOAP_ENCODED,
				'exceptions' => 0
			)
		);
		
		$result = $soap->getEarnings($StartDate, $EndDate, $campaignid, $username, $password);
		//print_r($result);
		if (is_soap_fault($result)) return false;
		else return true;
	}
	
	
	static public function downloadTransactions($username, $password, $campaignid, $fromTS, $tillTS) {
	
		$StartDate = date('Y-m-d H:i:s', $fromTS);
		$EndDate = date('Y-m-d H:i:s', $tillTS);
	
		$soap = new SoapClient (NULL, 
			array ( 
				"location"   => "http://ws.webgains.com/aws.php",
				"uri"        => "urn:http://ws.webgains.com/aws.php",
				"style"      => SOAP_RPC,
				"use"        => SOAP_ENCODED,
				'exceptions' => 0
			)
		);
		
		$result = $soap->getFullEarnings($StartDate, $EndDate, $campaignid, $username, $password);
		//print_r($result);
		
		if (is_soap_fault($result)) {
			//todo: error handling, mail to admin etc.
			return array();
		}
		
		$output_transactions = array();
		
		foreach ($result as $transaction) {
		
			$number = $transaction->transactionID;
			$datetime = $transaction->date;
			$sub_id = $transaction->clickRef;
			$shop_id = $transaction->programID;
			$shop_name = $transaction->programName;
			$price = $transaction->saleValue;
			$commission = $transaction->commission;
			$checkdatetime = $transaction->validationDate;
			$status_webgains = $transaction->status;
			
			$datetime_db = str_replace('T', ' ', $datetime);
			$checkdatetime_db = str_replace('T', ' ', $checkdatetime);
			
			if ($status_webgains == 'delayed') $status = 'Open';
			elseif ($status_webgains == 'confirmed') $status = 'Confirmed';
			elseif ($status_webgains == 'cancelled') $status = 'Cancelled';
		
			if ($price > 0) $transaction_type = 'S';
			else $transaction_type = 'L';
			
			if ($status == 'Confirmed') $confirmed = $commission;
			else $confirmed = 0;
		
			$output_transactions[] = array(
				'network' => 'webgains', 
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