
jQuery(document).ready(function(){


ajax_wp = function (date) {
	var operation = jQuery('#smd-datepicker').multiDatesPicker("getDates").includes(date) ? 'ins' : 'del';
	var data = {
		'action': 'save_my_dates',
		'date': date,
		'operation': operation
	};
	jQuery.post(ajaxurl, data, function(response) {
		alert(response);
	});

	

}

  if ( loc_vars.marked_dates.length > 0 ) {
		jQuery('#smd-datepicker').multiDatesPicker( {
			dateFormat: "yy-mm-dd",
			addDates: loc_vars.marked_dates.map( ( a, b, c ) => ( a.date  )),
			onSelect: ajax_wp
		} );
	} else {
		jQuery('#smd-datepicker').multiDatesPicker( {
			dateFormat: "yy-mm-dd",
			onSelect: ajax_wp
	  } );
	}



});

