<?php



add_action('admin_init', array('Affiliate_Power_Menu', 'adminInit'));
add_action('admin_menu', array('Affiliate_Power_Menu', 'adminMenu'));
add_action('wp_ajax_ap_download_transactions', array('Affiliate_Power_Apis', 'downloadTransactions'));

$options = get_option('affiliate-power-options');
if ($options['add-sub-ids'] !== 0) {
	add_filter('manage_posts_columns', array('Affiliate_Power_Menu', 'addEarningsColummnToPosts'));
	add_action('manage_posts_custom_column', array('Affiliate_Power_Menu', 'addEarningsToPosts'), 10, 2);
}

//todo: fix sort filter
//add_filter('manage_edit-post_sortable_columns', array('Affiliate_Power_Menu', 'makeEarningsColumnSortable') );
//add_filter('request', array('Affiliate_Power_Menu', 'handleSortEarningsColumn') );



class Affiliate_Power_Menu {

	static public function adminInit() {
		register_setting( 'affiliate-power-options', 'affiliate-power-options', array('Affiliate_Power_Menu', 'optionsValidate') );
		
		add_settings_section('affiliate-power-main', 'Grundeinstellungen', array('Affiliate_Power_Menu', 'optionsMainText'), 'affiliate-power-options');
		add_settings_field('affiliate-power-add-sub-ids', 'Artikel Tracking aktiv', array('Affiliate_Power_Menu', 'addSubIdsField'), 'affiliate-power-options', 'affiliate-power-main');
		
		//add_settings_field('affiliate-power-download-method', 'Methode Sale/Lead Download', array('Affiliate_Power_Menu', 'downloadMethod'), 'affiliate-power-options', 'affiliate-power-main');
		
		add_settings_section('affiliate-power-networks', 'Affiliate-Netzwerke', array('Affiliate_Power_Menu', 'optionsNetworksText'), 'affiliate-power-options');
		add_settings_field('affiliate-power-affili-id', 'Affili.net UserId', array('Affiliate_Power_Menu', 'addAffiliIdField'), 'affiliate-power-options', 'affiliate-power-networks');
		add_settings_field('affiliate-power-affili-password', 'Affili.net PublisherWebservice Passwort:', array('Affiliate_Power_Menu', 'addAffiliPasswordField'), 'affiliate-power-options', 'affiliate-power-networks');
		add_settings_field('affiliate-power-zanox-connect-id', 'Zanox ConnectId', array('Affiliate_Power_Menu', 'addZanoxConnectIdField'), 'affiliate-power-options', 'affiliate-power-networks');
		add_settings_field('affiliate-power-zanox-public-key', 'Zanox Public Key', array('Affiliate_Power_Menu', 'addZanoxPublicKeyField'), 'affiliate-power-options', 'affiliate-power-networks');
		add_settings_field('affiliate-power-zanox-secret-key', 'Zanox Secret Key', array('Affiliate_Power_Menu', 'addZanoxSecretKeyField'), 'affiliate-power-options', 'affiliate-power-networks');
		add_settings_field('affiliate-power-tradedoubler-key', 'Tradedoubler Report Key', array('Affiliate_Power_Menu', 'addTradedoublerKeyField'), 'affiliate-power-options', 'affiliate-power-networks');
		
	}

	static public function adminMenu() {
		add_menu_page('Affiliate Power', 'Affiliate Power', 'manage_options', 'affiliate-power', array('Affiliate_Power_Menu', 'dummyFunction'));
		add_submenu_page('affiliate-power', 'Funktionen aktivieren, API-Keys hinterlegen etc.', 'Einstellungen', 'manage_options', 'affiliate-power', array('Affiliate_Power_Menu', 'optionsPage') );
		add_submenu_page('affiliate-power', 'Transaktionen ansehen und analysieren', 'Leads / Sales', 'manage_options', 'affiliate-power-transactions', array('Affiliate_Power_Menu', 'transactionsPage') );
		add_submenu_page('affiliate-power', 'Statistiken einsehen', 'Statistiken', 'manage_options', 'affiliate-power-statistics', array('Affiliate_Power_Menu', 'statisticsPage') );
	}
	
	
	
	
	//--------------
	//PAGES
	//--------------
	
