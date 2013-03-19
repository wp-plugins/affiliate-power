jQuery(document).ready(function(){
	jQuery('.datepicker').datepicker({
		dateFormat : 'dd.mm.yy',
		monthNames: [ "Januar", "Februar", "März", "April", "Mai", "Juni", "Juli", "August", "September", "Oktober", "November", "Dezember" ],
		dayNamesMin: [ "So", "Mo", "Di", "Mi", "Do", "Fr", "Sa" ] 
	});
});