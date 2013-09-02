<?php

class Affiliate_Power_Api_Webworker_Dashboard {	


	static public function checkLogin($username, $apikey) {
		
		$apiserver = "https://www.webworker-dashboard.de/api";
		$arr_page = parse_url(home_url('/'));
		
		//webworker dashboard does not like www domains
		$arr_page['host'] = str_replace('www.', '', $arr_page['host']);
		
		$data = array(
			'method' => 'checkstatus',
			'key' => $apikey,
			'domain' => $arr_page['host'],
		);
		
		$http_params = array (
			'method' => 'POST',
			'headers' => array(
				'Authorization' => 'Basic ' . base64_encode( $username . ':' . $apikey )
			),
			'body' => $data
		);
		
		$http_answer = wp_remote_post($apiserver, $http_params);
		if (is_wp_error($http_answer) || $http_answer['response']['code'] != 200) return false;
		$obj_answer = json_decode($http_answer['body']);
		if ( !isset($obj_answer->result) || $obj_answer->result != 'ok') return false;
		
		return true;
	}
	
	

	static public function sendEarnings($username, $apikey, $day_ts, $earnings) {
	
		$apiserver = "https://www.webworker-dashboard.de/api";
		$arr_page = parse_url(home_url('/'));
		
		//webworker dashboard does not like www domains
		$arr_page['host'] = str_replace('www.', '', $arr_page['host']);

		$data = array(
			'method' => 'addinvest',
			'key' => $apikey,
			'domain' => $arr_page['host'],
			'date' => date('Y-m-d', $day_ts),
			'euro' => $earnings,
			'hours' => '0.00',
			'type' => 'affiliatepower',
			'description' => __('Affiliate Power', 'affiliate-power') 
		);
		
		$http_params = array (
			'method' => 'POST',
			'headers' => array(
				'Authorization' => 'Basic ' . base64_encode( $username . ':' . $apikey )
			),
			'body' => $data
		);
		
		$http_answer = wp_remote_post($apiserver, $http_params);
		if (is_wp_error($http_answer) || $http_answer['response']['code'] != 200) return false;
		$obj_answer = json_decode($http_answer);
		if ( !isset($obj_answer->result) || $obj_answer->result != 'ok') return false;

		return true;
	}
	
}
?>