<?php
/*
PLUGIN NAME: Affiliate Power
PLUGIN URI: http://www.j-breuer.de/wordpress-plugins/affiliate-power/
DESCRIPTION: With Affiliate Power you can analyze your Affiliate income per Article, Referer, Keyword etc.
AUTHOR: Jonas Breuer
AUTHOR URI: http://www.j-breuer.de
VERSION: 1.5.1
Min WP Version: 3.1
Max WP Version: 4.2
*/
if (!defined('ABSPATH')) die; //no direct access




define('AFFILIATE_POWER_VERSION', '1.5.1');
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
//add_filter('plugins_api', array('Affiliate_Power_Apis', 'getNewVersionInfo'), 10, 3);  


//pretty link integration
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
if(is_plugin_active('pretty-link/pretty-link.php') && $options['add-sub-ids'] == 1) {
	include_once("affiliate-power-prli.php");
}


class Affiliate_Power {

	static public function activation() {
		global $wpdb;
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		
		//since 1.1.0, daily import is at 3:00
		if ( wp_next_scheduled( 'affiliate_power_daily_event' ) ) wp_clear_scheduled_hook('affiliate_power_daily_event');
		wp_schedule_event( strtotime('tomorrow')+3600*3, 'daily', 'affiliate_power_daily_event');
		
		//standard options if this is a new installation
		$options = get_option('affiliate-power-options');
		if (!$options) {
			$options = array('add-sub-ids' => 1);
			update_option('affiliate-power-options', $options);
		}
		$sql = 'CREATE TABLE '.$wpdb->prefix.'ap_transaction (
				ap_transactionID bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				network varchar(32) NOT NULL,
				TransactionId_network varchar(128) NOT NULL,
				Date datetime NOT NULL,
				SubId varchar(64) NOT NULL,
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
		
		//meta options for infotext etc.
		$meta_options = get_option('affiliate-power-meta-options');
		if (!$meta_options) $meta_options = array();
		if (!isset($meta_options['installstamp']) || $meta_options['installstamp'] == '') $meta_options['installstamp'] = date('U');
		//create subid prefix if not exists
		if (!isset($meta_options['subid-prefix']) || $meta_options['subid-prefix'] == '') {
			$subid_prefix = '';
			$chars = 'abcdefghijklmnopqrstuvwxyz';
			for ($i=1; $i <= 3; $i++) $subid_prefix .= substr($chars, mt_rand(0,strlen($chars)-1), 1);
			$meta_options['subid-prefix'] = $subid_prefix;
		}
		$user = wp_get_current_user();
		$first_name = ($user->user_firstname != '') ? $user->user_firstname : $user->user_login;
		
		//register infotexts
		$meta_options['infotext'] = sprintf( __('<h3>Now: 30%s Discount On The Premium Version until 6th of April</h3><p>Hey %s, the new version of Affiliate Power is a big update. The plugin now supports 4 new networks: eBay, Digistore24, Tradetracker and Webgains. There is also a new pricing structure with separate licences for one or multiple sites. To celebrate the new version I\'m offering a huge 30%s discount on the multisite licence. There is a good chance, that the Premium Version will never be so cheap again.<br><h3><a href="http://www.affiliatepowerplugin.com/premium/">Buy your discounted Premium Version now</a></h3><a href="#" class="affiliate-power-hide-infotext">Hide this message</a>', 'affiliate-power'), '%', $first_name, '%');
		
		$meta_options['infotext30'] = sprintf( __('<h3>Hey %s, do you like Affiliate Power?</h3><p>You are using Affiliate Power for more than 30 days now.</p><p>If you like the plugin, a positive review on <a href="http://wordpress.org/support/view/plugin-reviews/affiliate-power" target="_blank">wordpress.org</a> would be great.</p><p>You can also share the plugin in your favorite social networks.</p><ul><li><a href="http://www.facebook.com/sharer/sharer.php?s=100&p[url]=http://www.affiliatepowerplugin.com&p[images][0]=http://www.j-breuer.de/blog/wp-content/uploads/2013/04/affiliate-power-logo.png&p[title]=Affiliate%%20Power&p[summary]=With%%20the%%20WordPress%%20Plugin%%20Affiliate%%20Power%%20you%%20can%%20analyze%%20your%%20Affiliate%%20income%%20per%%20post,%%20traffic%%20source,%%20keyword%%20etc.%%20Focus%%20on%%20things%%20that%%20pay!" target="_blank">Share on Facebook</a></li><li><a href="https://plus.google.com/share?url=http://www.affiliatepowerplugin.com" target="_blank">Share on Google+</a></li><li><a href="http://twitter.com/home?status=With%%20Affiliate%%20Power%%20you%%20can%%20analyze%%20your%%20Affiliate%%20income.%%20Focus%%20on%%20things%%20that%%20pay!%%20http://www.affiliatepowerplugin.com" target="_blank">Share on Twitter</a></li></ul><br /><br /><a href="#" class="affiliate-power-hide-infotext">Hide this message</a>', 'affiliate-power'), $first_name );
		
		$meta_options['infotext60'] = sprintf( __('<h3>Affiliate Power Newsletter</h3><p>You are using Affiliate Power for more than 60 days now. I am glad, that you like the plugin that much. How about a Newsletter to further increase your Affiliate income? As a subscriber to the Affiliate Power Newsletter you get tips and news about Affiliate Marketing once a month.</p><p>Just check your data and click Subscribe.</p><form method="post" target="_blank" action="http://47353.seu1.cleverreach.com/f/47353-107394/wcs/"><input type="text" name="1050108" size="30" value="%s" placeholder="First Name"> <input type="text" name="email" size="30" value="%s" placeholder="Email"> <input type="submit" value="Subscribe" /></form>You can always unsubscribe from the Newsletter and I will not give your Email to anyone else.<br /><br /><p><a href="#" class="affiliate-power-hide-infotext">Hide this message</a></p>', 'affiliate-power'), $first_name, $user->user_email );
		
		$meta_options['infotext90'] = sprintf( __('<h3>Your opinion about Affiliate Power</h3><p>Hey %s, you are using Affiliate Power for 3 months now. What do you think about the plugin?<form method="post" target="_blank" action="http://www.affiliatepowerplugin.com/contact/"><input type="text" name="ec_name" value="%s" placeholder="First Name"><br /><input type="text" name="ec_email" value="%s" placeholder="Email" /><br /><textarea rows="4" cols="40" name="ec_message" placeholder="Your opinion"></textarea><br /><input type="hidden" name="ec_subject" value="Affiliate Power Opinion" /><input type="hidden" value="process" name="ec_stage"><input type="hidden" value="" name="ec_referer"><input type="hidden" value="" name="ec_orig_referer"><input type="submit"  name="submit" value="Send" /></form><br /><br /><p><a href="#" class="affiliate-power-hide-infotext">Hide this message</a></p>', 'affiliate-power'), $first_name, $first_name, $user->user_email );
		
		$meta_options['infotext120'] = sprintf( __('<h3>Earn money with Affiliate Power!</h3><p>Hey %s, you are using Affiliate Power for more than 120 days now. I am glad, that you like the plugin that much.</p><p>Would you like to recommend the the plugin to others? With the Affiliate Program you earn awesome 30%% for each sale of the Premium version. Click the link below.</p><h3><a href="http://www.affiliatepowerplugin.com/affiliate-program/" target="_blank">All Information about the Affiliate program</a></h3><br /><br /><a href="#" class="affiliate-power-hide-infotext">Hide this message</a>', 'affiliate-power'), $first_name );
		
		//show infotext only for updating users
		//1.5.X show infotext for everyone until 07.04.
		//if ($version != '0.0.0') $meta_options['hide-infotext'] = 0;
		if (date('n') == 3 || date('n') == 4 && date('d') < 8) $meta_options['hide-infotext'] = 0;
		else $meta_options['hide-infotext'] = 1;
		update_option('affiliate-power-meta-options', $meta_options);
		
		//welcome message when first install or just upgraded to premium
		$premium = get_option('affiliate-power-premium', false);
		if ($version == '0.0.0' || $premium == false && AFFILIATE_POWER_PREMIUM == true) add_action( 'admin_notices', array('Affiliate_Power', 'activationMessage') );
	}
	
