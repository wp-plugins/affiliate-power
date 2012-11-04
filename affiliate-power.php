<?php
/*
PLUGIN NAME: Affiliate Power
PLUGIN URI: http://www.j-breuer.de/wordpress-plugins/affiliate-power/
DESCRIPTION: Affiliate Power ermöglicht es, die Affiliate-Einnahmen durch bestimmte Artikel zu ermitteln. 
AUTHOR: Jonas Breuer
AUTHOR URI: http://www.j-breuer.de
VERSION: 0.3.0
Min WP Version: 3.1
Max WP Version: 3.4.2
*/


/* Copyright 2012 Jonas Breuer (email : kontakt@j-breuer.de)
 
This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License, version 3, as
 published by the Free Software Foundation.
 
This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/



include_once("affiliate-power-menu.php"); //admin menu
include_once("affiliate-power-apis.php"); //APIs for transaction download

register_activation_hook(__FILE__, array('Affiliate_Power', 'activation'));
register_deactivation_hook(__FILE__, array('Affiliate_Power', 'deactivation'));
register_uninstall_hook(__FILE__, array('Affiliate_Power', 'uninstall'));

add_action('affiliate_power_daily_event', array('Affiliate_Power_Apis', 'downloadTransactions'));
add_filter('the_content', array('Affiliate_Power', 'addSubIds'));

//pretty link integration
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if(is_plugin_active('pretty-link/pretty-link.php')) {
	include_once("affiliate-power-prli.php");
}


//Affiliate_Power_Apis::downloadTransactions();

class Affiliate_Power {

	static public function activation() {
		global $wpdb;
		wp_schedule_event( current_time( 'timestamp' ), 'daily', 'affiliate_power_daily_event');
		
		$sql = 'CREATE TABLE IF NOT EXISTS '.$wpdb->prefix.'ap_transaction (
					ap_transactionID bigint(20) unsigned NOT NULL AUTO_INCREMENT,
					network varchar(32) NOT NULL,
					TransactionId_network varchar(128) NOT NULL,
					Date datetime NOT NULL,
					SubId int(10) unsigned NOT NULL,
					ProgramId int(10) unsigned NOT NULL,
					ProgramTitle varchar(1024) NOT NULL,
					Transaction char(1) NOT NULL,
					Price float unsigned NOT NULL,
					Commission float NOT NULL,
					Confirmed float NOT NULL,
					CheckDate datetime NOT NULL,
					TransactionStatus varchar(64) NOT NULL,
					PRIMARY KEY  (ap_transactionID),
					KEY SubId (SubId),
					KEY TransactionStatus (TransactionStatus),
					KEY ProgramId (ProgramId),
					KEY network (network),
					KEY Date (Date)
				);';
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
		
	}
	
	static public function deactivation() {
		wp_clear_scheduled_hook('affiliate_power_daily_event');
	}
	
	static public function uninstall() {
		global $wpdb;
		$sql = 'DROP TABLE '.$wpdb->prefix.'ap_transaction;';
		$wpdb->query($sql);
		delete_option('affiliate-power-options');
	}

	static public function addSubIds ($content) {
		
		$options = get_option('affiliate-power-options');
		if ($options['add-sub-ids'] === 0) {
			return $content;
		}
		
		$id = get_the_ID();

		//affili.net
		$content = preg_replace("@(['\"]http://partners\.webmasterplan\.com/click\.asp[^'\"]+)(['\"])@", "$1&subid=".$id."$2", $content);
		
		//zanox
		$content = preg_replace("@(['\"]http://[a-z\-\.]+zanox[a-z\-\.]+/ppc/[A-Z0-9\?]+)T(['\"])@", "$1S".$id."T$2", $content);
		
		//tradedoubler
		$content = preg_replace("@(['\"]http://clkde\.tradedoubler\.com/click[^'\"]+)(['\"])@", "$1&epi=".$id."$2", $content);
		
		
		return $content;
	}
	
}



?>