<?php
if (!defined('ABSPATH')) die; //no direct access


add_action('admin_init', array('Affiliate_Power_Settings', 'addSettings'));


class Affiliate_Power_Settings {


	static public function addSettings() {
		register_setting( 'affiliate-power-options', 'affiliate-power-options', array('Affiliate_Power_Settings', 'optionsValidate') );
		
		add_settings_section('affiliate-power-main', __('Basic settings', 'affiliate-power'), array('Affiliate_Power_Settings', 'optionsMainText'), 'affiliate-power-options');
		add_settings_field('affiliate-power-add-sub-ids', __('Activate tracking', 'affiliate-power'), array('Affiliate_Power_Settings', 'addSubIdsField'), 'affiliate-power-options', 'affiliate-power-main');
		//add_settings_field('affiliate-power-prli-homepage', 'Startseiten Integration', array('Affiliate_Power_Settings', 'addHomepageField'), 'affiliate-power-options', 'affiliate-power-main');
		add_settings_field('affiliate-power-send-mail-transactions', __('Daily email report', 'affiliate-power'), array('Affiliate_Power_Settings', 'addSendMailTransactionsField'), 'affiliate-power-options', 'affiliate-power-main');
		add_settings_field('affiliate-power-licence-key', __('Licence key', 'affiliate-power'), array('Affiliate_Power_Settings', 'addLicenceKeyField'), 'affiliate-power-options', 'affiliate-power-main');
		add_settings_field('affiliate-power-landing-params', __('URL-Parameter', 'affiliate-power'), array('Affiliate_Power_Settings', 'addLandingParamsField'), 'affiliate-power-options', 'affiliate-power-main');
		
		//add_settings_field('affiliate-power-download-method', 'Methode Sale/Lead Download', array('Affiliate_Power_Settings', 'downloadMethod'), 'affiliate-power-options', 'affiliate-power-main');
		
		//Webworker Dashboard
		add_settings_section('affiliate-power-webworker-dashboard', __('Webworker Dashboard', 'affiliate-power'), array('Affiliate_Power_Settings', 'optionsWebworkerDashboardText'), 'affiliate-power-options');
		add_settings_field('affiliate-power-webworker-dashboard-username', __('Webworker Dashboard Email address', 'affiliate-power'), array('Affiliate_Power_Settings', 'addWebworkerDashboardUsernameField'), 'affiliate-power-options', 'affiliate-power-webworker-dashboard');
		add_settings_field('affiliate-power-webworker-dashboard-apikey', __('Webworker Dashboard API Key', 'affiliate-power'), array('Affiliate_Power_Settings', 'addWebworkerDashboardApiKeyField'), 'affiliate-power-options', 'affiliate-power-webworker-dashboard');
		
		
		add_settings_section('affiliate-power-networks', __('Affiliate-Networks', 'affiliate-power'), array('Affiliate_Power_Settings', 'optionsNetworksText'), 'affiliate-power-options');
		
		//Adcell
		add_settings_section('affiliate-power-networks-adcell', __('Adcell', 'affiliate-power'), array('Affiliate_Power_Settings', 'dummyFunction'), 'affiliate-power-options');
		add_settings_field('affiliate-power-adcell-username', __('Adcell Username', 'affiliate-power'), array('Affiliate_Power_Settings', 'addAdcellUsernameField'), 'affiliate-power-options', 'affiliate-power-networks-adcell');
		add_settings_field('affiliate-power-adcell-password', __('Adcell API Password', 'affiliate-power'), array('Affiliate_Power_Settings', 'addAdcellPasswordField'), 'affiliate-power-options', 'affiliate-power-networks-adcell');
		add_settings_field('affiliate-power-adcell-referer-filter', __('Adcell Website Filter', 'affiliate-power'), array('Affiliate_Power_Settings', 'addAdcellRefererFilterField'), 'affiliate-power-options', 'affiliate-power-networks-adcell');

		//affili.net
		add_settings_section('affiliate-power-networks-affili', __('Affili.net', 'affiliate-power'), array('Affiliate_Power_Settings', 'dummyFunction'), 'affiliate-power-options');
		add_settings_field('affiliate-power-affili-id', __('Affili.net UserId', 'affiliate-power'), array('Affiliate_Power_Settings', 'addAffiliIdField'), 'affiliate-power-options', 'affiliate-power-networks-affili');
		add_settings_field('affiliate-power-affili-password', __('Affili.net PublisherWebservice Password', 'affiliate-power'), array('Affiliate_Power_Settings', 'addAffiliPasswordField'), 'affiliate-power-options', 'affiliate-power-networks-affili');
		
		//belboon
		add_settings_section('affiliate-power-networks-belboon', __('Belboon', 'affiliate_power'), array('Affiliate_Power_Settings', 'dummyFunction'), 'affiliate-power-options');
		add_settings_field('affiliate-power-belboon-username', __('Belboon Username', 'affiliate-power'), array('Affiliate_Power_Settings', 'addBelboonUsernameField'), 'affiliate-power-options', 'affiliate-power-networks-belboon');
		add_settings_field('affiliate-power-belboon-passwords', __('Belboon WebService Password', 'affiliate-power'), array('Affiliate_Power_Settings', 'addBelboonPasswordField'), 'affiliate-power-options', 'affiliate-power-networks-belboon');
		add_settings_field('affiliate-power-belboon-platform', __('Belboon Ad Platform Name', 'affiliate-power'), array('Affiliate_Power_Settings', 'addBelboonPlatformField'), 'affiliate-power-options', 'affiliate-power-networks-belboon');
		
		//Commission Junction
		add_settings_section('affiliate-power-networks-cj', __('Commission Junction', 'affiliate-power'), array('Affiliate_Power_Settings', 'dummyFunction'), 'affiliate-power-options');
		add_settings_field('affiliate-power-cj-id', __('Commission Junction PID', 'affiliate-power'), array('Affiliate_Power_Settings', 'addCjIdField'), 'affiliate-power-options', 'affiliate-power-networks-cj');
		add_settings_field('affiliate-power-cj-key', __('Commission Junction Developer Key', 'affiliate-power'), array('Affiliate_Power_Settings', 'addCjKeyField'), 'affiliate-power-options', 'affiliate-power-networks-cj');
		
		//superclix
		add_settings_section('affiliate-power-networks-superclix', __('Superclix', 'affiliate-power'), array('Affiliate_Power_Settings', 'dummyFunction'), 'affiliate-power-options');
		add_settings_field('affiliate-power-superclix-usename', __('Superclix Username', 'affiliate-power'), array('Affiliate_Power_Settings', 'addSuperclixUSernameField'), 'affiliate-power-options', 'affiliate-power-networks-superclix');
		add_settings_field('affiliate-power-superclix-password', __('Superclix Export Password', 'affiliate-power'), array('Affiliate_Power_Settings', 'addSuperclixPasswordField'), 'affiliate-power-options', 'affiliate-power-networks-superclix');
		add_settings_field('affiliate-power-superclix-referer-filter', __('Superclix Website Filter', 'affiliate-power'), array('Affiliate_Power_Settings', 'addSuperclixRefererFilterField'), 'affiliate-power-options', 'affiliate-power-networks-superclix');
		
		//tradedoubler
		add_settings_section('affiliate-power-networks-tradedoubler', __('Tradedoubler', 'affiliate-power'), array('Affiliate_Power_Settings', 'dummyFunction'), 'affiliate-power-options');
		add_settings_field('affiliate-power-tradedoubler-key', __('Tradedoubler Report Key', 'affiliate-power'), array('Affiliate_Power_Settings', 'addTradedoublerKeyField'), 'affiliate-power-options', 'affiliate-power-networks-tradedoubler');
		add_settings_field('affiliate-power-tradedoubler-sitename', __('Tradedoubler Site name', 'affiliate-power'), array('Affiliate_Power_Settings', 'addTradedoublerSitenameField'), 'affiliate-power-options', 'affiliate-power-networks-tradedoubler');
		
		//zanox
		add_settings_section('affiliate-power-networks-zanox', __('Zanox', 'affiliate-power'), array('Affiliate_Power_Settings', 'dummyFunction'), 'affiliate-power-options');
		add_settings_field('affiliate-power-zanox-connect-id', __('Zanox ConnectId', 'affiliate-power'), array('Affiliate_Power_Settings', 'addZanoxConnectIdField'), 'affiliate-power-options', 'affiliate-power-networks-zanox');
		add_settings_field('affiliate-power-zanox-public-key', __('Zanox Public Key', 'affiliate-power'), array('Affiliate_Power_Settings', 'addZanoxPublicKeyField'), 'affiliate-power-options', 'affiliate-power-networks-zanox');
		add_settings_field('affiliate-power-zanox-secret-key', __('Zanox Secret Key', 'affiliate-power'), array('Affiliate_Power_Settings', 'addZanoxSecretKeyField'), 'affiliate-power-options', 'affiliate-power-networks-zanox');
		add_settings_field('affiliate-power-zanox-adspace', __('Zanox Ad space name', 'affiliate-power'), array('Affiliate_Power_Settings', 'addZanoxAdspaceField'), 'affiliate-power-options', 'affiliate-power-networks-zanox');
		
		
		
		
	}
	
	
	static public function dummyFunction() {
	}
	
	
	static public function optionsPage() {
		include_once 'options-head.php'; //we need this to show error messages
		?>
		<div class="wrap">
		<div class="icon32" style="background:url(<?php echo plugins_url('affiliate-power/img/affiliate-power-36.png'); ?>) no-repeat;"><br/></div>
		<h2><?php _e('Affiliate Power Settings', 'affiliate-power'); ?></h2>
		<?php
		$meta_options = get_option('affiliate-power-meta-options');
		//Check Licence
		$options = get_option('affiliate-power-options');
		if (isset($options['licence-key'])) {
			echo '<div class="updated"><p><strong>'.__('You entered a valid licence key but you did not download the premium version yet. Please go to the <a href="update-core.php">Update Page</a> and update to the premium version. It can take up to 5 minutes until WordPress notifies you about the new version.', 'affiliate-power').'</strong></p></div>';
		}
		//Infotext
		if (isset($meta_options['infotext']) && $meta_options['hide-infotext'] == 0) {
			echo '<div class="updated">'.$meta_options['infotext'].'</div>';
		}
		
		_e('<p>If you have problems or ideas for new features I always appreciate a comment on the <a href="http://www.affiliatepowerplugin.com" target="_blank">Plugin Page</a> or a <a href="http://www.affiliatepowerplugin.com/contact/" target="_blank">Message</a>.</p>', 'affiliate-power');
		
		_e('<p>You like the plugin but you want to know more about your income? There is also a premium version of Affiliate Power where you can track the User Source, Keyword, Landing Page and URL-Parameters like utm_campaign. Find more information on the <a href="http://www.affiliatepowerplugin.com/premium/" target="_blank">Premium Page</a>.<p>', 'affiliate-power');
		
		_e('<p><strong>Now you can earn directly money with Affiliate Power.</strong> You get 30% Commission for each sale of the Premium-Version. <a href="http://www.affiliatepowerplugin.com/affiliate-program/" target="_blank"><strong>All Information about the Affiliate program.</strong></a></p>', 'affiliate-power');
		
		_e('<p>Please be patient when saving the settings. The plugin performs a test login at the networks while saving.</p>', 'affiliate-power');
		
		$user = wp_get_current_user();
		$first_name = ($user->user_firstname != '') ? $user->user_firstname : $user->user_login;
		printf( __('<h3>Newsletter</h3><p>As a subscriber to the Affiliate Power Newsletter you get tips and news about Affiliate Marketing once a month. Just check your data and click Subscribe.</p><form method="post" target="_blank" action="http://47353.seu1.cleverreach.com/f/47353-107394/wcs/"><input type="text" name="1050108" size="30" value="%s" placeholder="First Name"> <input type="text" name="email" size="30" value="%s" placeholder="Email"> <input type="submit" value="Subscribe"></form>You can always unsubscribe from the Newsletter and I will not give your Email to anyone else.', 'affiliate-power'), $first_name, $user->user_email );
		?>
		<form action="options.php" method="post">
		<?php settings_fields('affiliate-power-options'); ?>
		<?php do_settings_sections('affiliate-power-options'); ?>
		<p><input name="Submit" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" /></p>
		</form>
		
		</div>
		<?php
	}
	
	
	
