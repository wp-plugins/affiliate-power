<?php

include_once("affiliate-power-settings.php");
include_once('affiliate-power-transactions.php');
include_once("affiliate-power-statistics.php");


add_action('admin_menu', array('Affiliate_Power_Menu', 'adminMenu'));

$options = get_option('affiliate-power-options');
if ($options['add-sub-ids'] !== 0) {
	add_filter('manage_posts_columns', array('Affiliate_Power_Menu', 'addEarningsColummnToPosts'));
	add_action('manage_posts_custom_column', array('Affiliate_Power_Menu', 'addEarningsToPosts'), 10, 2);
}

//todo: fix sort filter
//add_filter('manage_edit-post_sortable_columns', array('Affiliate_Power_Menu', 'makeEarningsColumnSortable') );
//add_filter('request', array('Affiliate_Power_Menu', 'handleSortEarningsColumn') );



class Affiliate_Power_Menu {
	

	static public function adminMenu() {
		add_menu_page('Affiliate Power', 'Affiliate Power', 'manage_options', 'affiliate-power', array('Affiliate_Power_Menu', 'dummyFunction'));
		add_submenu_page('affiliate-power', 'Affiliate Power Einstellungen', 'Einstellungen', 'manage_options', 'affiliate-power', array('Affiliate_Power_Settings', 'optionsPage') );
		add_submenu_page('affiliate-power', 'Transaktionen ansehen und analysieren', 'Leads / Sales', 'manage_options', 'affiliate-power-transactions', array('Affiliate_Power_Transactions', 'transactionsPage') );
		add_submenu_page('affiliate-power', 'Statistiken einsehen', 'Statistiken', 'manage_options', 'affiliate-power-statistics', array('Affiliate_Power_Statistics', 'statisticsPage') );
	}
	
	
	
	static public function dummyFunction() {
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