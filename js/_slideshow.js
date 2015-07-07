var j; // first item shown
var k; // next item to show
var pause = false; // set to true when user clicks a carouselbutton
var interval = 4000; // set interval of carousel in milliseconds (5000 = 5s)

$(document).ready(function() {
	$("#navigation button").click(function(event) {
		showCarouselItem($(this).index());
		pause = true; // pause when user clicks a button
	});
	$('#more-info').click(function(event) {
		if(pause == true) pause = false;
		else pause = true;
		return false;
	});
});

function showCarouselItem(i) {
	if (j != i) { // do nothing when current item is shown already
		
		var number = $("#slideshow .slide").length; // number of carouselitems
		(i+1) == number ? k = 0 : k = (i+1); // numbr of item to show after "next"
		
		var prev = $("#slideshow .slide").eq(j); // last shown item
		var next = $("#slideshow .slide").eq(i); // item to show on animation
		
		next.addClass("active").css({'opacity':1, 'z-index':50});
		prev.removeClass("active").css({'opacity':0, 'z-index':60}); // set classes for css transitions

		$("#navigation button").removeClass("active");
		$("#navigation button").eq(i).addClass("active");
		
		j = i; // update j for next animation

	} // if (j != i)

	setTimeout(function () {
		if (typeof p != 'undefined') { if (p == true) pause = false; }
		if (!pause) { showCarouselItem(k); } 
	}, interval)
}

$(window).load(function() {
	showCarouselItem(0);
	$("#slideshow").mouseenter(function () { pause = true; });
	$("#slideshow").mouseleave(function () { pause = false; });
});