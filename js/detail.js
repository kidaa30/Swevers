//@codekit-prepend "_slideshow.js";
//@codekit-prepend "vendor/jquery.scrollTo.min.js";
// @codekit-prepend "vendor/infobox.js";

$(document).ready(function() {
	$('#more-info').click(function(){
		$('#more-info, #detail, header[role="property"]').toggleClass('open');
		$(this).find('span').text(function(i, text){
        	return text === "meer info" ? "minder info" : "meer info";
      	})
		return false;
	});

	$("#thumbnails .thumbnails-wrapper").css({
			height: Math.ceil($("#thumbnails .row").length/4)*100+'%'
		});

	if ($("#thumbnails a").length > 6) {

		$("#thumbnails .thumbnails-scrollwindow").scroll(function(){
			if ($(this).scrollTop() > 0){
				$(".arrow.top").removeClass('disabled');
			} else {
				$(".arrow.top").addClass('disabled');
			}
			if ($(this).scrollTop() < $(this).find('.thumbnails-wrapper').height() - $(this).height()){
				$(".arrow.bottom").removeClass('disabled');
			} else {
				$(".arrow.bottom").addClass('disabled');
			}
		});

		$(".arrow.top").click(function(){
			$("#thumbnails .thumbnails-scrollwindow").stop().scrollTo('-='+($("#thumbnails .thumbnails-scrollwindow").height()),300, {axis:'y'} );
		});

		$(".arrow.bottom").click(function(){
			$("#thumbnails .thumbnails-scrollwindow").stop().scrollTo('+='+($("#thumbnails .thumbnails-scrollwindow").height()),300, {axis:'y'} );
		});
	}

	if (typeof coord_lat !== 'undefined' && typeof coord_lon !== 'undefined' && coord_lat && coord_lon) {
		var latlng = new google.maps.LatLng(coord_lat, coord_lon);
		var myOptions = {
				zoom: 15,
				center: latlng,
				mapTypeId: google.maps.MapTypeId.ROADMAP,
				streetViewControl: true,
				mapTypeControl: false,
				scrollwheel: false,
				mapTypeControl: true,
				panControl: !$('html').hasClass('no-touch'),
				draggable: $('html').hasClass('no-touch'),
				mapTypeControlOptions: {
						style: google.maps.MapTypeControlStyle.DEFAULT,
						mapTypeIds: ["roadmap", "hybrid"]
				},
				zoomControlOptions: {
						style: google.maps.ZoomControlStyle.SMALL,
						position: google.maps.ControlPosition.LEFT_TOP
				}
		};
		var map = new google.maps.Map($('#map-canvas')[0], myOptions);
		if (showMarker) {
				var infoBox = new InfoBox({
						latlng: latlng,
						map: map,
						content: '<div class="content">' + markerContent + '</div><div class="marker"></div>'
				});
		}
		$(window).resize(function() {
				map.setCenter(latlng);
				$('#mapbox').width($(window).width() - 70);
		});
	} else {
			$('#map-canvas').hide();
	}
});