<?php

//for ap_download_transactions see affiliate-power.php
add_action('wp_ajax_ap_export_csv', array('Affiliate_Power_Transactions', 'exportTransactions'));


class Affiliate_Power_Transactions {

	static public function transactionsPage() {
		
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
			<input type="button" id="button_export_csv" value="Excel Download" />
			
			<script type="text/javascript">
				jQuery(document).ready(function($) {
				
					$("#button_download_transactions").bind("click", function(e){
						$(this).val('Bitte warten...');
						$.post(ajaxurl, { action: 'ap_download_transactions'}, function(response) {
							location.reload();
						});
					});
					
					$("#button_export_csv").bind("click", function(e){
						$(this).val('Bitte warten...');
						$.post(ajaxurl, { action: 'ap_export_csv'}, function(response) {
							$("#button_export_csv").val('Excel Download');
							$("body").append("<iframe src='<?php echo plugins_url( "affiliate-power/csv-download.php", dirname(__FILE__ )); ?>' style='display: none;' ></iframe>")
							
						});
					});
					
				});
			</script>
			
		</div>
		<?php
	}
	
	
	public static function exportTransactions() {
		
		global $wpdb;
		$csv_content = 'Id;Datum;Netzwerk;Merchant;Typ;Einkaufspreis;Provision;Status;ArtikelId;ArtikelName' . "\r\n";
		
		$sql = $wpdb->prepare('
			SELECT 
				'.$wpdb->prefix.'ap_transaction.ap_transactionID,
				date_format('.$wpdb->prefix.'ap_transaction.`Date`, "%%d.%%m.%%Y %%T") AS datetime_de,
				'.$wpdb->prefix.'ap_transaction.network,
				'.$wpdb->prefix.'ap_transaction.ProgramTitle,
				'.$wpdb->prefix.'ap_transaction.Transaction,
				'.$wpdb->prefix.'ap_transaction.Price,
				'.$wpdb->prefix.'ap_transaction.Commission,
				'.$wpdb->prefix.'ap_transaction.TransactionStatus,
				'.$wpdb->prefix.'posts.ID AS postID,
			    '.$wpdb->posts.'.post_title
			FROM '.$wpdb->prefix.'ap_transaction
			LEFT JOIN '.$wpdb->posts.'
			ON '.$wpdb->prefix.'ap_transaction.SubId = '.$wpdb->prefix.'posts.ID
			ORDER by `Date` ASC
		');
		$transactions = $wpdb->get_results($sql, ARRAY_A);
		
		foreach ($transactions as $transaction) {
			$transaction['Price'] = str_replace('.', ',', $transaction['Price']);
			$transaction['Commission'] = str_replace('.', ',', $transaction['Commission']);
			$csv_content .= implode(';', $transaction) . "\r\n";
		}
		
		if (!session_id()) session_start();
		$_SESSION['affiliate-power-csv'] = $csv_content;
		
		die();
	}
	
}

	
	
if(!class_exists('WP_List_Table')){
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}


class Affiliate_Power_Transaction_List extends WP_List_Table {

	

