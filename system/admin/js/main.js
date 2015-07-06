$(function() {
	var sortingAllowed = true;
	$("table.sortable,ul.sortable").each(function(){
		var sortable = $(this);
		$(this).sortable({
	        handle: ".sort-handle",
	        tolerance: "pointer",
	        items: $(this).children('*[data-id]'),
	        update: function(event, ui) {
	        	if (!sortingAllowed) return;
	        	var i = 0;
	        	var parent = ui.item.parents('.sortable');
	        	parent.find('td:first-child input[type="hidden"]').each(function(index,el){
	        		$(el).val(++i);
	        	});
	        	var objectname = parent.data('objectname');
	        	if (!objectname) return;
	        	var page = parent.data('page');
	        	var data = parent.find('input[type="hidden"]').serializeArray();
	        	data.push({
		        	name: 'page',
		        	value: page
	        	});
	        	$.ajax({
	        		type: "POST",
	        		url: admin+"ajax_sort/"+objectname+'/',
	        		data: data
	        	});
	        },
	        start: function(event, ui) {
	            var item = $(ui.item[0]);
	            $(this).find('tr[data-root="'+item.data('id')+'"]').hide();
	            sortable.sortable( "refreshPositions" );
	            var objectlist = $(this).parents('.object-list');
	            objectlist.find('.object-pagination a[data-page]:not(.active)').addClass('droptarget');
	        },
	        stop: function(event, ui) {
	            var item = $(ui.item[0]);
	            $(this).find('tr[data-root="'+item.data('id')+'"]').show();
	            var objectlist = $(this).parents('.object-list');
	            objectlist.find('.object-pagination a.droptarget').removeClass('droptarget');
	        }
	    });
	});
    $('.object-pagination a[data-page]').droppable({
		tolerance: "pointer",
		accept: ".sortable tr",
		hoverClass: "ui-state-hover",
		drop: function( event, ui ) {
			if ($(this).hasClass('active')) return;
			sortingAllowed = false;
			var table = $(this).parents('.object-list').find('table.list');
			var objectname = table.data('objectname');
		    var parentname = table.data('parentname');
		    var parentid = table.data('parentid');
			var page = parseInt($(this).text(),10);
			var id = ui.draggable.data('id');			
		    table.find('tbody').stop(true,true).animate({
			    opacity: 0
		    },200);
			ui.draggable.css({
				opacity: 0
			});
			table.data('page',page);
			$.ajax({
				type: "POST",
				url: admin+"ajax_move_to_page/"+objectname+'/',
				data: 'page='+page+'&id='+id+'&parentname='+parentname+'&parentid='+parentid,
				success: function (html) {
		            table.find('tbody').remove();
		            table.find('thead').after(html);
		            table.find('tbody').css({
					    opacity: 0
				    });
		            var tr = table.find('tr[data-id="'+id+'"]');
		            tr.animate({
			            backgroundColor: '#265db8',
			            color: '#fff'
		            },300).animate({
			            backgroundColor: '#fff',
			            color: '#666'
		            },600);
		            table.find('tbody').stop(true,true).animate({
					    opacity: 1
				    },200);
				    sortingAllowed = true;
		        }
			});    
		    $(this).parents('.object-list').find('.object-pagination a.active').removeClass('active');
		    $(this).parents('.object-list').find('.object-pagination a[data-page="'+page+'"]').addClass('active');
		    formatPaginations($(this).parents('.object-list').find('.object-pagination'));
		}
    });
    $('.object-filter .search .button').click(function(){
    
    	var objectList = $(this).parents('.object-list');
    
	    objectList.find('.object-pagination a.active').removeClass('active');
	    objectList.find('.object-pagination a[data-page="1"]').addClass('active');
	    formatPaginations(objectList.find('.object-pagination'));
	    
	    doFilter(objectList,1);
	    return false;
    });
    $('.object-filter .search input').keyup(function(event){
	    if (event.which == 13) {
	    	var objectList = $(this).parents('.object-list');
    
		    objectList.find('.object-pagination a.active').removeClass('active');
		    objectList.find('.object-pagination a[data-page="1"]').addClass('active');
		    formatPaginations(objectList.find('.object-pagination'));
	    
	    	doFilter(objectList,1);
	    	
			$(this).blur();
			event.preventDefault();
	    	return false;
	    }
    });
    $('.object-filter .search input').keydown(function(event){
	    if (event.which == 13) {
			event.preventDefault();
	    	return false;
	    }
    });
    $('.object-filter .search input').keypress(function(event){
	    if (event.which == 13) {
			event.preventDefault();
	    	return false;
	    }
    });
    
    $('.object-filter .reset a').click(function(){
    	var objectList = $(this).parents('.object-list');
        objectList.find('.object-pagination').slideDown(200);
    	var filter = objectList.find('.object-filter');
    	objectList.data('filtering',false);
	    filter.find('.reset').animate({
	    	'width' : 0
    	},500,'easeOutCubic');
    	filter.find('.search input').val('');
    	objectList.find('.object-pagination a.active').removeClass('active');
    	objectList.find('.object-pagination a[data-page="1"]').click();
	
		objectList.find('.object-pagination').each(function(){
			$(this).find('.space.after').insertBefore($(this).find('a[data-page]:last'));
		});
		objectList.find('.object-filter select').each(function(){
			var selector = $(this).parents('.selector');
			var span = selector.find('span');
			span.html('<strong>'+$(this).data('label')+'</strong>');
			$(this).val('');
		});
		
		if (objectList.find('.object-pagination a').length == 0) {
		
			var table = objectList.find('table.list');
			var objectname = table.data('objectname');
		    var parentname = table.data('parentname');
		    var parentid = table.data('parentid');
			    
		    table.data('page',1);
			    
		    table.find('tbody').stop(true,true).animate({
			    opacity: 0
		    },200);
				
			sortingAllowed = false;
		    
			$.ajax({
				type: "POST",
				url: admin+"ajax_object_page/"+objectname+'/',
				data: 'page=1&parentname='+parentname+'&parentid='+parentid,
				success: function (html) {
					table.find('tbody').remove();
		            table.find('thead').after(html);
		            table.find('tbody').css({
					    opacity: 0
				    });
		            table.find('tbody').stop(true,true).animate({
					    opacity: 1
				    },200);
				    sortingAllowed = true;
		        }
			});
		}
		
    	return false;
    });
    
    var tinymceOptions = {
		script_url : adminurl+'js/tiny_mce/tiny_mce.js',
		theme : "advanced",
		language : language,
		plugins : "inlinepopups,lists,pagebreak,style,layer,table,fwimage,fwvideo,downloads,media,print,paste,noneditable,visualchars,nonbreaking,xhtmlxtras,heading,hr",
		contentStyles : tinyMCEStyles,
		theme_advanced_buttons2 : "",
		theme_advanced_buttons3 : "",
		theme_advanced_buttons4 : "",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_resizing : false,
		force_br_newlines : true,
        force_p_newlines : false,
        convert_newlines_to_brs: true,
        invalid_elements : "p",
        forced_root_block : '',
        paste_text_sticky: true,
        paste_text_sticky_default: true,
        popup_css : false,
        relative_urls : false,
        default_color : '#000000',
        force_hex_style_colors : true,
	    paste_preprocess : function(pl, o) {
	      o.content = strip_tags( o.content,'<b><u><i><br>' );
	    },
	    paste_text_linebreaktype : 'br'
	};
	if (textcolors.length > 0) {
		tinymceOptions.theme_advanced_text_colors = textcolors;
		tinymceOptions.theme_advanced_default_foreground_color = '#'+defaulttextcolor;
	}
	var tinymceOptionsSimple = JSON.parse(JSON.stringify(tinymceOptions));
	var tinymceOptionsLimited = JSON.parse(JSON.stringify(tinymceOptions));
	tinymceOptions.theme_advanced_buttons1 = "bold,italic,underline"+(textcolors.length > 0?",|,forecolor":"")+",|,justifyleft,justifycenter,justifyright,|,bullist,numlist,|,link,fwimage,downloads,fwvideo";
	tinymceOptionsSimple.oninit = function(ed) {
    	setTimeout(function(){$('div.translatable.editor.hidden').hide();$('.mceLayout').fadeIn(300);$('.textarea-loader').remove();$('.translate-container .selector,.translate-container select').fadeIn(300);},100);
    };
	tinymceOptionsLimited.oninit = function(ed) {
    	setTimeout(function(){$('div.translatable.editor.hidden').hide();$('.mceLayout').fadeIn(300);$('.textarea-loader').remove();$('.translate-container .selector,.translate-container select').fadeIn(300);},100);
    };
	$('textarea:not(.simple):not(.limited)').each(function(){
		var options = jQuery.extend({}, tinymceOptions);
		if ($(this).data('controls') && $(this).data('controls').length > 0) options.theme_advanced_buttons1 = "bold,italic,underline"+(textcolors.length > 0?",|,forecolor":"")+",|,"+$(this).data('controls')+",|,justifyleft,justifycenter,justifyright,|,bullist,numlist,|,link,fwimage,downloads,fwvideo";
		else options.theme_advanced_buttons1 = "bold,italic,underline"+(textcolors.length > 0?",|,forecolor":"")+",|,justifyleft,justifycenter,justifyright,|,bullist,numlist,|,link,fwimage,downloads,fwvideo";
		
		var textarea = $(this);
		
		options.oninit = function(ed) {
	    	setTimeout(function(){
	    		$('div.translatable.editor.hidden').hide();
	    		$('.mceLayout').fadeIn(300);
	    		$('.textarea-loader').remove();
	    		$('.translate-container .selector,.translate-container select').fadeIn(300);
	    	},100);
	    };
	    if (textarea.data('placeholder-names')) {
			var placeholderlabels = textarea.data('placeholder-labels').split(',');
			var placeholdericons = textarea.data('placeholder-icons').split(',');
			$.each(textarea.data('placeholder-names').split(','),function(index){
				options.theme_advanced_buttons1 += ',placeholder' + this;
			});
			options.setup = function(ed) {
				var placeholdernames = textarea.data('placeholder-names').split(',');
				var placeholderlabels = textarea.data('placeholder-labels').split(',');
				var placeholdericons = textarea.data('placeholder-icons').split(',');
				$.each(textarea.data('placeholder-names').split(','),function(index){
			    	ed.addButton('placeholder' + this, {
						type: 'button',
			            image: placeholdericons[index],
						onclick : function() {
							ed.execCommand('mceInsertContent',false,'<input class="placeholder" value="'+placeholderlabels[index]+'" data-name="'+placeholdernames[index]+'"/>');
						}
					});
				});
		    };
		}
		
		$(this).tinymce(options);
	});
	$('textarea.simple').each(function(){
		var options = jQuery.extend({}, tinymceOptionsSimple);
		if ($(this).data('controls') && $(this).data('controls').length > 0) options.theme_advanced_buttons1 = "bold,italic,underline,|,"+$(this).data('controls');
		else options.theme_advanced_buttons1 = "bold,italic,underline";
		$(this).tinymce(options);
	});
	$('textarea.limited').each(function(){
		var options = jQuery.extend({}, tinymceOptionsLimited);
		if ($(this).data('controls') && $(this).data('controls').length > 0) options.theme_advanced_buttons1 = "bold,italic,underline"+(textcolors.length > 0?",|,forecolor":"")+",|,justifyleft,justifycenter,justifyright,|,bullist,numlist,|,"+$(this).data('controls');
		else options.theme_advanced_buttons1 = "bold,italic,underline"+(textcolors.length > 0?",|,forecolor":"")+",|,justifyleft,justifycenter,justifyright,|,bullist,numlist";
		$(this).tinymce(options);
	});
	$('input.date:not(.readonly)').each(function(){
		var options = {
			dateFormat: 'dd/mm/yy'
		};
		if ($(this).data('limit') == 'future') options.minDate = 0;
		else if ($(this).data('limit') == 'past') options.maxDate = 0;
		$(this).datepicker(options);
	});
	$('input.timedate:not(.readonly)').each(function(){
		var options = {
			dateFormat: 'dd/mm/yy',
			timeFormat: 'hh:mm',
			stepMinute: 5,
			showButtonPanel: false
		};
		if ($(this).data('limit') == 'future') options.minDate = 0;
		else if ($(this).data('limit') == 'past') options.maxDate = 0;
		$(this).datetimepicker(options);
	});
	
	$('form').submit(function(){
		$('input.invalid,textarea.invalid').removeClass('invalid');
        $('input').each(function(){
			if ($(this).attr('type') == 'email' && ($(this).val() !='')){
                if(!validateEmail($(this).val())) $(this).addClass('invalid');    
            }
		});
		$('input.required,textarea.required').each(function(){
			if ($(this).attr('type') == 'email' && !validateEmail($(this).val())) $(this).addClass('invalid');
			else if (!$(this).val()) $(this).addClass('invalid');
		});
		if ($('input.invalid,textarea.invalid').length > 0) {
			var message = 'Gelieve alle velden correct in te vullen.';
			if (language == 'fr') message = 'S\'il vous plaît remplir tous les champs.';
			else if (language == 'en') message = 'Please enter all fields correctly.';
			alert(message);
			$('input.invalid,textarea.invalid').first().focus();
			return false;
		}
	});
	
	$('input.required,textarea.required').change(function(){
		$(this).removeClass('invalid');
	});

	if (!Modernizr.input.multiple) {
		$('input[multiple]').each(function(){
			$(this).data('label',$(this).siblings('label').first());
			handleSelectFileToUpload($(this));
		});
	}
	
	$('table.list .overflow').each(function(){
		$(this).width($(this).parents('td').width());
		$(this).addClass('overflowing');
	});
	
	$('select.langswitch').change(function(){
		var container = $(this).parents('.translate-container');
		container.find('.translatable').hide();
		container.find('.translatable.lang_'+$(this).val()).show();
	});
	
	$('select:not(.notfixed)').uniform({selectAutoWidth:false});
	$('select.notfixed').uniform();
	
	$('.object-filter select').change(function(){
		var selector = $(this).parents('.selector');
		var span = selector.find('span');

		if ($(this).val() == '') span.html('<strong>'+$(this).data('label')+'</strong>');
		else span.html('<strong>'+$(this).data('label')+':</strong> '+span.html());
		
		if (span.data('lastVal') != $(this).val()) {
		
			var objectList = $(this).parents('.object-list');
	    
		    objectList.find('.object-pagination a.active').removeClass('active');
		    objectList.find('.object-pagination a[data-page="1"]').addClass('active');
		    formatPaginations(objectList.find('.object-pagination'));
		    
		    doFilter(objectList,1);
			
			span.data('lastVal',$(this).val());
			
		}
	});
	$('.object-list').each(function(){
		var hasFilterAlready = false;
		$(this).find('.object-filter select').each(function(){
			var selector = $(this).parents('.selector');
			var span = selector.find('span');
			if ($(this).val() == '') {
				span.html('<strong>'+$(this).data('label')+'</strong>');
			} else {
				span.html('<strong>'+$(this).data('label')+':</strong> '+span.html());
				hasFilterAlready = true;
			}
			span.data('lastVal',$(this).val());
		});
		if ($(this).find('.object-filter input[name="search"]').val()) hasFilterAlready = true;
		if (hasFilterAlready) {
			$(this).find('.object-filter .reset').css({width:25});
		    var pagination = $(this).find('.object-pagination');
		    if (pagination.length > 0) var page = parseInt(pagination.find('a.active').eq(0).text(),10);
		    else var page = 1;
			doFilter($(this),page);
		}
	});
	
	$('.object-pagination a').click(navigateToPage);
	
	formatPaginations($('.object-pagination'));
	
	$('*[data-enabled-condition]').each(function(){
		var field = $('#input-'+$(this).data('enabled-condition'));
		var targetField = $(this);
		if (field.attr('type') == 'checkbox') targetField.prop('disabled',!field.prop('checked'));
		else targetField.prop('disabled',field.val() == '');
		$.uniform.update("select[data-enabled-condition]");
		field.change(function(){
			if ($(this).attr('type') == 'checkbox') targetField.prop('disabled',!$(this).prop('checked'));
			else targetField.prop('disabled',$(this).val() == '');
			if (targetField.attr('type') == 'checkbox' && targetField.prop('disabled')) targetField.prop('checked',false);
			else if (targetField.prop('disabled')) targetField.val('');
			$(targetField).change();
			$.uniform.update("select[data-enabled-condition]");
		});
	});
	$('*[data-visible-condition]').each(function(){
		var field = $('#input-'+$(this).data('visible-condition'));
		var targetField = $(this);
		var tohide = targetField;
		if (targetField.parents('.field').length > 0) tohide = targetField.parents('.field');
		else if (targetField.parents('.input').length > 0) tohide = targetField.parents('.input');
		if (field.attr('type') == 'checkbox') tohide.toggle(field.prop('checked'));
		else tohide.toggle(field.val() != '');
		$.uniform.update("select[data-visible-condition]");
		field.change(function(){
			if ($(this).attr('type') == 'checkbox') tohide.toggle($(this).prop('checked'));
			else tohide.toggle($(this).val() != '');
			if (targetField.attr('type') == 'checkbox' && !tohide.is(':visible')) targetField.prop('checked',false);
			else if (!tohide.is(':visible')) targetField.val('');
			$(targetField).change();
			$.uniform.update("select[data-enabled-condition]");
		});
	});
	
	jQuery('input.number').keyup(function () { 
	   if(this.value.match(/[^0-9\.\,]/g)){
           this.value = this.value.replace(/[^0-9\.\,]/g,'');
        }
        if(this.value.match(/(^[\.|\,])/g)){
            this.value = this.value.replace(/(^[\.|\,])/g,'');
        }
        if(this.value.match(/[\.|\,]{1,}[0-9A-z]*[\.|\,]{1,}/g)){
            var valueGetal = this.value;
            var lastChar = valueGetal.substring(valueGetal.length-1);
            if(lastChar == '.'||lastChar == ','){
                var countPoints = (valueGetal.match(/\.|\,/g) || []).length;
                while(countPoints > 1 && (lastChar == '.'||lastChar==',')){
                    valueGetal = valueGetal.substring(0, ((valueGetal.length)-1));
                    countPoints = (valueGetal.match(/\.|\,/g) || []).length;
                    var lastChar = valueGetal.substring(valueGetal.length-1);
                }
            }else{
                var seperator = ',';
                var positionKomma = 0;
                var positionPunt = 0;
                if(valueGetal.match(/\./)){
                    positionPunt = valueGetal.indexOf('.');
                }
                if(valueGetal.match(/\,/)){
                    positionKomma = valueGetal.indexOf(',');   
                }
                if(((positionKomma > positionPunt)&&(positionPunt != 0))||(positionKomma == 0)){
                    seperator = '.';
                }else{
                    seperator = ',';
                }
                var arrayPieces = valueGetal.split(/\.|\,/);
                valueGetal = '';
                for(i=0, l=arrayPieces.length; i<l; i++){
                    valueGetal = valueGetal + arrayPieces[i];
                    if(i==0){
                        valueGetal = valueGetal + seperator;
                    }
                }
            }
            this.value = valueGetal;
        }
	});
	
	$("input.float").blur(function(){
		$(this).formatCurrency({
			decimalSymbol: ",",
			digitGroupSymbol: ".",
			positiveFormat: "%n",
			symbol: euroSign
		});
	});
	
	var versionsmenutimer = 0;
	$('a.button.versions').click(function(){
		$(this).siblings('.versionsmenu').slideToggle(250);
		return false;
	});
	$('a.button.versions,.versionsmenu').mouseenter(function(){
		if (versionsmenutimer) clearTimeout(versionsmenutimer);
	});
	$('a.button.versions,.versionsmenu').mouseleave(function(){
		versionsmenutimer = setTimeout(function(){
			$('.versionsmenu').slideUp(250);
		}, 1000);
	});
});
    
