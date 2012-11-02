<?php

class Affiliate_Power_Apis {


	static public function downloadTransactions() {
		$fromTS = time()-3600*24*50; //50 Tage in die Verg.
		$tillTS = time()-3600*2; //Jetzt in UTC
		
		$options = get_option('affiliate-power-options');
		
		//affili
		if(is_numeric($options['affili-id']) && strlen($options['affili-password']) == 20) {
			
			$transactions = self::downloadTransactionsAffili($options['affili-id'], $options['affili-password'], $fromTS, $tillTS);
			foreach ($transactions as $transaction) self::handleTransaction($transaction);
		}
		
		//zanox
		if(strlen($options['zanox-connect-id']) == 20 && strlen($options['zanox-public-key']) == 20 && strlen($options['zanox-secret-key']) >= 20) {
			
			$transactions = self::downloadTransactionsZanox($options['zanox-connect-id'], $options['zanox-public-key'], $options['zanox-secret-key'], $fromTS, $tillTS);
			foreach ($transactions as $transaction) self::handleTransaction($transaction);
		}
		
		//tradedoubler
		if(strlen($options['tradedoubler-key']) >= 32) {
			
			$transactions = self::downloadTransactionsTradedoubler($options['tradedoubler-key'], $fromTS, $tillTS);
			foreach ($transactions as $transaction) self::handleTransaction($transaction);
		}
		
		die(); //for proper AJAX request
		
	}
	
	
	static public function handleTransaction($transaction) {
		global $wpdb;
		
		$sql = 'SELECT ap_transactionID, 
			TransactionId_network, 
			Commission, 
			TransactionStatus 
			FROM '.$wpdb->prefix.'ap_transaction
			WHERE TransactionId_network="'.$transaction['number'].'"
			AND network="'.$transaction['network'].'"
			LIMIT 1';
		
		$existing_transaction = $wpdb->get_row( $wpdb->prepare( $sql ) );
		
		
		//Transaktion existiert noch nicht => INSERT
		if ($existing_transaction == null) {
			$wpdb->insert( 
					$wpdb->prefix.'ap_transaction', 
					array( 
						'network' => $transaction['network'],
						'TransactionId_network' => $transaction['number'],
						'Date' => $transaction['datetime_db'],
						'SubId' => $transaction['sub_id'],
						'ProgramId' => $transaction['shop_id'],
						'ProgramTitle' => $transaction['shop_name'],
						'Transaction' => $transaction['transaction_type'],
						'Price' => (float)$transaction['price'],
						'Commission' => (float)$transaction['commission'],	
						'Confirmed' => (float)$transaction['confirmed'],
						'CheckDate' => $transaction['checkdatetime_db'],
						'TransactionStatus' => $transaction['status']
					), 
					array( 
						'%s', //network
						'%s', //number	
						'%s', //datetime_db
						'%d', //sub_id
						'%d', //shop_id
						'%s', //shop_name
						'%s', //transaction_type
						'%f', //price
						'%f', //commission
						'%f', //confirmed
						'%s', //checkdatetime_db
						'%s' //status
					) 
				);
		
		}
						
						
		
		//Transaktion existiert bereits, aber der Status hat sich geändert => UPDATE
		elseif ($existing_transaction != null && $transaction['status'] != $existing_transaction->TransactionStatus) {
		
			$wpdb->update( 
				$wpdb->prefix.'ap_transaction', 
				array( 
					'Commission' => $transaction['Commission'],	
					'Confirmed' => $transaction['Confirmed'],
					'TransactionStatus' => $transaction['status']
				), 
				array( 'ap_transactionID' => $existing_transaction->ap_transactionID ), 
				array( 
					'%f',	// Commission
					'%f',	// Confirmed
					'%s',	// Status
				), 
				array( '%d' ) //ap_transactionID
			);
		}
	
	}
	
	
	
