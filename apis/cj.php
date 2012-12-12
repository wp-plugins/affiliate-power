<?php

class Affiliate_Power_Api_Cj {


	static public function checkLogin($website_id, $auth_key) {
		
		$StartDate = date('Y-m-d', time()-3600*24);
		$EndDate = date('Y-m-d');
		
		$report_url = 'https://commission-detail.api.cj.com/v3/commissions';
		$report_url .= '?date-type=event';
		$report_url .= '&start-date='.$StartDate; //inclusive
		$report_url .= '&end-date='.$EndDate; //exclusive
		$report_url .= '&website-ids='.$website_id;

		$http_params = array (
			'headers' => array('Authorization' => $auth_key)
		);
		
		$http_answer = wp_remote_get($report_url, $http_params);
		
		if (is_wp_error($http_answer) || $http_answer['response']['code'] != 200) return false;
		
		return true;
	}
	
	
	static public function downloadTransactions($website_id, $auth_key, $fromTS, $tillTS) {
	
		$fromTS_temp = $fromTS;
		$tillTS_temp = $fromTS + 3600*24*30; //cj does not allow more than 30 days
		
		while ($tillTS_temp < $tillTS) {
		
			$StartDate = date('Y-m-d', $fromTS_temp);
			$EndDate = date('Y-m-d', $tillTS_temp);
			
			$report_url = 'https://commission-detail.api.cj.com/v3/commissions';
			$report_url .= '?date-type=event';
			$report_url .= '&start-date='.$StartDate; //inclusive
			$report_url .= '&end-date='.$EndDate; //exclusive
			$report_url .= '&website-ids='.$website_id;

			$http_params = array (
				'headers' => array('Authorization' => $auth_key)
			);
			
			$http_answer = wp_remote_get($report_url, $http_params);
	
			//print_r($http_answer);
			
			if (is_wp_error($http_answer) || $http_answer['response']['code'] != 200) {
				//todo: error handling, mail to admin etc.
				return array();
			}
			
			if(!class_exists("DOMDocument")) {
				//todo: error handling, mail to admin etc.
				return array();
			}
			
			$dom = new DOMDocument();
			$dom->loadXML($http_answer['body']); //posted XML
			$arrTransactions = $dom->getElementsByTagName('commission');
			foreach ($arrTransactions as $transaction) {
			
				$number = $transaction->getElementsByTagName('commission-id')->item(0)->nodeValue;
				$datetime = $transaction->getElementsByTagName('event-date')->item(0)->nodeValue;
				$sub_id = $transaction->getElementsByTagName('sid')->item(0)->nodeValue;
				$shop_id = $transaction->getElementsByTagName('cid')->item(0)->nodeValue;
				$shop_name = $transaction->getElementsByTagName('advertiser-name')->item(0)->nodeValue;
				$price = $transaction->getElementsByTagName('sale-amount')->item(0)->nodeValue;
				$commission = $transaction->getElementsByTagName('commission-amount')->item(0)->nodeValue;
				$checkdatetime = $transaction->getElementsByTagName('locking-date')->item(0)->nodeValue;
				$status = $transaction->getElementsByTagName('action-status')->item(0)->nodeValue;
				
				$arr_datetime= explode("T", $datetime);
				$arr_datetime[1] = substr($arr_datetime[1], 0, 8); //ms und +2 abschneiden
				$datetime_db = implode(" ", $arr_datetime);
				
				$arr_checkdatetime= explode("T", $checkdatetime);
				$arr_checkdatetime[1] = substr($arr_checkdatetime[1], 0, 8); //ms und +2 abschneiden
				$checkdatetime_db = implode(" ", $arr_checkdatetime);
				
				if ($price == '' || $price == 0)$transaction_type = 'L';
				else $transaction_type = 'S';
				
				if ($status == 'locked' || $status == 'closed') {
					$status = 'Confirmed';
					$confirmed = $commission;
				}
				else {
					$status = 'Open';
					$confirmed = 0;
				}

				$output_transactions[] = array(
								'network' => 'cj', 
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
			
			//prepare next request
			$fromTS_temp = $tillTS_temp;
			$tillTS_temp += 3600*24*30;
		}
		
		return $output_transactions;
		
	} //function




}