function doFilter(objectList,page) {

	var validFilter = false;
	
	objectList.find('.object-filter select').each(function(){
		if ($(this).val() !== '') validFilter = true;
	});
	objectList.find('.object-filter input').each(function(){
		if ($(this).val()) validFilter = true;
	});
	
	if (!validFilter) {
		$('.object-filter .reset a').click();
		return;
	}

	objectList.find('.reset').animate({
    	'width' : 25
	},500,'easeOutBack');
	var data = objectList.find('.object-filter select,.object-filter input').serialize();
	data += '&page='+page;
	var table = objectList.find('table.list');
	var objectname = table.data('objectname');
	table.find('tbody').stop(true,true).animate({
	    opacity: 0
    },200);
    
	objectList.data('filtering',true);
    
    $.ajax({
		type: "POST",
		url: admin+"ajax_filter/"+objectname+'/',
		data: data,
		dataType:'json',
		success: function (json) {
			table.find('tbody').remove();
            table.find('thead').after(json.html);
            table.find('tbody').css({
			    opacity: 0
		    });
            table.find('tbody').stop(true,true);
            if (json.pages < 2) {
	            objectList.find('.object-pagination').slideUp(200);
            } else {
	            objectList.find('.object-pagination').slideDown(200);
            }
            table.find('tbody').animate({
			    opacity: 1
		    },200);
			objectList.find('.object-pagination').each(function(){
				$(this).find('.space.after').insertBefore($(this).find('a[data-page="'+json.pages+'"]'));
			});
			objectList.data('filtered-pages',json.pages);
			formatPaginations(objectList.find('.object-pagination'));
        },
        error: function (data) {
	        console.log(data);
        }
	});
    sortingAllowed = false;
}