	static public function optionsMainText() {
		echo '';
	}
	
	
	static public function addSubIdsField() {
		$options = get_option('affiliate-power-options');
		if (!isset($options['add-sub-ids'])) $options['add-sub-ids'] = 0;
		$checked = $options['add-sub-ids'] ? ' checked' : '';
		echo "<input type='checkbox' id='affiliate-power-add-sub-ids' name='affiliate-power-options[add-sub-ids]' value='1' ".$checked." /> ";
		echo "<span style='font-size:1em;'><a href='#' onclick='document.getElementById(\"ap_sub_id_info\").style.display=\"block\"; return false;'>[?]</a></span>";
		_e("<div id='ap_sub_id_info' style='display:none;'>With the activated tracking, you can find out which articles/referer/keywords etc. led to which income. Sales, which occurred before the plugin installation can not be analyzed. This option makes sense for almost all plugin users.</div>", "affiliate-power");
	}
	
	
	static public function addSendMailTransactionsField() {
		$options = get_option('affiliate-power-options');
		if (!isset($options['send-mail-transactions'])) $options['send-mail-transactions'] = 0;
		$checked = $options['send-mail-transactions'] ? ' checked' : '';
		echo "<input type='checkbox' id='affiliate-power-send-mail-transactions' name='affiliate-power-options[send-mail-transactions]' value='1' ".$checked." /> ";
		echo "<span style='font-size:1em;'><a href='#' onclick='document.getElementById(\"ap_send_mail_transactions_info\").style.display=\"block\"; return false;'>[?]</a></span>";
		_e("<div id='ap_send_mail_transactions_info' style='display:none;'>Receive a daily email with all new or changed sales. If there aren't any sales, no email will be send.</div>", "affiliate-power");
	}
	
