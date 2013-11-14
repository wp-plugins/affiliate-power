<?php
if (!defined('ABSPATH')) die; //no direct access


add_action('wp_dashboard_setup', array('Affiliate_Power_Widget', 'add') );


class Affiliate_Power_Widget {

	static public function add() {
		if ( current_user_can('manage_options') ) {
			wp_add_dashboard_widget('affiliate-power', __('Affiliate Income', 'affiliate-power'), array('Affiliate_Power_Widget', 'getContent'));	
		}
	}
	
	static public function getContent() {
		global $wpdb;
		
		
		//today
		$sql = '
			SELECT sum(Commission)
			FROM '.$wpdb->prefix.'ap_transaction
			WHERE date(`Date`) = date(now())
			AND TransactionStatus <> "Cancelled"
		';
		$sum_today = $wpdb->get_var($sql);
		
		
		//yesterday
		$sql = '
			SELECT sum(Commission)
			FROM '.$wpdb->prefix.'ap_transaction
			WHERE date(`Date`) = date(now() - interval 1 day)
			AND TransactionStatus <> "Cancelled"
		';
		$sum_yesterday = $wpdb->get_var($sql);
		
		
		//this month
		$sql = '
			SELECT sum(Commission)
			FROM '.$wpdb->prefix.'ap_transaction
			WHERE month(`Date`) = month(now())
			AND year(`Date`) = year(now())
			AND TransactionStatus <> "Cancelled"
		';
		$sum_this_month = $wpdb->get_var($sql);
		
		
		//last month
		$month = date('n') - 1;
		$year = date('Y');
		if ($month == 0) {$month=12; $year--;}
		
		$sql = '
			SELECT sum(Commission)
			FROM '.$wpdb->prefix.'ap_transaction
			WHERE month(`Date`) = '.$month.'
			AND year(`Date`) = '.$year.'
			AND TransactionStatus <> "Cancelled"
		';
		$sum_last_month = $wpdb->get_var($sql);
		
		
		//total
		$sql = '
			SELECT sum(Commission)
			FROM '.$wpdb->prefix.'ap_transaction
			WHERE TransactionStatus <> "Cancelled"
		';
		$sum_total = $wpdb->get_var($sql);
		
		$refresh_button = '<input type="button" style="float:left; width:100px;" id="button_download_transactions" value="'.__('Refresh', 'affiliate-power').'" /><span class="spinner" style="float:left;"></span>
			<script type="text/javascript">
				jQuery(document).ready(function($) {
					var data = { action: \'ap_download_transactions\', nonce: \'' . wp_create_nonce( 'affiliate-power-download-transactions' ) . '\'};
					$("#button_download_transactions").bind("click", function(e){
						$(this).val(\''.__('Please wait', 'affiliate-power').'...\');
						$(\'.spinner\').css(\'display\', \'block\');
						$.post(ajaxurl, data, function(response) {
							location.reload();
						});
					});
				});
			</script>';
		
	
		echo $refresh_button.'<br>
			<table style="clear:left;">
				<tr><td>'.__('Today', 'affiliate-power').':</td><td style="text-align:right">'.number_format($sum_today, 2, ',', '.').' €</td></tr>
				<tr><td>'.__('Yesterday', 'affiliate-power').':</td><td style="text-align:right">'.number_format($sum_yesterday, 2, ',', '.').' €</td></tr>
				<tr><td>'.__('This month', 'affiliate-power').':</td><td style="text-align:right">'.number_format($sum_this_month, 2, ',', '.').' €</td></tr>
				<tr><td>'.__('Last month', 'affiliate-power').':</td><td style="text-align:right">'.number_format($sum_last_month, 2, ',', '.').' €</td></tr>
				<tr><td>'.__('Total', 'affiliate-power').':</td><td style="text-align:right">'.number_format($sum_total, 2, ',', '.').' €</td></tr>
			</table>
			<a href="admin.php?page=affiliate-power">'.__('Details', 'affiliate-power').'</a>';
		
	}
	

}







?>