function navigateToPage() {
	if ($(this).hasClass('active')) return false;
	var objectList = $(this).parents('.object-list');

	var table = objectList.find('table.list');
	var objectname = table.data('objectname');
    var pagination = $(this).parents('.object-pagination');
    var page = parseInt(pagination.find('a.active').text(),10);
    var parentname = table.data('parentname');
    var parentid = table.data('parentid');
    
    if ($(this).hasClass('next')) page += 1;
    else if ($(this).hasClass('prev')) page -= 1;
    else page = parseInt($(this).text(),10);
    
    objectList.find('.object-pagination a.active').removeClass('active');
    objectList.find('.object-pagination a[data-page="'+page+'"]').addClass('active');
    formatPaginations($(this).parents('.object-list').find('.object-pagination'));
	    
    table.data('page',page);
	    
    table.find('tbody').stop(true,true).animate({
	    opacity: 0
    },200);
	
	if (objectList.data('filtering')) {
		
		doFilter(objectList,page);
		
	} else {
		
		sortingAllowed = false;
	    
		$.ajax({
			type: "POST",
			url: admin+"ajax_object_page/"+objectname+'/',
			data: 'page='+page+'&parentname='+parentname+'&parentid='+parentid,
			success: function (html) {
				table.find('tbody').remove();
	            table.find('thead').after(html);
	            table.find('tbody').css({
				    opacity: 0
			    });
	            table.find('tbody').stop(true,true).animate({
				    opacity: 1
			    },200);
			    sortingAllowed = true;
	            pagination.show();
	        }
		});
		
	}
    
	return false;
}

