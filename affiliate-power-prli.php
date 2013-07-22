<?php
if (!defined('ABSPATH')) die; //no direct access


//add SubIds on redirect
add_filter('prli_target_url', array('Affiliate_Power_Prli', 'saveClickout'), 110); //priority has to be >99 to work with the feature "Target URL Rotation" of Prli Pro

	

class Affiliate_Power_Prli {

	
	static public function saveClickout ($arrLinkInfo) {
		
		$source_url = esc_url_raw($_SERVER['HTTP_REFERER']);
		$target_url = $arrLinkInfo['url'];
		
		if (!empty($source_url)) {
			$new_target_url = Affiliate_Power::saveClickout($source_url, $target_url);
			$arrLinkInfo['url'] = $new_target_url;
		}
		
		return $arrLinkInfo;
	}

}

	
?>