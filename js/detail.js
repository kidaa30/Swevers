//@codekit-prepend "_slideshow.js";

$(document).ready(function() {
	$('#more-info').click(function(){
		$('#detail, header[role="property"]').toggleClass('open');
		return false;
	});
});