function formatPaginations(paginations) {
	paginations.each(function(){
		var page = parseInt($(this).find('a.active').text(),10);
		
		if ($(this).parents('.object-list').data('filtering')) {
			var pages = parseInt($(this).parents('.object-list').data('filtered-pages'),10);
		} else {
			var pages = parseInt($(this).data('pages'),10);
		}
		
	    if (page == 1) $(this).find('a.prev').hide();
	    else $(this).find('a.prev').css('display', 'inline-block');
	    
	    if (page == pages) $(this).find('a.next').hide();
	    else $(this).find('a.next').css('display', 'inline-block');
	    
	    $(this).find('a[data-page]').hide();
	    for (var i = Math.max(0,page-3); i < Math.min(pages,page+4); i++) {
		    $(this).find('a[data-page="'+i+'"]').css('display', 'inline-block');
	    }
	    $(this).find('a[data-page="1"],a[data-page="'+pages+'"]').css('display', 'inline-block');
	    
	    if (page < pages - 4) $(this).find('.space.after').css('display', 'inline-block');
	    else $(this).find('.space.after').hide();
	    
	    if (page > 5) $(this).find('.space.before').css('display', 'inline-block');
	    else $(this).find('.space.before').hide();
	});
}

function handleSelectFileToUpload(input) {
	if ($.browser.msie) {
	    input.click(function(event){
	        setTimeout(function(){
	            didSelectFileToUpload(input);
	        }, 0);
	    });
	} else {
	    input.change(function(){didSelectFileToUpload($(this));});
	}
}
function didSelectFileToUpload(input) {
	var label = input.siblings('label').first();
	var newInput = input.clone().insertAfter(input);
	var newLabel = label.clone().insertBefore(newInput).css('opacity',0);
	newInput.data('label',newLabel);
	handleSelectFileToUpload(newInput);
	var deleteText = 'Verwijderen';
	if (language == 'fr') deleteText = 'Supprimer';
	else if (language = 'en') deleteText = 'Delete';
	$('<a href="#"><img alt="'+deleteText+'" title="'+deleteText+'" src="'+adminurl+'images/del.png"/></a>').data('input',input).click(function(){
		$(this).data('input').data('label').remove();
		$(this).siblings('label').first().css('opacity',1);
		$(this).data('input').remove();
		$(this).remove();
		return false;
	}).insertAfter(input);
}