	function __construct(){
		global $status, $page;
                
        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'Transaktion',     //singular name of the listed records
            'plural'    => 'Transaktionen',    //plural name of the listed records
            'ajax'      => false        //does this table support ajax?
        ) ); 
    }
	
	
	function column_default($item, $column_name){
	
		switch ($column_name) {
		
			case 'Transaction' :
				if ($item['Transaction'] == 'S') $value = 'Sale';
				elseif ($item['Transaction'] == 'L') $value = 'Lead';
				elseif ($item['Transaction'] == 'K') $value = 'Kombi';
				else $value = $item['Transaction'];
				break;
		
			case 'Price' :
				if ($item['Price'] == 0) $value = '---';
				else {
					$value = number_format($item['Price'], 2, ',', '.');
					$value .= ' €';
				}
				break;
				
				
			case 'Commission' :
				$value = number_format($item['Commission'], 2, ',', '.');
				$value .= ' €';
				break;
				
			case 'TransactionStatus' :
				if ($item['TransactionStatus'] == 'Cancelled') $value = 'Abgelehnt';
				elseif ($item['TransactionStatus'] == 'Confirmed') $value = 'Bestätigt';
				else $value = 'Vorgemerkt';
				break;
				
			case 'post_title' :
				$options = get_option('affiliate-power-options');
				if ($options['add-sub-ids'] === 0) {
					$value = $item['postID'];
				}
				else {
					if ($item['post_title'] == '') {
						$value = '- unbekannt -';
						break;
					} 
					
					$permalink = get_permalink($item['postID']);
					
					$actions = array(
						'view'    => sprintf('<a href="%s" target="_blank">Ansehen</a>',$permalink),
						'edit'    => sprintf('<a href="post.php?post=%d&action=%s">Edit</a>',$item['postID'],'edit')
					);
					$value = sprintf('%1$s %2$s',
						/*$1%s*/ $item['post_title'],
						/*$2%s*/ $this->row_actions($actions)
					);
				}
				break;
				
			default :
				$value = $item[$column_name];
		}
	
		if ($item['TransactionStatus'] == 'Cancelled') $color = '#FF0000';
		elseif ($item['TransactionStatus'] == 'Confirmed') $color = 'green';
		else $color = '#666666';
		
		$output = '<span style="color:'.$color.';">'.$value.'</span>';
		return $output;
	
    }


	
	
	function get_columns(){
		$options = get_option('affiliate-power-options');
		if ($options['add-sub-ids'] !== 0) $post_title_text = 'Artikel';
		else $post_title_text = 'SubId';

        $columns = array(
            'germanDate'     => 'Datum',
            'network'    => 'Netzwerk',
            'ProgramTitle'  => 'Merchant',
			'Transaction' => 'Typ',
			'Price' => 'Einkaufswert',
			'Commission'  => 'Provision',
			'TransactionStatus' => 'Status',
			'post_title' => $post_title_text
        );
        return $columns;
    }
	
	
	function get_sortable_columns() {
        $sortable_columns = array(
            'germanDate'     => array('Date',true),     //true means its already sorted
            'network'    => array('network',false),
            'ProgramTitle'  => array('ProgramTitle',false),
			'Transaction'  => array('Transaction',false),
			'Price'  => array('Price',false),
			'Commission'  => array('Commission',false),
			'TransactionStatus'  => array('TransactionStatus',false),
			'post_title'  => array('post_title',false)
			
        );
        return $sortable_columns;
    }
	
	
	
	
	function prepare_items() {
        
        $per_page = 20;
        
        
        /**
         * REQUIRED. Now we need to define our column headers. This includes a complete
         * array of columns to be displayed (slugs & titles), a list of columns
         * to keep hidden, and a list of columns that are sortable. Each of these
         * can be defined in another method (as we've done here) before being
         * used to build the value for our _column_headers property.
         */
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        
        
        /**
         * REQUIRED. Finally, we build an array to be used by the class for column 
         * headers. The $this->_column_headers property takes an array which contains
         * 3 other arrays. One for all columns, one for hidden columns, and one
         * for sortable columns.
         */
        $this->_column_headers = array($columns, $hidden, $sortable);
        
               
        
        
		$orderby = (!empty($_REQUEST['orderby'])) ? $_REQUEST['orderby'] : 'Date'; //If no sort, default to date, todo: whitelist
        $order = ($_REQUEST['order'] == 'asc') ?  'asc' : 'desc'; //If no order, default to asc
			
        global $wpdb;
	
		$sql = ' 
		SELECT '.$wpdb->prefix.'ap_transaction.ap_transactionID,
			   '.$wpdb->prefix.'ap_transaction.network,
			   '.$wpdb->prefix.'ap_transaction.Date,
			   date_format('.$wpdb->prefix.'ap_transaction.Date, "%d.%m.%Y - %T") AS germanDate,
			   '.$wpdb->prefix.'ap_transaction.SubId,
			   '.$wpdb->prefix.'ap_transaction.ProgramTitle,
			   '.$wpdb->prefix.'ap_transaction.Transaction,
			   '.$wpdb->prefix.'ap_transaction.Price,
			   '.$wpdb->prefix.'ap_transaction.Commission,
			   '.$wpdb->prefix.'ap_transaction.Confirmed,
			   '.$wpdb->prefix.'ap_transaction.TransactionStatus,
			   '.$wpdb->prefix.'posts.ID AS postID,
			   '.$wpdb->posts.'.post_title
		FROM '.$wpdb->prefix.'ap_transaction
		LEFT JOIN '.$wpdb->posts.'
		ON '.$wpdb->prefix.'ap_transaction.SubId = '.$wpdb->prefix.'posts.ID
		ORDER BY '.$orderby.' '.$order;
		
		$transactionData = $wpdb->get_results($sql, ARRAY_A);
        
                
        /**
         * REQUIRED for pagination. Let's figure out what page the user is currently 
         * looking at. We'll need this later, so you should always include it in 
         * your own package classes.
         */
        $current_page = $this->get_pagenum();
        
        /**
         * REQUIRED for pagination. Let's check how many items are in our data array. 
         * In real-world use, this would be the total number of items in your database, 
         * without filtering. We'll need this later, so you should always include it 
         * in your own package classes.
         */
        $total_items = $wpdb->num_rows;
        
        
        /**
         * The WP_List_Table class does not handle pagination for us, so we need
         * to ensure that the data is trimmed to only the current page. We can use
         * array_slice() to 
         */
        $transactionData = array_slice($transactionData,(($current_page-1)*$per_page),$per_page);
        
        
        
        /**
         * REQUIRED. Now we can add our *sorted* data to the items property, where 
         * it can be used by the rest of the class.
         */
        $this->items = $transactionData;
        
        
        /**
         * REQUIRED. We also have to register our pagination options & calculations.
         */
        $this->set_pagination_args( array(
            'total_items' => $total_items,                  //WE have to calculate the total number of items
            'per_page'    => $per_page,                     //WE have to determine how many items to show on a page
            'total_pages' => ceil($total_items/$per_page)   //WE have to calculate the total number of pages
        ) );
    }
	

}



?>