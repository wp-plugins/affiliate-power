<?php

//add SubIds on redirect
add_filter('prli_target_url', array('Affiliate_Power_Prli', 'saveClickout'));

	

class Affiliate_Power_Prli {

	
	static public function saveClickout ($arrLinkInfo) {
		
		//if (!session_id()) session_start();
		$source_url = esc_url_raw($_SERVER['HTTP_REFERER']);
		$target_url = $arrLinkInfo['url'];
		//$ap_art = (int)$_SESSION['ap_art'];
		
		if (!empty($source_url)) {
			$new_target_url = Affiliate_Power::saveClickout($source_url, $target_url);
			$arrLinkInfo['url'] = $new_target_url;
		}
		
		return $arrLinkInfo;
	}

}

	
?>