jQuery.easing.jswing=jQuery.easing.swing;jQuery.extend(jQuery.easing,{def:"easeOutQuad",swing:function(e,f,a,h,g){return jQuery.easing[jQuery.easing.def](e,f,a,h,g)},easeInQuad:function(e,f,a,h,g){return h*(f/=g)*f+a},easeOutQuad:function(e,f,a,h,g){return -h*(f/=g)*(f-2)+a},easeInOutQuad:function(e,f,a,h,g){if((f/=g/2)<1){return h/2*f*f+a}return -h/2*((--f)*(f-2)-1)+a},easeInCubic:function(e,f,a,h,g){return h*(f/=g)*f*f+a},easeOutCubic:function(e,f,a,h,g){return h*((f=f/g-1)*f*f+1)+a},easeInOutCubic:function(e,f,a,h,g){if((f/=g/2)<1){return h/2*f*f*f+a}return h/2*((f-=2)*f*f+2)+a},easeInQuart:function(e,f,a,h,g){return h*(f/=g)*f*f*f+a},easeOutQuart:function(e,f,a,h,g){return -h*((f=f/g-1)*f*f*f-1)+a},easeInOutQuart:function(e,f,a,h,g){if((f/=g/2)<1){return h/2*f*f*f*f+a}return -h/2*((f-=2)*f*f*f-2)+a},easeInQuint:function(e,f,a,h,g){return h*(f/=g)*f*f*f*f+a},easeOutQuint:function(e,f,a,h,g){return h*((f=f/g-1)*f*f*f*f+1)+a},easeInOutQuint:function(e,f,a,h,g){if((f/=g/2)<1){return h/2*f*f*f*f*f+a}return h/2*((f-=2)*f*f*f*f+2)+a},easeInSine:function(e,f,a,h,g){return -h*Math.cos(f/g*(Math.PI/2))+h+a},easeOutSine:function(e,f,a,h,g){return h*Math.sin(f/g*(Math.PI/2))+a},easeInOutSine:function(e,f,a,h,g){return -h/2*(Math.cos(Math.PI*f/g)-1)+a},easeInExpo:function(e,f,a,h,g){return(f==0)?a:h*Math.pow(2,10*(f/g-1))+a},easeOutExpo:function(e,f,a,h,g){return(f==g)?a+h:h*(-Math.pow(2,-10*f/g)+1)+a},easeInOutExpo:function(e,f,a,h,g){if(f==0){return a}if(f==g){return a+h}if((f/=g/2)<1){return h/2*Math.pow(2,10*(f-1))+a}return h/2*(-Math.pow(2,-10*--f)+2)+a},easeInCirc:function(e,f,a,h,g){return -h*(Math.sqrt(1-(f/=g)*f)-1)+a},easeOutCirc:function(e,f,a,h,g){return h*Math.sqrt(1-(f=f/g-1)*f)+a},easeInOutCirc:function(e,f,a,h,g){if((f/=g/2)<1){return -h/2*(Math.sqrt(1-f*f)-1)+a}return h/2*(Math.sqrt(1-(f-=2)*f)+1)+a},easeInElastic:function(f,h,e,l,k){var i=1.70158;var j=0;var g=l;if(h==0){return e}if((h/=k)==1){return e+l}if(!j){j=k*0.3}if(g<Math.abs(l)){g=l;var i=j/4}else{var i=j/(2*Math.PI)*Math.asin(l/g)}return -(g*Math.pow(2,10*(h-=1))*Math.sin((h*k-i)*(2*Math.PI)/j))+e},easeOutElastic:function(f,h,e,l,k){var i=1.70158;var j=0;var g=l;if(h==0){return e}if((h/=k)==1){return e+l}if(!j){j=k*0.3}if(g<Math.abs(l)){g=l;var i=j/4}else{var i=j/(2*Math.PI)*Math.asin(l/g)}return g*Math.pow(2,-10*h)*Math.sin((h*k-i)*(2*Math.PI)/j)+l+e},easeInOutElastic:function(f,h,e,l,k){var i=1.70158;var j=0;var g=l;if(h==0){return e}if((h/=k/2)==2){return e+l}if(!j){j=k*(0.3*1.5)}if(g<Math.abs(l)){g=l;var i=j/4}else{var i=j/(2*Math.PI)*Math.asin(l/g)}if(h<1){return -0.5*(g*Math.pow(2,10*(h-=1))*Math.sin((h*k-i)*(2*Math.PI)/j))+e}return g*Math.pow(2,-10*(h-=1))*Math.sin((h*k-i)*(2*Math.PI)/j)*0.5+l+e},easeInBack:function(e,f,a,i,h,g){if(g==undefined){g=1.70158}return i*(f/=h)*f*((g+1)*f-g)+a},easeOutBack:function(e,f,a,i,h,g){if(g==undefined){g=1.70158}return i*((f=f/h-1)*f*((g+1)*f+g)+1)+a},easeInOutBack:function(e,f,a,i,h,g){if(g==undefined){g=1.70158}if((f/=h/2)<1){return i/2*(f*f*(((g*=(1.525))+1)*f-g))+a}return i/2*((f-=2)*f*(((g*=(1.525))+1)*f+g)+2)+a},easeInBounce:function(e,f,a,h,g){return h-jQuery.easing.easeOutBounce(e,g-f,0,h,g)+a},easeOutBounce:function(e,f,a,h,g){if((f/=g)<(1/2.75)){return h*(7.5625*f*f)+a}else{if(f<(2/2.75)){return h*(7.5625*(f-=(1.5/2.75))*f+0.75)+a}else{if(f<(2.5/2.75)){return h*(7.5625*(f-=(2.25/2.75))*f+0.9375)+a}else{return h*(7.5625*(f-=(2.625/2.75))*f+0.984375)+a}}}},easeInOutBounce:function(e,f,a,h,g){if(f<g/2){return jQuery.easing.easeInBounce(e,f*2,0,h,g)*0.5+a}return jQuery.easing.easeOutBounce(e,f*2-g,0,h,g)*0.5+h*0.5+a}});

