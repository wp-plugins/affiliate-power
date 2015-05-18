<?php
if (!defined('ABSPATH')) die; //no direct access

class Affiliate_Power_Api_Ebay {


	static public function checkLogin($username, $password) {
	
		$StartDate = date('m/d/y', time()-86400);
		$EndDate = date('m/d/y', time());
		
		$password = urlencode(html_entity_decode($password, ENT_QUOTES | ENT_HTML5));
		
		$report_url = 'https://publisher.ebaypartnernetwork.com/PublisherReportsTx?pt=2&user_name='.$username.'&user_password='.$password.'&start_date='.$StartDate.'&end_date='.$EndDate;
		$http_answer = wp_remote_get($report_url);
		//print_r($http_answer);
		if (is_wp_error($http_answer) || $http_answer['response']['code'] != 200) return false;
		$str_report = $http_answer['body'];
		if (strpos($str_report, 'invalid_login') !== false || strpos($str_report, 'Member Login') !== false ) return false;
		
		return true;
	}
	
	
	static public function downloadTransactions($username, $password, $filter_campaign, $fromTS, $tillTS) {
	
		$StartDate = date('m/d/y', $fromTS);
		$EndDate = date('m/d/y', $tillTS);
		
		$password = urlencode(html_entity_decode($password, ENT_QUOTES | ENT_HTML5));
		
		$report_url = 'https://publisher.ebaypartnernetwork.com/PublisherReportsTx?pt=2&user_name='.$username.'&user_password='.$password.'&start_date='.$StartDate.'&end_date='.$EndDate;
		
		//echo $report_url.'<br><br>';
		$args = array('timeout' => 20);
		$http_answer = wp_remote_get($report_url, $args);
		
		//print_r($http_answer);
		
		if (is_wp_error($http_answer) || $http_answer['response']['code'] != 200) {
			//todo: error handling, mail to admin etc.
			return array();
		}
		
		$str_report = $http_answer['body'];
		
		if (strpos($str_report, 'invalid_login') !== false) {
			//todo: error handling, mail to admin etc.
			return array();
		}
		
		$arr_filter_campaign = explode(',', $filter_campaign);
		$arr_filter_campaign = array_map('trim', $arr_filter_campaign);
		
		$arr_report = explode("\n", $str_report);
		$output_transactions = array();
		
		foreach($arr_report as $transaction)
		{
			$arr_transaction = explode("\t", $transaction);
			//print_r($arr_transaction);
			$campaign = $arr_transaction[7];
			if ( $filter_campaign != '' && !in_array($campaign, $arr_filter_campaign) ) continue;
			
			
			$number = $arr_transaction[18];
			$datetime_db =  $arr_transaction[0] . ' 00:00:00';
			$sub_id = $arr_transaction[10];
			$shop_id = $arr_transaction[4];
			$shop_name = $arr_transaction[5];
			$transaction_type = 'S';
			$price = $arr_transaction[15];
			$commission = $arr_transaction[20];
			$confirmed = $commission;
			$checkdatetime_db =  $arr_transaction[1] . ' 00:00:00';
			$status = 'Confirmed';
			
			$str_commission = (string)$commission;
			$str_price = (string)$price;
			if ($str_commission == '') continue;
			if ($str_price == '') $price = 0;
			
			$output_transactions[] = array(
				'network' => 'ebay', 
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