	static public function addLicenceKeyField() {
		$options = get_option('affiliate-power-options');
		if (!isset($options['licence-key'])) $options['licence-key'] = '';
		echo "<input type='password' id='affiliate-power-licence-key' name='affiliate-power-options[licence-key]' size='40' value='".$options['licence-key']."' /> ";
		echo "<span style='font-size:1em;'><a href='#' onclick='document.getElementById(\"ap_licence_key_info\").style.display=\"block\"; return false;'>[?]</a></span>";
		_e("<div id='ap_licence_key_info' style='display:none;'>There is also a premium version of Affiliate Power which requires a licence key. Find more information about the premium version on the <a href='http://www.affiliatepowerplugin.com/premium/' target='_blank'>Premium Page</a>. For using the basic version, leave this field empty.</div>", "affiliate-power");
	}
	
	static public function addLandingParamsField() {
		
		echo "<input type='text' size='80' value='".__('Only in the premium version', 'affiliate-power')."' readonly='readonly' style='color:#888; cursor:pointer;' onclick='window.open(\"".__('http://www.affiliatepowerplugin.com/premium/', 'affiliate-power')."\", \"_blank\")' /> ";
		echo "<span style='font-size:1em;'><a href='#' onclick='document.getElementById(\"ap_landing_params_info\").style.display=\"block\"; return false;'>[?]</a></span>";
		_e("<div id='ap_landing_params_info' style='display:none;'>Here you can define URL-Parameters you want to track. You can separate several parameters with comma. If you are using Google Analytics campaign tracking, this values may be a good start: <em>utm_campaign,utm_source,utm_medium,utm_term,utm_content</em>.</div>", "affiliate-power");
	}
	
	
	//Webworker Dashboard
	static public function optionsWebworkerDashboardText() {
		_e('With the <a href="https://www.webworker-dashboard.de" target="_blank">Webworker Dashboard</a> you can keep track of all your projects. If you have an account there, you can import your affiliate earnings automatically by entering your account details here. This is completely optional. If you do not enter your account details, no data will be send.', 'affiliate-power');
	}
	
	static public function addWebworkerDashboardUsernameField() {
		$options = get_option('affiliate-power-options');
		if (!isset($options['webworker-dashboard-username'])) $options['webworker-dashboard-username'] = '';
		echo "<input type='text' id='affiliate-power-webworker-dashboard-username' name='affiliate-power-options[webworker-dashboard-username]' size='40' value='".$options['webworker-dashboard-username']."' /> ";
		echo "<span style='font-size:1em;'><a href='#' onclick='document.getElementById(\"ap_webworker_dashboard_username_info\").style.display=\"block\"; return false;'>[?]</a></span>";
		_e("<div id='ap_webworker_dashboard_username_info' style='display:none;'>This is the normal Email address you use to login at the Webworker Dashboard.</div>", "affiliate-power");
	}
	
	static public function addWebworkerDashboardApiKeyField() {
		$options = get_option('affiliate-power-options');
		if (!isset($options['webworker-dashboard-apikey'])) $options['webworker-dashboard-apikey'] = '';
		echo "<input type='text' id='affiliate-power-webworker-dashboard-apikey' name='affiliate-power-options[webworker-dashboard-apikey]' size='40' value='".$options['webworker-dashboard-apikey']."' /> ";
		echo "<span style='font-size:1em;'><a href='#' onclick='document.getElementById(\"ap_webworker_dashboard_apikey_info\").style.display=\"block\"; return false;'>[?]</a></span>";
		_e("<div id='ap_webworker_dashboard_apikey_info' style='display:none;'>The API password is a special access to the Webworker Dashboard API. Please do <strong>not</strong> enter your normal Webworker Dashboard password here. The API Key can be created in the login area, menu item Settings -> APIs. Select \"Webworker Dashboard\" in the select box and click \"Add API\".</div>", "affiliate-power");
	}
	
	
	
	//Network Settings
	static public function optionsNetworksText() {
		_e('<p>In order to download your sales, you have to enter your data of the affiliate networks you are using. <a href=" http://www.affiliatepowerplugin.com/#data-security" target="_blank">Whats about data security?</a></p>', 'affiliate-power');
	}
	
	
	//Adcell
	static public function addAdcellUsernameField() {
		$options = get_option('affiliate-power-options');
		if (!isset($options['adcell-username'])) $options['adcell-username'] = '';
		echo "<input type='text' id='affiliate-power-adcell-username' name='affiliate-power-options[adcell-username]' size='40' value='".$options['adcell-username']."' /> ";
		echo "<span style='font-size:1em;'><a href='#' onclick='document.getElementById(\"ap_adcell_username_info\").style.display=\"block\"; return false;'>[?]</a></span>";
		_e("<div id='ap_adcell_username_info' style='display:none;'>The Adcell Username is the number, you are using to login on the adcell page.</div>", "affiliate-power");
	}
	
