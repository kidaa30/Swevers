/*

	jQuery Tags Input Plugin 1.3.3
	
	Copyright (c) 2011 XOXCO, Inc
	
	Documentation for this plugin lives here:
	http://xoxco.com/clickable/jquery-tags-input
	
	Licensed under the MIT license:
	http://www.opensource.org/licenses/mit-license.php

	ben@xoxco.com

*/

(function($) {

	var delimiter = new Array();
	var tags_callbacks = new Array();
	$.fn.doAutosize = function(o){
	    var minWidth = $(this).data('minwidth'),
	        maxWidth = $(this).data('maxwidth'),
	        val = '',
	        input = $(this),
	        testSubject = $('#'+$(this).data('tester_id'));
	
	    if (val === (val = input.val())) {return;}
	
	    // Enter new content into testSubject
	    var escaped = val.replace(/&/g, '&amp;').replace(/\s/g,' ').replace(/</g, '&lt;').replace(/>/g, '&gt;');
	    testSubject.html(escaped);
	    // Calculate new width + whether to change
	    var testerWidth = testSubject.width(),
	        newWidth = (testerWidth + o.comfortZone) >= minWidth ? testerWidth + o.comfortZone : minWidth,
	        currentWidth = input.width(),
	        isValidWidthChange = (newWidth < currentWidth && newWidth >= minWidth)
	                             || (newWidth > minWidth && newWidth < maxWidth);
	
	    // Animate width
	    if (isValidWidthChange) {
	        input.width(newWidth);
	    }


  };
  $.fn.resetAutosize = function(options){
    // alert(JSON.stringify(options));
    var minWidth =  $(this).data('minwidth') || options.minInputWidth || $(this).width(),
        maxWidth = $(this).data('maxwidth') || options.maxInputWidth || ($(this).closest('.tagsinput').width() - options.inputPadding),
        val = '',
        input = $(this),
        testSubject = $('<tester/>').css({
            position: 'absolute',
            top: -9999,
            left: -9999,
            width: 'auto',
            fontSize: input.css('fontSize'),
            fontFamily: input.css('fontFamily'),
            fontWeight: input.css('fontWeight'),
            letterSpacing: input.css('letterSpacing'),
            whiteSpace: 'nowrap'
        }),
        testerId = $(this).prop('id')+'_autosize_tester';
    if(! $('#'+testerId).length > 0){
      testSubject.prop('id', testerId);
      testSubject.appendTo('body');
    }

    input.data('minwidth', minWidth);
    input.data('maxwidth', maxWidth);
    input.data('tester_id', testerId);
  };
  
	$.fn.addTag = function(value,options) {
			options = jQuery.extend({focus:false,callback:true},options);
			this.each(function() { 
				var id = $(this).prop('id');

				var tagslist = $(this).val().split(delimiter[id]);
				if (tagslist[0] == '') { 
					tagslist = new Array();
				}

				value = jQuery.trim(value);
		
				if (options.unique) {
					var skipTag = $(this).tagExist(value);
					if(skipTag == true) {
					    //Marks fake input as not_valid to let styling it
    				    $('#'+id+'_tag').addClass('not_valid');
    				}
				} else {
					var skipTag = false; 
				}
				
				if (value !='' && skipTag != true) { 
                    var newTag = $('<span>').addClass('tag').append(
                        $('<span>').text(value).append('&nbsp;&nbsp;'),
                        $('<a>', {
                            href  : '#',
                            text  : '\u00D7'
                        }).click(function () {
                            return $('#' + id).removeTag(escape(value));
                        })
                    ).insertBefore('#' + id + '_addTag');
                    
                    if ($(this).hasClass('translatable')) {
	                    var input = $(this);
	                    var langs = $(this).data('languages');
	                    var fieldname = $(this).data('fieldname');
	                    if (typeof langs == 'string') langs = JSON.parse(langs);
	                    var translated = window['tagtranslations_'+fieldname];
	                    $.each(langs,function(){
	                    	var transvalue = '';
	                    	if (translated[this][value.toLowerCase()]) transvalue = translated[this][value.toLowerCase()];
	                    	window['currenttags_'+fieldname][this].push(transvalue);
	                    	input.siblings('input[data-language="'+this+'"]').val(window['currenttags_'+fieldname][this].join(','));
	                    });
		                newTag.mouseenter(function(){
			                if ($(this).data('hideTimer')) {
				                clearTimeout($(this).data('hideTimer'));
				             	$(this).removeData('hideTimer');
			                } else {
			                    var translator = $($('<div>',{
			                    	'class' : 'tag-translator'
		                    	}).hide().fadeIn(200));
		                    	translator.bind('click',function(){
			                    	return false;
		                    	});
		                    	$(this).append(translator);
		                    	$.each(langs,function(){
			                    	var lang = this;
			                    	var transvalue = window['currenttags_'+fieldname][this][newTag.index()];
			                    	translator.append('<div class="language"><input type="text" data-language="'+this+'" class="with_lang_label lowmargin" maxlength="100"><span class="lang_label">'+this.toUpperCase()+'</span></div>');
			                    	translator.find('input[data-language="'+this+'"]').val(transvalue);
			                    	translator.find('input[data-language="'+this+'"]').bind('keyup',function(){
				                    	window['currenttags_'+fieldname][lang][newTag.index()] = $(this).val();
				                    	window['tagtranslations_'+fieldname][lang][value.toLowerCase()] = $(this).val();
			                    	});
			                    	translator.find('input[data-language="'+this+'"]').bind('blur',function(){
				                    	input.siblings('input[data-language="'+lang+'"]').val(window['currenttags_'+fieldname][lang].join(','));
			                    	});
			                    });
			                    translator.find('input').bind('focus',function(){
				                    if ($(this).parents('.tag').data('hideTimer')) {
						                clearTimeout($(this).parents('.tag').data('hideTimer'));
						             	$(this).parents('.tag').removeData('hideTimer');
					                }
			                    });
			                    translator.find('input').bind('blur',function(){
				                    var $this = $(this).parents('.tag');
				                    var timer = setTimeout(function(){
					                    if ($this.find('.tag-translator input:focus').length > 0) return;
					                    $this.find('.tag-translator').remove();
					                    $this.removeData('hideTimer');
					                },300);
					                $this.data('hideTimer',timer);
			                    });
		                    }
	                    }).mouseleave(function(){
		                    var $this = $(this);
		                    var timer = setTimeout(function(){
			                    if ($this.find('.tag-translator input:focus').length > 0) return;
			                    $this.find('.tag-translator').remove();
			                    $this.removeData('hideTimer');
			                },300);
			                $(this).data('hideTimer',timer);
	                    });
	                }

					tagslist.push(value);
				
					$('#'+id+'_tag').val('');
					if (options.focus) {
						$('#'+id+'_tag').focus();
					} else {		
						$('#'+id+'_tag').blur();
					}
					
					$.fn.tagsInput.updateTagsField(this,tagslist);
					
					if (options.callback && tags_callbacks[id] && tags_callbacks[id]['onAddTag']) {
						var f = tags_callbacks[id]['onAddTag'];
						f.call(this, value);
					}
					if(tags_callbacks[id] && tags_callbacks[id]['onChange'])
					{
						var i = tagslist.length;
						var f = tags_callbacks[id]['onChange'];
						f.call(this, $(this), tagslist[i-1]);
					}					
				}
		
			});		
			
			return false;
		};
		
	$.fn.removeTag = function(value) { 
			value = unescape(value);
			this.each(function() { 
				var id = $(this).prop('id');
	
				var old = $(this).val().split(delimiter[id]);
				
				if ($(this).hasClass('translatable')) {
                    var langs = $(this).data('languages');
                    var input = $(this);
                    var fieldname = $(this).data('fieldname');
                    var index = $.inArray(value,old);
					$.each(langs,function(){
                    	window['currenttags_'+fieldname][this] = [];
                    	input.siblings('input[data-language="'+this+'"]').val("");
                    });
				}
					
				$('#'+id+'_tagsinput .tag').remove();
				str = '';
				for (i=0; i< old.length; i++) { 
					if (old[i]!=value) { 
						str = str + delimiter[id] +old[i];
					}
				}
				
				$.fn.tagsInput.importTags(this,str);

				if (tags_callbacks[id] && tags_callbacks[id]['onRemoveTag']) {
					var f = tags_callbacks[id]['onRemoveTag'];
					f.call(this, value);
				}
			});
					
			return false;
		};
	
	$.fn.tagExist = function(val) {
		var id = $(this).prop('id');
		var tagslist = $(this).val().split(delimiter[id]);
		return (jQuery.inArray(val, tagslist) >= 0); //true when tag exists, false when not
	};
	
	// clear all existing tags and import new ones from a string
	$.fn.importTags = function(str) {
                id = $(this).prop('id');
		$('#'+id+'_tagsinput .tag').remove();
		$.fn.tagsInput.importTags(this,str);
	}
		
	$.fn.tagsInput = function(options) { 
    var settings = jQuery.extend({
      interactive:true,
      defaultText:'add a tag',
      minChars:0,
      height:'25px',
      autocomplete: {selectFirst: false },
      'hide':true,
      'delimiter':',',
      'unique':true,
      removeWithBackspace:true,
      placeholderColor:'#999',
      autosize: true,
      comfortZone: 20,
      inputPadding: 6*2
    },options);

		this.each(function() { 
			if (settings.hide) { 
				$(this).hide();				
			}
			var id = $(this).prop('id');
			if (!id || delimiter[$(this).prop('id')]) {
				id = $(this).prop('id', 'tags' + new Date().getTime()).prop('id');
			}
			
			var data = jQuery.extend({
				pid:id,
				real_input: '#'+id,
				holder: '#'+id+'_tagsinput',
				input_wrapper: '#'+id+'_addTag',
				fake_input: '#'+id+'_tag',
				allow_space_after: ['de','le','la','les','s',"'s",'sankt']
			},settings);
	
			delimiter[id] = data.delimiter;
			
			if (settings.onAddTag || settings.onRemoveTag || settings.onChange) {
				tags_callbacks[id] = new Array();
				tags_callbacks[id]['onAddTag'] = settings.onAddTag;
				tags_callbacks[id]['onRemoveTag'] = settings.onRemoveTag;
				tags_callbacks[id]['onChange'] = settings.onChange;
			}
	
			var markup = '<div id="'+id+'_tagsinput" class="tagsinput"><div id="'+id+'_addTag">';
			
			if (settings.interactive) {
				markup = markup + '<input id="'+id+'_tag" value="" data-default="'+settings.defaultText+'" />';
			}
			
			markup = markup + '</div><div class="tags_clear"></div></div>';
			
			$(markup).insertAfter(this);

			$(data.holder).css('min-height',settings.height);
			$(data.holder).css('height','100%');
	
			if ($(data.real_input).val()!='') { 
				$.fn.tagsInput.importTags($(data.real_input),$(data.real_input).val());
			}		
			if (settings.interactive) { 
				$(data.fake_input).val($(data.fake_input).data('default'));
				//$(data.fake_input).css('color',settings.placeholderColor);
		        $(data.fake_input).resetAutosize(settings);
		
				$(data.holder).bind('click',data,function(event) {
					$(event.data.fake_input).focus();
				});
			
				$(data.fake_input).bind('focus',data,function(event) {
					$(event.data.holder).addClass('focus');
					if ($(event.data.fake_input).val()==$(event.data.fake_input).data('default')) { 
						$(event.data.fake_input).val('');
					}
					//$(event.data.fake_input).css('color','#000000');		
				});
				$(data.fake_input).bind('blur',data,function(event) {
					$(event.data.holder).removeClass('focus');
				});
						
				if (settings.autocomplete_url != undefined) {
					autocomplete_options = {source: settings.autocomplete_url};
					for (attrname in settings.autocomplete) { 
						autocomplete_options[attrname] = settings.autocomplete[attrname]; 
					}
				
					if (jQuery.Autocompleter !== undefined) {
						$(data.fake_input).autocomplete(settings.autocomplete_url, settings.autocomplete);
						$(data.fake_input).bind('result',data,function(event,data,formatted) {
							if (data) {
								$('#'+id).addTag(data[0] + "",{focus:true,unique:(settings.unique)});
							}
					  	});
					} else if (jQuery.ui.autocomplete !== undefined) {
						$(data.fake_input).autocomplete(autocomplete_options);
						$(data.fake_input).bind('autocompleteselect',data,function(event,ui) {
							$(event.data.real_input).addTag(ui.item.value,{focus:true,unique:(settings.unique)});
							return false;
						});
					
						$(data.fake_input).bind('blur',data,function(event) {
							var d = $(this).data('default');
							if ($(event.data.fake_input).val() == '') { 
								$(event.data.fake_input).val($(event.data.fake_input).data('default'));
								//$(event.data.fake_input).css('color',settings.placeholderColor);
							}
							var autocomplete = $( this ).data( "ui-autocomplete" );
						    if (!$(this).val() ) { return; }
						    
						    if (autocomplete.options.autoFocus && !autocomplete.selectedItem) {
							
							    var matcher = new RegExp( "^" + $.ui.autocomplete.escapeRegex( $(this).val() ) + "", "i" );
							    autocomplete.widget().children( ".ui-menu-item" ).each(function() {
							        var item = $( this ).data( "uiAutocompleteItem" );
							        if ( matcher.test( item.label || item.value || item ) ) {
							            autocomplete.selectedItem = item;
							            return false;
							        }
							    });
							    if ( autocomplete.selectedItem ) {
							        autocomplete._trigger( "select", event, { item: autocomplete.selectedItem } );
							    }
							    
							    return false;
							}
						    
						    var d = $(this).data('default');
							if ($(event.data.fake_input).val()!='' && $(event.data.fake_input).val()!=d) { 
								if( (event.data.minChars <= $(event.data.fake_input).val().length) && (!event.data.maxChars || (event.data.maxChars >= $(event.data.fake_input).val().length)) )
									$(event.data.real_input).addTag($(event.data.fake_input).val(),{focus:true,unique:(settings.unique)});
							} else {
								$(event.data.fake_input).val($(event.data.fake_input).data('default'));
								//$(event.data.fake_input).css('color',settings.placeholderColor);
							}
							return false;
						});
					
						$(data.fake_input).parents('form').bind('submit',data,function(event) { 
							var autocomplete = $(data.fake_input).data( "ui-autocomplete" );
						    if ( !autocomplete.options.autoFocus || autocomplete.selectedItem || !$(data.fake_input).val() ) { return true; }
						
						    var matcher = new RegExp( "^" + $.ui.autocomplete.escapeRegex( $(data.fake_input).val() ) + "", "i" );
						    autocomplete.widget().children( ".ui-menu-item" ).each(function() {
						        var item = $( this ).data( "uiAutocompleteItem" );
						        if ( matcher.test( item.label || item.value || item ) ) {
						            autocomplete.selectedItem = item;
						            return false;
						        }
						    });
						    if ( autocomplete.selectedItem ) {
						        autocomplete._trigger( "select", event, { item: autocomplete.selectedItem } );
						    }
						    return true;
						});
					}
				
					
				} else {
						// if a user tabs out of the field, create a new tag
						// this is only available if autocomplete is not used.
						$(data.fake_input).bind('blur',data,function(event) {
							var d = $(this).data('default');
							if ($(event.data.fake_input).val()!='' && $(event.data.fake_input).val()!=d) { 
								if( (event.data.minChars <= $(event.data.fake_input).val().length) && (!event.data.maxChars || (event.data.maxChars >= $(event.data.fake_input).val().length)) )
									$(event.data.real_input).addTag($(event.data.fake_input).val(),{focus:true,unique:(settings.unique)});
							} else {
								$(event.data.fake_input).val($(event.data.fake_input).data('default'));
								//$(event.data.fake_input).css('color',settings.placeholderColor);
							}
							return false;
						});
				
				}
				// if user types a comma, create a new tag
				$(data.fake_input).bind('keypress',data,function(event) {
					if (event.which==event.data.delimiter.charCodeAt(0) || event.which==13) {
					    event.preventDefault();
						if( (event.data.minChars <= $(event.data.fake_input).val().length) && (!event.data.maxChars || (event.data.maxChars >= $(event.data.fake_input).val().length)) ) {
							$(event.data.real_input).addTag($(event.data.fake_input).val(),{focus:true,unique:(settings.unique)});
							var d = $(this).data('default');
							if ($(event.data.fake_input).val() == '') { 
								$(event.data.fake_input).val($(event.data.fake_input).data('default'));
								//$(event.data.fake_input).css('color',settings.placeholderColor);
							}
							var autocomplete = $(data.fake_input).data( "ui-autocomplete" );
						    if ( !autocomplete.options.autoFocus || autocomplete.selectedItem || !$(this).val() ) { return; }
						
						    var matcher = new RegExp( "^" + $.ui.autocomplete.escapeRegex( $(this).val() ) + "", "i" );
						    autocomplete.widget().children( ".ui-menu-item" ).each(function() {
						        var item = $( this ).data( "uiAutocompleteItem" );
						        if ( matcher.test( item.label || item.value || item ) ) {
						            autocomplete.selectedItem = item;
						            return false;
						        }
						    });
						    if ( autocomplete.selectedItem ) {
						        autocomplete._trigger( "select", event, { item: autocomplete.selectedItem } );
						    }
							
						}
					  	$(event.data.fake_input).resetAutosize(settings);
						return false;
					} else if (event.data.autosize) {
			            $(event.data.fake_input).doAutosize(settings);
            
          			}
				});
				//Delete last tag on backspace
				data.removeWithBackspace && $(data.fake_input).bind('keydown', function(event)
				{
					if(event.keyCode == 8 && $(this).val() == '')
					{
						 event.preventDefault();
						 var last_tag = $(this).closest('.tagsinput').find('.tag:last').text();
						 var id = $(this).prop('id').replace(/_tag$/, '');
						 last_tag = last_tag.replace(/[\s]+\S$/, '');
						 $('#' + id).removeTag(escape(last_tag));
						 $(this).trigger('focus');
					}
				});
				$(data.fake_input).blur();
				
				//Removes the not_valid class when user changes the value of the fake input
				if(data.unique) {
				    $(data.fake_input).keydown(function(event){
				        if(event.keyCode == 8 || String.fromCharCode(event.which).match(/\w+|[áéíóúÁÉÍÓÚñÑ,/]+/)) {
				            $(this).removeClass('not_valid');
				        }
				    });
				}
			} // if settings.interactive
		});
			
		return this;
	
	};
	
	$.fn.tagsInput.updateTagsField = function(obj,tagslist) { 
		var id = $(obj).prop('id');
		$(obj).val(tagslist.join(delimiter[id]));
	};
	
	$.fn.tagsInput.importTags = function(obj,val) {			
		$(obj).val('');
		var id = $(obj).prop('id');
		var tags = val.split(delimiter[id]);
		for (i=0; i<tags.length; i++) { 
			$(obj).addTag(tags[i],{focus:false,callback:false});
		}
		if(tags_callbacks[id] && tags_callbacks[id]['onChange'])
		{
			var f = tags_callbacks[id]['onChange'];
			f.call(obj, obj, tags[i]);
		}
	};

})(jQuery);
