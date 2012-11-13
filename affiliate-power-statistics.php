<?php

class Affiliate_Power_Statistics {


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
		LIMIT 12');
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
		LIMIT 12');
		$topPartnerData = $wpdb->get_results($sql, ARRAY_A);
		
		
		//Networks
		$sql = $wpdb->prepare('
		SELECT network,
			   round(sum(Commission),2) as commission,
			   round(sum(Confirmed), 2) as confirmed
		FROM '.$wpdb->prefix.'ap_transaction 
		WHERE TransactionStatus <> "Cancelled" 
		GROUP BY network
		ORDER BY sum(Commission) DESC
		LIMIT 12');
		$networkData = $wpdb->get_results($sql, ARRAY_A);
		
		
		//Last Days
		$sql = $wpdb->prepare('
		SELECT date_format(date, "%%d.%%m.%%Y") as date_de,
			   round(sum(Commission),2) as commission,
			   round(sum(Confirmed), 2) as confirmed
		FROM '.$wpdb->prefix.'ap_transaction 
		WHERE TransactionStatus <> "Cancelled" 
		GROUP BY date(date)
		ORDER BY date DESC
		LIMIT 12');
		$dayData = $wpdb->get_results($sql, ARRAY_A);
		
		//Last Weeks
		$sql = $wpdb->prepare('
		SELECT concat ("KW ", weekofyear(date)) as week,
			   round(sum(Commission),2) as commission,
			   round(sum(Confirmed), 2) as confirmed
		FROM '.$wpdb->prefix.'ap_transaction 
		WHERE TransactionStatus <> "Cancelled" 
		GROUP BY weekofyear(date)
		ORDER BY date DESC
		LIMIT 12');
		$weekData = $wpdb->get_results($sql, ARRAY_A);
		
		
		
		//Last Months
		$sql = $wpdb->prepare('
		SELECT concat (monthname(date), " ", year(date)) as month_year,
			   round(sum(Commission),2) as commission,
			   round(sum(Confirmed), 2) as confirmed
		FROM '.$wpdb->prefix.'ap_transaction 
		WHERE TransactionStatus <> "Cancelled" 
		GROUP BY month(date), year(date)
		ORDER BY year(date) DESC,
				 month(date) DESC
		LIMIT 12');
		$monthData = $wpdb->get_results($sql, ARRAY_A);
		
		?>
		<h2>Statistiken</h2>
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
		<h3>Netzwerke</h3>
		<table class="widefat">
			<thead>
				<tr>
					<th>Netzwerk</th>
					<th>Einnahmen</th>      
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th>Netzwerk</th>
					<th>Einnahmen</th>
				</tr>
			</tfoot>
			<tbody>
			   <?php 
			   foreach ($networkData as $network) {
			     $total_earning = number_format($network['commission'], 2, ',', '.');
				 $confirmed_earning = number_format($network['confirmed'], 2, ',', '.');
				 $output_earnings = $total_earning . ' € (<span style="color:green;">'.$confirmed_earning.' €</span>)';
			     echo ('<tr><td>'.$network['network'].'</td><td>'.$output_earnings.'</td></tr>');
			   }
			   ?>
			</tbody>
		</table>
		</div>
		
		<div style="clear:both;">&nbsp;</div>
		
		<div style="width:30%; float:left; margin-right:20px;">
		<h3>Die letzten Tage</h3>
		<table class="widefat">
			<thead>
				<tr>
					<th>Tag</th>
					<th>Einnahmen</th>      
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th>Tag</th>
					<th>Einnahmen</th>
				</tr>
			</tfoot>
			<tbody>
			   <?php 
			   foreach ($dayData as $day) {
			     $total_earning = number_format($day['commission'], 2, ',', '.');
				 $confirmed_earning = number_format($day['confirmed'], 2, ',', '.');
				 $output_earnings = $total_earning . ' € (<span style="color:green;">'.$confirmed_earning.' €</span>)';
			     echo ('<tr><td>'.$day['date_de'].'</td><td>'.$output_earnings.'</td></tr>');
			   }
			   ?>
			</tbody>
		</table>
		</div>
		
		<div style="width:30%; float:left; margin-right:20px;">
		<h3>Die letzten Wochen</h3>
		<table class="widefat">
			<thead>
				<tr>
					<th>Woche</th>
					<th>Einnahmen</th>      
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th>Woche</th>
					<th>Einnahmen</th>
				</tr>
			</tfoot>
			<tbody>
			   <?php 
			   foreach ($weekData as $week) {
			     $total_earning = number_format($week['commission'], 2, ',', '.');
				 $confirmed_earning = number_format($week['confirmed'], 2, ',', '.');
				 $output_earnings = $total_earning . ' € (<span style="color:green;">'.$confirmed_earning.' €</span>)';
			     echo ('<tr><td>'.$week['week'].'</td><td>'.$output_earnings.'</td></tr>');
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


}




?>