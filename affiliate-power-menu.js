jQuery(document).ready(function(){

	jQuery('.affiliate-power-hide-infotext').bind('click', function(e) {
		e.preventDefault();
		window.location.href = window.location.href + "&action=affiliate-power-hide-infotext";
	});

	jQuery('#datepicker_from').datepicker({
		dateFormat : 'dd.mm.yy',
		monthNames: [ objL10n.month1, objL10n.month2, objL10n.month3, objL10n.month4, objL10n.month5, objL10n.month6, objL10n.month7, objL10n.month8, objL10n.month9, objL10n.month10, objL10n.month11, objL10n.month12 ],
		dayNamesMin: [ objL10n.day1, objL10n.day2, objL10n.day3, objL10n.day4, objL10n.day5, objL10n.day6, objL10n.day7 ], 
		numberOfMonths: 3,
		onClose: function( selectedDate ) {
			jQuery( "#datepicker_to" ).datepicker( "option", "minDate", selectedDate );
			jQuery( "#datepicker_predefined").val("custom");
		}
	});
	
	jQuery('#datepicker_to').datepicker({
		dateFormat : 'dd.mm.yy',
		monthNames: [ objL10n.month1, objL10n.month2, objL10n.month3, objL10n.month4, objL10n.month5, objL10n.month6, objL10n.month7, objL10n.month8, objL10n.month9, objL10n.month10, objL10n.month11, objL10n.month12 ],
		dayNamesMin: [ objL10n.day1, objL10n.day2, objL10n.day3, objL10n.day4, objL10n.day5, objL10n.day6, objL10n.day7 ], 
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