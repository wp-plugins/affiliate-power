<?php


class Affiliate_Power_Api_Adcell {


	static public function checkLogin($username, $password) {
		
		$StartDate = time()-3600*24;
		$EndDate = time();

		$report_url = 'http://www.adcell.de/csv_affilistats.php?sarts=x&pid=a&status=a&subid=&eventid=a';
		$report_url .= '&timestart='.$StartDate;
		$report_url .= '&timeend='.$EndDate;
		$report_url .= '&uname='.$username;
		$report_url .= '&pass='.$password;

		$http_answer = wp_remote_get($report_url);
		
		if (is_wp_error($http_answer) || $http_answer['response']['code'] != 200) return false;
		$str_report = $http_answer['body'];
		if (strpos($str_report, 'Fehler - keine gültigen Rechte!') !== false) return false;
		
		
		return true;
	}
	
	


	static public function downloadTransactions($username, $password, $referer_filter, $fromTS, $tillTS) {
	
		$output_transactions = array();
			
		$StartDate = $fromTS;
		$EndDate = $tillTS;

		$report_url = 'http://www.adcell.de/csv_affilistats.php?sarts=x&pid=a&status=a&subid=&eventid=a';
		$report_url .= '&timestart='.$StartDate;
		$report_url .= '&timeend='.$EndDate;
		$report_url .= '&uname='.$username;
		$report_url .= '&pass='.$password;

		$http_answer = wp_remote_get($report_url);
		
		if (is_wp_error($http_answer) || $http_answer['response']['code'] != 200) {
			//todo: error handling, mail to admin etc.
			return array();
		}
		
		$str_report = $http_answer['body'];
		
		if (strpos($str_report, 'Fehler - keine gültigen Rechte!') !== false) {
			//todo: error handling, mail to admin etc.
			return array();
		}
		
		
		$str_report = utf8_encode($str_report);
		$str_report = str_replace('"', '', $str_report);
		$arr_report = explode("\n", $str_report);
		
		//print_r($arr_report);
		
		array_shift($arr_report);
		array_pop($arr_report);
		
		$shopnames = array();
		
		foreach($arr_report as $transaction)
		{
			$arr_transaction = explode(';', $transaction);
			
			if ($referer_filter) {
				$arr_referer = parse_url($arr_transaction[10]);
				$arr_page = parse_url(home_url('/'));
				if ($arr_referer['host'] != $arr_page['host']) continue;
			}
			
			//$shop_name = 'Adcell Programm '.$arr_transaction[3];
			$shop_id = $arr_transaction[3];
			$date = $arr_transaction[1];
			$checkdate = $arr_transaction[2];
			$number = $arr_transaction[0];
			$sub_id = $arr_transaction[6];
			$status = $arr_transaction[7];
			$price = $arr_transaction[8];
			$commission = $arr_transaction[9];
			
			
			//get shopname
			if (!isset($shopnames[$shop_id])) {
				$http_answer_shoppage = wp_remote_get('http://www.adcell.de/partnerprogramme/'.$shop_id);
				if (!is_wp_error($http_answer_shoppage) && $http_answer_shoppage['response']['code'] == 200) {
					preg_match('/<meta name="keywords" content="([^,]+)/', $http_answer_shoppage['body'], $meta_keywords);
					if (isset($meta_keywords[1])) $shopnames[$shop_id] = $meta_keywords[1];
				}
				if (!isset($shopnames[$shop_id])) $shopnames[$shop_id] = 'Adcell Programm '.$shop_id; //fallback
			}
			$shop_name = $shopnames[$shop_id];
			
			
			//convert dates
			$arr_datetime = explode(' ', $date);
			$arr_date = explode('.', $arr_datetime[0]);
			$datetime_db = $arr_date[2].'-'.$arr_date[1].'-'.$arr_date[0].' '.$arr_datetime[1];
			
			if ($checkdate == 'n.a.') $checkdatetime_db = $datetime_db;
			else {
				$arr_checkdatetime = explode(' ', $checkdate);
				$arr_checkdate = explode('.', $arr_checkdatetime[0]);
				$checkdatetime_db = $arr_checkdate[2].'-'.$arr_checkdate[1].'-'.$arr_checkdate[0].' '.$arr_checkdatetime[1];
			}
			
			
			//convert other stuff
			if ($status == 'offen') $status = 'Open';
			elseif ($status == 'bestätigt') $status = 'Confirmed';
			else $status = 'Cancelled'; //TODO: CHECK CORRECT CANCELLED WORDING
			
			if ($sub_id == '[ohne Subid]') $sub_id = 0;
			
			$price = str_replace(array('.','€'), '', $price); //remove 1000-separator and € sign
			$commission = str_replace(array('.','€'), '', $commission);
			$price = str_replace(',', '.', $price);
			$commission = str_replace(',', '.', $commission);
			
			if ($price > 0) $transaction_type = 'S';
			else $transaction_type = 'L';
			
			if ($status == 'Confirmed') $confirmed = $commission;
			else $confirmed = 0;

			
			$output_transactions[] = array(
						'network' => 'adcell', 
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