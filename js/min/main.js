function validateEmail(e){var t=/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;return t.test(e)}!function(e,t,$,i){"use strict";var n=$(e),o=$(t),a=$.fancybox=function(){a.open.apply(this,arguments)},r=navigator.userAgent.match(/msie/),s=null,l=t.createTouch!==i,c=function(e){return e&&e.hasOwnProperty&&e instanceof $},d=function(e){return e&&"string"===$.type(e)},p=function(e){return d(e)&&e.indexOf("%")>0},u=function(e){return e&&!(e.style.overflow&&"hidden"===e.style.overflow)&&(e.clientWidth&&e.scrollWidth>e.clientWidth||e.clientHeight&&e.scrollHeight>e.clientHeight)},h=function(e,t){var i=parseInt(e,10)||0;return t&&p(e)&&(i=a.getViewport()[t]/100*i),Math.ceil(i)},f=function(e,t){return h(e,t)+"px"};$.extend(a,{version:"2.1.4",defaults:{padding:15,margin:20,width:800,height:600,minWidth:100,minHeight:100,maxWidth:9999,maxHeight:9999,autoSize:!0,autoHeight:!1,autoWidth:!1,autoResize:!0,autoCenter:!l,fitToView:!0,aspectRatio:!1,topRatio:.5,leftRatio:.5,scrolling:"auto",wrapCSS:"",arrows:!0,closeBtn:!0,closeClick:!1,nextClick:!1,mouseWheel:!0,autoPlay:!1,playSpeed:3e3,preload:3,modal:!1,loop:!0,ajax:{dataType:"html",headers:{"X-fancyBox":!0}},iframe:{scrolling:"auto",preload:!0},swf:{wmode:"transparent",allowfullscreen:"true",allowscriptaccess:"always"},keys:{next:{13:"left",34:"up",39:"left",40:"up"},prev:{8:"right",33:"down",37:"right",38:"down"},close:[27],play:[32],toggle:[70]},direction:{next:"left",prev:"right"},scrollOutside:!0,index:0,type:null,href:null,content:null,title:null,tpl:{wrap:'<div class="fancybox-wrap" tabIndex="-1"><div class="fancybox-skin"><div class="fancybox-outer"><div class="fancybox-inner"></div></div></div></div>',image:'<img class="fancybox-image" src="{href}" alt="" />',iframe:'<iframe id="fancybox-frame{rnd}" name="fancybox-frame{rnd}" class="fancybox-iframe" frameborder="0" vspace="0" hspace="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen'+(r?' allowtransparency="true"':"")+"></iframe>",error:'<p class="fancybox-error">The requested content cannot be loaded.<br/>Please try again later.</p>',closeBtn:'<a title="Close" class="fancybox-item fancybox-close" href="javascript:;"></a>',next:'<a title="Next" class="fancybox-nav fancybox-next" href="javascript:;"><span></span></a>',prev:'<a title="Previous" class="fancybox-nav fancybox-prev" href="javascript:;"><span></span></a>'},openEffect:"fade",openSpeed:250,openEasing:"swing",openOpacity:!0,openMethod:"zoomIn",closeEffect:"fade",closeSpeed:250,closeEasing:"swing",closeOpacity:!0,closeMethod:"zoomOut",nextEffect:"elastic",nextSpeed:250,nextEasing:"swing",nextMethod:"changeIn",prevEffect:"elastic",prevSpeed:250,prevEasing:"swing",prevMethod:"changeOut",helpers:{overlay:!0,title:!0},onCancel:$.noop,beforeLoad:$.noop,afterLoad:$.noop,beforeShow:$.noop,afterShow:$.noop,beforeChange:$.noop,beforeClose:$.noop,afterClose:$.noop},group:{},opts:{},previous:null,coming:null,current:null,isActive:!1,isOpen:!1,isOpened:!1,wrap:null,skin:null,outer:null,inner:null,player:{timer:null,isActive:!1},ajaxLoad:null,imgPreload:null,transitions:{},helpers:{},open:function(e,t){return e&&($.isPlainObject(t)||(t={}),!1!==a.close(!0))?($.isArray(e)||(e=c(e)?$(e).get():[e]),$.each(e,function(n,o){var r={},s,l,p,u,h,f,g;"object"===$.type(o)&&(o.nodeType&&(o=$(o)),c(o)?(r={href:o.data("fancybox-href")||o.attr("href"),title:o.data("fancybox-title")||o.attr("title"),isDom:!0,element:o},$.metadata&&$.extend(!0,r,o.metadata())):r=o),s=t.href||r.href||(d(o)?o:null),l=t.title!==i?t.title:r.title||"",p=t.content||r.content,u=p?"html":t.type||r.type,!u&&r.isDom&&(u=o.data("fancybox-type"),u||(h=o.prop("class").match(/fancybox\.(\w+)/),u=h?h[1]:null)),d(s)&&(u||(a.isImage(s)?u="image":a.isSWF(s)?u="swf":"#"===s.charAt(0)?u="inline":d(o)&&(u="html",p=o)),"ajax"===u&&(f=s.split(/\s+/,2),s=f.shift(),g=f.shift())),p||("inline"===u?s?p=$(d(s)?s.replace(/.*(?=#[^\s]+$)/,""):s):r.isDom&&(p=o):"html"===u?p=s:u||s||!r.isDom||(u="inline",p=o)),$.extend(r,{href:s,type:u,content:p,title:l,selector:g}),e[n]=r}),a.opts=$.extend(!0,{},a.defaults,t),t.keys!==i&&(a.opts.keys=t.keys?$.extend({},a.defaults.keys,t.keys):!1),a.group=e,a._start(a.opts.index)):void 0},cancel:function(){var e=a.coming;e&&!1!==a.trigger("onCancel")&&(a.hideLoading(),a.ajaxLoad&&a.ajaxLoad.abort(),a.ajaxLoad=null,a.imgPreload&&(a.imgPreload.onload=a.imgPreload.onerror=null),e.wrap&&e.wrap.stop(!0,!0).trigger("onReset").remove(),a.coming=null,a.current||a._afterZoomOut(e))},close:function(e){a.cancel(),!1!==a.trigger("beforeClose")&&(a.unbindEvents(),a.isActive&&(a.isOpen&&e!==!0?(a.isOpen=a.isOpened=!1,a.isClosing=!0,$(".fancybox-item, .fancybox-nav").remove(),a.wrap.stop(!0,!0).removeClass("fancybox-opened"),a.transitions[a.current.closeMethod]()):($(".fancybox-wrap").stop(!0).trigger("onReset").remove(),a._afterZoomOut())))},play:function(e){var t=function(){clearTimeout(a.player.timer)},i=function(){t(),a.current&&a.player.isActive&&(a.player.timer=setTimeout(a.next,a.current.playSpeed))},n=function(){t(),$("body").unbind(".player"),a.player.isActive=!1,a.trigger("onPlayEnd")},o=function(){a.current&&(a.current.loop||a.current.index<a.group.length-1)&&(a.player.isActive=!0,$("body").bind({"afterShow.player onUpdate.player":i,"onCancel.player beforeClose.player":n,"beforeLoad.player":t}),i(),a.trigger("onPlayStart"))};e===!0||!a.player.isActive&&e!==!1?o():n()},next:function(e){var t=a.current;t&&(d(e)||(e=t.direction.next),a.jumpto(t.index+1,e,"next"))},prev:function(e){var t=a.current;t&&(d(e)||(e=t.direction.prev),a.jumpto(t.index-1,e,"prev"))},jumpto:function(e,t,n){var o=a.current;o&&(e=h(e),a.direction=t||o.direction[e>=o.index?"next":"prev"],a.router=n||"jumpto",o.loop&&(0>e&&(e=o.group.length+e%o.group.length),e%=o.group.length),o.group[e]!==i&&(a.cancel(),a._start(e)))},reposition:function(e,t){var i=a.current,n=i?i.wrap:null,o;n&&(o=a._getPosition(t),e&&"scroll"===e.type?(delete o.position,n.stop(!0,!0).animate(o,200)):(n.css(o),i.pos=$.extend({},i.dim,o)))},update:function(e){var t=e&&e.type,i=!t||"orientationchange"===t;i&&(clearTimeout(s),s=null),a.isOpen&&!s&&(s=setTimeout(function(){var n=a.current;n&&!a.isClosing&&(a.wrap.removeClass("fancybox-tmp"),(i||"load"===t||"resize"===t&&n.autoResize)&&a._setDimension(),"scroll"===t&&n.canShrink||a.reposition(e),a.trigger("onUpdate"),s=null)},i&&!l?0:300))},toggle:function(e){a.isOpen&&(a.current.fitToView="boolean"===$.type(e)?e:!a.current.fitToView,l&&(a.wrap.removeAttr("style").addClass("fancybox-tmp"),a.trigger("onUpdate")),a.update())},hideLoading:function(){o.unbind(".loading"),$("#fancybox-loading").remove()},showLoading:function(){var e,t;a.hideLoading(),e=$('<div id="fancybox-loading"><div></div></div>').click(a.cancel).appendTo("body"),o.bind("keydown.loading",function(e){27===(e.which||e.keyCode)&&(e.preventDefault(),a.cancel())}),a.defaults.fixed||(t=a.getViewport(),e.css({position:"absolute",top:.5*t.h+t.y,left:.5*t.w+t.x}))},getViewport:function(){var t=a.current&&a.current.locked||!1,i={x:n.scrollLeft(),y:n.scrollTop()};return t?(i.w=t[0].clientWidth,i.h=t[0].clientHeight):(i.w=l&&e.innerWidth?e.innerWidth:n.width(),i.h=l&&e.innerHeight?e.innerHeight:n.height()),i},unbindEvents:function(){a.wrap&&c(a.wrap)&&a.wrap.unbind(".fb"),o.unbind(".fb"),n.unbind(".fb")},bindEvents:function(){var e=a.current,t;e&&(n.bind("orientationchange.fb"+(l?"":" resize.fb")+(e.autoCenter&&!e.locked?" scroll.fb":""),a.update),t=e.keys,t&&o.bind("keydown.fb",function(n){var o=n.which||n.keyCode,r=n.target||n.srcElement;return 27===o&&a.coming?!1:void(n.ctrlKey||n.altKey||n.shiftKey||n.metaKey||r&&(r.type||$(r).is("[contenteditable]"))||$.each(t,function(t,r){return e.group.length>1&&r[o]!==i?(a[t](r[o]),n.preventDefault(),!1):$.inArray(o,r)>-1?(a[t](),n.preventDefault(),!1):void 0}))}),$.fn.mousewheel&&e.mouseWheel&&a.wrap.bind("mousewheel.fb",function(t,i,n,o){for(var r=t.target||null,s=$(r),l=!1;s.length&&!(l||s.is(".fancybox-skin")||s.is(".fancybox-wrap"));)l=u(s[0]),s=$(s).parent();0===i||l||a.group.length>1&&!e.canShrink&&(o>0||n>0?a.prev(o>0?"down":"left"):(0>o||0>n)&&a.next(0>o?"up":"right"),t.preventDefault())}))},trigger:function(e,t){var i,n=t||a.coming||a.current;if(n){if($.isFunction(n[e])&&(i=n[e].apply(n,Array.prototype.slice.call(arguments,1))),i===!1)return!1;n.helpers&&$.each(n.helpers,function(t,i){i&&a.helpers[t]&&$.isFunction(a.helpers[t][e])&&(i=$.extend(!0,{},a.helpers[t].defaults,i),a.helpers[t][e](i,n))}),$.event.trigger(e+".fb")}},isImage:function(e){return d(e)&&e.match(/(^data:image\/.*,)|(\.(jp(e|g|eg)|gif|png|bmp|webp)((\?|#).*)?$)/i)},isSWF:function(e){return d(e)&&e.match(/\.(swf)((\?|#).*)?$/i)},_start:function(e){var t={},i,n,o,r,s;if(e=h(e),i=a.group[e]||null,!i)return!1;if(t=$.extend(!0,{},a.opts,i),r=t.margin,s=t.padding,"number"===$.type(r)&&(t.margin=[r,r,r,r]),"number"===$.type(s)&&(t.padding=[s,s,s,s]),t.modal&&$.extend(!0,t,{closeBtn:!1,closeClick:!1,nextClick:!1,arrows:!1,mouseWheel:!1,keys:null,helpers:{overlay:{closeClick:!1}}}),t.autoSize&&(t.autoWidth=t.autoHeight=!0),"auto"===t.width&&(t.autoWidth=!0),"auto"===t.height&&(t.autoHeight=!0),t.group=a.group,t.index=e,a.coming=t,!1===a.trigger("beforeLoad"))return void(a.coming=null);if(o=t.type,n=t.href,!o)return a.coming=null,a.current&&a.router&&"jumpto"!==a.router?(a.current.index=e,a[a.router](a.direction)):!1;if(a.isActive=!0,("image"===o||"swf"===o)&&(t.autoHeight=t.autoWidth=!1,t.scrolling="visible"),"image"===o&&(t.aspectRatio=!0),"iframe"===o&&l&&(t.scrolling="scroll"),t.wrap=$(t.tpl.wrap).addClass("fancybox-"+(l?"mobile":"desktop")+" fancybox-type-"+o+" fancybox-tmp "+t.wrapCSS).appendTo(t.parent||"body"),$.extend(t,{skin:$(".fancybox-skin",t.wrap),outer:$(".fancybox-outer",t.wrap),inner:$(".fancybox-inner",t.wrap)}),$.each(["Top","Right","Bottom","Left"],function(e,i){t.skin.css("padding"+i,f(t.padding[e]))}),a.trigger("onReady"),"inline"===o||"html"===o){if(!t.content||!t.content.length)return a._error("content")}else if(!n)return a._error("href");"image"===o?a._loadImage():"ajax"===o?a._loadAjax():"iframe"===o?a._loadIframe():a._afterLoad()},_error:function(e){$.extend(a.coming,{type:"html",autoWidth:!0,autoHeight:!0,minWidth:0,minHeight:0,scrolling:"no",hasError:e,content:a.coming.tpl.error}),a._afterLoad()},_loadImage:function(){var e=a.imgPreload=new Image;e.onload=function(){this.onload=this.onerror=null,a.coming.width=this.width,a.coming.height=this.height,a._afterLoad()},e.onerror=function(){this.onload=this.onerror=null,a._error("image")},e.src=a.coming.href,e.complete!==!0&&a.showLoading()},_loadAjax:function(){var e=a.coming;a.showLoading(),a.ajaxLoad=$.ajax($.extend({},e.ajax,{url:e.href,error:function(e,t){a.coming&&"abort"!==t?a._error("ajax",e):a.hideLoading()},success:function(t,i){"success"===i&&(e.content=t,a._afterLoad())}}))},_loadIframe:function(){var e=a.coming,t=$(e.tpl.iframe.replace(/\{rnd\}/g,(new Date).getTime())).attr("scrolling",l?"auto":e.iframe.scrolling).attr("src",e.href);$(e.wrap).bind("onReset",function(){try{$(this).find("iframe").hide().attr("src","//about:blank").end().empty()}catch(e){}}),e.iframe.preload&&(a.showLoading(),t.one("load",function(){$(this).data("ready",1),l||$(this).bind("load.fb",a.update),$(this).parents(".fancybox-wrap").width("100%").removeClass("fancybox-tmp").show(),a._afterLoad()})),e.content=t.appendTo(e.inner),e.iframe.preload||a._afterLoad()},_preloadImages:function(){var e=a.group,t=a.current,i=e.length,n=t.preload?Math.min(t.preload,i-1):0,o,r;for(r=1;n>=r;r+=1)o=e[(t.index+r)%i],"image"===o.type&&o.href&&((new Image).src=o.href)},_afterLoad:function(){var e=a.coming,t=a.current,i="fancybox-placeholder",n,o,r,s,l,d;if(a.hideLoading(),e&&a.isActive!==!1){if(!1===a.trigger("afterLoad",e,t))return e.wrap.stop(!0).trigger("onReset").remove(),void(a.coming=null);switch(t&&(a.trigger("beforeChange",t),t.wrap.stop(!0).removeClass("fancybox-opened").find(".fancybox-item, .fancybox-nav").remove()),a.unbindEvents(),n=e,o=e.content,r=e.type,s=e.scrolling,$.extend(a,{wrap:n.wrap,skin:n.skin,outer:n.outer,inner:n.inner,current:n,previous:t}),l=n.href,r){case"inline":case"ajax":case"html":n.selector?o=$("<div>").html(o).find(n.selector):c(o)&&(o.data(i)||o.data(i,$('<div class="'+i+'"></div>').insertAfter(o).hide()),o=o.show().detach(),n.wrap.bind("onReset",function(){$(this).find(o).length&&o.hide().replaceAll(o.data(i)).data(i,!1)}));break;case"image":o=n.tpl.image.replace("{href}",l);break;case"swf":o='<object id="fancybox-swf" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="100%" height="100%"><param name="movie" value="'+l+'"></param>',d="",$.each(n.swf,function(e,t){o+='<param name="'+e+'" value="'+t+'"></param>',d+=" "+e+'="'+t+'"'}),o+='<embed src="'+l+'" type="application/x-shockwave-flash" width="100%" height="100%"'+d+"></embed></object>"}c(o)&&o.parent().is(n.inner)||n.inner.append(o),a.trigger("beforeShow"),n.inner.css("overflow","yes"===s?"scroll":"no"===s?"hidden":s),a._setDimension(),a.reposition(),a.isOpen=!1,a.coming=null,a.bindEvents(),a.isOpened?t.prevMethod&&a.transitions[t.prevMethod]():$(".fancybox-wrap").not(n.wrap).stop(!0).trigger("onReset").remove(),a.transitions[a.isOpened?n.nextMethod:n.openMethod](),a._preloadImages()}},_setDimension:function(){var e=a.getViewport(),t=0,i=!1,n=!1,o=a.wrap,r=a.skin,s=a.inner,l=a.current,c=l.width,d=l.height,u=l.minWidth,g=l.minHeight,m=l.maxWidth,y=l.maxHeight,b=l.scrolling,v=l.scrollOutside?l.scrollbarWidth:0,w=l.margin,x=h(w[1]+w[3]),k=h(w[0]+w[2]),C,S,E,O,P,_,j,T,W,A,L,M,D,H,R;if(o.add(r).add(s).width("auto").height("auto").removeClass("fancybox-tmp"),C=h(r.outerWidth(!0)-r.width()),S=h(r.outerHeight(!0)-r.height()),E=x+C,O=k+S,P=p(c)?(e.w-E)*h(c)/100:c,_=p(d)?(e.h-O)*h(d)/100:d,"iframe"===l.type){if(H=l.content,l.autoHeight&&1===H.data("ready"))try{H[0].contentWindow.document.location&&(s.width(P).height(9999),R=H.contents().find("body"),v&&R.css("overflow-x","hidden"),_=R.height())}catch(z){}}else(l.autoWidth||l.autoHeight)&&(s.addClass("fancybox-tmp"),l.autoWidth||s.width(P),l.autoHeight||s.height(_),l.autoWidth&&(P=s.width()),l.autoHeight&&(_=s.height()),s.removeClass("fancybox-tmp"));if(c=h(P),d=h(_),W=P/_,u=h(p(u)?h(u,"w")-E:u),m=h(p(m)?h(m,"w")-E:m),g=h(p(g)?h(g,"h")-O:g),y=h(p(y)?h(y,"h")-O:y),j=m,T=y,l.fitToView&&(m=Math.min(e.w-E,m),y=Math.min(e.h-O,y)),M=e.w-x,D=e.h-k,l.aspectRatio?(c>m&&(c=m,d=h(c/W)),d>y&&(d=y,c=h(d*W)),u>c&&(c=u,d=h(c/W)),g>d&&(d=g,c=h(d*W))):(c=Math.max(u,Math.min(c,m)),l.autoHeight&&"iframe"!==l.type&&(s.width(c),d=s.height()),d=Math.max(g,Math.min(d,y))),l.fitToView)if(s.width(c).height(d),o.width(c+C),A=o.width(),L=o.height(),l.aspectRatio)for(;(A>M||L>D)&&c>u&&d>g&&!(t++>19);)d=Math.max(g,Math.min(y,d-10)),c=h(d*W),u>c&&(c=u,d=h(c/W)),c>m&&(c=m,d=h(c/W)),s.width(c).height(d),o.width(c+C),A=o.width(),L=o.height();else c=Math.max(u,Math.min(c,c-(A-M))),d=Math.max(g,Math.min(d,d-(L-D)));v&&"auto"===b&&_>d&&M>c+C+v&&(c+=v),s.width(c).height(d),o.width(c+C),A=o.width(),L=o.height(),i=(A>M||L>D)&&c>u&&d>g,n=l.aspectRatio?j>c&&T>d&&P>c&&_>d:(j>c||T>d)&&(P>c||_>d),$.extend(l,{dim:{width:f(A),height:f(L)},origWidth:P,origHeight:_,canShrink:i,canExpand:n,wPadding:C,hPadding:S,wrapSpace:L-r.outerHeight(!0),skinSpace:r.height()-d}),!H&&l.autoHeight&&d>g&&y>d&&!n&&s.height("auto")},_getPosition:function(e){var t=a.current,i=a.getViewport(),n=t.margin,o=a.wrap.width()+n[1]+n[3],r=a.wrap.height()+n[0]+n[2],s={position:"absolute",top:n[0],left:n[3]};return t.autoCenter&&t.fixed&&!e&&r<=i.h&&o<=i.w?s.position="fixed":t.locked||(s.top+=i.y,s.left+=i.x),s.top=f(Math.max(s.top,s.top+(i.h-r)*t.topRatio)),s.left=f(Math.max(s.left,s.left+(i.w-o)*t.leftRatio)),s},_afterZoomIn:function(){var e=a.current;e&&(a.isOpen=a.isOpened=!0,a.wrap.css("overflow","visible").addClass("fancybox-opened"),a.update(),(e.closeClick||e.nextClick&&a.group.length>1)&&a.inner.css("cursor","pointer").bind("click.fb",function(t){$(t.target).is("a")||$(t.target).parent().is("a")||(t.preventDefault(),a[e.closeClick?"close":"next"]())}),e.closeBtn&&$(e.tpl.closeBtn).appendTo(a.skin).bind("click.fb",function(e){e.preventDefault(),a.close()}),e.arrows&&a.group.length>1&&((e.loop||e.index>0)&&$(e.tpl.prev).appendTo(a.outer).bind("click.fb",a.prev),(e.loop||e.index<a.group.length-1)&&$(e.tpl.next).appendTo(a.outer).bind("click.fb",a.next)),a.trigger("afterShow"),e.loop||e.index!==e.group.length-1?a.opts.autoPlay&&!a.player.isActive&&(a.opts.autoPlay=!1,a.play()):a.play(!1))},_afterZoomOut:function(e){e=e||a.current,$(".fancybox-wrap").trigger("onReset").remove(),$.extend(a,{group:{},opts:{},router:!1,current:null,isActive:!1,isOpened:!1,isOpen:!1,isClosing:!1,wrap:null,skin:null,outer:null,inner:null}),a.trigger("afterClose",e)}}),a.transitions={getOrigPosition:function(){var e=a.current,t=e.element,i=e.orig,n={},o=50,r=50,s=e.hPadding,l=e.wPadding,d=a.getViewport();return!i&&e.isDom&&t.is(":visible")&&(i=t.find("img:first"),i.length||(i=t)),c(i)?(n=i.offset(),i.is("img")&&(o=i.outerWidth(),r=i.outerHeight())):(n.top=d.y+(d.h-r)*e.topRatio,n.left=d.x+(d.w-o)*e.leftRatio),("fixed"===a.wrap.css("position")||e.locked)&&(n.top-=d.y,n.left-=d.x),n={top:f(n.top-s*e.topRatio),left:f(n.left-l*e.leftRatio),width:f(o+l),height:f(r+s)}},step:function(e,t){var i,n,o,r=t.prop,s=a.current,l=s.wrapSpace,c=s.skinSpace;("width"===r||"height"===r)&&(i=t.end===t.start?1:(e-t.start)/(t.end-t.start),a.isClosing&&(i=1-i),n="width"===r?s.wPadding:s.hPadding,o=e-n,a.skin[r](h("width"===r?o:o-l*i)),a.inner[r](h("width"===r?o:o-l*i-c*i)))},zoomIn:function(){var e=a.current,t=e.pos,i=e.openEffect,n="elastic"===i,o=$.extend({opacity:1},t);delete o.position,n?(t=this.getOrigPosition(),e.openOpacity&&(t.opacity=.1)):"fade"===i&&(t.opacity=.1),a.wrap.css(t).animate(o,{duration:"none"===i?0:e.openSpeed,easing:e.openEasing,step:n?this.step:null,complete:a._afterZoomIn})},zoomOut:function(){var e=a.current,t=e.closeEffect,i="elastic"===t,n={opacity:.1};i&&(n=this.getOrigPosition(),e.closeOpacity&&(n.opacity=.1)),a.wrap.animate(n,{duration:"none"===t?0:e.closeSpeed,easing:e.closeEasing,step:i?this.step:null,complete:a._afterZoomOut})},changeIn:function(){var e=a.current,t=e.nextEffect,i=e.pos,n={opacity:1},o=a.direction,r=200,s;i.opacity=.1,"elastic"===t&&(s="down"===o||"up"===o?"top":"left","down"===o||"right"===o?(i[s]=f(h(i[s])-r),n[s]="+="+r+"px"):(i[s]=f(h(i[s])+r),n[s]="-="+r+"px")),"none"===t?a._afterZoomIn():a.wrap.css(i).animate(n,{duration:e.nextSpeed,easing:e.nextEasing,complete:a._afterZoomIn})},changeOut:function(){var e=a.previous,t=e.prevEffect,i={opacity:.1},n=a.direction,o=200;"elastic"===t&&(i["down"===n||"up"===n?"top":"left"]=("up"===n||"left"===n?"-":"+")+"="+o+"px"),e.wrap.animate(i,{duration:"none"===t?0:e.prevSpeed,easing:e.prevEasing,complete:function(){$(this).trigger("onReset").remove()}})}},a.helpers.overlay={defaults:{closeClick:!0,speedOut:200,showEarly:!0,css:{},locked:!l,fixed:!0},overlay:null,fixed:!1,create:function(e){e=$.extend({},this.defaults,e),this.overlay&&this.close(),this.overlay=$('<div class="fancybox-overlay"></div>').appendTo("body"),this.fixed=!1,e.fixed&&a.defaults.fixed&&(this.overlay.addClass("fancybox-overlay-fixed"),this.fixed=!0)},open:function(e){var t=this;e=$.extend({},this.defaults,e),this.overlay?this.overlay.unbind(".overlay").width("auto").height("auto"):this.create(e),this.fixed||(n.bind("resize.overlay",$.proxy(this.update,this)),this.update()),e.closeClick&&this.overlay.bind("click.overlay",function(e){$(e.target).hasClass("fancybox-overlay")&&(a.isActive?a.close():t.close())}),this.overlay.css(e.css).show()},close:function(){$(".fancybox-overlay").remove(),n.unbind("resize.overlay"),this.overlay=null,this.margin!==!1&&($("body").css("margin-right",this.margin),this.margin=!1),this.el&&this.el.removeClass("fancybox-lock")},update:function(){var e="100%",i;this.overlay.width(e).height("100%"),r?(i=Math.max(t.documentElement.offsetWidth,t.body.offsetWidth),o.width()>i&&(e=o.width())):o.width()>n.width()&&(e=o.width()),this.overlay.width(e).height(o.height())},onReady:function(e,i){$(".fancybox-overlay").stop(!0,!0),this.overlay||(this.margin=o.height()>n.height()||"scroll"===$("body").css("overflow-y")?$("body").css("margin-right"):!1,this.el=$(t.all&&!t.querySelector?"html":"body"),this.create(e)),e.locked&&this.fixed&&(i.locked=this.overlay.append(i.wrap),i.fixed=!1),e.showEarly===!0&&this.beforeShow.apply(this,arguments)},beforeShow:function(e,t){t.locked&&(this.el.addClass("fancybox-lock"),this.margin!==!1&&$("body").css("margin-right",h(this.margin)+t.scrollbarWidth)),this.open(e)},onUpdate:function(){this.fixed||this.update()},afterClose:function(e){this.overlay&&!a.isActive&&this.overlay.fadeOut(e.speedOut,$.proxy(this.close,this))}},a.helpers.title={defaults:{type:"float",position:"bottom"},beforeShow:function(e){var t=a.current,i=t.title,n=e.type,o,s;if($.isFunction(i)&&(i=i.call(t.element,t)),d(i)&&""!==$.trim(i)){switch(o=$('<div class="fancybox-title fancybox-title-'+n+'-wrap">'+i+"</div>"),n){case"inside":s=a.skin;break;case"outside":s=a.wrap;break;case"over":s=a.inner;break;default:s=a.skin,o.appendTo("body"),r&&o.width(o.width()),o.wrapInner('<span class="child"></span>'),a.current.margin[2]+=Math.abs(h(o.css("margin-bottom")))}o["top"===e.position?"prependTo":"appendTo"](s)}}},$.fn.fancybox=function(e){var t,i=$(this),n=this.selector||"",r=function(o){var r=$(this).blur(),s=t,l,c;o.ctrlKey||o.altKey||o.shiftKey||o.metaKey||r.is(".fancybox-wrap")||(l=e.groupAttr||"data-fancybox-group",c=r.attr(l),c||(l="rel",c=r.get(0)[l]),c&&""!==c&&"nofollow"!==c&&(r=n.length?$(n):i,r=r.filter("["+l+'="'+c+'"]'),s=r.index(this)),e.index=s,a.open(r,e)!==!1&&o.preventDefault())};return e=e||{},t=e.index||0,n&&e.live!==!1?o.undelegate(n,"click.fb-start").delegate(n+":not('.fancybox-item, .fancybox-nav')","click.fb-start",r):i.unbind("click.fb-start").bind("click.fb-start",r),this.filter("[data-fancybox-start=1]").trigger("click"),this},o.ready(function(){$.scrollbarWidth===i&&($.scrollbarWidth=function(){var e=$('<div style="width:50px;height:50px;overflow:auto"><div/></div>').appendTo("body"),t=e.children(),i=t.innerWidth()-t.height(99).innerWidth();return e.remove(),i}),$.support.fixedPosition===i&&($.support.fixedPosition=function(){var e=$('<div style="position:fixed;top:20px;"></div>').appendTo("body"),t=20===e[0].offsetTop||15===e[0].offsetTop;return e.remove(),t}()),$.extend(a.defaults,{scrollbarWidth:$.scrollbarWidth(),fixed:$.support.fixedPosition,parent:$("body")})})}(window,document,jQuery),function($){"use strict";var e=$.fancybox,t=function(e,t,i){return i=i||"","object"===$.type(i)&&(i=$.param(i,!0)),$.each(t,function(t,i){e=e.replace("$"+t,i||"")}),i.length&&(e+=(e.indexOf("?")>0?"&":"?")+i),e};e.helpers.media={defaults:{youtube:{matcher:/(youtube\.com|youtu\.be)\/(watch\?v=|v\/|u\/|embed\/?)?(videoseries\?list=(.*)|[\w-]{11}|\?listType=(.*)&list=(.*)).*/i,params:{autoplay:1,autohide:1,fs:1,rel:0,hd:1,wmode:"opaque",enablejsapi:1},type:"iframe",url:"//www.youtube.com/embed/$3"},vimeo:{matcher:/(?:vimeo(?:pro)?.com)\/(?:[^\d]+)?(\d+)(?:.*)/,params:{autoplay:1,hd:1,show_title:1,show_byline:1,show_portrait:0,fullscreen:1},type:"iframe",url:"//player.vimeo.com/video/$1"},metacafe:{matcher:/metacafe.com\/(?:watch|fplayer)\/([\w\-]{1,10})/,params:{autoPlay:"yes"},type:"swf",url:function(e,t,i){return i.swf.flashVars="playerVars="+$.param(t,!0),"//www.metacafe.com/fplayer/"+e[1]+"/.swf"}},dailymotion:{matcher:/dailymotion.com\/video\/(.*)\/?(.*)/,params:{additionalInfos:0,autoStart:1},type:"swf",url:"//www.dailymotion.com/swf/video/$1"},twitvid:{matcher:/twitvid\.com\/([a-zA-Z0-9_\-\?\=]+)/i,params:{autoplay:0},type:"iframe",url:"//www.twitvid.com/embed.php?guid=$1"},twitpic:{matcher:/twitpic\.com\/(?!(?:place|photos|events)\/)([a-zA-Z0-9\?\=\-]+)/i,type:"image",url:"//twitpic.com/show/full/$1/"},instagram:{matcher:/(instagr\.am|instagram\.com)\/p\/([a-zA-Z0-9_\-]+)\/?/i,type:"image",url:"//$1/p/$2/media/"},google_maps:{matcher:/maps\.google\.([a-z]{2,3}(\.[a-z]{2})?)\/(\?ll=|maps\?)(.*)/i,type:"iframe",url:function(e){return"//maps.google."+e[1]+"/"+e[3]+e[4]+"&output="+(e[4].indexOf("layer=c")>0?"svembed":"embed")}}},beforeLoad:function(e,i){var n=i.href||"",o=!1,a,r,s,l;for(a in e)if(r=e[a],s=n.match(r.matcher)){o=r.type,l=$.extend(!0,{},r.params,i[a]||($.isPlainObject(e[a])?e[a].params:null)),n="function"===$.type(r.url)?r.url.call(this,s,l,i):t(r.url,s,l);break}o&&(i.href=n,i.type=o,i.autoHeight=!1)}}}(jQuery),function($){var e=$.fancybox;e.helpers.buttons={defaults:{skipSingle:!1,position:"top",tpl:'<div id="fancybox-buttons"><ul><li><a class="btnPrev" title="Previous" href="javascript:;"></a></li><li><a class="btnPlay" title="Start slideshow" href="javascript:;"></a></li><li><a class="btnNext" title="Next" href="javascript:;"></a></li><li><a class="btnToggle" title="Toggle size" href="javascript:;"></a></li><li><a class="btnClose" title="Close" href="javascript:jQuery.fancybox.close();"></a></li></ul></div>'},list:null,buttons:null,beforeLoad:function(e,t){return e.skipSingle&&t.group.length<2?(t.helpers.buttons=!1,void(t.closeBtn=!0)):void(t.margin["bottom"===e.position?2:0]+=30)},onPlayStart:function(){this.buttons&&this.buttons.play.attr("title","Pause slideshow").addClass("btnPlayOn")},onPlayEnd:function(){this.buttons&&this.buttons.play.attr("title","Start slideshow").removeClass("btnPlayOn")},afterShow:function(t,i){var n=this.buttons;n||(this.list=$(t.tpl).addClass(t.position).appendTo("body"),n={prev:this.list.find(".btnPrev").click(e.prev),next:this.list.find(".btnNext").click(e.next),play:this.list.find(".btnPlay").click(e.play),toggle:this.list.find(".btnToggle").click(e.toggle)}),i.index>0||i.loop?n.prev.removeClass("btnDisabled"):n.prev.addClass("btnDisabled"),i.loop||i.index<i.group.length-1?(n.next.removeClass("btnDisabled"),n.play.removeClass("btnDisabled")):(n.next.addClass("btnDisabled"),n.play.addClass("btnDisabled")),this.buttons=n,this.onUpdate(t,i)},onUpdate:function(e,t){var i;this.buttons&&(i=this.buttons.toggle.removeClass("btnDisabled btnToggleOn"),t.canShrink?i.addClass("btnToggleOn"):t.canExpand||i.addClass("btnDisabled"))},beforeClose:function(){this.list&&this.list.remove(),this.list=null,this.buttons=null}}}(jQuery),/**
 * jQuery.fastClick.js
 *
 * Work around the 300ms delay for the click event in some mobile browsers.
 *
 * Code based on <http://code.google.com/mobile/articles/fast_buttons.html>
 *
 * @usage
 * $('button').fastClick(function() {alert('clicked!');});
 *
 * @license MIT
 * @author Dave Hulbert (dave1010)
 * @version 1.0.0 2013-01-17
 */
function($){$.fn.fastClick=function(e){return $(this).each(function(){$.FastButton($(this)[0],e)})},$.FastButton=function(e,t){var i,n,o=function(){$(e).unbind("touchend"),$("body").unbind("touchmove.fastClick")},a=function(e){e.stopPropagation(),o(),t.call(this,e),"touchend"===e.type&&$.clickbuster.preventGhostClick(i,n)},r=function(e){(Math.abs(e.originalEvent.touches[0].clientX-i)>10||Math.abs(e.originalEvent.touches[0].clientY-n)>10)&&o()},s=function(t){t.stopPropagation(),$(e).bind("touchend",a),$("body").bind("touchmove.fastClick",r),i=t.originalEvent.touches[0].clientX,n=t.originalEvent.touches[0].clientY};$(e).bind({touchstart:s,click:a})},$.clickbuster={coordinates:[],preventGhostClick:function(e,t){$.clickbuster.coordinates.push(e,t),window.setTimeout($.clickbuster.pop,2500)},pop:function(){$.clickbuster.coordinates.splice(0,2)},onClick:function(e){var t,i,n;for(n=0;n<$.clickbuster.coordinates.length;n+=2)t=$.clickbuster.coordinates[n],i=$.clickbuster.coordinates[n+1],Math.abs(e.clientX-t)<25&&Math.abs(e.clientY-i)<25&&(e.stopPropagation(),e.preventDefault())}},$(function(){document.addEventListener?document.addEventListener("click",$.clickbuster.onClick,!0):document.attachEvent&&document.attachEvent("onclick",$.clickbuster.onClick)})}(jQuery),$(function(){$("a.youtube").click(function(){return $.fancybox({type:"iframe",fitToView:!0,autoSize:!0,closeClick:!1,width:720,height:405,openEffect:"fade",closeEffect:"fade",aspectRatio:!0,href:this.href,helpers:{overlay:{css:{background:"rgba(30,30,30,0.8)"}},media:{}}}),!1}),$("a.fancybox").fancybox({closeBtn:!0,helpers:{buttons:{position:"bottom"},overlay:{css:{background:"rgba(30,30,30,0.8)"},locked:!1}}}),$("form").submit(function(e){return $(this).find("input.invalid,textarea.invalid,select.invalid").removeClass("invalid"),$(this).find("input[required],textarea[required],select[required]").each(function(){"email"!=$(this).attr("type")||validateEmail($(this).val())?$(this).val()||$(this).addClass("invalid"):$(this).addClass("invalid")}),$(this).find("input.invalid,textarea.invalid,select.invalid").length>0?(alert("Gelieve alle velden correct in te vullen."),$(this).find("input.invalid,textarea.invalid,select.invalid").first().focus(),e.stopImmediatePropagation(),!1):void 0})});