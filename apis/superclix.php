<?php

class Affiliate_Power_Api_Superclix {

	static public function checkLogin($username, $password) {
	
		$StartDate = date('Y-m-d', time()-3600*24);
		$EndDate = date('Y-m-d');
		
		$report_url = 'http://clix.superclix.de/export/partner/exportstats303.php';
		$report_url .= '?id='.$username;
		$report_url .= '&pw='.$password;
		$report_url .= '&begin='.$StartDate;
		$report_url .= '&end='.$EndDate;
		$report_url .= '&confdate=1';
		
		$http_answer = wp_remote_get($report_url);
		
		if (is_wp_error($http_answer) || $http_answer['response']['code'] != 200) return false;
		
		$str_report = $http_answer['body'];
		
		if (strpos($str_report, 'Nummer') === false) return false;
		
		return true;
	}
	
	
	static public function downloadTransactions($username, $password, $fromTS, $tillTS) {
	
		$StartDate = date('Y-m-d', $fromTS);
		$EndDate = date('Y-m-d', $tillTS);
		
		$report_url = 'http://clix.superclix.de/export/partner/exportstats303.php';
		$report_url .= '?id='.$username;
		$report_url .= '&pw='.$password;
		$report_url .= '&begin='.$StartDate;
		$report_url .= '&end='.$EndDate;
		$report_url .= '&confdate=1';
		
		$http_answer = wp_remote_get($report_url);
		
	
		
		if (is_wp_error($http_answer) || $http_answer['response']['code'] != 200) {
			//todo: error handling, mail to admin etc.
			return array();
		}
	
		$str_report = $http_answer['body'];
		if (strpos($str_report, 'Nummer') === false) {
			//todo: error handling, mail to admin etc.
			return array();
		}
			
		
		$arr_report = explode("\n", $str_report);
		array_shift($arr_report);
		array_pop($arr_report);
		
		//print_r($arr_report);
		
		foreach($arr_report as $transaction)
		{
			$transaction = str_replace('"', '', $transaction);
			$arr_transaction = explode(';', $transaction);
			$arr_shop_type = explode('=', $arr_transaction[3]);
			
			$shop_name = $arr_shop_type[1];
			$transaction_type = substr($arr_shop_type[0], 0, 1);
			$shop_id = $arr_transaction[11];
			$datetime_db = $arr_transaction[7];
			$checkdatetime_db = $arr_transaction[8];
			$number = $arr_transaction[0];
			$sub_id = $arr_transaction[2];
			$status = $arr_transaction[1];
			$price = $arr_transaction[5];
			$commission = $arr_transaction[4];
			
			if ($status == 'offen') $status = 'Open';
			elseif ($status == 'freigegeben') $status = 'Confirmed';
			elseif ($status == 'storniert') $status = 'Cancelled';
			
			$price = str_replace('.', '', $price); //remove 1000-separator
			$commission = str_replace('.', '', $commission);
			$price = str_replace(',', '.', $price);
			$commission = str_replace(',', '.', $commission);
			
			if ($status == 'Confirmed') $confirmed = $commission;
			else $confirmed = 0;

			$output_transactions[] = array(
						'network' => 'superclix', 
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