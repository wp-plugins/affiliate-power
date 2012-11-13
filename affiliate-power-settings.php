<?php

add_action('admin_init', array('Affiliate_Power_Settings', 'addSettings'));


class Affiliate_Power_Settings {


	static public function addSettings() {
		register_setting( 'affiliate-power-options', 'affiliate-power-options', array('Affiliate_Power_Settings', 'optionsValidate') );
		
		add_settings_section('affiliate-power-main', 'Grundeinstellungen', array('Affiliate_Power_Settings', 'optionsMainText'), 'affiliate-power-options');
		add_settings_field('affiliate-power-add-sub-ids', 'Artikel Tracking aktiv', array('Affiliate_Power_Settings', 'addSubIdsField'), 'affiliate-power-options', 'affiliate-power-main');
		add_settings_field('affiliate-power-prli-homepage', 'Pretty Link Startseiten Integration', array('Affiliate_Power_Settings', 'prliHomepageField'), 'affiliate-power-options', 'affiliate-power-main');
		
		//add_settings_field('affiliate-power-download-method', 'Methode Sale/Lead Download', array('Affiliate_Power_Settings', 'downloadMethod'), 'affiliate-power-options', 'affiliate-power-main');
		
		add_settings_section('affiliate-power-networks', 'Affiliate-Netzwerke', array('Affiliate_Power_Settings', 'optionsNetworksText'), 'affiliate-power-options');
		
		//affili.net
		add_settings_section('affiliate-power-networks-affili', 'Affili.net', array('Affiliate_Power_Settings', 'dummyFunction'), 'affiliate-power-options');
		add_settings_field('affiliate-power-affili-id', 'Affili.net UserId', array('Affiliate_Power_Settings', 'addAffiliIdField'), 'affiliate-power-options', 'affiliate-power-networks-affili');
		add_settings_field('affiliate-power-affili-password', 'Affili.net PublisherWebservice Passwort:', array('Affiliate_Power_Settings', 'addAffiliPasswordField'), 'affiliate-power-options', 'affiliate-power-networks-affili');
		
		//belboon
		add_settings_section('affiliate-power-networks-belboon', 'Belboon', array('Affiliate_Power_Settings', 'dummyFunction'), 'affiliate-power-options');
		add_settings_field('affiliate-power-belboon-username', 'Belboon Benutzername', array('Affiliate_Power_Settings', 'addBelboonUsernameField'), 'affiliate-power-options', 'affiliate-power-networks-belboon');
		add_settings_field('affiliate-power-belboon-passwords', 'Belboon WebService Passwort', array('Affiliate_Power_Settings', 'addBelboonPasswordField'), 'affiliate-power-options', 'affiliate-power-networks-belboon');
		add_settings_field('affiliate-power-belboon-platform', 'Belboon Werbeplattform', array('Affiliate_Power_Settings', 'addBelboonPlatformField'), 'affiliate-power-options', 'affiliate-power-networks-belboon');
		
		//superclix
		add_settings_section('affiliate-power-networks-superclix', 'Superclix', array('Affiliate_Power_Settings', 'dummyFunction'), 'affiliate-power-options');
		add_settings_field('affiliate-power-superclix-usename', 'Superclix Username', array('Affiliate_Power_Settings', 'addSuperclixUSernameField'), 'affiliate-power-options', 'affiliate-power-networks-superclix');
		add_settings_field('affiliate-power-superclix-password', 'Superclix Export Passwort', array('Affiliate_Power_Settings', 'addSuperclixPasswordField'), 'affiliate-power-options', 'affiliate-power-networks-superclix');
		
		//tradedoubler
		add_settings_section('affiliate-power-networks-tradedoubler', 'Tradedoubler', array('Affiliate_Power_Settings', 'dummyFunction'), 'affiliate-power-options');
		add_settings_field('affiliate-power-tradedoubler-key', 'Tradedoubler Report Key', array('Affiliate_Power_Settings', 'addTradedoublerKeyField'), 'affiliate-power-options', 'affiliate-power-networks-tradedoubler');
		add_settings_field('affiliate-power-tradedoubler-sitename', 'Tradedoubler Seitenname', array('Affiliate_Power_Settings', 'addTradedoublerSitenameField'), 'affiliate-power-options', 'affiliate-power-networks-tradedoubler');
		
		//zanox
		add_settings_section('affiliate-power-networks-zanox', 'Zanox', array('Affiliate_Power_Settings', 'dummyFunction'), 'affiliate-power-options');
		add_settings_field('affiliate-power-zanox-connect-id', 'Zanox ConnectId', array('Affiliate_Power_Settings', 'addZanoxConnectIdField'), 'affiliate-power-options', 'affiliate-power-networks-zanox');
		add_settings_field('affiliate-power-zanox-public-key', 'Zanox Public Key', array('Affiliate_Power_Settings', 'addZanoxPublicKeyField'), 'affiliate-power-options', 'affiliate-power-networks-zanox');
		add_settings_field('affiliate-power-zanox-secret-key', 'Zanox Secret Key', array('Affiliate_Power_Settings', 'addZanoxSecretKeyField'), 'affiliate-power-options', 'affiliate-power-networks-zanox');
		add_settings_field('affiliate-power-zanox-adspace', 'Zanox Werbefläche', array('Affiliate_Power_Settings', 'addZanoxAdspaceField'), 'affiliate-power-options', 'affiliate-power-networks-zanox');
		
		
		
		
	}
	
	
	static public function dummyFunction() {
	}
	
	
	static public function optionsPage() {
		include_once 'options-head.php'; //we need this to show error messages
		?>
		<div class="wrap">
		<h2>Affiliate Power</h2>
		<p>Herzlich Willkommen bei der Beta-Version von Affiliate Power. Das Plugin befindet sich noch in der Entwicklung. Sollte du Probleme und Vorschläge für neue Features haben, freue ich mich über einen Kommentar auf der <a href="http://www.j-breuer.de/wordpress-plugins/affiliate-power/" target="_blank">Plugin Seite</a>. Sollte dir das Plugin gefallen und du einen Blog haben, wo es thematisch passt, würde ich mich über eine Vorstellung des Plugins sehr freuen.</p>
		
		<p>Auf dieser Seite kannst du die Einstellungen von Affiliate Power bearbeiten. Bitte habe etwas Geduld beim Speichern der Daten. Das Plugin führt einen Testlogin bei den Netzwerken durch.</p>
		
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
	
	/*
	static public function downloadMethod() {
		$options = get_option('affiliate-power-options');
		$download_methods = array(1 => 'Cronjob (empfohlen)', 2 => 'Auto Cron durch Admin', 3 => 'nur manuell');
		echo "<select id='affiliate-power-download-method' name='affiliate-power-options[download-method]'>";
		foreach ($download_methods as $value => $text) {
			echo '<option value="'.$value.'"';
			if ($options['download-method'] == $value) echo ' selected';
			echo '>'.$text.'</option>';
		}
		echo '</select>';
	}
	*/
	
	static public function addSubIdsField() {
		$options = get_option('affiliate-power-options');
		$checked = $options['add-sub-ids'] ? ' checked' : '';
		echo "<input type='checkbox' id='affiliate-power-add-sub-ids' name='affiliate-power-options[add-sub-ids]' value='1' ".$checked." /> ";
		echo "<span style='font-size:1em;'><a href='#' onclick='document.getElementById(\"ap_sub_id_info\").style.display=\"block\"'>[?]</a></span>";
		echo "<div id='ap_sub_id_info' style='display:none;'>Mit dem Artikel Tracking kannst du herausfinden, welcher Artikel zu welchen Einnahmen geführt hat. Einnahmen, die du vor der Installation des Plugins erzielt hast, können nicht zugeordnet werden. Du solltest diese Option nicht aktivieren, falls du ein eigenes SubId-Tracking durchführst.</div>";
	}
	
	
	static public function prliHomepageField() {
		$options = get_option('affiliate-power-options');
		$checked = $options['prli-homepage'] ? ' checked' : '';
		echo "<input type='checkbox' id='affiliate-power-prli-homepage' name='affiliate-power-options[prli-homepage]' value='1' ".$checked." /> ";
		echo "<span style='font-size:1em;'><a href='#' onclick='document.getElementById(\"ap_prli_homepage_info\").style.display=\"block\"'>[?]</a></span>";
		echo "<div id='ap_prli_homepage_info' style='display:none;'>Diese Option solltest du aktivieren, wenn du das Plugin Pretty Link zur Verschleierung deiner Affiliate-Links benutzt und Affiliate-Links auf der Startseite sichtbar sind.</div>";
	}
	
	
	//Network Settings
	static public function optionsNetworksText() {
		echo '<p>Damit das Tracking funktioniert, musst du hier deine Daten bei den Affiliate-Netzwerken hinterlegen, die du benutzt. <a href=" http://www.j-breuer.de/wordpress-plugins/affiliate-power/#daten-sicherheit" target="_blank">Sind meine Daten sicher?</a></p>';
	}
	
	
	//Affili.net
	static public function addAffiliIdField() {
		$options = get_option('affiliate-power-options');
		echo "<input type='text' id='affiliate-power-affili-id' name='affiliate-power-options[affili-id]' size='40' value='".$options['affili-id']."' /> ";
		echo "<span style='font-size:1em;'><a href='#' onclick='document.getElementById(\"ap_affili_id_info\").style.display=\"block\"'>[?]</a></span>";
		echo "<div id='ap_affili_id_info' style='display:none;'>Die Affili.net UserId ist die 6-stellige Nummer, mit der du dich auch auf affili.net einloggst.</div>";
	}
	
	static public function addAffiliPasswordField() {
		$options = get_option('affiliate-power-options');
		echo "<input type='text' id='affiliate-power-affili-password' name='affiliate-power-options[affili-password]' size='40' value='".$options['affili-password']."' /> ";
		echo "<span style='font-size:1em;'><a href='#' onclick='document.getElementById(\"ap_affili_password_info\").style.display=\"block\"'>[?]</a></span>";
		echo "<div id='ap_affili_password_info' style='display:none;'>Das Affili.net PublisherWebservice Passwort ist ein spezieller Zugang zur API von Affili.net. Bitte gib hier <strong>nicht</strong> das normale affili.net Passwort an. Du findest das PublisherWebservice Passwort im Login-Bereich von affili.net unter Konto -> Technische Einstellungen -> Webservices. Eventuell musst du das Passwort erst noch anfordern.</div>";
	}
	
	
	
	//Belboon
	static public function addBelboonUsernameField() {
		$options = get_option('affiliate-power-options');
		echo "<input type='text' id='affiliate-power-belboon-username' name='affiliate-power-options[belboon-username]' size='40' value='".$options['belboon-username']."' /> ";
		echo "<span style='font-size:1em;'><a href='#' onclick='document.getElementById(\"ap_belboon_username_info\").style.display=\"block\"'>[?]</a></span>";
		echo "<div id='ap_belboon_username_info' style='display:none;'>Der Belboon Benutzername ist der Name, den du auch beim normalen Login eingibst. Bitte achte hier unbedingt auf Groß- und Kleinschreibung.</div>";
	}
	
	static public function addBelboonPasswordField() {
		$options = get_option('affiliate-power-options');
		echo "<input type='text' id='affiliate-power-belboon-password' name='affiliate-power-options[belboon-password]' size='40' value='".$options['belboon-password']."' /> ";
		echo "<span style='font-size:1em;'><a href='#' onclick='document.getElementById(\"ap_belboon_password_info\").style.display=\"block\"'>[?]</a></span>";
		echo "<div id='ap_belboon_password_info' style='display:none;'>Das Belboon WebService Passwort ist ein spezieller Zugang zur API von Belboon. Bitte gib hier <strong>nicht</strong> das normale Belboon Passwort an. Du findest das WebService Passwort im Login-Bereich auf der linken Seite unter Tools & Services -> Webservices. Eventuell musst du das Passwort erst noch anfordern.</div>";
	}
	
	static public function addBelboonPlatformField() {
		$options = get_option('affiliate-power-options');
		echo "<input type='text' id='affiliate-power-belboon-platform' name='affiliate-power-options[belboon-platform]' size='40' value='".$options['belboon-platform']."' /> ";
		echo "<span style='font-size:1em;'><a href='#' onclick='document.getElementById(\"ap_belboon_platform_info\").style.display=\"block\"'>[?]</a></span>";
		echo "<div id='ap_belboon_platform_info' style='display:none;'>Wenn du deinen Belboon Account für mehrere Seiten benutzt, kannst du hier die bei Belboon eingetragene Werbeplattform für diese Seite eintragen. Das Plugin wird dann nur die Sales importieren, die zu dieser Werbeplattform gehören. Wenn du den Account sowieso nur für diese Seite nutzt, kannst du das Feld einfach leer lassen. In diesem Fall importiert das Plugin alle Sales.</div>";
	}
	
	
	
	//Superclix
	static public function addSuperclixUsernameField() {
		$options = get_option('affiliate-power-options');
		echo "<input type='text' id='affiliate-power-superclix-username' name='affiliate-power-options[superclix-username]' size='40' value='".$options['superclix-username']."' /> ";
		echo "<span style='font-size:1em;'><a href='#' onclick='document.getElementById(\"ap_superclix_username_info\").style.display=\"block\"'>[?]</a></span>";
		echo "<div id='ap_superclix_username_info' style='display:none;'>Der Superclix Username ist der Name, den du auch beim normalen Login eingibst. Bitte achte hier unbedingt auf Groß- und Kleinschreibung.</div>";
	}
	
	static public function addSuperclixPasswordField() {
		$options = get_option('affiliate-power-options');
		echo "<input type='text' id='affiliate-power-superclix-password' name='affiliate-power-options[superclix-password]' size='40' value='".$options['superclix-password']."' /> ";
		echo "<span style='font-size:1em;'><a href='#' onclick='document.getElementById(\"ap_superclix_password_info\").style.display=\"block\"'>[?]</a></span>";
		echo "<div id='ap_superclix_password_info' style='display:none;'>Das Superclix Export Passwort ist ein spezieller Zugang zur API von Superclix. Bitte gib hier <strong>nicht</strong> das normale Superclix Passwort an. Du musst das Export Passwort im Login-Bereich von Superclix unter \"Konto -> Passwort ändern \" festlegen und hier eintragen.</div>";
	}
	
	
	//Tradedoubler
	static public function addTradedoublerKeyField() {
		$options = get_option('affiliate-power-options');
		echo "<input type='text' id='affiliate-power-tradedoubler-key' name='affiliate-power-options[tradedoubler-key]' size='40' value='".$options['tradedoubler-key']."' /> ";
		echo "<span style='font-size:1em;'><a href='#' onclick='document.getElementById(\"ap_tradedoubler_key_info\").style.display=\"block\"'>[?]</a></span>";
		echo "<div id='ap_tradedoubler_key_info' style='display:none;'>Der Tradedoubler Report Key ist ein spezieller Zugang zur API von Tradedoubler. Bitte gib hier <strong>nicht</strong> das normale Tradedoubler Passwort an. Um den Tradedoubler Key zu bekommen, musst du eine E-Mail mit deinem Tradedoubler Benutzernamen an <a href='mailto:support.de@tradedoubler.com'>support.de@tradedoubler.com</a> schicken mit der Bitte für die Report API freigeschaltet zu werden. Du bekommst dann eine E-Mail mit dem Key.</div>";
	}
	
	static public function addTradedoublerSitenameField() {
		$options = get_option('affiliate-power-options');
		echo "<input type='text' id='affiliate-power-tradedoubler-sitename' name='affiliate-power-options[tradedoubler-sitename]' size='40' value='".$options['tradedoubler-sitename']."' /> ";
		echo "<span style='font-size:1em;'><a href='#' onclick='document.getElementById(\"ap_tradedoubler_sitename_info\").style.display=\"block\"'>[?]</a></span>";
		echo "<div id='ap_tradedoubler_sitename_info' style='display:none;'>Wenn du deinen Tradedoubler Account für mehrere Seiten benutzt, kannst du hier den bei Tradedoubler eingetragenen Seitennamen für diese Seite eintragen. Das Plugin wird dann nur die Sales importieren, die zu diesem Seitennamen gehören. Wenn du den Account sowieso nur für diese Seite nutzt, kannst du das Feld einfach leer lassen. In diesem Fall importiert das Plugin alle Sales.</div>";
	}
	
	
	
	//Zanox
	static public function addZanoxConnectIdField() {
		$options = get_option('affiliate-power-options');
		echo "<input type='text' id='affiliate-power-zanox-connect-id' name='affiliate-power-options[zanox-connect-id]' size='40' value='".$options['zanox-connect-id']."' /> ";
		echo "<span style='font-size:1em;'><a href='#' onclick='document.getElementById(\"ap_zanox_connect_info\").style.display=\"block\"'>[?]</a></span>";
		echo "<div id='ap_zanox_connect_info' style='display:none;'>Die Zanox ConnectId wird für den Zugriff auf die Zanox Transaktionen benötigt. Die Schritte, um diese zu bekommen sind bei Zanox etwas umständlich aber gemeinsam bekommen wir das schon hin:
		<ol>
		<li>Auf <a href='http://apps.zanox.com'>http://apps.zanox.com</a> oben rechts auf \"Connect with Zanox\" klicken und dich mit deinen normalen Zanox-Daten einloggen</li>
		<li>Im Tab \"Developers\" unter \"My own Applications\" den Bedingungen zustimmen und auf \"Become a developer\" klicken</li>
		<li>Auf den neu erschienen Button \"New application\" und dann auf \"zanox keys\" klicken</li>
		</ol>
		</div>";
	}
	
	static public function addZanoxPublicKeyField() {
		$options = get_option('affiliate-power-options');
		echo "<input type='text' id='affiliate-power-zanox-public-key' name='affiliate-power-options[zanox-public-key]' size='40' value='".$options['zanox-public-key']."' /> ";
		echo "<span style='font-size:1em;'><a href='#' onclick='document.getElementById(\"ap_zanox_public_info\").style.display=\"block\"'>[?]</a></span>";
		echo "<div id='ap_zanox_public_info' style='display:none;'>Der Zanox PublicKey wird für den Zugriff auf die Zanox Transaktionen benötigt. Die Schritte, um diesen zu bekommen sind bei Zanox etwas umständlich aber gemeinsam bekommen wir das schon hin:
		<ol>
		<li>Auf <a href='http://apps.zanox.com'>http://apps.zanox.com</a> oben rechts auf \"Connect with Zanox\" klicken und dich mit deinen normalen Zanox-Daten einloggen</li>
		<li>Im Tab \"Developers\" unter \"My own Applications\" den Bedingungen zustimmen und auf \"Become a developer\" klicken</li>
		<li>Auf den neu erschienen Button \"New application\" und dann auf \"zanox keys\" klicken</li>
		</ol>
		</div>";
	}
	
	static public function addZanoxSecretKeyField() {
		$options = get_option('affiliate-power-options');
		echo "<input type='text' id='affiliate-power-zanox-secret-key' name='affiliate-power-options[zanox-secret-key]' size='40' value='".$options['zanox-secret-key']."' /> ";
		echo "<span style='font-size:1em;'><a href='#' onclick='document.getElementById(\"ap_zanox_secret_info\").style.display=\"block\"'>[?]</a></span>";
		echo "<div id='ap_zanox_secret_info' style='display:none;'>Der Zanox SecretKey wird für den Zugriff auf die Zanox Transaktionen benötigt. Die Schritte, um diesen zu bekommen sind bei Zanox etwas umständlich aber gemeinsam bekommen wir das schon hin:
		<ol>
		<li>Auf <a href='http://apps.zanox.com'>http://apps.zanox.com</a> oben rechts auf \"Connect with Zanox\" klicken und dich mit deinen normalen Zanox-Daten einloggen</li>
		<li>Im Tab \"Developers\" unter \"My own Applications\" den Bedingungen zustimmen und auf \"Become a developer\" klicken</li>
		<li>Auf den neu erschienen Button \"New application\" und dann auf \"zanox keys\" klicken</li>
		</ol>
		</div>";
	}
	
	static public function addZanoxAdspaceField() {
		$options = get_option('affiliate-power-options');
		echo "<input type='text' id='affiliate-power-zanox-adspace' name='affiliate-power-options[zanox-adspace]' size='40' value='".$options['zanox-adspace']."' /> ";
		echo "<span style='font-size:1em;'><a href='#' onclick='document.getElementById(\"ap_zanox_adspace_info\").style.display=\"block\"'>[?]</a></span>";
		echo "<div id='ap_zanox_adspace_info' style='display:none;'>Wenn du deinen Zanox Account für mehrere Seiten benutzt, kannst du hier die bei Zanox eingetragene Werbefläche für diese Seite eintragen. Das Plugin wird dann nur die Sales importieren, die zu dieser Werbefläche gehören. Wenn du den Account sowieso nur für diese Seite nutzt, kannst du das Feld einfach leer lassen. In diesem Fall importiert das Plugin alle Sales.</div>";
	}
	
	
	
	//Validation
	static public function optionsValidate($input) {
	
		//Main Settings
		$whitelist['add-sub-ids'] = $input['add-sub-ids'];
		if ($whitelist['add-sub-ids'] != 1) $whitelist['add-sub-ids'] = 0;
		
		$whitelist['prli-homepage'] = $input['prli-homepage'];
		if ($whitelist['prli-homepage'] != 1) $whitelist['prli-homepage'] = 0;
		
		//if (is_numeric($input['download-method'])) $whitelist['download-method'] = (int)$input['download-method'];
		
		
		
		//Affili.net
		if (is_numeric($input['affili-id'])) $whitelist['affili-id'] = $input['affili-id'];
		elseif (!empty($input['affili-id'])) add_settings_error('affiliate-power-options', 'affiliate-power-error-affili-id', 'Ungültige Affili.net Id. Die Id darf ausschließlich aus Zahlen bestehen', 'error');
		
		if (ctype_alnum($input['affili-password']) && strlen($input['affili-password']) == 20) $whitelist['affili-password'] = $input['affili-password'];
		elseif (!empty($input['affili-password'])) add_settings_error('affiliate-power-options', 'affiliate-power-error-affili-password', 'Ungültiges Affili.net PublisherWebservice Passwort. Das Passwort muss 20 Zeichen lang sein und nur Zahlen und Buchstaben enthalten. Bitte gib nicht dein normales Affili.net Passwort an, sondern dein PublisherWebservice Passwort.', 'error');
		
		if (isset($whitelist['affili-id']) && isset($whitelist['affili-password'])) {
			include_once('apis/affili.php');	
			if (!extension_loaded('soap')) {
				add_settings_error('affiliate-power-options', 'affiliate-power-error-soap', 'Für den Download der Affili.net Transaktionen wird das PHP-Modul SOAP benötigt. Dieses ist bei dir nicht aktiviert. Bitte aktiviere das Modul.', 'error');
			}
			elseif (!Affiliate_Power_Api_Affili::checkLogin($whitelist['affili-id'], $whitelist['affili-password'])){
				add_settings_error('affiliate-power-options', 'affiliate-power-error-affili-login', 'Testlogin bei Affili.net fehlgeschlagen. Bitte überprüfe Deine Daten.', 'error');
			}
		}
		
		
		
		//Belboon
		if (!empty($input['belboon-username'])) $whitelist['belboon-username'] = esc_html($input['belboon-username']);
		
		if (ctype_alnum($input['belboon-password']) && strlen($input['belboon-password']) == 20) $whitelist['belboon-password'] = $input['belboon-password'];
		elseif (!empty($input['belboon-password'])) add_settings_error('affiliate-power-options', 'affiliate-power-error-belboon-password', 'Ungültiges Belboon  WebService Passwort. Das Passwort muss 20 Zeichen lang sein und nur Zahlen und Buchstaben enthalten. Bitte gib nicht dein normales Belboon Passwort an, sondern dein WebService Passwort.', 'error');
		
		if (isset($whitelist['belboon-username']) && isset($whitelist['belboon-password'])) {
			include_once('apis/belboon.php');
			if (!extension_loaded('soap')) {
				add_settings_error('affiliate-power-options', 'affiliate-power-error-soap', 'Für den Download der Belboon Transaktionen wird das PHP-Modul SOAP benötigt. Dieses ist bei dir nicht aktiviert. Bitte aktiviere das Modul.', 'error');
			}
			elseif (!Affiliate_Power_Api_Belboon::checkLogin($whitelist['belboon-username'], $whitelist['belboon-password'])){
				add_settings_error('affiliate-power-options', 'affiliate-power-error-belboon-login', 'Testlogin bei Belboon fehlgeschlagen. Bitte überprüfe Deine Daten.', 'error');
			}
		}
		
		if (!empty($input['belboon-platform'])) $whitelist['belboon-platform'] = esc_html($input['belboon-platform']);
		
		
		
		//Superclix
		if (!empty($input['superclix-username'])) $whitelist['superclix-username'] = esc_html($input['superclix-username']);
		
		if (!empty($input['superclix-password'])) $whitelist['superclix-password'] = esc_html($input['superclix-password']);
		
		if (isset($whitelist['superclix-username']) && isset($whitelist['superclix-password'])) {
			include_once('apis/superclix.php');
			if (!Affiliate_Power_Api_Superclix::checkLogin($whitelist['superclix-username'], $whitelist['superclix-password'])){
				add_settings_error('affiliate-power-options', 'affiliate-power-error-superclix-login', 'Testlogin bei Superclix fehlgeschlagen. Bitte überprüfe Deine Daten. Denk daran, dass spezielle Export Passwort anzugeben und nicht das normale Passwort', 'error');
			}
		}
		
		
		//Tradedoubler
		if (ctype_alnum($input['tradedoubler-key']) && strlen($input['tradedoubler-key']) >= 32) $whitelist['tradedoubler-key'] = $input['tradedoubler-key'];
		elseif (!empty($input['tradedoubler-key'])) add_settings_error('affiliate-power-options', 'affiliate-power-error-tradedoubler-key', 'Ungültiger Tradedoubler Report Key. Der Key muss mindestens 32 Zeichen lang sein. Bitte gib nicht dein normales Tadedoubler Passwort an, sondern den Report Key.', 'error');
		
		if (isset($whitelist['tradedoubler-key'])) {
			include_once('apis/tradedoubler.php');
			if (!Affiliate_Power_Api_Tradedoubler::checkLogin($whitelist['tradedoubler-key'])){
				add_settings_error('affiliate-power-options', 'affiliate-power-error-tradedoubler-login', 'Testlogin bei Tradedoubler fehlgeschlagen. Bitte überprüfe Deine Daten.', 'error');
			}
		}
		
		if (!empty($input['tradedoubler-sitename'])) $whitelist['tradedoubler-sitename'] = esc_html($input['tradedoubler-sitename']);
		
			
		
		//Zanox
		if (ctype_alnum($input['zanox-connect-id']) && strlen($input['zanox-connect-id']) == 20) $whitelist['zanox-connect-id'] = $input['zanox-connect-id'];
		elseif (!empty($input['zanox-connect-id'])) add_settings_error('affiliate-power-options', 'affiliate-power-error-zanox-connect-id', 'Ungültige Zanox Connect Id. Die Id muss 20 Zeichen lang sein und nur Zahlen und Buchstaben enthalten. Bitte gib nicht deinen normalen Zanox Account an, sondern die Conenct Id.', 'error');
		
		if (ctype_alnum($input['zanox-public-key']) && strlen($input['zanox-public-key']) == 20) $whitelist['zanox-public-key'] = $input['zanox-public-key'];
		elseif (!empty($input['zanox-public-key'])) add_settings_error('affiliate-power-options', 'affiliate-power-error-zanox-public-key', 'Ungültiger Zanox Public Key. Der Key muss 20 Zeichen lang sein und nur Zahlen und Buchstaben enthalten. Bitte gib nicht deinen normalen Zanox Account an, sondern den Public Key.', 'error');
		
		if (strlen($input['zanox-secret-key']) >= 20) $whitelist['zanox-secret-key'] = $input['zanox-secret-key'];
		elseif (!empty($input['zanox-secret-key'])) add_settings_error('affiliate-power-options', 'affiliate-power-error-zanox-secret-key', 'Ungültiger Zanox Secret Key. Der Key muss mindestens 20 Zeichen lang sein. Bitte gib nicht dein normales Zanox Passwort an, sondern den Secret Key.', 'error');
		
		if (isset($whitelist['zanox-connect-id']) && isset($whitelist['zanox-public-key']) && isset($whitelist['zanox-secret-key'])) {
			include_once('apis/zanox.php');
			if (!extension_loaded('soap')) {
				add_settings_error('affiliate-power-options', 'affiliate-power-error-soap', 'Für den Download der Zanox Transaktionen wird das PHP-Modul SOAP benötigt. Dieses ist bei dir nicht aktiviert. Bitte aktiviere das Modul.', 'error');
			}
			elseif (!Affiliate_Power_Api_Zanox::checkLogin($whitelist['zanox-connect-id'], $whitelist['zanox-public-key'], $whitelist['zanox-secret-key'])){
				add_settings_error('affiliate-power-options', 'affiliate-power-error-zanox-login', 'Testlogin bei Zanox fehlgeschlagen. Bitte überprüfe Deine Daten.', 'error');
			}
		}
		
		if (!empty($input['zanox-adspace'])) $whitelist['zanox-adspace'] = esc_html($input['zanox-adspace']);
		

		
		//settings_errors('affiliate-power-options');
		return $whitelist;
	}

}




?>