	static public function checkLoginAffili($username, $password) {
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
	
	
	static public function checkLoginZanox($connect_id, $public_key, $secret_key) {
		include_once ("zanox-api/ApiClient.php");
		
		try {
			$zx = ApiClient::factory(PROTOCOL_SOAP);
			$zx->setConnectId($connect_id);
			$zx->setSecretKey($secret_key);
			$zx->setPublicKey($public_key);
			
			$filter_date = date("Y-m-d");
			$zx->GetLeads($filter_date, 'tracking_date', NULL, NULL, NULL, 0, 50);
		}
		catch (Exception $e) {
			return false;
		}
		return true;
	}
	
	
	static public function checkLoginTradedoubler($report_key) {
		
		$StartDate = date('d.m.y', time()-3600*24);
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
		$str_report = $http_answer['body'];
		if (strpos($str_report, 'Access Denied') !== false) return false;
		else return true;
	}
	
	
	
	static public function downloadTransactionsAffili($username, $password, $fromTS, $tillTS) {
		define ("WSDL_LOGON", "https://api.affili.net/V2.0/Logon.svc?wsdl");
		define ("WSDL_STATS",  "https://api.affili.net/V2.0/PublisherStatistics.svc?wsdl");
		
		$SOAP_LOGON = new SoapClient(WSDL_LOGON);
		$Token      = $SOAP_LOGON->Logon(array(
             'Username'  => $username,
             'Password'  => $password,
             'WebServiceType' => 'Publisher'
             ));
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
				
			if (is_array($req->TransactionCollection->Transaction))
			{
				foreach($req->TransactionCollection->Transaction as $obj_transaction)
				{
					$date = $obj_transaction->RegistrationDate;
					$number = $obj_transaction->TransactionId;
					$sub_id = $obj_transaction->SubId;
					$shop_id = $obj_transaction->ProgramId;
					$shop_name = mysql_real_escape_string($obj_transaction->ProgramTitle);
					$transaction_fulltext = $obj_transaction->RateInfo->RateMode;
					$price = $obj_transaction->NetPrice;
					$commission = $obj_transaction->PublisherCommission;
					$status = $obj_transaction->TransactionStatus;
					$checkdate = $obj_transaction->CheckDate;
					
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
	
	
	
	static public function downloadTransactionsZanox($connect_id, $public_key, $secret_key, $fromTS, $tillTS) {
		include_once ("zanox-api/ApiClient.php");
		
		$zx = ApiClient::factory(PROTOCOL_SOAP);
		$zx->setConnectId($connect_id);
		$zx->setSecretKey($secret_key);
		$zx->setPublicKey($public_key);
		
		$filter_date_stamp = $tillTS;
		$filter_date = date("Y-m-d", $filter_date_stamp);
		
		$output_transactions = array();
		
		while($filter_date_stamp > $fromTS) 
		{
			//Leads
			$page=-1;
			do
			{
				$page++;
				try { $result = $zx->GetLeads($filter_date, 'tracking_date', NULL, NULL, NULL, $page, 50); }
				catch (Exception $e) { return $output_transactions; }
				//print_r($result); //vorrübergehend zu Testzwecken

				if ($result->items > 0)
				{
					for ($i=0;$i<count($result->leadItems->leadItem);$i++)
					{
						$date = $result->leadItems->leadItem[$i]->trackingDate;
						$number = $result->leadItems->leadItem[$i]->id;
						$sub_id = isset($result->saleItems->saleItem[$i]->subPublisher->id) ? $result->saleItems->saleItem[$i]->subPublisher->id : 0;
						$shop_id = $result->leadItems->leadItem[$i]->program->id;
						$shop_name = mysql_real_escape_string($result->leadItems->leadItem[$i]->program->_);
						$commission = $result->leadItems->leadItem[$i]->commission;
						$status = $result->leadItems->leadItem[$i]->reviewState;
						$checkdate = $result->leadItems->leadItem[$i]->modifiedDate;

						if ($status == "confirmed") $confirmed = $commission;
						else $confirmed = 0;
						if ($status == "approved") $status = "open"; //keine Unterscheidung zwischen open und approved(=Partner hat Transkation bestätigt, aber noch kein Geld an Zanox überwiesen)
						if ($status == "rejected" || $status == "refused") $status = "cancelled"; //einheitliche Bezeichnungen

						$arr_date = explode("T", $date);
						$arr_date[1] = substr($arr_date[1], 0, 8); //ms und +2 abschneiden
						$datetime_db = implode(" ", $arr_date);

						$arr_checkdate = explode("T", $checkdate);
						$arr_checkdate[1] = substr($arr_checkdate[1], 0, 8);
						$checkdatetime_db = implode(" ", $arr_checkdate);

						$status = ucfirst(strtolower($status));
						
						$output_transactions[] = array(
						'network' => 'zanox', 
						'number' => $number,
						'datetime_db' => $datetime_db,
						'sub_id' => $sub_id,
						'shop_id' => $shop_id,
						'shop_name' => $shop_name,
						'transaction_type' => 'L',
						'price' => 0,
						'commission' => $commission,
						'confirmed' => $confirmed,
						'checkdatetime_db' => $checkdatetime_db,
						'status' => $status
						);
						
					}
				}
			}
			while (($page+1)*50 < $result->total);


			//Sales
			$page=-1;
			do
			{
				$page++;
				try { $result = $zx->GetSales($filter_date, 'tracking_date', NULL, NULL, NULL, $page, 50); }
				catch (Exception $e) { return $output_transactions; }
				//print_r($result); //vorrübergehend zu Testzwecken

				if ($result->items > 0)
				{
					for ($i=0;$i<count($result->saleItems->saleItem);$i++)
					{
						$date = $result->saleItems->saleItem[$i]->trackingDate;
						$number = $result->saleItems->saleItem[$i]->id;
						$sub_id = isset($result->saleItems->saleItem[$i]->subPublisher->id) ? $result->saleItems->saleItem[$i]->subPublisher->id : 0;
						$shop_id = $result->saleItems->saleItem[$i]->program->id;
						$shop_name = mysql_real_escape_string($result->saleItems->saleItem[$i]->program->_);
						$price = $result->saleItems->saleItem[$i]->amount;
						$commission = $result->saleItems->saleItem[$i]->commission;
						$status = $result->saleItems->saleItem[$i]->reviewState;
						$checkdate = $result->saleItems->saleItem[$i]->modifiedDate;

						if ($status == "confirmed") $confirmed = $commission;
						else $confirmed = 0;
						if ($status == "approved") $status = "open"; //keine Unterscheidung zwischen open und approved(=Partner hat Transkation bestätigt, aber noch kein Geld an Zanox überwiesen)
						if ($status == "rejected" || $status == "refused") $status = "cancelled"; //einheitliche Bezeichnungen

						$arr_date = explode("T", $date);
						$arr_date[1] = substr($arr_date[1], 0, 8); //ms und +2 abschneiden
						$datetime_db = implode(" ", $arr_date);

						$arr_checkdate = explode("T", $checkdate);
						$arr_checkdate[1] = substr($arr_checkdate[1], 0, 8);
						$checkdatetime_db = implode(" ", $arr_checkdate);

						$status = ucfirst(strtolower($status));
						

						$output_transactions[] = array(
						'network' => 'zanox', 
						'number' => $number,
						'datetime_db' => $datetime_db,
						'sub_id' => $sub_id,
						'shop_id' => $shop_id,
						'shop_name' => $shop_name,
						'transaction_type' => 'S',
						'price' => $price,
						'commission' => $commission,
						'confirmed' => $confirmed,
						'checkdatetime_db' => $checkdatetime_db,
						'status' => $status
						);
						
					} //for ($i=0;$i<count($result->saleItems->saleItem);$i++)
				} //if ($result->items > 0)
			} //do
			while (($page+1)*50 < $result->total);
			
			$filter_date_stamp -= 60*60*24;
			$arr_filter_date = getdate($filter_date_stamp);
			if ($arr_filter_date['mon'] < 10) $arr_filter_date['mon'] = "0".$arr_filter_date['mon'];
			if ($arr_filter_date['mday'] < 10) $arr_filter_date['mday'] = "0".$arr_filter_date['mday'];
			$filter_date = $arr_filter_date['year'].'-'.$arr_filter_date['mon'].'-'.$arr_filter_date['mday'];
			//echo $select_filter['date']."<br>";
			
		} //while($filter_date_stamp > $end_stamp) 
		
		return $output_transactions;
	}
		
		
	
	static public function downloadTransactionsTradedoubler($report_key, $fromTS, $tillTS) {
	
		$output_transactions = array();
			
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
		$str_report = $http_answer['body'];
		$arr_report = explode("\r\n", $str_report);
		
		
		array_shift($arr_report);
		array_shift($arr_report);
		array_pop($arr_report);
		
		//print_r($arr_report);

		


		foreach($arr_report as $transaction)
		{
			$arr_transaction = explode(';', $transaction);
			$shop_name = mysql_real_escape_string($arr_transaction[0]);
			$shop_id = $arr_transaction[1];
			$date = $arr_transaction[2];
			$checkdate = $arr_transaction[3];
			$arr_number['lead'] = $arr_transaction[4];
			$arr_number['sale'] = $arr_transaction[5];
			$sub_id = $arr_transaction[6];
			$arr_number['event'] = $arr_transaction[7];
			$status = $arr_transaction[8];
			$price = $arr_transaction[9];
			$commission = $arr_transaction[10];

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