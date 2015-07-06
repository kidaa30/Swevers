//@codekit-prepend "_slideshow.js";

$(document).ready(function() {

	$('#top-button').fastClick(function(event){
		$('body').animate({
			scrollTop: $(window).height() + 100},
			300);
		event.preventDefault();
		return false;
	});
	
	$.each($('.properties-wrapper'), function(index, val) {
		$(this).children('li:gt(0)').hide();
		if($(this).children('li').length > 1) propertiesCarousel($(this));
	});

	$(window).scroll(function(event) {
		if($(window).scrollTop() > $(window).height() - $('header[role="banner"]').height() - $(window).height() / 20)$('header[role="banner"], #slideshow').addClass('scrolling');
		else $('header[role="banner"], #slideshow').removeClass('scrolling');
		$('#slideshow').css({
			top: -$(window).scrollTop()/2
		});
	});
});

function propertiesCarousel(wrapper){
	setInterval(function() { 
	  $(wrapper).find('li:first')
		.fadeOut(1000)
		.next()
		.fadeIn(1000)
		.end()
		.appendTo($(wrapper));
	},  3000);	
}