jQuery(document).ready(function(){

	var ap_art;

    jQuery("a").bind("click", function(e){
		
		if (ap_art = jQuery(this).attr("ap_art")) {
			var data = { action: 'ap_save_article_in_session', ap_art: ap_art};
			jQuery.post(affiliatePower.ajaxurl, data, function(response) {
				return true;
			});
		}
	});
	
});