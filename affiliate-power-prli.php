<?php

//always add SubIds on redirect
add_filter('prli_target_url', array('Affiliate_Power_Prli', 'addSubIds'));


//to track prli-links on the blog homepage, we have to add an attribute with the article id and save that one in the session
$options = get_option('affiliate-power-options');
if ($options['prli-homepage'] == 1 ) {
	add_action('wp_enqueue_scripts', array('Affiliate_Power_Prli', 'addJs'));
	add_filter('the_content', array('Affiliate_Power_Prli', 'addArtId'));
	add_action('wp_ajax_ap_save_article_in_session', array('Affiliate_Power_Prli', 'saveArticleInSession'));
	add_action('wp_ajax_nopriv_ap_save_article_in_session', array('Affiliate_Power_Prli', 'saveArticleInSession'));
}

	

class Affiliate_Power_Prli {

	static public function addJs() {
		wp_enqueue_script('affiliate-power', plugins_url('affiliate-power-pretty-link.js', __FILE__));
		wp_localize_script( 'affiliate-power', 'affiliatePower', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
	}
	
	
	static public function addArtId ($content) {
		
		$id = get_the_ID();
		
		$content = preg_replace("@<a(.*?)>@i", "<a$1 ap_art=\"".$id."\">", $content);
		return $content;
	}
	
	
	static public function saveArticleInSession() {
		$_SESSION['ap_art'] = (int)$_POST['ap_art'];
		die();
	}
	
	
	static public function addSubIds ($arrLinkInfo) {
		
		$options = get_option('affiliate-power-options');
		if ($options['add-sub-ids'] === 0) {
			return $arrLinkInfo;
		}
		
		$referer = esc_url($_SERVER['HTTP_REFERER']);
		$id = url_to_postid($referer);
		if ($id == 0) $id = (int)$_SESSION['ap_art'];
		
		$link = $arrLinkInfo['url'];
		
		//affili.net
		if (strpos($link, 'partners.webmasterplan.com')) {
			$link .= '&subid='.$id; 
		}
		
		//zanox
		elseif (strpos($link, "zanox")) {
			$link = str_replace("T", "S".$id."T", $link);
		}
		
		//tradedoubler
		elseif (strpos($link, "tradedoubler")) {
			$link .= '&epi='.$id; 
		}

		
		$arrLinkInfo['url'] = $link;
		return $arrLinkInfo;
	}

}

	
?>