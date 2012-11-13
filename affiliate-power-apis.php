<?php

class Affiliate_Power_Apis {


	static public function downloadTransactions() {
		$fromTS = time()-3600*24*100; //100 Tage in die Verg.
		$tillTS = time()-3600*2; //Jetzt in UTC
		
		$options = get_option('affiliate-power-options');

		//affili
		if(is_numeric($options['affili-id']) && strlen($options['affili-password']) == 20) {
			include_once('apis/affili.php');
			$transactions = Affiliate_Power_Api_Affili::downloadTransactions($options['affili-id'], $options['affili-password'], $fromTS, $tillTS);
			foreach ($transactions as $transaction) self::handleTransaction($transaction);
		}
		
		//belboon
		if (isset($options['belboon-username']) && strlen($options['belboon-password']) == 20) {
			include_once('apis/belboon.php');
			$transactions = Affiliate_Power_Api_Belboon::downloadTransactions($options['belboon-username'], $options['belboon-password'], $options['belboon-platform'], $fromTS, $tillTS);
			foreach ($transactions as $transaction) self::handleTransaction($transaction);
		}
		
		//superclix
		if (isset($options['superclix-username']) && isset($options['superclix-password'])) {
			include_once('apis/superclix.php');
			$transactions = Affiliate_Power_Api_Superclix::downloadTransactions($options['superclix-username'], $options['superclix-password'], $fromTS, $tillTS);
			foreach ($transactions as $transaction) self::handleTransaction($transaction);
		}	
		
		//tradedoubler
		if(strlen($options['tradedoubler-key']) >= 32) {
			include_once('apis/tradedoubler.php');
			$transactions = Affiliate_Power_Api_Tradedoubler::downloadTransactions($options['tradedoubler-key'], $options['tradedoubler-sitename'], $fromTS, $tillTS);
			foreach ($transactions as $transaction) self::handleTransaction($transaction);
		}
		
		//zanox
		if(strlen($options['zanox-connect-id']) == 20 && strlen($options['zanox-public-key']) == 20 && strlen($options['zanox-secret-key']) >= 20) {
			include_once('apis/zanox.php');
			$transactions = Affiliate_Power_Api_Zanox::downloadTransactions($options['zanox-connect-id'], $options['zanox-public-key'], $options['zanox-secret-key'], $options['zanox-adspace'], $fromTS, $tillTS);
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


}
?>