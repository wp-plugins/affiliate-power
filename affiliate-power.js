jQuery(document).ready(function(){
		
	jQuery("body").delegate("a", "click", function(e){
	
		if (!this.href.match(/^http/) || (this.hostname == location.hostname)) return true;
		if (!this.href.match(/(adcell)|(webmasterplan\.com)|(belboon)|(digistore24)|(#aff=)|(ebay)|(superclix)|(tradedoubler)|(tradetracker)|(webgains)|(zanox)|(click-[0-9]+-[0-9]+)/)) return true;

		e.preventDefault();
		
		var source_url = window.location.href;
		var target_url = jQuery(this).attr('href');
		if (e.target.hasAttribute("target") || this.hasAttribute("target")) var new_window = window.open('about:blank', '_blank');
		
		var data = { action: 'ap_save_clickout', source_url: source_url, target_url: target_url};
		jQuery.post(affiliatePower.ajaxurl, data, function(response) {
			var arr_response = response.split('~');
			if (arr_response[1]) target_url = arr_response[1];
			
			if (new_window) new_window.location.href = target_url;
			else document.location.href = target_url;
		});	
	});
	
});