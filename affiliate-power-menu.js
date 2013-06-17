jQuery(document).ready(function(){

	jQuery('.affiliate-power-hide-infotext').bind('click', function(e) {
		e.preventDefault();
		window.location.href = window.location.href + "&action=affiliate-power-hide-infotext";
	});

	jQuery('#datepicker_from').datepicker({
		dateFormat : 'dd.mm.yy',
		monthNames: [ "Januar", "Februar", "März", "April", "Mai", "Juni", "Juli", "August", "September", "Oktober", "November", "Dezember" ],
		dayNamesMin: [ "So", "Mo", "Di", "Mi", "Do", "Fr", "Sa" ], 
		numberOfMonths: 3,
		onClose: function( selectedDate ) {
			jQuery( "#datepicker_to" ).datepicker( "option", "minDate", selectedDate );
			jQuery( "#datepicker_predefined").val("custom");
		}
	});
	
	jQuery('#datepicker_to').datepicker({
		dateFormat : 'dd.mm.yy',
		monthNames: [ "Januar", "Februar", "März", "April", "Mai", "Juni", "Juli", "August", "September", "Oktober", "November", "Dezember" ],
		dayNamesMin: [ "So", "Mo", "Di", "Mi", "Do", "Fr", "Sa" ],
		numberOfMonths: 3,
		onClose: function( selectedDate ) {
			jQuery( "#datepicker_from" ).datepicker( "option", "maxDate", selectedDate );
			jQuery( "#datepicker_predefined").val("custom");
		}
	});
	
	jQuery( "#datepicker_predefined").bind("change", function() {
		var selected_values = jQuery(this).val();
		var date_from = new Date();
		var date_to   = new Date();
		
		switch (selected_values) {
			case "custom": 
				return;
			case "today":
				break;
			case "yesterday":
				date_from.setDate(date_from.getDate() - 1);
				date_to.setDate(date_to.getDate() - 1);
				break;
			case "last_7_days":
				date_from.setDate(date_from.getDate() - 7);
				date_to.setDate(date_to.getDate() - 1);
				break;
			case "last_30_days":
				date_from.setDate(date_from.getDate() - 30);
				date_to.setDate(date_to.getDate() - 1);
				break;
			case "this_month":
				date_from.setDate(1);
				break;
			case "last_month":
				date_from.setDate(1);
				date_from.setMonth(date_from.getMonth() - 1);
				date_to.setDate(0);
				break;
			case "all":
				date_from = new Date(first_transaction_ts*1000);
				break;
		}
		
		jQuery( "#datepicker_from" ).datepicker( "setDate", date_from );
		jQuery( "#datepicker_to" ).datepicker( "setDate", date_to );
		formDate.submit();
	});
	
	
	
});