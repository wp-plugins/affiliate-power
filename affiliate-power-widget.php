<?php

add_action('wp_dashboard_setup', array('Affiliate_Power_Widget', 'add') );


class Affiliate_Power_Widget {

	static public function add() {
		wp_add_dashboard_widget('affiliate-power', 'Affiliate Einnahmen', array('Affiliate_Power_Widget', 'getContent'));	
	}
	
	static public function getContent() {
		global $wpdb;
		
		
		//today
		$sql = $wpdb->prepare('
			SELECT sum(Commission)
			FROM '.$wpdb->prefix.'ap_transaction
			WHERE date(`Date`) = date(now())
			AND TransactionStatus <> "Cancelled"
		');
		$sum_today = $wpdb->get_var($sql);
		
		
		//yesterday
		$sql = $wpdb->prepare('
			SELECT sum(Commission)
			FROM '.$wpdb->prefix.'ap_transaction
			WHERE date(`Date`) = date(now() - interval 1 day)
			AND TransactionStatus <> "Cancelled"
		');
		$sum_yesterday = $wpdb->get_var($sql);
		
		
		//this month
		$sql = $wpdb->prepare('
			SELECT sum(Commission)
			FROM '.$wpdb->prefix.'ap_transaction
			WHERE month(`Date`) = month(now())
			AND year(`Date`) = year(now())
			AND TransactionStatus <> "Cancelled"
		');
		$sum_this_month = $wpdb->get_var($sql);
		
		
		//last month
		$month = date('n') - 1;
		$year = date('Y');
		if ($month == 0) {$month=12; $year--;}
		
		$sql = $wpdb->prepare('
			SELECT sum(Commission)
			FROM '.$wpdb->prefix.'ap_transaction
			WHERE month(`Date`) = '.$month.'
			AND year(`Date`) = '.$year.'
			AND TransactionStatus <> "Cancelled"
		');
		$sum_last_month = $wpdb->get_var($sql);
		
		
		//total
		$sql = $wpdb->prepare('
			SELECT sum(Commission)
			FROM '.$wpdb->prefix.'ap_transaction
			WHERE TransactionStatus <> "Cancelled"
		');
		$sum_total = $wpdb->get_var($sql);
		
		$refresh_button = '<input type="button" id="button_download_transactions" value="Aktualisieren" />
			<script type="text/javascript">
				jQuery(document).ready(function($) {
					var data = { action: \'ap_download_transactions\'};
					$("#button_download_transactions").bind("click", function(e){
						$(this).val(\'Bitte warten...\');
						$.post(ajaxurl, data, function(response) {
							location.reload();
						});
					});
				});
			</script>';
		
	
		echo $refresh_button.'<br>
			<table>
				<tr><td>Heute:</td><td style="text-align:right">'.number_format($sum_today, 2, ',', '.').' €</td></tr>
				<tr><td>Gestern:</td><td style="text-align:right">'.number_format($sum_yesterday, 2, ',', '.').' €</td></tr>
				<tr><td>Dieser Monat:</td><td style="text-align:right">'.number_format($sum_this_month, 2, ',', '.').' €</td></tr>
				<tr><td>Letzter Monat:</td><td style="text-align:right">'.number_format($sum_last_month, 2, ',', '.').' €</td></tr>
				<tr><td>Gesamt:</td><td style="text-align:right">'.number_format($sum_total, 2, ',', '.').' €</td></tr>
			</table>';
		
	}
	

}







?>