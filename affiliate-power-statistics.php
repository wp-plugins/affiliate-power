<?php

class Affiliate_Power_Statistics {


	static public function statisticsPage() {
		global $wpdb;
		$options = get_option('affiliate-power-options');
		
		$date_from = isset($_POST['date_from']) && preg_match("/^[0-9]{2}\.[0-9]{2}\.[0-9]{4}$/", $_POST['date_from']) ? $_POST['date_from'] : date('d.m.Y', time()-86400*30);
		$date_to = isset($_POST['date_to']) && preg_match("/^[0-9]{2}\.[0-9]{2}\.[0-9]{4}$/", $_POST['date_to']) ? $_POST['date_to'] : date('d.m.Y');
		$arr_date_from = explode('.', $date_from);
		$arr_date_to = explode('.', $date_to);
		$date_from_db = $arr_date_from[2].'-'.$arr_date_from[1].'-'.$arr_date_from[0];
		$date_to_db = $arr_date_to[2].'-'.$arr_date_to[1].'-'.$arr_date_to[0] . ' 23:59:59';
		
		
		//Top Articles
		$sql = $wpdb->prepare('
		SELECT
			round(sum('.$wpdb->prefix.'ap_transaction.Commission),2) as commission,
			round(sum('.$wpdb->prefix.'ap_transaction.Confirmed), 2) as confirmed,
			'.$wpdb->posts.'.ID AS postID,
			ifnull('.$wpdb->posts.'.post_title, "- unbekannt -") as name
		FROM '.$wpdb->prefix.'ap_transaction 
		LEFT JOIN '.$wpdb->prefix.'ap_clickout
		ON '.$wpdb->prefix.'ap_transaction.SubId = '.$wpdb->prefix.'ap_clickout.ap_clickoutID
		LEFT JOIN '.$wpdb->posts.'
		ON '.$wpdb->prefix.'ap_clickout.postID = '.$wpdb->prefix.'posts.ID
		OR '.$wpdb->prefix.'ap_transaction.SubId = '.$wpdb->prefix.'posts.ID AND '.$wpdb->prefix.'posts.ID < 1000000
		WHERE TransactionStatus <> "Cancelled"
		AND date BETWEEN %s and %s
		GROUP BY name 
		ORDER BY sum('.$wpdb->prefix.'ap_transaction.Commission) DESC
		LIMIT 12', $date_from_db, $date_to_db);
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
				'name' => '<a href="http://www.j-breuer.de/wordpress-plugins/affiliate-power/#premium" target="_blank">Nur in der Premium Version</a>',
				'commission' => '0',
				'confirmed' => '0'
			)
		);

		/*
		//Last Days
		$sql = '
		SELECT date_format(date, "%d.%m.%Y") as name,
			   round(sum(Commission),2) as commission,
			   round(sum(Confirmed), 2) as confirmed
		FROM '.$wpdb->prefix.'ap_transaction 
		WHERE TransactionStatus <> "Cancelled" 
		GROUP BY date(date)
		ORDER BY date DESC
		LIMIT 12';
		$dayData = $wpdb->get_results($sql, ARRAY_A);
		
		//Last Weeks
		$sql = '
		SELECT concat ("KW ", weekofyear(date)) as name,
			   round(sum(Commission),2) as commission,
			   round(sum(Confirmed), 2) as confirmed
		FROM '.$wpdb->prefix.'ap_transaction 
		WHERE TransactionStatus <> "Cancelled" 
		GROUP BY weekofyear(date)
		ORDER BY date DESC
		LIMIT 12';
		$weekData = $wpdb->get_results($sql, ARRAY_A);
		
		
		
		//Last Months
		$sql = '
		SELECT concat (monthname(date), " ", year(date)) as name,
			   round(sum(Commission),2) as commission,
			   round(sum(Confirmed), 2) as confirmed
		FROM '.$wpdb->prefix.'ap_transaction 
		WHERE TransactionStatus <> "Cancelled" 
		GROUP BY month(date), year(date)
		ORDER BY year(date) DESC,
				 month(date) DESC
		LIMIT 12';
		$monthData = $wpdb->get_results($sql, ARRAY_A);
		*/
		

		
		//statistics to create
		$arr_statistics = array(
			'Artikel' => $topArticleData,
			'Partner' => $topPartnerData,
			'Netzwerk' => $networkData,
			'Einstiegsseite' => $landingData,
			'Besucherquelle' => $refererData,
			'Keyword' => $keywordData
		);
		
		$statisticHtml = '';
		$i = 1;
		foreach ($arr_statistics as $headline => $statistic) {
			$statisticHtml .= self::getStatisticHtml($headline, $statistic);
			if ($i % 3 == 0) $statisticHtml .= '<div style="clear:both;">&nbsp;</div>';
			$i += 1;
		}
		echo '<h2>Statistiken</h2>';
		
		
		echo '<form method="post" action=""><p>Von: <input type="date" name="date_from" class="datepicker" value="'.esc_attr($date_from).'" /> Bis:  <input type="date" name="date_to" class="datepicker" value="'.esc_attr($date_to).'" /> <input type="submit" value="OK" /></p></form>';
		echo $statisticHtml;
	
	}
	
	
	function getStatisticHtml($headline, $statistic) {
		$html = ' 
			<div style="width:30%; float:left; margin-right:20px;">
				<h3>'.$headline.'</h3>
				<table class="widefat">
					<thead>
						<tr>
							<th>'.$headline.'</th>
							<th>Einnahmen</th>      
						</tr>
					</thead>
					<tfoot>
						<tr>
							<th>'.$headline.'</th>
							<th>Einnahmen</th>
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