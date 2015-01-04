<?php

class Affiliate_Power_Api_Belboon {


	static public function checkLogin($username, $password) {
		define('WSDL_SERVER', 'http://api.belboon.com/?wsdl');
		
		$config = array(
			'login' => $username,
			'password' => $password,
			'trace' => true
		);
		
		try {
			$client = new SoapClient(WSDL_SERVER, $config);
			$result = $client->getAccountInfo();
		}
		catch( Exception $e ) {
			return false;
		}
		
		return true;
	}
	
	
	
	public static function downloadTransactions($username, $password, $filter_platform, $fromTS, $tillTS) {
		
		define('WSDL_SERVER', 'http://api.belboon.com/?wsdl');
		
		$arr_filter_platform = explode(',', $filter_platform);
		$arr_filter_platform = array_map('trim', $arr_filter_platform);
		$StartDate = date('Y-m-d', $fromTS);
		$EndDate = date('Y-m-d', $tillTS);
		$config = array(
			'login' => $username,
			'password' => $password,
			'trace' => true
		);
		$output_transactions = array();
		
		try {
			$client = new SoapClient(WSDL_SERVER, $config);
			$result = $client->getEventList(
				null, // adPlatformIds
				null, // programId
				null, // eventType
				null, // eventStatus
				'EUR', // eventCurrency
				$StartDate, // eventDateStart
				$EndDate, // eventDateEnd
				null, // eventChangeDateStart
				null, // eventChangeDateEnd
				array('eventdate' => 'ASC'), // orderBy
				null, // limit
				0 // offset
		  );
		}
		catch (Expection $e) {
			//todo: error handling, mail to admin etc.
			return array();
		}
		
		//print_r($result);
		
		foreach ($result->handler->events as $arr_transaction)
		{
			//print_r($transaction);
			
			$platform = $arr_transaction['platformname'];
			if ( $filter_platform != '' && !in_array($platform, $arr_filter_platform) ) continue;
						
			$number = $arr_transaction['eventid'];
			$datetime_db = $arr_transaction['eventdate'];
			$sub_id = str_replace('subid=', '', $arr_transaction['subid']);
			$shop_id = $arr_transaction['programid'];
			$shop_name = $arr_transaction['programname'];
			$transaction_type = substr($arr_transaction['eventtype'], 0, 1);
			$price = $arr_transaction['netvalue'];
			$commission = $arr_transaction['eventcommission'];
			$checkdatetime_db = $arr_transaction['lastchangedate'];

			if ($arr_transaction['eventstatus'] == 'PENDING') {
				$status = 'Open';
				$confirmed = 0;
			}
			elseif ($arr_transaction['eventstatus'] == 'APPROVED') {
				$status = 'Confirmed';
				$confirmed = $commission;
			}
			elseif ($arr_transaction['eventstatus'] == 'REJECTED') {
				$status = 'Cancelled';
				$confirmed = 0;
			}
			
			$output_transactions[] = array(
						'network' => 'belboon', 
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
			
		} //foreach
		return $output_transactions;
	} //function


}



?>