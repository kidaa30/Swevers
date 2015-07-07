//@codekit-prepend "_slideshow.js";

$(document).ready(function() {
	$('#more-info').click(function(){
		$('#more-info, #detail, header[role="property"]').toggleClass('open');
		$(this).find('span').text(function(i, text){
        	return text === "Meer info" ? "Minder info" : "Meer info";
      	})
		return false;
	});
});