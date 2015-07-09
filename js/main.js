//@codekit-prepend "fancybox/jquery.fancybox.js";
//@codekit-prepend "fancybox/helpers/jquery.fancybox-media.js";
//@codekit-prepend "fancybox/helpers/jquery.fancybox-buttons.js";
//@codekit-prepend "vendor/jQuery.fastClick.js";

$(function(){
	
	$("a.youtube").click(function(){
		$.fancybox({
			type 		: 'iframe',
			fitToView	: true,
			autoSize	: true,
			closeClick	: false,
			width		: 720,
			height		: 405,
			openEffect	: 'fade',
			closeEffect	: 'fade',
			aspectRatio : true,
			href		: this.href,
			helpers		: {
				overlay : {
		            css : {
			            'background' : 'rgba(30,30,30,0.8)'
		            }
		        },
				media : {}
			}
		});
		return false;
	});
	
	$("a.fancybox").fancybox({
		closeBtn		: true,
		helpers		: {
			buttons	: { position: 'bottom'},
			overlay : {
	            css : {
		            'background' : 'rgba(30,30,30,0.8)'
	            },
	            locked : false
	        }
		}
	});
		
	$('form').submit(function(event){
		$(this).find('input.invalid,textarea.invalid,select.invalid').removeClass('invalid');
		$(this).find('input[required],textarea[required],select[required]').each(function(){
			if ($(this).attr('type') == 'email' && !validateEmail($(this).val())) $(this).addClass('invalid');
			else if (!$(this).val()) $(this).addClass('invalid');
		});
		if ($(this).find('input.invalid,textarea.invalid,select.invalid').length > 0) {
			alert("Gelieve alle velden correct in te vullen.");
			$(this).find('input.invalid,textarea.invalid,select.invalid').first().focus();
			event.stopImmediatePropagation();
			return false;
		}
	});

	$('.mobile-menu').fastClick(function(){
		$(this).toggleClass('active');
		$('nav[role="navigation"]').toggleClass('active');
		return false;
	});
	
});

function validateEmail(elementValue){
	var emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;
	return emailPattern.test(elementValue);
}

function l(strings) {
	if ($('body').hasClass('fr')) return strings.fr;
	else if ($('body').hasClass('en')) return strings.en;
	else return strings.nl;
}