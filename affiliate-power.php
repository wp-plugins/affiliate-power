<?php
/*
PLUGIN NAME: Affiliate Power
PLUGIN URI: http://www.j-breuer.de/wordpress-plugins/affiliate-power/
DESCRIPTION: Affiliate Power ermöglicht es, Affiliate-Einnahmen nach Artikeln, Besucherquellen, Keywords etc. zu analyisren
AUTHOR: Jonas Breuer
AUTHOR URI: http://www.j-breuer.de
VERSION: 0.6.0
Min WP Version: 3.1
Max WP Version: 3.5.1
*/


/* Copyright 2013 Jonas Breuer (email : kontakt@j-breuer.de)
 
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


define('AFFILIATE_POWER_VERSION', '0.6.0');
define('AFFILIATE_POWER_PREMIUM', false);

include_once("affiliate-power-menu.php"); //admin menu
include_once("affiliate-power-apis.php"); //APIs for transaction download
include_once("affiliate-power-widget.php"); //dashboard widget, requires apis

register_activation_hook(__FILE__, array('Affiliate_Power', 'activation'));
register_deactivation_hook(__FILE__, array('Affiliate_Power', 'deactivation'));
register_uninstall_hook(__FILE__, array('Affiliate_Power', 'uninstall'));

$options = get_option('affiliate-power-options');

if ($options['add-sub-ids'] == 1 ) add_action('wp_enqueue_scripts', array('Affiliate_Power', 'addJs'));
add_action('init', array('Affiliate_Power', 'init'));

//if ($options['homepage-tracking'] == 1 ) add_filter('the_content', array('Affiliate_Power', 'addArtId'));

add_action('affiliate_power_daily_event', array('Affiliate_Power_Apis', 'downloadTransactions'));
add_action('wp_ajax_ap_download_transactions', array('Affiliate_Power_Apis', 'downloadTransactionsQuick'));

add_action('wp_ajax_ap_save_clickout', array('Affiliate_Power', 'clickoutAjax'));
add_action('wp_ajax_nopriv_ap_save_clickout', array('Affiliate_Power', 'clickoutAjax'));

add_filter('pre_set_site_transient_update_plugins', array('Affiliate_Power_Apis', 'checkVersion'));  
add_filter('plugins_api', array('Affiliate_Power_Apis', 'getNewVersionInfo'), 10, 3);  


//pretty link integration
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if(is_plugin_active('pretty-link/pretty-link.php') && $options['add-sub-ids'] == 1) {
	include_once("affiliate-power-prli.php");
}


class Affiliate_Power {

	static public function activation() {
		global $wpdb;
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		
		if ( !wp_next_scheduled( 'affiliate_power_daily_event' ) ) {
			wp_schedule_event( current_time( 'timestamp' ), 'daily', 'affiliate_power_daily_event');
		}
		
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
		dbDelta($sql);
		
		$sql = 'CREATE TABLE IF NOT EXISTS '.$wpdb->prefix.'ap_clickout (
				ap_clickoutID bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				ap_visitID bigint(20) unsigned NOT NULL,
				clickout_datetime datetime NOT NULL,
				postID bigint(20) unsigned NOT NULL,
				source_url varchar(512) NOT NULL,
				target_url varchar(512) NOT NULL,
				PRIMARY KEY  (ap_clickoutID)
				)AUTO_INCREMENT=1000001;';
		dbDelta($sql);
	}
	
	static public function deactivation() {
		wp_clear_scheduled_hook('affiliate_power_daily_event');
	}
	
	static public function uninstall() {
		global $wpdb;
		$sql = 'DROP TABLE '.$wpdb->prefix.'ap_transaction;';
		$wpdb->query($sql);
		$sql = 'DROP TABLE '.$wpdb->prefix.'ap_clickout;';
		$wpdb->query($sql);
		delete_option('affiliate-power-options');
		delete_option('affiliate-power-version');
		delete_option('affiliate-power-premium');
	}
	
	
	static public function init() {
		
		//create tables etc. if user updated the plugin
		$version = get_option('affiliate-power-version', '0.0.0');
		$premium = get_option('affiliate-power-premium', false);
		if ($version != AFFILIATE_POWER_VERSION || $premium == false && AFFILIATE_POWER_PREMIUM == true) {
			self::activation();
			update_option('affiliate-power-version', AFFILIATE_POWER_VERSION);
			update_option('affiliate-power-premium', AFFILIATE_POWER_PREMIUM);
		}
		
	}
	
	
	static public function addJs() {
		wp_enqueue_script('affiliate-power', plugins_url('affiliate-power.js', __FILE__), array('jquery'));
		
		wp_localize_script( 'affiliate-power', 'affiliatePower', 
			array( 
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce_clickout' => wp_create_nonce( 'affiliate-power-clickout' )
			)
		);
	}
	
	/*
	static public function addArtId ($content) {
		
		$id = get_the_ID();
		$content = preg_replace("@<a(.*?)>@i", "<a$1 ap_art=\"".$id."\">", $content);
		return $content;
	}
	*/

	
	static public function clickoutAjax() {
	
		//check nonce and create a new if the user wants to make another clickout on the same page
		check_ajax_referer( 'affiliate-power-clickout', 'nonce' );
		$new_nonce = wp_create_nonce( 'affiliate-power-clickout' );
		
		//sanitize
		$target_url = esc_url_raw($_POST['target_url']);
		$source_url = esc_url_raw($_POST['source_url']);
		//$ap_art = (int)$_POST['ap_art'];
		
		//get source and target URLs
		$arr_target_url = parse_url($target_url);
		$arr_source_url = parse_url($source_url);
		
		//internal: just save ap_art in session in case this is a pretty link
		/*
		if ($arr_target_url['host'] == $arr_source_url['host']) {
			if (!session_id()) session_start();
			$_SESSION['ap_art'] = $ap_art;
			$new_target_url = $target_url;
		}
		*/
		
		$new_target_url = self::saveClickout($source_url, $target_url);
		
		echo $new_nonce.'~'.$new_target_url;
		die;
	}
	
	
	static public function saveClickout($source_url, $target_url) {
	
		//is this an affiliate link?
		$network = false;
		$affiliate_strings = array(
			'affili' => array('webmasterplan.com', 'string'),
			'belboon' => array('belboon', 'string'),
			'superclix' => array('superclix', 'string'),
			'tradedoubler' => array('tradedoubler', 'string'),
			'zanox' => array('zanox', 'string'),
			'CJ' =>  array('/click-[0-9]+-[0-9]+/', 'regexp')
		);
		
		foreach ($affiliate_strings as $affiliate_network => $affiliate_string) {
			if (
				$affiliate_string[1] == 'string' && strpos($target_url, $affiliate_string[0]) !== false ||
				$affiliate_string[1] == 'regexp' && preg_match($affiliate_string[0], $target_url) != 0   //!= 0 matches 0(=no hit) and false(=error)
			) {
				$network = $affiliate_network;
				break;
			}
		}
		
		//no affiliate link, ignore
		if (!$network) {
			return $target_url;
		}
		
		//save clickout in db and use as subid
		global $wpdb;
		if (!session_id()) session_start();
		
		$post_id = url_to_postid($source_url);
		//if ($post_id == 0) $post_id = (int)$ap_art;
		
		$wpdb->insert(
			$wpdb->prefix.'ap_clickout',
			array(
				'ap_visitID' => 0,
				'postID' => $post_id,
				'clickout_datetime' => current_time('mysql'), 
				'source_url' => $source_url,
				'target_url' => $target_url
			),
			array ('%d', '%d', '%s', '%s', '%s')
		);
		$subid = $wpdb->insert_id;
	
		if ($network == 'affili') $target_url .= '&subid='.$subid;
		elseif ($network == 'belboon') $target_url .= '/subid='.$subid;
		elseif ($network == 'superclix') $target_url .= '&subid='.$subid;
		elseif ($network == 'tradedoubler') $target_url .= '&epi='.$subid;
		elseif ($network == 'zanox') $target_url = str_replace("T", "S".$subid."T", $target_url);
		elseif ($network == 'CJ') {
			if (strpos($target_url, "?") !== false) $target_url .= '&SID='.$subid;
			else $target_url .= '?SID='.$subid; 
		}
		
		return $target_url;
	}
	
}



?>