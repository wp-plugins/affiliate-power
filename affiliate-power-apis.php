<?php
if (!defined('ABSPATH')) die; //no direct access


class Affiliate_Power_Apis {

	
	static public function downloadTransactionsQuick() {
		check_ajax_referer( 'affiliate-power-download-transactions', 'nonce' );
		
		global $wpdb;
		$transaction_count = $wpdb->get_var('SELECT count(*) FROM '.$wpdb->prefix.'ap_transaction');
		if ($transaction_count == 0) $downloadDays = 99;
		else $downloadDays = 3;
	
		self::downloadTransactions($downloadDays);
	}


	static public function downloadTransactions($days = 100) {
	
		global $wpdb;
		
		$fromTS = time()-3600*2-3600*24*$days; //$days in the psat
		$tillTS = time()-3600*2; //now in UTC
		
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
			if (!isset($options['superclix-referer-filter'])) $options['superclix-referer-filter'] = 0;
			$transactions = Affiliate_Power_Api_Superclix::downloadTransactions($options['superclix-username'], $options['superclix-password'], $options['superclix-referer-filter'], $fromTS, $tillTS);
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
		
		
		
		//some stuff only on daily update($days==100))
		if ($days == 100) {
		
			//1.1.0: sales for mail are all sales from the last day now
			$transaction_changes['new'] = $wpdb->get_results('SELECT Date, ProgramTitle, Commission FROM '.$wpdb->prefix.'ap_transaction WHERE date(CheckDate) = date(now() - INTERVAL 1 DAY) AND TransactionStatus <> "Cancelled"', ARRAY_A);
			$transaction_changes['confirmed'] = $wpdb->get_results('SELECT Date, ProgramTitle, Commission FROM '.$wpdb->prefix.'ap_transaction WHERE date(CheckDate) = date(now() - INTERVAL 1 DAY) AND TransactionStatus = "Confirmed"', ARRAY_A);
			$transaction_changes['cancelled'] = $wpdb->get_results('SELECT Date, ProgramTitle, Commission FROM '.$wpdb->prefix.'ap_transaction WHERE date(CheckDate) = date(now() - INTERVAL 1 DAY) AND TransactionStatus = "Cancelled"', ARRAY_A);
			
			$new_transactions_total = $wpdb->get_row('SELECT sum(Commission) as commission, count(*) as cnt FROM '.$wpdb->prefix.'ap_transaction WHERE date(Date) = date(now() - INTERVAL 1 DAY) AND TransactionStatus <> "Cancelled"', ARRAY_A);
			$confirmed_transactions_total = $wpdb->get_row('SELECT sum(Commission) as commission, count(*) as cnt FROM '.$wpdb->prefix.'ap_transaction WHERE date(CheckDate) = date(now() - INTERVAL 1 DAY) AND TransactionStatus = "Confirmed"', ARRAY_A);
			$cancelled_transactions_total = $wpdb->get_row('SELECT sum(Commission) as commission, count(*) as cnt FROM '.$wpdb->prefix.'ap_transaction WHERE date(CheckDate) = date(now() - INTERVAL 1 DAY) AND TransactionStatus = "Cancelled"', ARRAY_A);
			
			
			//Send mail to admin (only when activated )
			if ($options['send-mail-transactions'] == 1) {
			
				$list_transactions = '';
				$type_mapper = array(
				'new' => _x('New', 'multiple', 'affiliate-power'),
				'confirmed' => _x('Confirmed', 'multiple', 'affiliate-power'),
				'cancelled' => _x('Cancelled', 'multiple', 'affiliate-power')
			);
			
				foreach ($transaction_changes as $type => $transactions) {
				
					$list_items = '';
					
					foreach ($transactions as $transaction) {
						$datetime_de = date('d.m.Y H:i:s', strtotime($transaction['Date']));
						$list_items .= '<li>'.$datetime_de.': '.$transaction['ProgramTitle'].': '.number_format($transaction['Commission'], 2, ',', '.').' &euro;</li>';
					}
					
					if ($list_items != '') {
						if ($type == 'new') $total = $new_transactions_total;
						elseif ($type == 'confirmed') $total = $confirmed_transactions_total;
						else $total = $cancelled_transactions_total;
						$list_transactions .= '
						<p><strong>'.$type_mapper[$type].' '.__('Transactions', 'affiliate-power').'</strong></p>
						<ul>'.$list_items.'</ul>'
						.sprintf( __('Total: %d Transactions, %s', 'affiliate-power'), $total['cnt'], number_format($total['commission'], 2, ',', '.').' &euro;' );
					}
				}
				
				//only send if there is any transaction
				if ($list_transactions != '') {
				
					$admin_email = get_option('admin_email');
					$blogname = get_option('blogname');
					
					$mailtext = sprintf (__('<p>Hello Admin,</p><p>This is your daily income report from Affiliate Power for your page <strong>%s</strong>. You can always deactivate this report in the Affiliate Power settings, if you are annoyed about it.</p>', 'affiliate-power'), $blogname);
						
					$mailtext .= $list_transactions;
						
					mail($admin_email, sprintf(__('Affiliate-Power Report for %s', 'affiliate-power'), $blogname), $mailtext, 'content-type: text/html; charset=UTF-8');
				}
			
			}
			
			
			//Send new transactions to webworker dashboard(only when activated)
			if (isset($options['webworker-dashboard-username']) && isset($options['webworker-dashboard-apikey']) ) {
				
				
				include_once('apis/webworker-dashboard.php');
				if ($new_transactions_total['commission'] > 0) {
					Affiliate_Power_Api_Webworker_Dashboard::sendEarnings($options['webworker-dashboard-username'], $options['webworker-dashboard-apikey'], time()-3600*12, $new_transactions_total['commission']);
				}
				if ($cancelled_transactions_total['commission'] > 0) {
					Affiliate_Power_Api_Webworker_Dashboard::sendEarnings($options['webworker-dashboard-username'], $options['webworker-dashboard-apikey'], time()-3600*12, $cancelled_transactions_total['commission']*(-1));
				}
				
			}
					
					
			//Is it time to activate a new infotext?
			$meta_options = get_option('affiliate-power-meta-options');
			$days_since_install = round( (date('U') - $meta_options['installstamp']) / 86400 );
			if (isset($meta_options['infotext'.$days_since_install])) {
				$meta_options['infotext'] = $meta_options['infotext'.$days_since_install];
				$meta_options['hide-infotext'] = 0;
				update_option('affiliate-power-meta-options', $meta_options);
			}
			
			
			//delete user data which led to no sales
			
			//$wpdb->query('delete from '.$wpdb->prefix.'ap_clickout left join '.$wpdb->prefix.'ap_transaction where '.$wpdb->prefix.'ap_transaction.ap_transactionID is null and '.$wpdb->prefix.'ap_clickout.clickout_datetime + interval 120 day < now()');
			
			if(AFFILIATE_POWER_PREMIUM) {
				$wpdb->query('delete from '.$wpdb->prefix.'ap_visitor where not exists (select ap_clickoutID from '.$wpdb->prefix.'ap_clickout where ap_visitID in (select ap_visitID from '.$wpdb->prefix.'ap_visit where ap_visitorID = '.$wpdb->prefix.'ap_visitor.ap_visitorID)) and (select max(visit_datetime) from '.$wpdb->prefix.'ap_visit where ap_visitorID = '.$wpdb->prefix.'ap_visitor.ap_visitorID) + interval 30 day < now()');
				
				$wpdb->query('delete from '.$wpdb->prefix.'ap_visit where not exists (select ap_visitorID from '.$wpdb->prefix.'ap_visitor where ap_visitorID = '.$wpdb->prefix.'ap_visit.ap_visitorID)');
			}
		
		
		} //if ($days == 100)
		
		
		die(); //for proper AJAX request
		
	}
	
	
	static public function handleTransaction($transaction) {
		global $wpdb;
		
		$transaction['number'] = (string)$transaction['number'];
		$transaction['price'] = (float)$transaction['price'];
		$transaction['commission'] = (float)$transaction['commission'];
		$transaction['confirmed'] = (float)$transaction['confirmed'];
		
		$sql = $wpdb->prepare('SELECT ap_transactionID, 
			TransactionId_network, 
			Commission, 
			TransactionStatus 
			FROM '.$wpdb->prefix.'ap_transaction
			WHERE TransactionId_network = %s
			AND network = %s
			LIMIT 1',
			$transaction['number'], $transaction['network']);
		
		$existing_transaction = $wpdb->get_row( $sql );
			
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
						'Price' => $transaction['price'],
						'Commission' => $transaction['commission'],	
						'Confirmed' => $transaction['confirmed'],
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
					'CheckDate'  => $transaction['checkdatetime_db'],
					'Commission' => $transaction['commission'],	
					'Confirmed' => $transaction['confirmed'],
					'TransactionStatus' => $transaction['status']
				), 
				array( 'ap_transactionID' => $existing_transaction->ap_transactionID ), 
				array( 
					'%s',   //checkdatetime_db
					'%f',	// Commission
					'%f',	// Confirmed
					'%s',	// Status
				), 
				array( '%d' ) //ap_transactionID
			);
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
		$premium_valid = false;
		if (isset($options['licence-key']) && !empty($options['licence-key'])) {
			$licence_status = self::checkLicenceKey($options['licence-key']);
			if ($licence_status == 'ok') $premium_valid = true;
		}
		
		if (!AFFILIATE_POWER_PREMIUM && $premium_valid) $updating_to_premium = true;
		else $updating_to_premium = false;
	
		$http_answer = wp_remote_post('http://www.j-breuer.de/ap-api/api.php', array('body' => array('action' => 'version')));
		if (is_wp_error($http_answer) || $http_answer['response']['code'] != 200) $new_version = AFFILIATE_POWER_VERSION;
		else $new_version = $http_answer['body'];
		
	    if ( (version_compare(AFFILIATE_POWER_VERSION, $new_version, '<') && AFFILIATE_POWER_PREMIUM) || $updating_to_premium ) {  
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