var defaultDiacriticsRemovalMap = [
    {'base':'A', 'letters':/[\u0041\u24B6\uFF21\u00C0\u00C1\u00C2\u1EA6\u1EA4\u1EAA\u1EA8\u00C3\u0100\u0102\u1EB0\u1EAE\u1EB4\u1EB2\u0226\u01E0\u00C4\u01DE\u1EA2\u00C5\u01FA\u01CD\u0200\u0202\u1EA0\u1EAC\u1EB6\u1E00\u0104\u023A\u2C6F]/g},
    {'base':'AA','letters':/[\uA732]/g},
    {'base':'AE','letters':/[\u00C6\u01FC\u01E2]/g},
    {'base':'AO','letters':/[\uA734]/g},
    {'base':'AU','letters':/[\uA736]/g},
    {'base':'AV','letters':/[\uA738\uA73A]/g},
    {'base':'AY','letters':/[\uA73C]/g},
    {'base':'B', 'letters':/[\u0042\u24B7\uFF22\u1E02\u1E04\u1E06\u0243\u0182\u0181]/g},
    {'base':'C', 'letters':/[\u0043\u24B8\uFF23\u0106\u0108\u010A\u010C\u00C7\u1E08\u0187\u023B\uA73E]/g},
    {'base':'D', 'letters':/[\u0044\u24B9\uFF24\u1E0A\u010E\u1E0C\u1E10\u1E12\u1E0E\u0110\u018B\u018A\u0189\uA779]/g},
    {'base':'DZ','letters':/[\u01F1\u01C4]/g},
    {'base':'Dz','letters':/[\u01F2\u01C5]/g},
    {'base':'E', 'letters':/[\u0045\u24BA\uFF25\u00C8\u00C9\u00CA\u1EC0\u1EBE\u1EC4\u1EC2\u1EBC\u0112\u1E14\u1E16\u0114\u0116\u00CB\u1EBA\u011A\u0204\u0206\u1EB8\u1EC6\u0228\u1E1C\u0118\u1E18\u1E1A\u0190\u018E]/g},
    {'base':'F', 'letters':/[\u0046\u24BB\uFF26\u1E1E\u0191\uA77B]/g},
    {'base':'G', 'letters':/[\u0047\u24BC\uFF27\u01F4\u011C\u1E20\u011E\u0120\u01E6\u0122\u01E4\u0193\uA7A0\uA77D\uA77E]/g},
    {'base':'H', 'letters':/[\u0048\u24BD\uFF28\u0124\u1E22\u1E26\u021E\u1E24\u1E28\u1E2A\u0126\u2C67\u2C75\uA78D]/g},
    {'base':'I', 'letters':/[\u0049\u24BE\uFF29\u00CC\u00CD\u00CE\u0128\u012A\u012C\u0130\u00CF\u1E2E\u1EC8\u01CF\u0208\u020A\u1ECA\u012E\u1E2C\u0197]/g},
    {'base':'J', 'letters':/[\u004A\u24BF\uFF2A\u0134\u0248]/g},
    {'base':'K', 'letters':/[\u004B\u24C0\uFF2B\u1E30\u01E8\u1E32\u0136\u1E34\u0198\u2C69\uA740\uA742\uA744\uA7A2]/g},
    {'base':'L', 'letters':/[\u004C\u24C1\uFF2C\u013F\u0139\u013D\u1E36\u1E38\u013B\u1E3C\u1E3A\u0141\u023D\u2C62\u2C60\uA748\uA746\uA780]/g},
    {'base':'LJ','letters':/[\u01C7]/g},
    {'base':'Lj','letters':/[\u01C8]/g},
    {'base':'M', 'letters':/[\u004D\u24C2\uFF2D\u1E3E\u1E40\u1E42\u2C6E\u019C]/g},
    {'base':'N', 'letters':/[\u004E\u24C3\uFF2E\u01F8\u0143\u00D1\u1E44\u0147\u1E46\u0145\u1E4A\u1E48\u0220\u019D\uA790\uA7A4]/g},
    {'base':'NJ','letters':/[\u01CA]/g},
    {'base':'Nj','letters':/[\u01CB]/g},
    {'base':'O', 'letters':/[\u004F\u24C4\uFF2F\u00D2\u00D3\u00D4\u1ED2\u1ED0\u1ED6\u1ED4\u00D5\u1E4C\u022C\u1E4E\u014C\u1E50\u1E52\u014E\u022E\u0230\u00D6\u022A\u1ECE\u0150\u01D1\u020C\u020E\u01A0\u1EDC\u1EDA\u1EE0\u1EDE\u1EE2\u1ECC\u1ED8\u01EA\u01EC\u00D8\u01FE\u0186\u019F\uA74A\uA74C]/g},
    {'base':'OI','letters':/[\u01A2]/g},
    {'base':'OO','letters':/[\uA74E]/g},
    {'base':'OU','letters':/[\u0222]/g},
    {'base':'P', 'letters':/[\u0050\u24C5\uFF30\u1E54\u1E56\u01A4\u2C63\uA750\uA752\uA754]/g},
    {'base':'Q', 'letters':/[\u0051\u24C6\uFF31\uA756\uA758\u024A]/g},
    {'base':'R', 'letters':/[\u0052\u24C7\uFF32\u0154\u1E58\u0158\u0210\u0212\u1E5A\u1E5C\u0156\u1E5E\u024C\u2C64\uA75A\uA7A6\uA782]/g},
    {'base':'S', 'letters':/[\u0053\u24C8\uFF33\u1E9E\u015A\u1E64\u015C\u1E60\u0160\u1E66\u1E62\u1E68\u0218\u015E\u2C7E\uA7A8\uA784]/g},
    {'base':'T', 'letters':/[\u0054\u24C9\uFF34\u1E6A\u0164\u1E6C\u021A\u0162\u1E70\u1E6E\u0166\u01AC\u01AE\u023E\uA786]/g},
    {'base':'TZ','letters':/[\uA728]/g},
    {'base':'U', 'letters':/[\u0055\u24CA\uFF35\u00D9\u00DA\u00DB\u0168\u1E78\u016A\u1E7A\u016C\u00DC\u01DB\u01D7\u01D5\u01D9\u1EE6\u016E\u0170\u01D3\u0214\u0216\u01AF\u1EEA\u1EE8\u1EEE\u1EEC\u1EF0\u1EE4\u1E72\u0172\u1E76\u1E74\u0244]/g},
    {'base':'V', 'letters':/[\u0056\u24CB\uFF36\u1E7C\u1E7E\u01B2\uA75E\u0245]/g},
    {'base':'VY','letters':/[\uA760]/g},
    {'base':'W', 'letters':/[\u0057\u24CC\uFF37\u1E80\u1E82\u0174\u1E86\u1E84\u1E88\u2C72]/g},
    {'base':'X', 'letters':/[\u0058\u24CD\uFF38\u1E8A\u1E8C]/g},
    {'base':'Y', 'letters':/[\u0059\u24CE\uFF39\u1EF2\u00DD\u0176\u1EF8\u0232\u1E8E\u0178\u1EF6\u1EF4\u01B3\u024E\u1EFE]/g},
    {'base':'Z', 'letters':/[\u005A\u24CF\uFF3A\u0179\u1E90\u017B\u017D\u1E92\u1E94\u01B5\u0224\u2C7F\u2C6B\uA762]/g},
    {'base':'a', 'letters':/[\u0061\u24D0\uFF41\u1E9A\u00E0\u00E1\u00E2\u1EA7\u1EA5\u1EAB\u1EA9\u00E3\u0101\u0103\u1EB1\u1EAF\u1EB5\u1EB3\u0227\u01E1\u00E4\u01DF\u1EA3\u00E5\u01FB\u01CE\u0201\u0203\u1EA1\u1EAD\u1EB7\u1E01\u0105\u2C65\u0250]/g},
    {'base':'aa','letters':/[\uA733]/g},
    {'base':'ae','letters':/[\u00E6\u01FD\u01E3]/g},
    {'base':'ao','letters':/[\uA735]/g},
    {'base':'au','letters':/[\uA737]/g},
    {'base':'av','letters':/[\uA739\uA73B]/g},
    {'base':'ay','letters':/[\uA73D]/g},
    {'base':'b', 'letters':/[\u0062\u24D1\uFF42\u1E03\u1E05\u1E07\u0180\u0183\u0253]/g},
    {'base':'c', 'letters':/[\u0063\u24D2\uFF43\u0107\u0109\u010B\u010D\u00E7\u1E09\u0188\u023C\uA73F\u2184]/g},
    {'base':'d', 'letters':/[\u0064\u24D3\uFF44\u1E0B\u010F\u1E0D\u1E11\u1E13\u1E0F\u0111\u018C\u0256\u0257\uA77A]/g},
    {'base':'dz','letters':/[\u01F3\u01C6]/g},
    {'base':'e', 'letters':/[\u0065\u24D4\uFF45\u00E8\u00E9\u00EA\u1EC1\u1EBF\u1EC5\u1EC3\u1EBD\u0113\u1E15\u1E17\u0115\u0117\u00EB\u1EBB\u011B\u0205\u0207\u1EB9\u1EC7\u0229\u1E1D\u0119\u1E19\u1E1B\u0247\u025B\u01DD]/g},
    {'base':'f', 'letters':/[\u0066\u24D5\uFF46\u1E1F\u0192\uA77C]/g},
    {'base':'g', 'letters':/[\u0067\u24D6\uFF47\u01F5\u011D\u1E21\u011F\u0121\u01E7\u0123\u01E5\u0260\uA7A1\u1D79\uA77F]/g},
    {'base':'h', 'letters':/[\u0068\u24D7\uFF48\u0125\u1E23\u1E27\u021F\u1E25\u1E29\u1E2B\u1E96\u0127\u2C68\u2C76\u0265]/g},
    {'base':'hv','letters':/[\u0195]/g},
    {'base':'i', 'letters':/[\u0069\u24D8\uFF49\u00EC\u00ED\u00EE\u0129\u012B\u012D\u00EF\u1E2F\u1EC9\u01D0\u0209\u020B\u1ECB\u012F\u1E2D\u0268\u0131]/g},
    {'base':'j', 'letters':/[\u006A\u24D9\uFF4A\u0135\u01F0\u0249]/g},
    {'base':'k', 'letters':/[\u006B\u24DA\uFF4B\u1E31\u01E9\u1E33\u0137\u1E35\u0199\u2C6A\uA741\uA743\uA745\uA7A3]/g},
    {'base':'l', 'letters':/[\u006C\u24DB\uFF4C\u0140\u013A\u013E\u1E37\u1E39\u013C\u1E3D\u1E3B\u017F\u0142\u019A\u026B\u2C61\uA749\uA781\uA747]/g},
    {'base':'lj','letters':/[\u01C9]/g},
    {'base':'m', 'letters':/[\u006D\u24DC\uFF4D\u1E3F\u1E41\u1E43\u0271\u026F]/g},
    {'base':'n', 'letters':/[\u006E\u24DD\uFF4E\u01F9\u0144\u00F1\u1E45\u0148\u1E47\u0146\u1E4B\u1E49\u019E\u0272\u0149\uA791\uA7A5]/g},
    {'base':'nj','letters':/[\u01CC]/g},
    {'base':'o', 'letters':/[\u006F\u24DE\uFF4F\u00F2\u00F3\u00F4\u1ED3\u1ED1\u1ED7\u1ED5\u00F5\u1E4D\u022D\u1E4F\u014D\u1E51\u1E53\u014F\u022F\u0231\u00F6\u022B\u1ECF\u0151\u01D2\u020D\u020F\u01A1\u1EDD\u1EDB\u1EE1\u1EDF\u1EE3\u1ECD\u1ED9\u01EB\u01ED\u00F8\u01FF\u0254\uA74B\uA74D\u0275]/g},
    {'base':'oi','letters':/[\u01A3]/g},
    {'base':'ou','letters':/[\u0223]/g},
    {'base':'oo','letters':/[\uA74F]/g},
    {'base':'p','letters':/[\u0070\u24DF\uFF50\u1E55\u1E57\u01A5\u1D7D\uA751\uA753\uA755]/g},
    {'base':'q','letters':/[\u0071\u24E0\uFF51\u024B\uA757\uA759]/g},
    {'base':'r','letters':/[\u0072\u24E1\uFF52\u0155\u1E59\u0159\u0211\u0213\u1E5B\u1E5D\u0157\u1E5F\u024D\u027D\uA75B\uA7A7\uA783]/g},
    {'base':'s','letters':/[\u0073\u24E2\uFF53\u00DF\u015B\u1E65\u015D\u1E61\u0161\u1E67\u1E63\u1E69\u0219\u015F\u023F\uA7A9\uA785\u1E9B]/g},
    {'base':'t','letters':/[\u0074\u24E3\uFF54\u1E6B\u1E97\u0165\u1E6D\u021B\u0163\u1E71\u1E6F\u0167\u01AD\u0288\u2C66\uA787]/g},
    {'base':'tz','letters':/[\uA729]/g},
    {'base':'u','letters':/[\u0075\u24E4\uFF55\u00F9\u00FA\u00FB\u0169\u1E79\u016B\u1E7B\u016D\u00FC\u01DC\u01D8\u01D6\u01DA\u1EE7\u016F\u0171\u01D4\u0215\u0217\u01B0\u1EEB\u1EE9\u1EEF\u1EED\u1EF1\u1EE5\u1E73\u0173\u1E77\u1E75\u0289]/g},
    {'base':'v','letters':/[\u0076\u24E5\uFF56\u1E7D\u1E7F\u028B\uA75F\u028C]/g},
    {'base':'vy','letters':/[\uA761]/g},
    {'base':'w','letters':/[\u0077\u24E6\uFF57\u1E81\u1E83\u0175\u1E87\u1E85\u1E98\u1E89\u2C73]/g},
    {'base':'x','letters':/[\u0078\u24E7\uFF58\u1E8B\u1E8D]/g},
    {'base':'y','letters':/[\u0079\u24E8\uFF59\u1EF3\u00FD\u0177\u1EF9\u0233\u1E8F\u00FF\u1EF7\u1E99\u1EF5\u01B4\u024F\u1EFF]/g},
    {'base':'z','letters':/[\u007A\u24E9\uFF5A\u017A\u1E91\u017C\u017E\u1E93\u1E95\u01B6\u0225\u0240\u2C6C\uA763]/g}
];
var changes;
function removeDiacritics(str) {
    if(!changes) {
        changes = defaultDiacriticsRemovalMap;
    }
    for(var i=0; i<changes.length; i++) {
        str = str.replace(changes[i].letters, changes[i].base);
    }
    return str;
}
function validateEmail(email) {
    var re = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
}

$('.content-editor .add-section .paragraph').click(function(){
	var section = $('<div class="section" data-type="paragraph"><img class="sort-handle" src="'+adminurl+'images/sort.png" width="10" height="11"/><div class="section-content"><div class="wysiwyg"></div></div></div>');
	$('.sections').append(section);
	var editor = new MediumEditor(section.find('.wysiwyg')[0], {
	    anchorInputPlaceholder: 'Geef een URL in',
	    placeholder: 'Inhoud',
	    buttons: ['bold', 'italic', 'anchor','unorderedlist','header1'],
	    diffTop: -5,
	    checkLinkFormat: true,
	    disableDoubleReturn: true,
	    imageDragging: false
	});
	section.find('.wysiwyg').focus();
	return false;
});