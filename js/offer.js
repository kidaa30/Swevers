// @codekit-prepend "vendor/jquery.uniform.min.js";
// @codekit-prepend "vendor/bootstrap-3.0.3.min.js";
// @codekit-prepend "vendor/bootstrap-multiselect.js";
// @codekit-prepend "vendor/cookie.js";
// @codekit-prepend "vendor/jquery.matchHeight-min.js";

$(document).ready(function() {

	$('select').uniform();
	$('.property-wrapper').matchHeight();

	var selects = {
		types : {
			nonSelectedText : 'alle types',
			allSelectedText : 'alle types',
			nSelectedText   : 'types',
			mobilelText     : 'kies uw types',
			selectAllText   : 'alles selecteren'
		},
		cities : {
			nonSelectedText : 'alle gemeentes',
			allSelectedText : 'alle gemeentes',
			nSelectedText   : 'gemeentes',
			mobilelText     : 'kies uw gemeentes',
			selectAllText   : 'alles selecteren'
		}
	}

	if ($('html').hasClass('touch')) {
		$('select[multiple]').addClass('visible');
		$('select[multiple]').each(function() {
    		var ids = $(this).attr('id');
			$(this).wrap('<div class="selector"></div>');
			$(this).before('<span>'+selects[ids].mobilelText+'</span>');
			$(this).change(function() {
				if (!$(this).val() || $(this).val().length == 0) $(this).siblings('span').text($(this).data('selectedall') ? $(this).data('selectedall') : selects[ids].mobilelText);
				else if ($(this).val().length == $(this).find('option').length) $(this).siblings('span').text($(this).data('selectedall') ? $(this).data('selectedall') : selects[ids].allSelectedText);
				else $(this).siblings('span').text($(this).val().length + selects[ids].nSelectedText);
			}).change();
		});
	} else {
		$('select[multiple]').each(function() {
			var ids = $(this).attr('id');
			var numberDisplayed =  (ids == 'rooms' ? 5 : 1);

			$(this).multiselect({
				onBuild: function(multiselect) {
					multiselect.$ul.find('input').uniform();
					if (multiselect.$ul.find("input:checkbox:not(:checked)").length == 0) multiselect.$button.text(multiselect.$select.data('selectedall') ? multiselect.$select.data('selectedall') : selects[ids].nonSelectedText);
				},
				onChange: function(item, checked, multiselect) {
					multiselect.$ul.find('input').uniform('update');
					if (multiselect.$ul.find("input:checkbox:not(:checked)").length == 0) multiselect.$button.text(multiselect.$select.data('selectedall') ? multiselect.$select.data('selectedall') : selects[ids].nonSelectedText);
				},
				includeSelectAllOption: true,
				numberDisplayed: numberDisplayed,
				nSelectedText: selects[ids].nSelectedText,
				nonSelectedText: selects[ids].nonSelectedText,
				dropRight: $(this).hasClass('dropRight'),
				selectAllText: selects[ids].selectAllText
			});
		});
	}

	$('#display a').fastClick(function(){
		if ($(this).hasClass('selected')) return false;

		var display = $(this).data('display');
		var cookiename = 'display';
		$.cookie(cookiename, display, { expires: 30, path: '/' });

		$('#display a.selected').removeClass('selected');
		$(this).addClass('selected');

		$('#properties .property-wrapper').stop(true,false).animate({
			opacity: 0
		},150,function(){
			$('#properties').removeClass().addClass('grid display-'+display);
			$('#properties .property-wrapper').animate({
				opacity: 1
			},150);
			$('.property-wrapper').matchHeight();
		});
	});
});