	static public function addAdcellPasswordField() {
		$options = get_option('affiliate-power-options');
		if (!isset($options['adcell-password'])) $options['adcell-password'] = '';
		echo "<input type='text' id='affiliate-power-adcell-password' name='affiliate-power-options[adcell-password]' size='40' value='".$options['adcell-password']."' /> ";
		echo "<span style='font-size:1em;'><a href='#' onclick='document.getElementById(\"ap_adcell_password_info\").style.display=\"block\"; return false;'>[?]</a></span>";
		_e("<div id='ap_adcell_password_info' style='display:none;'>The Adcell API password is a special access to the Adcell API. Please do <strong>not</strong> enter your normal Adcell password here. The API password can be defined in the publisher area, menu item \"My Data\".</div>", "affiliate-power");
	}
	
	static public function addAdcellRefererFilterField() {
		$options = get_option('affiliate-power-options');
		if (!isset($options['adcell-referer-filter'])) $options['adcell-referer-filter'] = 0;
		$checked = $options['adcell-referer-filter'] ? ' checked' : '';
		echo "<input type='checkbox' id='affiliate-power-adcell-referer-filter' name='affiliate-power-options[adcell-referer-filter]' value='1' ".$checked." /> ";
		echo "<span style='font-size:1em;'><a href='#' onclick='document.getElementById(\"ap_adcell_referer_filter_info\").style.display=\"block\"; return false;'>[?]</a></span>";
		_e("<div id='ap_adcell_referer_filter_info' style='display:none;'>Only save sales, which came from this domain. This option makes only sense if you are using your Adcell account for several pages", "affiliate-power");
	}

	
	//Affili.net
	static public function addAffiliIdField() {
		$options = get_option('affiliate-power-options');
		if (!isset($options['affili-id'])) $options['affili-id'] = '';
		echo "<input type='text' id='affiliate-power-affili-id' name='affiliate-power-options[affili-id]' size='40' value='".$options['affili-id']."' /> ";
		echo "<span style='font-size:1em;'><a href='#' onclick='document.getElementById(\"ap_affili_id_info\").style.display=\"block\"; return false;'>[?]</a></span>";
		_e("<div id='ap_affili_id_info' style='display:none;'>The Affili.net UserId is the 6-digit number, you are using to login on affili.net.</div>", "affiliate-power");
	}
	
	static public function addAffiliPasswordField() {
		$options = get_option('affiliate-power-options');
		if (!isset($options['affili-password'])) $options['affili-password'] = '';
		echo "<input type='text' id='affiliate-power-affili-password' name='affiliate-power-options[affili-password]' size='40' value='".$options['affili-password']."' /> ";
		echo "<span style='font-size:1em;'><a href='#' onclick='document.getElementById(\"ap_affili_password_info\").style.display=\"block\"; return false;'>[?]</a></span>";
		_e("<div id='ap_affili_password_info' style='display:none;'>The affili.net PublisherWebservice Password is a special access the the affili.net API. Please do <strong>not</strong> enter your normal affili.net password here. You can find the PublisherWebservice Password in the publisher area of affili.net, menu item Account -> Technical settings -> Webservices. It may be necessary to request the password via the request button first.</div>", "affiliate-power");
	}
	
	
	
	//Belboon
	static public function addBelboonUsernameField() {
		$options = get_option('affiliate-power-options');
		if (!isset($options['belboon-username'])) $options['belboon-username'] = '';
		echo "<input type='text' id='affiliate-power-belboon-username' name='affiliate-power-options[belboon-username]' size='40' value='".$options['belboon-username']."' /> ";
		echo "<span style='font-size:1em;'><a href='#' onclick='document.getElementById(\"ap_belboon_username_info\").style.display=\"block\"; return false;'>[?]</a></span>";
		_e("<div id='ap_belboon_username_info' style='display:none;'>The Belboon username is the name you use to login on the Belboon page. This name is case-sensitive.</div>", "affiliate-power");
	}
	
	static public function addBelboonPasswordField() {
		$options = get_option('affiliate-power-options');
		if (!isset($options['belboon-password'])) $options['belboon-password'] = '';
		echo "<input type='text' id='affiliate-power-belboon-password' name='affiliate-power-options[belboon-password]' size='40' value='".$options['belboon-password']."' /> ";
		echo "<span style='font-size:1em;'><a href='#' onclick='document.getElementById(\"ap_belboon_password_info\").style.display=\"block\"; return false;'>[?]</a></span>";
		_e("<div id='ap_belboon_password_info' style='display:none;'>The belboon WebService Password is a special access the the belboon API. Please do <strong>not</strong> enter your normal belboon password here. You can find the WebService Password in the publisher area, left-hand side, menu item Tools & Services -> Webservices. It may be necessary to request the password first.</div>", "affiliate-power");
	}
	
	static public function addBelboonPlatformField() {
		$options = get_option('affiliate-power-options');
		if (!isset($options['belboon-platform'])) $options['belboon-platform'] = '';
		echo "<input type='text' id='affiliate-power-belboon-platform' name='affiliate-power-options[belboon-platform]' size='40' value='".$options['belboon-platform']."' /> ";
		echo "<span style='font-size:1em;'><a href='#' onclick='document.getElementById(\"ap_belboon_platform_info\").style.display=\"block\"; return false;'>[?]</a></span>";
		_e("<div id='ap_belboon_platform_info' style='display:none;'>If you are using your belboon account for several pages, you can enter the ad platform name for this page here. The plugin will only import sales from this ad platform. Please do <strong>not</strong> enter the ad platform id but the name. You can find the ad platform name in the publisher area, left-hand side, menu item Overview ad platforms. If you are using several ad platforms for this page you can separate the ad platform names with comma. If you are using your belboon account only for this page anyway, just leave the field empty. In this case, the plugin will download all sales.</div>", "affiliate-power");
	}
	
	
	//Commission Junction
	static public function addCjIdField() {
		$options = get_option('affiliate-power-options');
		if (!isset($options['cj-id'])) $options['cj-id'] = '';
		echo "<input type='text' id='affiliate-power-cj-id' name='affiliate-power-options[cj-id]' size='40' value='".$options['cj-id']."' /> ";
		echo "<span style='font-size:1em;'><a href='#' onclick='document.getElementById(\"ap_cj_id_info\").style.display=\"block\"; return false;'>[?]</a></span>";
		_e("<div id='ap_cj_id_info' style='display:none;'>The PID identifies your website at Commission Junction. You can find it in the publisher area of Commission Junction at the menu item Account -> Website Settings.</div>", "affiliate-power");
	}
	
