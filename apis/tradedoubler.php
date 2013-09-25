<?php


class Affiliate_Power_Api_Tradedoubler {


	static public function checkLogin($report_key) {
		
		$StartDate = date('d.m.y', time()-3600*24*30);
		$EndDate = date('d.m.y');

		$report_url = 'http://www.tradedoubler.com/pan/aReport3Key.action';
		$report_url .= '?reportName=aAffiliateEventBreakdownReport';
		$report_url .= '&columns=eventId';
		$report_url .= '&startDate='.$StartDate;
		$report_url .= '&endDate='.$EndDate;
		$report_url .= '&affiliateId=';
		$report_url .= '&separator=;';
		$report_url .= '&format=CSV';
		$report_url .= '&key='.$report_key;

		$http_answer = wp_remote_get($report_url);
		
		if (is_wp_error($http_answer) || $http_answer['response']['code'] != 200) return false;
		
		$str_report = $http_answer['body'];
		if (strpos($str_report, 'Access Denied') !== false) return false;
		
		return true;
	}
	
	


	static public function downloadTransactions($report_key, $filter_sitename, $fromTS, $tillTS) {
	
		$output_transactions = array();
		
		$arr_filter_sitename = explode(',', $filter_sitename);
		$arr_filter_sitename = array_map('trim', $arr_filter_sitename);
		$StartDate = date('d.m.y', $fromTS);
		$EndDate = date('d.m.y', $tillTS);

		$report_url = 'http://www.tradedoubler.com/pan/aReport3Key.action';
		$report_url .= '?reportName=aAffiliateEventBreakdownReport';
		$report_url .= '&columns=programId';
		$report_url .= '&columns=timeOfEvent';
		$report_url .= '&columns=lastModified';
		$report_url .= '&columns=epi1';
		$report_url .= '&columns=pendingStatus';
		$report_url .= '&columns=affiliateCommission';
		$report_url .= '&columns=leadNR';
		$report_url .= '&columns=orderNR';
		$report_url .= '&columns=orderValue';
		$report_url .= '&columns=eventId';
		$report_url .= '&columns=siteName';
		$report_url .= '&startDate='.$StartDate;
		$report_url .= '&endDate='.$EndDate;
		$report_url .= '&metric1.lastOperator=/';
		$report_url .= '&currencyId=EUR';
		$report_url .= '&event_id=0';
		$report_url .= '&pending_status=1';
		$report_url .= '&metric1.summaryType=NONE';
		$report_url .= '&metric1.operator1=/';
		$report_url .= '&latestDayToExecute=0';
		$report_url .= '&breakdownOption=1';
		$report_url .= '&reportTitleTextKey=REPORT3_SERVICE_REPORTS_AAFFILIATEEVENTBREAKDOWNREPORT_TITLE';
		$report_url .= '&metric1.columnName1=orderValue';
		$report_url .= '&setColumns=true';
		$report_url .= '&metric1.columnName2=orderValue';
		$report_url .= '&decorator=popupDecorator';
		$report_url .= '&metric1.midOperator=/';
		$report_url .= '&affiliateId=';
		$report_url .= '&dateSelectionType=1';
		$report_url .= '&sortBy=timeOfEvent';
		$report_url .= '&customKeyMetricCount=0';
		$report_url .= '&applyNamedDecorator=true';
		$report_url .= '&separator=;';
		$report_url .= '&format=CSV';
		$report_url .= '&key='.$report_key;

		$http_answer = wp_remote_get($report_url);
		
		if (is_wp_error($http_answer) || $http_answer['response']['code'] != 200) {
			//todo: error handling, mail to admin etc.
			return array();
		}
	
		$str_report = $http_answer['body'];
		if (strpos($str_report, 'Access Denied') !== false) {
			//todo: error handling, mail to admin etc.
			return array();
		}
		
		$arr_report = explode("\r\n", $str_report);
		
		//print_r($arr_report);
		
		array_shift($arr_report);
		array_shift($arr_report);
		array_pop($arr_report);
		
		

		


		foreach($arr_report as $transaction)
		{
			$arr_transaction = explode(';', $transaction);
			
			$sitename = $arr_transaction[9];
			if ( $filter_sitename != '' && !in_array($sitename, $arr_filter_sitename) ) continue;
			
			$shop_name = $arr_transaction[0];
			$shop_id = $arr_transaction[1];
			$date = $arr_transaction[2];
			$checkdate = $arr_transaction[3];
			$arr_number['lead'] = $arr_transaction[4];
			$arr_number['sale'] = $arr_transaction[5];
			$sub_id = $arr_transaction[6];
			$arr_number['event'] = $arr_transaction[7];
			$status = $arr_transaction[8];
			$price = $arr_transaction[10];
			$commission = $arr_transaction[11];

			$arr_datetime = explode(' ', $date);
			$arr_date = explode('.', $arr_datetime[0]);
			$year_long = $arr_date[2] + 2000;
			$datetime_db = $year_long.'-'.$arr_date[1].'-'.$arr_date[0].' '.$arr_datetime[1];

			if ($checkdate == '') $checkdatetime_db = $datetime_db;
			else
			{
				$arr_checkdatetime = explode(' ', $checkdate);
				$arr_checkdate = explode('.', $arr_checkdatetime[0]);
				$checkyear_long = $arr_checkdate[2] + 2000;
				$checkdatetime_db = $checkyear_long.'-'.$arr_checkdate[1].'-'.$arr_checkdate[0].' '.$arr_checkdatetime[1];
			}

			//tradedoubler uses sometimes the same number for a lead and for a sale to show that they belong together, but we want two different transactions, so we add the transaction type to the number.
			//They also sometimes use the same number for several products of an order (damn you, tradedoubler developers), so we add the eventId
			if ($arr_number['sale'] == '' && $arr_number['lead'] != '')
			{
				$transaction_type = 'L';
				$number = 'L'.$arr_number['lead'].'-'.$arr_number['event'];
				$price = 0;
			}
			elseif ($arr_number['sale'] != '')
			{
				$transaction_type = 'S';
				$number = 'S'.$arr_number['sale'].'-'.$arr_number['event'];
			}
			else continue; //tradedoubler sometimes reports empty transactions 
			
			if ($status == 'P') $status = 'Open';
			elseif ($status == 'A') $status = 'Confirmed';
			elseif ($status == 'D') $status = 'Cancelled';
			$price = str_replace('.', '', $price); //remove 1000-separator
			$commission = str_replace('.', '', $commission);
			$price = str_replace(',', '.', $price);
			$commission = str_replace(',', '.', $commission);
			if ($status == 'Confirmed') $confirmed = $commission;
			else $confirmed = 0;

			$output_transactions[] = array(
						'network' => 'tradedoubler', 
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