<?php
if (!defined('ABSPATH')) die; //no direct access


include_once("affiliate-power-settings.php");
include_once('affiliate-power-transactions.php');
include_once("affiliate-power-statistics.php");


add_action('admin_menu', array('Affiliate_Power_Menu', 'adminMenu'));
add_action( 'admin_enqueue_scripts', array('Affiliate_Power_Menu', 'addJs') );

$options = get_option('affiliate-power-options');
if ($options['add-sub-ids'] !== 0) {
	add_filter('manage_pages_columns', array('Affiliate_Power_Menu', 'addEarningsColummnToPosts'));
	add_filter('manage_posts_columns', array('Affiliate_Power_Menu', 'addEarningsColummnToPosts'));
	add_action('manage_pages_custom_column', array('Affiliate_Power_Menu', 'addEarningsToPosts'), 10, 2);
	add_action('manage_posts_custom_column', array('Affiliate_Power_Menu', 'addEarningsToPosts'), 10, 2);
	
}

add_filter('plugin_action_links_affiliate-power/affiliate-power.php', array('Affiliate_Power_Menu', 'addPluginLinks'), 10, 2);

//todo: fix sort filter
//add_filter('manage_edit-post_sortable_columns', array('Affiliate_Power_Menu', 'makeEarningsColumnSortable') );
//add_filter('request', array('Affiliate_Power_Menu', 'handleSortEarningsColumn') );



class Affiliate_Power_Menu {
	

	static public function adminMenu() {
		add_menu_page('Affiliate Power', 'Affiliate Power', 'manage_options', 'affiliate-power', array('Affiliate_Power_Menu', 'dummyFunction'), plugins_url( 'affiliate-power/img/affiliate-power-16.png' ));
		add_submenu_page('affiliate-power', __('Affiliate Power Sales', 'affiliate-power'), __('Leads / Sales', 'affiliate-power'), 'manage_options', 'affiliate-power', array('Affiliate_Power_Transactions', 'transactionsPage') );
		add_submenu_page('affiliate-power', __('Affiliate Power Statistics', 'affiliate-power'), __('Statistics', 'affiliate-power'), 'manage_options', 'affiliate-power-statistics', array('Affiliate_Power_Statistics', 'statisticsPage') );
		add_submenu_page('affiliate-power', __('Affiliate Power Settings', 'affiliate-power'), __('Settings', 'affiliate-power'), 'manage_options', 'affiliate-power-settings', array('Affiliate_Power_Settings', 'optionsPage') );
	}
	
	
	
	static public function dummyFunction() {
	}
	
	
	static public function addJs() {
		wp_enqueue_script(
			'affiliate-power-menu',
			plugins_url('affiliate-power-menu.js', __FILE__),
			array('jquery', 'jquery-ui-core', 'jquery-ui-datepicker'),
			AFFILIATE_POWER_VERSION,
			true
		);	
		
		wp_localize_script( 'affiliate-power-menu', 'objL10n', array(
			'month1'  => __('January', 'affiliate-power'),
			'month2'  => __('February', 'affiliate-power'),
			'month3'  => __('March', 'affiliate-power'),
			'month4'  => __('April', 'affiliate-power'),
			'month5'  => __('May', 'affiliate-power'),
			'month6'  => __('June', 'affiliate-power'),
			'month7'  => __('July', 'affiliate-power'),
			'month8'  => __('August', 'affiliate-power'),
			'month9'  => __('September', 'affiliate-power'),
			'month10'  => __('October', 'affiliate-power'),
			'month11'  => __('November', 'affiliate-power'),
			'month12'  => __('December', 'affiliate-power'),
			'day1'  => __('Sun', 'affiliate-power'),
			'day2'  => __('Mon', 'affiliate-power'),
			'day3'  => __('Tue', 'affiliate-power'),
			'day4'  => __('Wed', 'affiliate-power'),
			'day5'  => __('Thu', 'affiliate-power'),
			'day6'  => __('Fri', 'affiliate-power'),
			'day7'  => __('Sat', 'affiliate-power')
		) );

		//wp_enqueue_style( 'wp-jquery-ui-datepicker' );
		wp_enqueue_style('jquery-ui-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
	}
	

	
	//--------------
	//CHANGE EXISTING ADMIN PAGES
	//--------------
	static public function addEarningsColummnToPosts($defaults) {
		$defaults['earnings'] = __('Affiliate Income', 'affiliate-power');
		return $defaults;
	}
	
	
	static public function addEarningsToPosts($column_name, $id) {
		if ( $column_name == 'earnings' ) {
		
			global $wpdb;
			$sql = $wpdb->prepare('
				SELECT sum(Commission), sum(Confirmed) 
				FROM '.$wpdb->prefix.'ap_transaction
				LEFT JOIN '.$wpdb->prefix.'ap_clickout
				ON '.$wpdb->prefix.'ap_transaction.SubId = '.$wpdb->prefix.'ap_clickout.ap_clickoutID
				WHERE '.$wpdb->prefix.'ap_clickout.postID = %d
				OR '.$wpdb->prefix.'ap_transaction.SubId = %d 
				AND TransactionStatus <> "Cancelled"', 
				$id, $id);
			$arr_earnings = $wpdb->get_row($sql, ARRAY_N);
			
			$total_earning = number_format($arr_earnings[0], 2, ',', '.');
			$confirmed_earning = number_format($arr_earnings[1], 2, ',', '.');
			$output = $total_earning . ' € (<span style="color:green;">'.$confirmed_earning.' €</span>)';
			//$output = $arr_earnings[0];
			echo $output;
		}
	}
	
	
	static public function addPluginLinks($links, $file) {
	
		$links[] = '<a href="'.admin_url('admin.php?page=affiliate-power-settings').'">'.__('Settings').'</a>';
		return $links;

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