	static public function addCjKeyField() {
		$options = get_option('affiliate-power-options');
		if (!isset($options['cj-key'])) $options['cj-key'] = '';
		echo "<input type='text' id='affiliate-power-cj-key' name='affiliate-power-options[cj-key]' size='40' value='".$options['cj-key']."' /> ";
		echo "<span style='font-size:1em;'><a href='#' onclick='document.getElementById(\"ap_cj_key_info\").style.display=\"block\"; return false;'>[?]</a></span>";
		_e("<div id='ap_cj_key_info' style='display:none;'>The Commission Junction Developer Key is a special access to the Commission Junction API. Please do <strong>not</strong> enter your normal password here. In order to get the key, you have to go to the <a href='http://www.cj.com/webservices' target='_blank'>Webservice-Site of Commission Junction</a>, click on \"Register for a Key\" and login with your normal account data.</div>", "affiliate-power");
	}
	
	
	
	//Superclix
	static public function addSuperclixUsernameField() {
		$options = get_option('affiliate-power-options');
		if (!isset($options['superclix-username'])) $options['superclix-username'] = '';
		echo "<input type='text' id='affiliate-power-superclix-username' name='affiliate-power-options[superclix-username]' size='40' value='".$options['superclix-username']."' /> ";
		echo "<span style='font-size:1em;'><a href='#' onclick='document.getElementById(\"ap_superclix_username_info\").style.display=\"block\"; return false;'>[?]</a></span>";
		_e("<div id='ap_superclix_username_info' style='display:none;'>The Superclix username is the name you use to login on the Superclix page. This name is case-sensitive.</div>", "affiliate-power");
	}
	
	static public function addSuperclixPasswordField() {
		$options = get_option('affiliate-power-options');
		if (!isset($options['superclix-password'])) $options['superclix-password'] = '';
		echo "<input type='text' id='affiliate-power-superclix-password' name='affiliate-power-options[superclix-password]' size='40' value='".$options['superclix-password']."' /> ";
		echo "<span style='font-size:1em;'><a href='#' onclick='document.getElementById(\"ap_superclix_password_info\").style.display=\"block\"; return false;'>[?]</a></span>";
		_e("<div id='ap_superclix_password_info' style='display:none;'>The Superclix export password is a special access to the Superclix API. Please do <strong>not</strong> enter your normal password here. The export password can be defined in the publisher area, menu item \"Account -> Change password \".</div>", "affiliate-power");
	}
	
	static public function addSuperclixRefererFilterField() {
		$options = get_option('affiliate-power-options');
		if (!isset($options['superclix-referer-filter'])) $options['superclix-referer-filter'] = 0;
		$checked = $options['superclix-referer-filter'] ? ' checked' : '';
		echo "<input type='checkbox' id='affiliate-power-superclix-referer-filter' name='affiliate-power-options[superclix-referer-filter]' value='1' ".$checked." /> ";
		echo "<span style='font-size:1em;'><a href='#' onclick='document.getElementById(\"ap_superclix_referer_filter_info\").style.display=\"block\"; return false;'>[?]</a></span>";
		_e("<div id='ap_superclix_referer_filter_info' style='display:none;'>Only save sales, which came from this domain. This option makes only sense if you are using your Superclix account for several pages", "affiliate-power");
	}
	
	
	//Tradedoubler
	static public function addTradedoublerKeyField() {
		$options = get_option('affiliate-power-options');
		if (!isset($options['tradedoubler-key'])) $options['tradedoubler-key'] = '';
		echo "<input type='text' id='affiliate-power-tradedoubler-key' name='affiliate-power-options[tradedoubler-key]' size='40' value='".$options['tradedoubler-key']."' /> ";
		echo "<span style='font-size:1em;'><a href='#' onclick='document.getElementById(\"ap_tradedoubler_key_info\").style.display=\"block\"; return false;'>[?]</a></span>";
		_e("<div id='ap_tradedoubler_key_info' style='display:none;'>The Tradedoubler Report Key is a special access to the Tradedoubler API. Please do <strong>not</strong> enter your normal Tradedoubler password here. In order to get the Tradedoubler Report Key, you have to wirte an email with your username to <a href='mailto:support.uk@tradedoubler.com'>support.uk@tradedoubler.com</a> (you can also change the country code if you are not in the UK) and ask for a Report Key. You will get an email with the key.</div>", "affiliate-power");
	}
	
	static public function addTradedoublerSitenameField() {
		$options = get_option('affiliate-power-options');
		if (!isset($options['tradedoubler-sitename'])) $options['tradedoubler-sitename'] = '';
		echo "<input type='text' id='affiliate-power-tradedoubler-sitename' name='affiliate-power-options[tradedoubler-sitename]' size='40' value='".$options['tradedoubler-sitename']."' /> ";
		echo "<span style='font-size:1em;'><a href='#' onclick='document.getElementById(\"ap_tradedoubler_sitename_info\").style.display=\"block\"; return false;'>[?]</a></span>";
		_e("<div id='ap_tradedoubler_sitename_info' style='display:none;'>If you are using your Tradedoubler account for several pages, enter the site name you defined in the Tradedoubler login area for this page. The plugin will only import sales from this site name. Please do <strong>not</strong> enter the site id but the name. You can find the site name in the publisher area, menu item Sites -> Sites. If you are using several sites for this page you can separate the site names with comma. If you are using your account only for this page anyway, just leave the field empty. In this case, the plugin will download all sales.</div>", "affiliate-power");
	}
	
	
	
