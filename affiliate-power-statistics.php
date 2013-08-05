<?php
if (!defined('ABSPATH')) die; //no direct access

class Affiliate_Power_Statistics {


	static public function statisticsPage() {
		global $wpdb;
		$options = get_option('affiliate-power-options');
		
		//convert dates for db
		$date_from = isset($_POST['date_from']) && preg_match("/^[0-9]{2}\.[0-9]{2}\.[0-9]{4}$/", $_POST['date_from']) ? $_POST['date_from'] : date('d.m.Y', time()-86400*30);
		$date_to = isset($_POST['date_to']) && preg_match("/^[0-9]{2}\.[0-9]{2}\.[0-9]{4}$/", $_POST['date_to']) ? $_POST['date_to'] : date('d.m.Y', time()-86400);
		$arr_date_from = explode('.', $date_from);
		$arr_date_to = explode('.', $date_to);
		$date_from_db = $arr_date_from[2].'-'.$arr_date_from[1].'-'.$arr_date_from[0];
		$date_to_db = $arr_date_to[2].'-'.$arr_date_to[1].'-'.$arr_date_to[0] . ' 23:59:59';
		
		
		//Top Articles
		$sql = $wpdb->prepare('
		(SELECT
			round(sum('.$wpdb->prefix.'ap_transaction.Commission),2) as commission,
			round(sum('.$wpdb->prefix.'ap_transaction.Confirmed), 2) as confirmed,
			'.$wpdb->posts.'.ID AS postID,
			if (postID = -1, "- - Homepage - -", ifnull('.$wpdb->posts.'.post_title, "- unknown -")) as name
		FROM '.$wpdb->prefix.'ap_transaction 
		LEFT JOIN '.$wpdb->prefix.'ap_clickout
		ON '.$wpdb->prefix.'ap_transaction.SubId = '.$wpdb->prefix.'ap_clickout.ap_clickoutID
		LEFT JOIN '.$wpdb->posts.'
		ON '.$wpdb->prefix.'ap_clickout.postID = '.$wpdb->prefix.'posts.ID
		WHERE '.$wpdb->prefix.'ap_transaction.SubId >= 1000000
		AND TransactionStatus <> "Cancelled"
		AND date BETWEEN %s and %s 
		GROUP BY name )
		UNION
		(SELECT
			round(sum('.$wpdb->prefix.'ap_transaction.Commission),2) as commission,
			round(sum('.$wpdb->prefix.'ap_transaction.Confirmed), 2) as confirmed,
			'.$wpdb->posts.'.ID AS postID,
			ifnull('.$wpdb->posts.'.post_title, "- unknown -") as name
		FROM '.$wpdb->prefix.'ap_transaction 
		LEFT JOIN '.$wpdb->posts.'
		ON '.$wpdb->prefix.'ap_transaction.SubId = '.$wpdb->prefix.'posts.ID
		WHERE '.$wpdb->prefix.'ap_transaction.SubId < 1000000
		AND TransactionStatus <> "Cancelled"
		AND date BETWEEN %s and %s
		GROUP BY name )
		ORDER BY commission DESC
		LIMIT 12', $date_from_db, $date_to_db, $date_from_db, $date_to_db);
		
		$topArticleData = $wpdb->get_results($sql, ARRAY_A);
		
		
		//Top Partner
		$sql = $wpdb->prepare('
		SELECT concat (ProgramTitle, " (", network, ")") as name,
			   round(sum(Commission),2) as commission,
			   round(sum(Confirmed), 2) as confirmed
		FROM '.$wpdb->prefix.'ap_transaction 
		WHERE TransactionStatus <> "Cancelled" 
		AND date BETWEEN %s and %s
		GROUP BY ProgramId, network
		ORDER BY sum(Commission) DESC
		LIMIT 12', $date_from_db, $date_to_db);
		$topPartnerData = $wpdb->get_results($sql, ARRAY_A);
		
		
		//Networks
		$sql = $wpdb->prepare('
		SELECT network as name,
			   round(sum(Commission),2) as commission,
			   round(sum(Confirmed), 2) as confirmed
		FROM '.$wpdb->prefix.'ap_transaction 
		WHERE TransactionStatus <> "Cancelled" 
		AND date BETWEEN %s and %s
		GROUP BY network
		ORDER BY sum(Commission) DESC
		LIMIT 12', $date_from_db, $date_to_db);
		$networkData = $wpdb->get_results($sql, ARRAY_A);
		
		
		$landingData = $refererData = $keywordData = array(
			array(
				'name' => __('<a href="http://www.affiliatepowerplugin.com/premium/" target="_blank">Only in the premium version</a>', 'affiliate-power'),
				'commission' => '0',
				'confirmed' => '0'
			)
		);	

		//Days
		$sql = $wpdb->prepare('
		SELECT date_format(date, "%%d.%%m.%%Y") as name,
			   round(sum(Commission),2) as commission,
			   round(sum(Confirmed), 2) as confirmed
		FROM '.$wpdb->prefix.'ap_transaction 
		WHERE TransactionStatus <> "Cancelled" 
		AND date BETWEEN %s and %s
		GROUP BY date(date)
		ORDER BY date DESC',
		$date_from_db, $date_to_db);

		$dayData = $wpdb->get_results($sql, ARRAY_A);
		
		//Weeks
		$sql = $wpdb->prepare('
		SELECT concat ("KW ", weekofyear(date), ", ", year(date)) as name,
			   round(sum(Commission),2) as commission,
			   round(sum(Confirmed), 2) as confirmed
		FROM '.$wpdb->prefix.'ap_transaction 
		WHERE TransactionStatus <> "Cancelled" 
		AND date BETWEEN %s and %s
		GROUP BY weekofyear(date)
		ORDER BY date DESC',
		$date_from_db, $date_to_db);

		$weekData = $wpdb->get_results($sql, ARRAY_A);
		
		
		
		//Months
		$sql = $wpdb->prepare('
		SELECT concat (
				date_format(date, "%%M"),
				" ", 
				year(date)) 
			   as name,
			   round(sum(Commission),2) as commission,
			   round(sum(Confirmed), 2) as confirmed
		FROM '.$wpdb->prefix.'ap_transaction 
		WHERE TransactionStatus <> "Cancelled" 
		AND date BETWEEN %s and %s
		GROUP BY month(date), year(date)
		ORDER BY year(date) DESC,
				 month(date) DESC',
		$date_from_db, $date_to_db);;

		$monthData = $wpdb->get_results($sql, ARRAY_A);
		

		
		//statistics to create
		$arr_statistics = array(
			'Artikel' => $topArticleData,
			'Partner' => $topPartnerData,
			'Netzwerk' => $networkData,
			'Einstiegsseite' => $landingData,
			'Besucherquelle' => $refererData,
			'Keyword' => $keywordData,
			'Tag' => $dayData,
			'Woche' => $weekData,
			'Monat' => $monthData
		);
		
		$statisticHtml = '';
		$i = 1;
		foreach ($arr_statistics as $headline => $statistic) {
			$statisticHtml .= self::getStatisticHtml($headline, $statistic);
			if ($i % 3 == 0) $statisticHtml .= '<div style="clear:both;">&nbsp;</div>';
			$i += 1;
		}
		echo '<div class="wrap">';
		echo '<div class="icon32" style="background:url('.plugins_url('affiliate-power/img/affiliate-power-36.png').') no-repeat;"><br/></div>';
		_e ('<h2>Affiliate Power Statistics</h2>', 'affiliate-power');
		
		//Check Licence
		if (isset($options['licence-key'])) {
			echo '<div class="error"><p><strong>'.__('You entered a valid licence key but you did not download the premium version yet. Please go to the a href="update-core.php">Update Page</a> and update to the premium version. It can take up to 5 minutes until WordPress notifies you about the new version.', 'affiliate-power').'</strong></p></div>';
		}
		
		//Infotext
		$meta_options = get_option('affiliate-power-meta-options');
		if (isset($meta_options['infotext']) && $meta_options['hide-infotext'] == 0) {
			echo '<div class="updated">'.$meta_options['infotext'].'</div>';
		}
		
		
		//Datepicker
		$dates_predefined = array (
			'custom' => __('Custom', 'affiliate-power'),
			'today' => __('Today', 'affiliate-power'),
			'yesterday' => __('Yesterday', 'affiliate-power'),
			'last_7_days' => __('Last 7 days', 'affiliate-power'),
			'last_30_days' => __('Last 30 days', 'affiliate-power'),
			'all' => __('All', 'affiliate-power')
		);
		$dates_predefined_options = '';
		foreach ($dates_predefined as $value => $text) {
			$dates_predefined_options .= '<option value="'.$value.'"';
			if (
				!isset($_POST['datepicker_predefined']) && $value == 'last_30_days' || 
				isset($_POST['datepicker_predefined']) && $value == $_POST['datepicker_predefined']) {
					$dates_predefined_options .= ' selected="selected"';
			}
			$dates_predefined_options .= '>'.$text.'</option>';
		}
		$first_transaction = $wpdb->get_var('SELECT unix_timestamp(Date) FROM '.$wpdb->prefix.'ap_transaction ORDER BY Date ASC LIMIT 1');
		
		
		//output
		echo '
			<script type="text/javascript">
			var first_transaction_ts = "'.$first_transaction.'";
			</script>
			<form method="post" action="" name="formDate"><p>
				'.__('From', 'affiliate-power').': <input type="text" name="date_from" id="datepicker_from" value="'.esc_attr($date_from).'" /> '.__('Till', 'affiliate-power').':  <input type="text" name="date_to" id="datepicker_to" value="'.esc_attr($date_to).'" /> <input type="submit" value="OK" /><br />
				'.__('Period', 'affiliate-power').': <select id="datepicker_predefined" name="datepicker_predefined">
					'.$dates_predefined_options.'
				</select>
				
			</p></form>';
		echo $statisticHtml;
		echo '</div>';
	
	}
	
	
	function getStatisticHtml($headline, $statistic) {
		$html = ' 
			<div style="width:30%; float:left; margin-right:20px;">
				<h3>'.$headline.'</h3>
				<table class="widefat" style="border-color:#666">
					<thead>
						<tr>
							<th>'.$headline.'</th>
							<th>'.__('Income', 'affiliate-power').'</th> 
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th>'.$headline.'</th>
							<th>'.__('Income', 'affiliate-power').'</th>
						</tr>
					</tfoot>
					<tbody>';
		   
		foreach ($statistic as $row) {
		    $total_earning = number_format($row['commission'], 2, ',', '.');
			$confirmed_earning = number_format($row['confirmed'], 2, ',', '.');
			$output_earnings = $total_earning . ' € (<span style="color:green;">'.$confirmed_earning.' €</span>)';
		    $html .= '<tr><td>'.$row['name'].'</td><td>'.$output_earnings.'</td></tr>';
		}
			
		$html .= '
					</tbody>
				</table>
			</div>';
			
		return $html;
	}
}
		
?>