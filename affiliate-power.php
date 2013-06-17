<?php
/*
PLUGIN NAME: Affiliate Power
PLUGIN URI: http://www.j-breuer.de/wordpress-plugins/affiliate-power/
DESCRIPTION: Affiliate Power ermöglicht es, Affiliate-Einnahmen nach Artikeln, Besucherquellen, Keywords etc. zu analyisren
AUTHOR: Jonas Breuer
AUTHOR URI: http://www.j-breuer.de
VERSION: 1.0.0
Min WP Version: 3.1
Max WP Version: 3.5.1
*/
if (!defined('ABSPATH')) die; //no direct access




define('AFFILIATE_POWER_VERSION', '1.0.0');
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
			wp_schedule_event( current_time( 'timestamp' )+86400, 'daily', 'affiliate_power_daily_event');
		}
		
		//standard options if this is a new installation
		if (!get_option('affiliate-power-options')) {
			$options = array('add-sub-ids' => 1);
			update_option('affiliate-power-options', $options);
		}
		$sql = 'CREATE TABLE '.$wpdb->prefix.'ap_transaction (
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
				UNIQUE KEY uniIdNetwork (TransactionId_network,network),
				KEY SubId (SubId),
				KEY TransactionStatus (TransactionStatus),
				KEY ProgramId (ProgramId),
				KEY network (network),
				KEY Date (Date)
			);';
		dbDelta($sql);
		
		$sql = 'CREATE TABLE '.$wpdb->prefix.'ap_clickout (
				ap_clickoutID bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				ap_visitID bigint(20) unsigned NOT NULL,
				clickout_datetime datetime NOT NULL,
				postID bigint(20) signed NOT NULL,
				source_url varchar(512) NOT NULL,
				target_url varchar(512) NOT NULL,
				PRIMARY KEY  (ap_clickoutID)
				)AUTO_INCREMENT=1000001;';
		dbDelta($sql);
		
		//version pre 0.7.0? update homepage postIDs
		$version = get_option('affiliate-power-version', '0.0.0');
		if (version_compare($version, '0.7.0', '<')) {
			$wpdb->query(
				$wpdb->prepare('
					UPDATE '.$wpdb->prefix.'ap_clickout
					SET postID = -1
					WHERE source_url = %s',
					home_url('/')
				)
			);
		}
		
		add_action( 'admin_notices', array('Affiliate_Power', 'activationMessage') );
	}
	
	static public function activationMessage() {
		ob_start();
		if (AFFILIATE_POWER_PREMIUM) {
			echo '<div id="message" class="updated">
			<img src="'.plugins_url('img/affiliate-power-36.png', __FILE__).'" alt="Affiliate Power" style="float:left; width:36px;" />
			<h2>Herzlich Willkommen bei Affiliate Power Premium!</h2>
			<p>Nochmal Glückwunsch zu der Entscheidung den Blindflug im Affiliate-Marketing zu verlassen und Licht in deine Einnahmen zu bringen. Das Plugin wird von nun an automatisch die neuen Statistiken für alle neuen Sales erstellen. Wenn du das URL-Parameter Tracking verwenden möchtest, solltest du auf der <a href="'.admin_url('admin.php?page=affiliate-power-settings').'">Einstellungsseite</a> die Parameter hinterlegen, die du benutzt.</p>
			</div>';
		}
		else {
			echo '<div id="message" class="updated">
			<img src="'.plugins_url('img/affiliate-power-36.png', __FILE__).'" alt="Affiliate Power" style="float:left; width:36px;" />
			<h2>Herzlich Willkommen bei Affiliate Power!</h2>
			<p>Wie geht es weiter? Zunächst solltest du auf der <a href="'.admin_url('admin.php?page=affiliate-power-settings').'">Einstellungsseite</a> die Daten der Affiliate-Netzwerke hinterlegen, die du benutzt. Dann kannst du deine bisherigen Sales herunterladen und die erste statistische Auswertung vornehmen. Die Auswertung deiner Artikel kann nur für neue Sales erstellt werden. Alle anderen Statistiken funktionieren auch für alte Sales.</p>
			</div>';
		}
		echo ob_get_clean();
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
				'ajaxurl' => admin_url( 'admin-ajax.php' )
			)
		);
	}
	
	static public function url_to_postid( $url ) {  
	
		$post_id = url_to_postid( $url );
		if ($post_id == 0 && $url == home_url('/')) $post_id = -1;  //-1 = Homepage
		if ( $post_id == 0 ) {
		
			// Try custom post types  
			$cpts = get_post_types( array(  
				'public'   => true,  
				'_builtin' => false  
			), 'objects', 'and' );  
			
			// Get path from URL  
			$url_parts = parse_url($url);
			$path = trim($url_parts['path'], '/'); //path has to be without slashes at start and end
			
			// Test against each CPT's rewrite slug  
			foreach ( $cpts as $cpt_name => $cpt ) { 
				$cpt_slug = $cpt->rewrite['slug'];
				
				if ( strlen( $path ) > strlen( $cpt_slug ) && substr( $path, 0, strlen( $cpt_slug ) ) == $cpt_slug ) { 
					$slug = substr( $path, strlen( $cpt_slug ) ); 

					$query = new WP_Query( array( 
						'post_type'         => $cpt_name, 
						'name'              => $slug, 
						'posts_per_page'    => 1  
					));  
					if ( is_object( $query->post ) ) { 
						$post_id = $query->post->ID; 
						break;
					}
				}  
			}  
		}  
		return $post_id;  
	}  
	
	static public function clickoutAjax() {

		
		//ignore bots
		if (strpos($_SERVER['HTTP_USER_AGENT'], 'Googlebot') !== false) die;
		//sanitize
		$target_url = esc_url_raw($_POST['target_url']);
		$source_url = esc_url_raw($_POST['source_url']);
		
		//get source and target URLs
		$arr_target_url = parse_url($target_url);
		$arr_source_url = parse_url($source_url);
		
		
		$new_target_url = self::saveClickout($source_url, $target_url);
		
		echo '~'.$new_target_url;
		die;
	}
	
	
	static public function saveClickout($source_url, $target_url) {
	
		//is this an affiliate link?
		$network = false;
		$affiliate_strings = array(
			'adcell' => array('adcell', 'string'),
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
		$post_id = self::url_to_postid($source_url);
		
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
	
		switch ($network) {
		
			case 'adcell':
				$target_url = preg_replace('/bid=([0-9]+)\-([0-9]+)/', 'bid=${1}-${2}-'.$subid, $target_url);
				break;
				
			case 'affili':
				if (strpos($target_url, '&diurl=') !== false) $target_url = str_replace('&diurl=', '&subid='.$subid.'&diurl=', $target_url);
				else $target_url .= '&subid='.$subid;
				break;
				
			case 'belboon':
				if (strpos($target_url, '/&deeplink=') !== false) $target_url = str_replace('/&deeplink=', '/subid='.$subid.'&deeplink=', $target_url);
				else $target_url .= '/subid='.$subid;
				break;
				
			case 'CJ':
				if (strpos($target_url, 'url=') !== false) $target_url = str_replace('url=', 'sid='.$subid.'&url=', $target_url);
				elseif (strpos($target_url, "?") !== false) $target_url .= '&sid='.$subid;
				else $target_url .= '?sid='.$subid;
				break;
				
			case 'superclix':
				if (strpos($target_url, '&page=') !== false) $target_url = str_replace('&page=', '&subid='.$subid.'&page=', $target_url);
				else $target_url .= '&subid='.$subid;
				break;
				
			case 'tradedoubler':
				if (strpos($target_url, '&url=') !== false) $target_url = str_replace('&url=', '&epi='.$subid.'&url=', $target_url);
				elseif (strpos($target_url, 'p=') !== false) $target_url .= '&epi='.$subid;
				elseif (strpos($target_url, 'url(') !== false) $target_url = str_replace('url(', 'epi('.$subid.')url(', $target_url);
				else $target_url .= 'epi('.$subid.')';
				break;
				
			case 'zanox':
				$target_url .= '&zpar4=[['.$subid.']]';
				break;
		}
		
		return $target_url;
	}
	
}



?>