	//Zanox
	static public function addZanoxConnectIdField() {
		$options = get_option('affiliate-power-options');
		if (!isset($options['zanox-connect-id'])) $options['zanox-connect-id'] = '';
		echo "<input type='text' id='affiliate-power-zanox-connect-id' name='affiliate-power-options[zanox-connect-id]' size='40' value='".$options['zanox-connect-id']."' /> ";
		echo "<span style='font-size:1em;'><a href='#' onclick='document.getElementById(\"ap_zanox_connect_info\").style.display=\"block\"; return false;'>[?]</a></span>";
		_e("<div id='ap_zanox_connect_info' style='display:none;'>The Zanox ConnectId is required to import sales from zanox. It's not that simple to get it, but together we will work it out:<ol><li>Go to <a href='http://apps.zanox.com' target='_blank'>http://apps.zanox.com</a>, click on \"Connect with Zanox\" in the top-right corner and login with your normal Zanox account data</li><li>Go to the tab \"Developers\" -> \"My own Applications\", accept the terms and click on \"Become a developer\"</li><li>Click on the new button \"New application\" and then \"zanox keys\"</li></ol></div>", "affiliate-power");
	}
	
	static public function addZanoxPublicKeyField() {
		$options = get_option('affiliate-power-options');
		if (!isset($options['zanox-public-key'])) $options['zanox-public-key'] = '';
		echo "<input type='text' id='affiliate-power-zanox-public-key' name='affiliate-power-options[zanox-public-key]' size='40' value='".$options['zanox-public-key']."' /> ";
		echo "<span style='font-size:1em;'><a href='#' onclick='document.getElementById(\"ap_zanox_public_info\").style.display=\"block\"; return false;'>[?]</a></span>";
		_e("<div id='ap_zanox_public_info' style='display:none;'>The Zanox PublicKey is required to import sales from zanox. It's not that simple to get it, but together we will work it out:<ol><li>Go to <a href='http://apps.zanox.com' target='_blank'>http://apps.zanox.com</a>, click on \"Connect with Zanox\" in the top-right corner and login with your normal Zanox account data</li><li>Go to the tab \"Developers\" -> \"My own Applications\", accept the terms and click on \"Become a developer\"</li><li>Click on the new button \"New application\" and then \"zanox keys\"</li></ol></div>", "affiliate-power");
	}
	
	static public function addZanoxSecretKeyField() {
		$options = get_option('affiliate-power-options');
		if (!isset($options['zanox-secret-key'])) $options['zanox-secret-key'] = '';
		echo "<input type='text' id='affiliate-power-zanox-secret-key' name='affiliate-power-options[zanox-secret-key]' size='40' value='".$options['zanox-secret-key']."' /> ";
		echo "<span style='font-size:1em;'><a href='#' onclick='document.getElementById(\"ap_zanox_secret_info\").style.display=\"block\"; return false;'>[?]</a></span>";
		_e("<div id='ap_zanox_secret_info' style='display:none;'>The Zanox SecretKey is required to import sales from zanox. It's not that simple to get it, but together we will work it out:<ol><li>Go to <a href='http://apps.zanox.com' target='_blank'>http://apps.zanox.com</a>, click on \"Connect with Zanox\" in the top-right corner and login with your normal Zanox account data</li><li>Go to the tab \"Developers\" -> \"My own Applications\", accept the terms and click on \"Become a developer\"</li><li>Click on the new button \"New application\" and then \"zanox keys\"</li></ol></div>", "affiliate-power");
	}
	
	static public function addZanoxAdspaceField() {
		$options = get_option('affiliate-power-options');
		if (!isset($options['zanox-adspace'])) $options['zanox-adspace'] = '';
		echo "<input type='text' id='affiliate-power-zanox-adspace' name='affiliate-power-options[zanox-adspace]' size='40' value='".$options['zanox-adspace']."' /> ";
		echo "<span style='font-size:1em;'><a href='#' onclick='document.getElementById(\"ap_zanox_adspace_info\").style.display=\"block\"; return false;'>[?]</a></span>";
		_e("<div id='ap_zanox_adspace_info' style='display:none;'>If you are using your Zanox account for several pages, enter the ad space name you defined in the  login area for this page. The plugin will only import sales from this ad space name. Please do <strong>not</strong> enter the ad space id but the name. You can find the ad space name in the publisher area, menu item Profile -> My Ad Spaces. If you are using several ad spaces for this page you can separate the ad space names with comma. If you are using your account only for this page anyway, just leave the field empty. In this case, the plugin will download all sales.</div>", "affiliate-power");
	}
	
	
	