	static public function dummyFunction() {
	}
	
	
	static public function optionsPage() {
		include_once 'options-head.php'; //we need this to show error messages
		?>
		<div class="wrap">
		<h2>Affiliate Power</h2>
		<p>Herzlich Willkommen bei der Beta-Version von Affiliate Power. Das Plugin befindet sich noch in der Entwicklung. Sollte du Probleme und Vorschläge für neue Features haben, freue ich mich einen Kommentar in <a href="http://www.j-breuer.de/wordpress-plugins/affiliate-power/" target="_blank">meinem Blog</a>.</p>
		
		<p>Auf dieser Seite kannst du die Einstellungen von Affiliate Power bearbeiten. Bitte habe etwas Geduld beim Speichern der Daten. Das Plugin führt einen Testlogin bei den Netzwerken durch.</p>
		
		<form action="options.php" method="post">
		<?php settings_fields('affiliate-power-options'); ?>
		<?php do_settings_sections('affiliate-power-options'); ?>
		<p><input name="Submit" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" /></p>
		</form>
		
		</div>
		<?php
	}
	
	
	
	static public function transactionsPage() {
		include_once('affiliate-power-transactions-list.php');
		
		$transactionList = new Affiliate_Power_Transaction_List();
		$transactionList->prepare_items();
		
		?>
		<div class="wrap">
			
			<div id="icon-users" class="icon32"><br/></div>
			<h2>Leads / Sales</h2>
			
			<form id="movies-filter" method="get">
				<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
				<?php $transactionList->display() ?>
			</form>
			
			<input type="button" id="button_download_transactions" value="Transaktionen aktualisieren" />
			<script type="text/javascript">
				jQuery(document).ready(function($) {
					var data = { action: 'ap_download_transactions'};
					$("#button_download_transactions").bind("click", function(e){
						$(this).val('Bitte warten...');
						$.post(ajaxurl, data, function(response) {
							location.reload();
						});
					});
				});
			</script>
			
		</div>
		<?php
	}
	
	
	static public function statisticsPage() {
		global $wpdb;
		$options = get_option('affiliate-power-options');
		
		//Top Article/SubIds
		$sql = $wpdb->prepare('
		SELECT '.$wpdb->prefix.'ap_transaction.SubId AS postID,
			round(sum('.$wpdb->prefix.'ap_transaction.Commission),2) as commission,
			round(sum('.$wpdb->prefix.'ap_transaction.Confirmed), 2) as confirmed,
			'.$wpdb->posts.'.post_title
		FROM '.$wpdb->prefix.'ap_transaction 
		LEFT JOIN '.$wpdb->posts.'
		ON '.$wpdb->prefix.'ap_transaction.SubId = '.$wpdb->prefix.'posts.ID
		WHERE TransactionStatus <> "Cancelled" 
		GROUP BY SubId 
		ORDER BY sum('.$wpdb->prefix.'ap_transaction.Commission) DESC
		LIMIT 20');
		$topArticleData = $wpdb->get_results($sql, ARRAY_A);
		
		if ($options['add-sub-ids'] === 0) {
			$topArticleHeadline = 'Top SubIds';
			$topArticelRowHeadline = 'SubId';
		}
		else {
			$topArticleHeadline = 'Top Artikel / Seiten';
			$topArticelRowHeadline = 'Artikel';
		}
		
		
		//Top Partner
		$sql = $wpdb->prepare('
		SELECT concat (ProgramTitle, " (", network, ")") as program,
			   round(sum(Commission),2) as commission,
			   round(sum(Confirmed), 2) as confirmed
		FROM '.$wpdb->prefix.'ap_transaction 
		WHERE TransactionStatus <> "Cancelled" 
		GROUP BY ProgramId, network
		ORDER BY sum(Commission) DESC
		LIMIT 20');
		$topPartnerData = $wpdb->get_results($sql, ARRAY_A);
		
		
		//Last Months
		$sql = $wpdb->prepare('
		SELECT concat (MONTHNAME(date), " ", year(date)) as month_year,
			   round(sum(Commission),2) as commission,
			   round(sum(Confirmed), 2) as confirmed
		FROM '.$wpdb->prefix.'ap_transaction 
		WHERE TransactionStatus <> "Cancelled" 
		GROUP BY month(date), year(date)
		ORDER BY year(date) DESC,
				 month(date) DESC
		LIMIT 24');
		$monthData = $wpdb->get_results($sql, ARRAY_A);
		
		
		?>
		<div style="width:30%; float:left; margin-right:20px;">
		<h3><?php echo $topArticleHeadline; ?></h3>
		<table class="widefat">
			<thead>
				<tr>
					<th><?php echo $topArticelRowHeadline; ?></th>
					<th>Einnahmen</th>      
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th><?php echo $topArticelRowHeadline; ?></th>
					<th>Einnahmen</th>
				</tr>
			</tfoot>
			<tbody>
			   <?php 
			   foreach ($topArticleData as $article) {
				 if ($options['add-sub-ids'] === 0) {
					$output_post = $article['postID'];
				 }
				 else {
					 if ($article['post_title'] == '') {
						$output_post = '- unbekannt - <span style="color:silver">(Id: '.$article['postID'].')</span>';
					 } else {
						$permalink = get_permalink($article['postID']);
						$output_post = sprintf('<a href="%s" target="_blank">%s</a>',$permalink, $article['post_title']);
					 }
				}
		
				 $total_earning = number_format($article['commission'], 2, ',', '.');
				 $confirmed_earning = number_format($article['confirmed'], 2, ',', '.');
				 $output_earning = $total_earning . ' € (<span style="color:green;">'.$confirmed_earning.' €</span>)';
				 
				 echo ('<tr><td>'.$output_post.'</td><td>'.$output_earning.'</td></tr>');
			   }
			   ?>
			</tbody>
		</table>
		</div>
		
		
		<div style="width:30%; float:left; margin-right:20px;">
		<h3>Top Partner</h3>
		<table class="widefat">
			<thead>
				<tr>
					<th>Partner</th>
					<th>Einnahmen</th>      
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th>Partner</th>
					<th>Einnahmen</th>
				</tr>
			</tfoot>
			<tbody>
			   <?php 
			   foreach ($topPartnerData as $partner) {
			     $total_earning = number_format($partner['commission'], 2, ',', '.');
				 $confirmed_earning = number_format($partner['confirmed'], 2, ',', '.');
				 $output_earnings = $total_earning . ' € (<span style="color:green;">'.$confirmed_earning.' €</span>)';
			     echo ('<tr><td>'.$partner['program'].'</td><td>'.$output_earnings.'</td></tr>');
			   }
			   ?>
			</tbody>
		</table>
		</div>
		
		
		<div style="width:30%; float:left; margin-right:20px;">
		<h3>Die letzten Monate</h3>
		<table class="widefat">
			<thead>
				<tr>
					<th>Monat</th>
					<th>Einnahmen</th>      
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th>Monat</th>
					<th>Einnahmen</th>
				</tr>
			</tfoot>
			<tbody>
			   <?php 
			   foreach ($monthData as $month) {
			     $total_earning = number_format($month['commission'], 2, ',', '.');
				 $confirmed_earning = number_format($month['confirmed'], 2, ',', '.');
				 $output_earnings = $total_earning . ' € (<span style="color:green;">'.$confirmed_earning.' €</span>)';
			     echo ('<tr><td>'.$month['month_year'].'</td><td>'.$output_earnings.'</td></tr>');
			   }
			   ?>
			</tbody>
		</table>
		</div>
			
		<?php
	}
	
	
	
	
	
	//--------------
	//SETTINGS
	//--------------
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
		echo "<input type='checkbox' id='affiliate-power-add-sub-ids' name='affiliate-power-options[add-sub-ids]' value='1' ".$checked." />";
	}
	
	
	//Network Settings
	static public function optionsNetworksText() {
		echo '<p>Damit das Tracking funktioniert, musst du hier deine Daten bei den Affiliate-Netzwerken hinterlegen, die du benutzt. <a href=" http://www.j-breuer.de/wordpress-plugins/affiliate-power/#daten-sicherheit" target="_blank">Sind meine Daten sicher?</a></p>';
	}
	
	
	//Affili.net
	static public function addAffiliIdField() {
		$options = get_option('affiliate-power-options');
		echo "<input type='text' id='affiliate-power-affili-id' name='affiliate-power-options[affili-id]' size='20' value='".$options['affili-id']."' /> ";
		echo "<span style='font-size:0.8em;'><a href='#' onclick='document.getElementById(\"ap_affili_id_info\").style.display=\"block\"'>Was ist das?</a></span>";
		echo "<div id='ap_affili_id_info' style='display:none;'>Die Affili.net UserId ist die 6-stellige Nummer, mit der du dich auch auf affili.net einloggst.</div>";
	}
	
	static public function addAffiliPasswordField() {
		$options = get_option('affiliate-power-options');
		echo "<input type='text' id='affiliate-power-affili-password' name='affiliate-power-options[affili-password]' size='40' value='".$options['affili-password']."' /> ";
		echo "<span style='font-size:0.8em;'><a href='#' onclick='document.getElementById(\"ap_affili_password_info\").style.display=\"block\"'>Was ist das?</a></span>";
		echo "<div id='ap_affili_password_info' style='display:none;'>Das Affili.net PublisherWebservice Passwort ist ein spezieller Zugang zur API von Affili.net. Bitte gib hier <strong>nicht</strong> das normale affili.net Passwort an. Du findest das PublisherWebservice Passwort im Login-Bereich von affili.net unter Konto -> Technische Einstellungen -> Webservices. Eventuell musst du das Passwort erst noch anfordern.</div>";
	}
	
	
	
	//Zanox
	static public function addZanoxConnectIdField() {
		$options = get_option('affiliate-power-options');
		echo "<input type='text' id='affiliate-power-zanox-connect-id' name='affiliate-power-options[zanox-connect-id]' size='40' value='".$options['zanox-connect-id']."' /> ";
		echo "<span style='font-size:0.8em;'><a href='#' onclick='document.getElementById(\"ap_zanox_connect_info\").style.display=\"block\"'>Was ist das?</a></span>";
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
		echo "<span style='font-size:0.8em;'><a href='#' onclick='document.getElementById(\"ap_zanox_public_info\").style.display=\"block\"'>Was ist das?</a></span>";
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
		echo "<span style='font-size:0.8em;'><a href='#' onclick='document.getElementById(\"ap_zanox_secret_info\").style.display=\"block\"'>Was ist das?</a></span>";
		echo "<div id='ap_zanox_secret_info' style='display:none;'>Der Zanox SecretKey wird für den Zugriff auf die Zanox Transaktionen benötigt. Die Schritte, um diesen zu bekommen sind bei Zanox etwas umständlich aber gemeinsam bekommen wir das schon hin:
		<ol>
		<li>Auf <a href='http://apps.zanox.com'>http://apps.zanox.com</a> oben rechts auf \"Connect with Zanox\" klicken und dich mit deinen normalen Zanox-Daten einloggen</li>
		<li>Im Tab \"Developers\" unter \"My own Applications\" den Bedingungen zustimmen und auf \"Become a developer\" klicken</li>
		<li>Auf den neu erschienen Button \"New application\" und dann auf \"zanox keys\" klicken</li>
		</ol>
		</div>";
	}
	
	
	//Tradedoubler
	static public function addTradedoublerKeyField() {
		$options = get_option('affiliate-power-options');
		echo "<input type='text' id='affiliate-power-tradedoubler-key' name='affiliate-power-options[tradedoubler-key]' size='40' value='".$options['tradedoubler-key']."' /> ";
		echo "<span style='font-size:0.8em;'><a href='#' onclick='document.getElementById(\"ap_tradedoubler_key_info\").style.display=\"block\"'>Was ist das?</a></span>";
		echo "<div id='ap_tradedoubler_key_info' style='display:none;'>Der Tradedoubler Report Key ist ein spezieller Zugang zur API von Tradedoubler. Bitte gib hier <strong>nicht</strong> das normale Tradedoubler Passwort an. Um den Tradedoubler Key zu bekommen, musst du eine E-Mail mit deinem Tradedoubler Benutzernamen an <a href='mailto:support.de@tradedoubler.com'>support.de@tradedoubler.com</a> schicken mit der Bitte für die Report API freigeschaltet zu werden. Du bekommst dann eine E-Mail mit dem Key.</div>";
	}
	
	
	
	//Validation
	static public function optionsValidate($input) {
	
		
	
		//Main Settings
		$whitelist['add-sub-ids'] = $input['add-sub-ids'];
		if ($whitelist['add-sub-ids'] != 1) $whitelist['add-sub-ids'] = 0;
		
		//if (is_numeric($input['download-method'])) $whitelist['download-method'] = (int)$input['download-method'];
		
		//Affili.net
		if (is_numeric($input['affili-id'])) $whitelist['affili-id'] = $input['affili-id'];
		elseif (!empty($input['affili-id'])) add_settings_error('affiliate-power-options', 'affiliate-power-error-affili-id', 'Ungültige Affili.net Id. Die Id darf ausschließlich aus Zahlen bestehen', 'error');
		
		if (preg_match('/[a-z0-9]/i', $input['affili-password']) && strlen($input['affili-password']) == 20) $whitelist['affili-password'] = $input['affili-password'];
		elseif (!empty($input['affili-password'])) add_settings_error('affiliate-power-options', 'affiliate-power-error-affili-password', 'Ungültiges Affili.net PublisherWebservice Passwort. Das Passwort muss 20 Zeichen lang sein und nur Zahlen und Buchstaben enthalten. Bitte gib nicht dein normales Affili.net Passwort an, sondern dein PublisherWebservice Passwort.', 'error');
		
		if (isset($whitelist['affili-id']) && isset($whitelist['affili-password'])) {
			if (!extension_loaded('soap')) {
				add_settings_error('affiliate-power-options', 'affiliate-power-error-soap', 'Für den Download der Affili.net Transaktionen wird das PHP-Modul SOAP benötigt. Dieses ist bei dir nicht aktiviert. Bitte aktiviere das Modul.', 'error');
			}
			elseif (!Affiliate_Power_Apis::checkLoginAffili($whitelist['affili-id'], $whitelist['affili-password'])){
				add_settings_error('affiliate-power-options', 'affiliate-power-error-affili-login', 'Testlogin bei Affili.net fehlgeschlagen. Bitte überprüfe Deine Daten.', 'error');
			}
		}
		
		//Zanox
		if (preg_match('/[a-z0-9]/i', $input['zanox-connect-id']) && strlen($input['zanox-connect-id']) == 20) $whitelist['zanox-connect-id'] = $input['zanox-connect-id'];
		elseif (!empty($input['zanox-connect-id'])) add_settings_error('affiliate-power-options', 'affiliate-power-error-zanox-connect-id', 'Ungültige Zanox Connect Id. Die Id muss 20 Zeichen lang sein und nur Zahlen und Buchstaben enthalten. Bitte gib nicht deinen normalen Zanox Account an, sondern die Conenct Id.', 'error');
		
		if (preg_match('/[a-z0-9]/i', $input['zanox-public-key']) && strlen($input['zanox-public-key']) == 20) $whitelist['zanox-public-key'] = $input['zanox-public-key'];
		elseif (!empty($input['zanox-public-key'])) add_settings_error('affiliate-power-options', 'affiliate-power-error-zanox-public-key', 'Ungültiger Zanox Public Key. Der Key muss 20 Zeichen lang sein und nur Zahlen und Buchstaben enthalten. Bitte gib nicht deinen normalen Zanox Account an, sondern den Public Key.', 'error');
		
		if (strlen($input['zanox-secret-key']) >= 20) $whitelist['zanox-secret-key'] = $input['zanox-secret-key'];
		elseif (!empty($input['zanox-secret-key'])) add_settings_error('affiliate-power-options', 'affiliate-power-error-zanox-secret-key', 'Ungültiger Zanox Secret Key. Der Key muss mindestens 20 Zeichen lang sein. Bitte gib nicht dein normales Zanox Passwort an, sondern den Secret Key.', 'error');
		
		if (isset($whitelist['zanox-connect-id']) && isset($whitelist['zanox-public-key']) && isset($whitelist['zanox-secret-key'])) {
			if (!extension_loaded('soap')) {
				add_settings_error('affiliate-power-options', 'affiliate-power-error-soap', 'Für den Download der Zanox Transaktionen wird das PHP-Modul SOAP benötigt. Dieses ist bei dir nicht aktiviert. Bitte aktiviere das Modul.', 'error');
			}
			elseif (!Affiliate_Power_Apis::checkLoginZanox($whitelist['zanox-connect-id'], $whitelist['zanox-public-key'], $whitelist['zanox-secret-key'])){
				add_settings_error('affiliate-power-options', 'affiliate-power-error-zanox-login', 'Testlogin bei Zanox fehlgeschlagen. Bitte überprüfe Deine Daten.', 'error');
			}
		}
		
		//Tradedoubler
		if (strlen($input['tradedoubler-key']) >= 32) $whitelist['tradedoubler-key'] = $input['tradedoubler-key'];
		elseif (!empty($input['tradedoubler-key'])) add_settings_error('affiliate-power-options', 'affiliate-power-error-tradedoubler-key', 'Ungültiger Tradedoubler Report Key. Der Key muss mindestens 32 Zeichen lang sein. Bitte gib nicht dein normales Tadedoubler Passwort an, sondern den Report Key.', 'error');
		
		if (isset($whitelist['tradedoubler-key'])) {
			if (!Affiliate_Power_Apis::checkLoginTradedoubler($whitelist['tradedoubler-key'])){
				add_settings_error('affiliate-power-options', 'affiliate-power-error-tradedoubler-login', 'Testlogin bei Tradedoubler fehlgeschlagen. Bitte überprüfe Deine Daten.', 'error');
			}
		}
		
		settings_errors('affiliate-power-options');
		return $whitelist;
	}
	
	
	
	
	
	//--------------
	//CHANGE EXISTING ADMIN PAGES
	//--------------
	static public function addEarningsColummnToPosts($defaults) {
		$defaults['earnings'] = __('Einnahmen');
		return $defaults;
	}
	
	
	static public function addEarningsToPosts($column_name, $id) {
		if ( $column_name == 'earnings' ) {
		
			global $wpdb;
			$sql = $wpdb->prepare('SELECT sum(Commission), sum(Confirmed) FROM '.$wpdb->prefix.'ap_transaction WHERE SubId = %d AND TransactionStatus <> "Cancelled"', $id);
			$arr_earnings = $wpdb->get_row($sql, ARRAY_N);
			
			$total_earning = number_format($arr_earnings[0], 2, ',', '.');
			$confirmed_earning = number_format($arr_earnings[1], 2, ',', '.');
			$output = $total_earning . ' € (<span style="color:green;">'.$confirmed_earning.' €</span>)';
			//$output = $arr_earnings[0];
			echo $output;
		}
	}
	
	
	static public function makeEarningsColumnSortable( $columns ) {
		$columns['earnings'] = 'earnings';
		return $columns;
	}


	//todo: fix sort, this is not working because earnings is not in post data table
	static public function handleSortEarningsColumn( $vars ) {
		if ( isset( $vars['orderby'] ) && $vars['orderby'] == 'earnings' ) {
			$vars = array_merge( $vars, array(
				'meta_key' => 'earnings',
				'orderby' => 'meta_value_num'
			) );
		}
	 
		return $vars;
	}


	
}



?>