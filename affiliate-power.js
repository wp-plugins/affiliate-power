jQuery(document).ready(function(){
		
	jQuery.expr[':'].external = function(obj){
    return !obj.href.match(/^mailto\:/)
            && (obj.hostname != location.hostname);
	};

    jQuery("a:external").bind("click", function(e){

		e.preventDefault();
		
		var source_url = window.location.href;
		var target_url = jQuery(this).attr('href');
		//var ap_art = jQuery(this).attr('ap_art');
		//if (ap_art == undefined) ap_art = 0;
		if (e.target.hasAttribute("target")) var new_window = window.open('about:blank', '_blank');
		
		var data = { action: 'ap_save_clickout', source_url: source_url, target_url: target_url};
		jQuery.post(affiliatePower.ajaxurl, data, function(response) {
			var arr_response = response.split('~');
			affiliatePower.nonce_clickout = arr_response[0];
			if (arr_response[1]) target_url = arr_response[1];
			
			if (new_window) new_window.location.href = target_url;
			else document.location.href = target_url;
		});	
	});
});