	//Validation
	static public function optionsValidate($input) {
	
		//Main Settings
		$whitelist['add-sub-ids'] = $input['add-sub-ids'];
		if ($whitelist['add-sub-ids'] != 1) $whitelist['add-sub-ids'] = 0;
		
		//$whitelist['homepage-tracking'] = $input['homepage-tracking'];
		//if ($whitelist['homepage-tracking'] != 1) $whitelist['homepage-tracking'] = 0;
		
		$whitelist['send-mail-transactions'] = $input['send-mail-transactions'];
		if ($whitelist['send-mail-transactions'] != 1) $whitelist['send-mail-transactions'] = 0;

		if (isset($input['licence-key']) && ctype_alnum($input['licence-key'])) {
			$check_result = Affiliate_Power_Apis::checkLicenceKey($input['licence-key']);
			if ($check_result == false || $check_result == 'database_error' || $check_result == 'database_charset_error') add_settings_error('affiliate-power-options', 'affiliate-power-error-licence-key', __('The licence key could not be checked. Please try again later and <a href="http://www.affiliatepowerplugin.com/contact/" target="_blank">let me know</a> if it is still not working.', 'affiliate-power') );
			elseif ($check_result == 'outdated_key') add_settings_error('affiliate-power-options', 'affiliate-power-error-licence-key', __('The licence key is outdated. Please renew your licence key.', 'affiliate-power') );
			elseif ($check_result == 'invalid_key_format' || $check_result == 'invalid_key') add_settings_error('affiliate-power-options', 'affiliate-power-error-licence-key', __('The licence key is invalid. Please check again. If you are sure you entered the right key, <a href="http://www.affiliatepowerplugin.com/contact/" target="_blank">let me know</a>, and I will check it out.', 'affiliate-power') );
			elseif ($check_result == 'ok') $whitelist['licence-key'] = $input['licence-key'];
		}
		elseif (!empty($input['licence-key'])) add_settings_error('affiliate-power-options', 'affiliate-power-error-licence-key', __('Invalid licence key. The key should only contain numbers and letters.', 'affiliate-power'));
		
		//if (is_numeric($input['download-method'])) $whitelist['download-method'] = (int)$input['download-method'];
		
		
		//Webworker Dashboard
		if (is_email($input['webworker-dashboard-username'])) $whitelist['webworker-dashboard-username'] = $input['webworker-dashboard-username'];
		elseif (!empty($input['webworker-dashboard-username'])) add_settings_error('affiliate-power-options', 'affiliate-power-error-webworker-dashboard-username', __('Invalid Webworker Dashboard email address. This is not a valid email address.', 'affiliate-power'), 'error');
		
		if (ctype_alnum($input['webworker-dashboard-apikey']) && strlen($input['webworker-dashboard-apikey']) == 32) $whitelist['webworker-dashboard-apikey'] = $input['webworker-dashboard-apikey'];
		elseif (!empty($input['webworker-dashboard-apikey'])) add_settings_error('affiliate-power-options', 'affiliate-power-error-webworker-dashboard-apikey', __('Invalid Webworker Dashboard Apikey. The Apikey should be 32 characters long and only contain numbers and letters. Please do not enter your normal Webworker Dashboard password, but yourApikey', 'affiliate-power'), 'error');
		
		if (isset($whitelist['webworker-dashboard-username']) && isset($whitelist['webworker-dashboard-apikey'])) {
			include_once('apis/webworker-dashboard.php');
			if (!Affiliate_Power_Api_Webworker_Dashboard::checkLogin($whitelist['webworker-dashboard-username'], $whitelist['webworker-dashboard-apikey'])){
				add_settings_error('affiliate-power-options', 'affiliate-power-error-webworker-dashboard-login', __('Webworker Dashboard test login failed. Please check your data.', 'affiliate-power'), 'error');
			}
		}
		
		
		
		//Adcell
		if (is_numeric($input['adcell-username'])) $whitelist['adcell-username'] = $input['adcell-username'];
		elseif (!empty($input['adcell-username'])) add_settings_error('affiliate-power-options', 'affiliate-power-error-adcell-username', __('Invalid Adcell username. The username should only contain numbers and letters.', 'affiliate-power'), 'error');
		
		if (!empty($input['adcell-password'])) $whitelist['adcell-password'] = esc_html($input['adcell-password']);
		
		if ($input['adcell-referer-filter'] != 1) $input['adcell-referer-filter'] = 0;
		$whitelist['adcell-referer-filter'] = $input['adcell-referer-filter'];
		
		if (isset($whitelist['adcell-username']) && isset($whitelist['adcell-password'])) {
			include_once('apis/adcell.php');
			if (!Affiliate_Power_Api_Adcell::checkLogin($whitelist['adcell-username'], $whitelist['adcell-password'])){
				add_settings_error('affiliate-power-options', 'affiliate-power-error-adcell-login', __('Adcell test login failed. Please check your data.', 'affiliate-power'), 'error');
			}
		}
		
		
		
		//Affili.net
		if (is_numeric($input['affili-id'])) $whitelist['affili-id'] = $input['affili-id'];
		elseif (!empty($input['affili-id'])) add_settings_error('affiliate-power-options', 'affiliate-power-error-affili-id', __('Invalid Affili.net Id. The Id should only contain numbers.', 'affiliate-power'), 'error');
		
		if (ctype_alnum($input['affili-password']) && strlen($input['affili-password']) == 20) $whitelist['affili-password'] = $input['affili-password'];
		elseif (!empty($input['affili-password'])) add_settings_error('affiliate-power-options', 'affiliate-power-error-affili-password', __('Invalid Affili.net PublisherWebservice password. The password should be 20 characters long and only contain numbers and letters. Please do not enter your normal Affili.net password, but your PublisherWebservice password', 'affiliate-power'), 'error');
		
		if (isset($whitelist['affili-id']) && isset($whitelist['affili-password'])) {
			include_once('apis/affili.php');	
			if (!extension_loaded('soap')) {
				add_settings_error('affiliate-power-options', 'affiliate-power-error-soap', __('In order to download the Affili.net sales the PHP-module SOAP is required. This seems to be not activated on your server. Please activate the module. If you don\'t know how to do this, your webhoster can assist you.', 'affiliate-power'), 'error');
			}
			elseif (!Affiliate_Power_Api_Affili::checkLogin($whitelist['affili-id'], $whitelist['affili-password'])){
				add_settings_error('affiliate-power-options', 'affiliate-power-error-affili-login', __('Affili.net test login failed. Please check your data.', 'affiliate-power'), 'error');
			}
		}
		
		
		
		//Belboon
		if (!empty($input['belboon-username'])) $whitelist['belboon-username'] = esc_html($input['belboon-username']);
		
		if (ctype_alnum($input['belboon-password']) && strlen($input['belboon-password']) == 20) $whitelist['belboon-password'] = $input['belboon-password'];
		elseif (!empty($input['belboon-password'])) add_settings_error('affiliate-power-options', 'affiliate-power-error-belboon-password', __('Invalid Belboon WebService password. The password should be 20 characters long and only contain numbers and letters. Please do not enter your normal Belboon password, but your WebService password.', 'affiliate-power'), 'error');
		
		if (isset($whitelist['belboon-username']) && isset($whitelist['belboon-password'])) {
			include_once('apis/belboon.php');
			if (!extension_loaded('soap')) {
				add_settings_error('affiliate-power-options', 'affiliate-power-error-soap', __('In order to download the Belboon sales the PHP-module SOAP is required. This seems to be not activated on your server. Please activate the module. If you don\'t know how to do this, your webhoster can assist you.', 'affiliate-power'), 'error');
			}
			elseif (!Affiliate_Power_Api_Belboon::checkLogin($whitelist['belboon-username'], $whitelist['belboon-password'])){
				add_settings_error('affiliate-power-options', 'affiliate-power-error-belboon-login', __('Belboon test login failed. Please check your data.', 'affiliate-power'), 'error');
			}
		}
		
		if (!empty($input['belboon-platform'])) $whitelist['belboon-platform'] = esc_html($input['belboon-platform']);
		
		
		//Commission Junction
		if (is_numeric($input['cj-id'])) $whitelist['cj-id'] = $input['cj-id'];
		elseif (!empty($input['cj-id'])) add_settings_error('affiliate-power-options', 'affiliate-power-error-cj-id', __('Invalid Commission Junction PID. The  PID should only contain numbers.', 'affiliate-power'), 'error');
		
		if (strlen($input['cj-key']) > 20) $whitelist['cj-key'] = esc_html($input['cj-key']);
		elseif (!empty($input['cj-key'])) add_settings_error('affiliate-power-options', 'affiliate-power-error-cj-key',  __('Invalid cj developer key. The key should be longer than 20 characters Please do not enter your normal cj password, but your developer key.', 'affiliate-power'), 'error');
		
		if (isset($whitelist['cj-id']) && isset($whitelist['cj-key'])) {
			include_once('apis/cj.php');	
			if (!class_exists('DOMDocument')) {
				add_settings_error('affiliate-power-options', 'affiliate-power-error-dom', __('In order to download the Commission Junction Sales, the PHP-Class DomDocument is required. This class semms to be not included on your server. Please include the class. If you don\'t know how to do this, your webhoster can assist you.', 'affiliate-power'), 'error');
			}
			elseif (!Affiliate_Power_Api_Cj::checkLogin($whitelist['cj-id'], $whitelist['cj-key'])){
				add_settings_error('affiliate-power-options', 'affiliate-power-error-cj-login', __('Commission Junction test login failed. Please check your data.', 'affiliate-power'), 'error');
			}
		}
		
		
		
		//Superclix
		if (!empty($input['superclix-username'])) $whitelist['superclix-username'] = esc_html($input['superclix-username']);
		
		if (!empty($input['superclix-password'])) $whitelist['superclix-password'] = esc_html($input['superclix-password']);
		
		if ($input['superclix-referer-filter'] != 1) $input['superclix-referer-filter'] = 0;
		$whitelist['superclix-referer-filter'] = $input['superclix-referer-filter'];
		
		if (isset($whitelist['superclix-username']) && isset($whitelist['superclix-password'])) {
			include_once('apis/superclix.php');
			if (!Affiliate_Power_Api_Superclix::checkLogin($whitelist['superclix-username'], $whitelist['superclix-password'])){
				add_settings_error('affiliate-power-options', 'affiliate-power-error-superclix-login', __('Superclix test login failed. Please check your data. Don\'t forget to specify your export password, not your normal password.', 'affiliate-power'), 'error');
			}
		}
		
		
		//Tradedoubler
		if (ctype_alnum($input['tradedoubler-key']) && strlen($input['tradedoubler-key']) >= 32) $whitelist['tradedoubler-key'] = $input['tradedoubler-key'];
		elseif (!empty($input['tradedoubler-key'])) add_settings_error('affiliate-power-options', 'affiliate-power-error-tradedoubler-key', __('Invalid tradedoubler report key. The key should be at least 32 characters long. Please do not enter your normal password, but your report key.', 'affiliate-power'), 'error');
		
		if (isset($whitelist['tradedoubler-key'])) {
			include_once('apis/tradedoubler.php');
			if (!Affiliate_Power_Api_Tradedoubler::checkLogin($whitelist['tradedoubler-key'])){
				add_settings_error('affiliate-power-options', 'affiliate-power-error-tradedoubler-login', __('Tradedoubler test login failed. Please check your data.', 'affiliate-power'), 'error');
			}
		}
		
		if (!empty($input['tradedoubler-sitename'])) $whitelist['tradedoubler-sitename'] = esc_html($input['tradedoubler-sitename']);
		
			
		
		//Zanox
		if (ctype_alnum($input['zanox-connect-id']) && strlen($input['zanox-connect-id']) == 20) $whitelist['zanox-connect-id'] = $input['zanox-connect-id'];
		elseif (!empty($input['zanox-connect-id'])) add_settings_error('affiliate-power-options', 'affiliate-power-error-zanox-connect-id', __('Invalid Zanox Connect Id. The Id should contain 20 characters of numbers and letters only. Please do not specify your normal Zanox account, but your Connect Id.', 'affiliate-power'), 'error');
		
		if (ctype_alnum($input['zanox-public-key']) && strlen($input['zanox-public-key']) == 20) $whitelist['zanox-public-key'] = $input['zanox-public-key'];
		elseif (!empty($input['zanox-public-key'])) add_settings_error('affiliate-power-options', 'affiliate-power-error-zanox-public-key', __('Invalid Zanox Public Key. The key should contain 20 characters of numbers and letters only. Please do not specify your normal Zanox account or password, but your Public Key.', 'affiliate-power'), 'error');
		
		if (strlen($input['zanox-secret-key']) >= 20) $whitelist['zanox-secret-key'] = $input['zanox-secret-key'];
		elseif (!empty($input['zanox-secret-key'])) add_settings_error('affiliate-power-options', 'affiliate-power-error-zanox-secret-key',  __('Invalid Zanox Secret Key. The key should contain at least 20 characters of numbers and letters only. Please do not specify your normal Zanox password, but your Secret Key.', 'affiliate-power'), 'error');
		
		if (isset($whitelist['zanox-connect-id']) && isset($whitelist['zanox-public-key']) && isset($whitelist['zanox-secret-key'])) {
			include_once('apis/zanox.php');
			if (!extension_loaded('soap')) {
				add_settings_error('affiliate-power-options', 'affiliate-power-error-soap', __('In order to download the Zanox sales the PHP-module SOAP is required. This seems to be not activated on your server. Please activate the module. If you don\'t know how to do this, your webhoster can assist you.', 'affiliate-power'), 'error');
			}
			elseif (!Affiliate_Power_Api_Zanox::checkLogin($whitelist['zanox-connect-id'], $whitelist['zanox-public-key'], $whitelist['zanox-secret-key'])){
				add_settings_error('affiliate-power-options', 'affiliate-power-error-zanox-login', __('Zanox test login failed. Please check your data.', 'affiliate-power'), 'error');
			}
		}
		
		if (!empty($input['zanox-adspace'])) $whitelist['zanox-adspace'] = esc_html($input['zanox-adspace']);
		

		
		//settings_errors('affiliate-power-options');
		return $whitelist;
	}

}




?>