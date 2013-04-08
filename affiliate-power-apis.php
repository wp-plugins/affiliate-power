<?php
if (!defined('ABSPATH')) die; //no direct access

class Affiliate_Power_Apis {

	static protected $transaction_changes = array(
			'new' => array(),
			'confirmed' => array(),
			'cancelled' => array()
	);
	
	
	static public function downloadTransactionsQuick() {
		check_ajax_referer( 'affiliate-power-download-transactions', 'nonce' );
		global $wpdb;
		$transaction_count = $wpdb->get_var('SELECT count(*) FROM '.$wpdb->prefix.'ap_transaction');
		if ($transaction_count == 0) $downloadDays = 100;
		else $downloadDays = 3;
	
		self::downloadTransactions($downloadDays);
	}


	static public function downloadTransactions($days = 100) {
		$fromTS = time()-3600*2-3600*24*$days; //$days Tage in die Verg.
		$tillTS = time()-3600*2; //Jetzt in UTC
		
		$options = get_option('affiliate-power-options');
		//adcell
		if(is_numeric($options['adcell-username']) && isset($options['adcell-password'])) {
			include_once('apis/adcell.php');
			if (!isset($options['adcell-referer-filter'])) $options['adcell-referer-filter'] = 0;
			$transactions = Affiliate_Power_Api_Adcell::downloadTransactions($options['adcell-username'], $options['adcell-password'], $options['adcell-referer-filter'], $fromTS, $tillTS);
			foreach ($transactions as $transaction) self::handleTransaction($transaction);
		}
				

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
		
		
		//Commission Junction
		if(is_numeric($options['cj-id']) && strlen($options['cj-key']) > 20) {
			include_once('apis/cj.php');
			$transactions = Affiliate_Power_Api_Cj::downloadTransactions($options['cj-id'], $options['cj-key'], $fromTS, $tillTS);
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
		
		
		
		//Send mail to admin
		if ($options['send-mail-transactions'] == 1) {
		
			$list_transactions = '';
			$type_mapper = array('new' => 'Neue', 'confirmed' => 'Bestätigte', 'cancelled' => 'Stornierte');
		
			foreach (self::$transaction_changes as $type => $transactions) {
			
				$list_items = '';
				
				foreach ($transactions as $transaction) {
					$datetime_de = date('d.m.Y H:i:s', strtotime($transaction['datetime_db']));
					$list_items .= '<li>'.$datetime_de.': '.$transaction['shop_name'].': '.number_format($transaction['commission'], 2, ',', '.').' &euro;</li>';
				}
				
				if ($list_items != '') {
					$list_transactions .= '
					<p><strong>'.$type_mapper[$type].' Transaktionen:</strong></p>
					<ul>'.$list_items.'</ul>';
				}
			}
			
			//only send if there is any transaction
			if ($list_transactions != '') {
			
				$admin_email = get_option('admin_email');
				$blogname = get_option('blogname');
				
				$mailtext = 
					'<p>Hallo Admin,</p>
					<p>dies ist dein täglicher Einnahmen-Report von Affiliate Power für deine Seite <strong>'.$blogname.'</strong>. Du kannst diesen Report jederzeit in den Einstellungen von Affiliate Power deaktivieren, wenn er dich nervt.</p>'.
					$list_transactions;
					
				mail($admin_email, 'Affiliate-Power Report für '.$blogname, $mailtext, 'content-type: text/html; charset=UTF-8');
			}
		
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
			WHERE TransactionId_network = %s
			AND network = %s
			LIMIT 1';
		
		$existing_transaction = $wpdb->get_row( $wpdb->prepare($sql, $transaction['number'], $transaction['network']) );
		
		
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
				
				self::$transaction_changes['new'][] = $transaction;
		
		}
						
						
		
		//Transaktion existiert bereits, aber der Status hat sich geändert => UPDATE
		elseif ($existing_transaction != null && $transaction['status'] != $existing_transaction->TransactionStatus) {
		
			$wpdb->update( 
				$wpdb->prefix.'ap_transaction', 
				array( 
					'Commission' => (float)$transaction['commission'],	
					'Confirmed' => (float)$transaction['confirmed'],
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
			
			if ($transaction['status'] == 'Confirmed') self::$transaction_changes['confirmed'][] = $transaction;
			elseif($transaction['status'] == 'Cancelled') self::$transaction_changes['cancelled'][] = $transaction;
		}
	
	}
	
	
	static public function checkLicenceKey ($licence_key) {
		
		$licence_key_hash = md5($licence_key);
		$http_answer = wp_remote_post('http://www.j-breuer.de/ap-api/api.php', array(
			'headers' => array('referer' => $_SERVER['HTTP_HOST']),
			'body' => array('action' => 'check', 'key' => $licence_key_hash)
		));
		if (is_wp_error($http_answer) || $http_answer['response']['code'] != 200) return false;
		
		return $http_answer['body'];
	}
	
	
	static public function checkVersion($transient) {
	
		if (empty($transient->checked)) {  
			return $transient;  
		}
		
		$options = get_option('affiliate-power-options');
		if (isset($options['licence-key']) && !empty($options['licence-key'])) {
			$licence_status = self::checkLicenceKey($options['licence-key']);
			if ($licence_status == 'ok') $premium = true;
			else return $transient;  
		}
		else return $transient;
		
		if (!AFFILIATE_POWER_PREMIUM) $updating_to_premium = true;
		else $updating_to_premium = false;
	
		$http_answer = wp_remote_post('http://www.j-breuer.de/ap-api/api.php', array('body' => array('action' => 'version')));
		if (is_wp_error($http_answer) || $http_answer['response']['code'] != 200) return $transient;
		$new_version = $http_answer['body'];
		
	    if (version_compare(AFFILIATE_POWER_VERSION, $new_version, '<') || $updating_to_premium) {  
            $obj = new stdClass();  
            $obj->slug = 'affiliate-power';
            $obj->new_version = $new_version;  
            $obj->url = 'http://www.j-breuer.de/ap-api/api.php';  
            $obj->package = 'http://www.j-breuer.de/ap-api/api.php?key=' . md5($options['licence-key']);
            $transient->response['affiliate-power/affiliate-power.php'] = $obj;  
        }  
	
        return $transient;
    }  


	static public function getNewVersionInfo($false, $action, $arg) {
		
		if (isset($arg->slug) && $arg->slug == 'affiliate-power') {  
			
			$http_answer = wp_remote_post('http://www.j-breuer.de/ap-api/api.php', array('body' => array('action' => 'info')));  
			if (is_wp_error($http_answer) || $http_answer['response']['code'] != 200) return $false;
			
			$information = unserialize($http_answer['body']);
			return $information;  
		}  
		return $false;  
	}

}
?>