	static public function activationMessage() {
		ob_start();
		if (AFFILIATE_POWER_PREMIUM) {
			printf(__('<div id="message" class="updated"><img src="%s" alt="Affiliate Power" style="float:left; width:36px; margin:6px;" /><h2>Welcome to Affiliate Power Premium!</h2><p>Congratulations again to your decision to shed light on your affiliate income. The plugin will now automatically create the new statistics for all your new sales. IF you want to use the URL-Parameter Tracking, you should enter your parameters on the <a href="%s">Settings Page</a>.</p></div>', 'affiliate-power'), plugins_url('img/affiliate-power-36.png', __FILE__), admin_url('admin.php?page=affiliate-power-settings'));
		}
		else {
			printf(__('<div id="message" class="updated"><img src="%s" alt="Affiliate Power" style="float:left; width:36px; margin:6px;" /><h2>Welcome to Affiliate Power Premium!</h2><p>Whats next? First, you should enter your Affiliate network data on the <a href="%s">Settings Page</a>. Then, you can download your old sales and the plugin will create the first statistics. The article statistic can only be created for new sales. All other statistics also work for old sales.</p></div>', 'affiliate-power'), plugins_url('img/affiliate-power-36.png', __FILE__), admin_url('admin.php?page=affiliate-power-settings'));
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
		delete_option('affiliate-power-meta-options');
		delete_option('affiliate-power-options');
		delete_option('affiliate-power-version');
		delete_option('affiliate-power-premium');
	}
	
	
	static public function init() {
		//load language file
		$plugin_dir = basename(dirname(__FILE__));
		load_plugin_textdomain( 'affiliate-power', '', $plugin_dir . '/languages/' );
		
		//create tables etc. if user updated the plugin
		$version = get_option('affiliate-power-version', '0.0.0');
		$premium = get_option('affiliate-power-premium', false);
		if ($version != AFFILIATE_POWER_VERSION || $premium == false && AFFILIATE_POWER_PREMIUM == true) {
			self::activation();
			update_option('affiliate-power-version', AFFILIATE_POWER_VERSION);
			update_option('affiliate-power-premium', AFFILIATE_POWER_PREMIUM);
		}
		//deactivate infotext
		if (isset($_GET['action']) && $_GET['action'] == 'affiliate-power-hide-infotext') {
			$meta_options = get_option('affiliate-power-meta-options');
			$meta_options['hide-infotext'] = 1;
			update_option('affiliate-power-meta-options', $meta_options);
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
		$target_url = str_replace(array('[', ']'), array('%5B', '%5D'), $_POST['target_url']); //prevent the square brackets being removed from esc_url_raw, they are required for zanox deeplinks
		$target_url = esc_url_raw($target_url);
		$target_url = str_replace(array('%5B', '%5D'), array('[', ']'), $target_url); 
		
		$source_url = esc_url_raw($_POST['source_url']);
		
		//get source and target URLs
		$arr_target_url = parse_url($target_url);
		$arr_source_url = parse_url($source_url);
		
		
		$new_target_url = self::saveClickout($source_url, $target_url);
		
		echo '~'.$new_target_url; // the ~ just separates the url from any unexpected output (notices etc.)
		die;
	}
	
	
	static public function saveClickout($source_url, $target_url) {
	
		//is this an affiliate link?
		$network = false;
		$affiliate_strings = array(
			'adcell' => array('adcell', 'string'),
			'affili' => array('webmasterplan.com', 'string'),
			'belboon' => array('belboon', 'string'),
			'digistore24_classic' => array('digistore24', 'string'),
			'digistore24_plugin' => array('#aff=', 'string'),
			'ebay' => array('ebay', 'string'),
			'superclix' => array('superclix', 'string'),
			'tradedoubler' => array('tradedoubler', 'string'),
			'tradetracker' => array('tradetracker', 'string'),
			'webgains' => array('webgains', 'string'),
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
				if (strpos($target_url, 'bid=') !== false) $target_url = preg_replace('/bid=([0-9]+)\-([0-9]+)/', 'bid=${1}-${2}-'.$subid, $target_url);
				elseif (strpos($target_url, 'encodingId') !== false) $target_url = preg_replace('@encodingId/([0-9a-z]+)@', 'encodingId/${1}/subid/'.$subid, $target_url);
				else $target_url = preg_replace('@slotId/([0-9]+)@', 'slotId/${1}/subid/'.$subid, $target_url);
				break;
				
			case 'affili':
				$options = get_option('affiliate-power-options');
				if ($options['affili-prefix-filter']) {
					$meta_options = get_option('affiliate-power-meta-options');
					$subid = $meta_options['subid-prefix'].$subid;
				}
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
			case 'digistore24_classic':
				if (substr($target_url, -1) == '/') $target_url .= $subid;
				else $target_url .= '/'.$subid;
				break;
				
			case 'digistore24_plugin':
				$target_url .= '&cam='.$subid;
				break;
				
			case 'ebay':
				if (strpos($target_url, "customid=&") !== false) $target_url = str_replace('customid=&', 'customid='.$subid.'&', $target_url);
				elseif (strpos($target_url, "?") !== false) $target_url .= '&customid='.$subid;
				else $target_url .= '?customid='.$subid;
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
				
			case 'tradetracker':
				if (strpos($target_url, "r=&") !== false) $target_url = str_replace('r=&', 'r='.$subid.'&', $target_url);
				elseif (strpos($target_url, '&u=') !== false) $target_url = str_replace('&u=', '&r='.$subid.'&u=', $target_url);
				else $target_url .= '&r='.$subid;
				break;
				
			case 'webgains':
				if (strpos($target_url, "clickref=&") !== false || preg_match('/&clickref=$/', $target_url)) $target_url = str_replace('clickref=', 'clickref='.$subid, $target_url);
				elseif (strpos($target_url, '&wgtarget=') !== false) $target_url = str_replace('&wgtarget=', '&clickref='.$subid.'&wgtarget=', $target_url);
				else $target_url .= '&clickref='.$subid;
				break;
			case 'zanox':
				$target_url .= '&zpar4=[['.$subid.']]';
				break;
		}
		
		return $target_url;
	}
	
}



?>