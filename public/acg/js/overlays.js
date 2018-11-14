(function($) {
	var init_grids = jQuery.makeArray();
	
	/* // GENERAL HUB // */
	mgom_hub = function(grid_id) {
		setTimeout(function() { // delay to avoid timing issues
			if(typeof(grid_id) != 'undefined' && typeof(init_grids[grid_id]) == 'undefined') {
				mgom_del_empty_txt(grid_id);
				init_grids[grid_id] = true;
			}
			
			// direction aware overlays
			jQuery('.mgom_mouse_dir').parents('.img_wrap').lc_hoverdir({
				hoverDelay : 25,
				overlaySelector: '.mgom_mouse_dir',
				noTransitionClass: 'mgom_md_init_pos'  
			});
			
			mgom_img_fx(grid_id);

			mgom_vc_txt(grid_id);
			mgom_txt_shortening(grid_id);	
			mgom_txt_vis(grid_id);	
		}, 70);
	}
	
	
	// remove empty textual divs
	var mgom_del_empty_txt = function(grid_id) {
		jQuery('#mg_grid_'+grid_id).find('.mg_box .mgom_txt_wrap > *, .mg_box .mg_title_under > *').not('.mgom_txt_block, .mgom_separator').each(function() {
            var cont = jQuery.trim( jQuery(this).html() );
			if(cont == '') {
				jQuery(this).remove();	
			}
        });
	}
	
	
	/* *** IMAGE EFFECTS *** */
	
	var mgom_img_fx = function(grid_id) {
		var timing = parseInt( jQuery('#mg_grid_'+grid_id).attr('mgom_timing'));
		var fx = jQuery('#mg_grid_'+grid_id).attr('mgom_fx');
		
		if(typeof(fx) != 'undefined') {
			var zoom_attr = jQuery('#mg_grid_'+grid_id).attr('mgom_zoom');
			if( typeof(zoom_attr) != 'undefined' ) {
				var zoom_perc = parseInt(zoom_attr) / 100;
			}
	
			if(fx.indexOf('zoom') != -1) { 
				mgom_zoom_fx( jQuery('#mg_grid_'+grid_id), zoom_perc, timing); 
			}
			
			if(fx.indexOf('grayscale') != -1) { 
				mgom_grayscale_fx( jQuery('#mg_grid_'+grid_id), timing); 
			}
			
			if(fx.indexOf('blur') != -1) { 
				mgom_blur_fx( jQuery('#mg_grid_'+grid_id), timing); 
			}
			
			if(fx.indexOf('no_border') != -1) { 
				var orig_padd = parseInt(jQuery('#mg_grid_'+grid_id).find('.img_wrap').css('padding-left'));
				if(orig_padd > 0) {
					mgom_no_border_fx( jQuery('#mg_grid_'+grid_id), orig_padd, timing); 
				}
			}
		}
	}
	
	// on resize - to match mobile/desktop modes
	jQuery(window).delegate('.mg_grid_wrap', 'mg_mobile_mode_switch', function() {
		var curr_mode = (mg_mobile_mode) ? 'mobile' : 'desktop';
		
		if(jQuery('.mg_container[mgom_fx=grayscale], .mg_container[mgom_fx=blur]').size() > 0) {
			var safe_mg_mobile = (typeof(mg_mobile) == 'undefined') ? 760 : mg_mobile;
			
			// erase current fx and create new one
			jQuery('.mg_container[mgom_fx=grayscale], .mg_container[mgom_fx=blur]').each(function() {
				var grid_id = this.id.substr(8);
				jQuery(this).find('.mgom_grayscale, .mgom_blur').remove();
				
				mgom_img_fx(grid_id);
			});
		}
	});
	

	
	/* ZOOMING */
	var mgom_zoom_fx = function($subj, perc, duration) {
		$subj.delegate('.mg_box:not(.mg_item_no_ol)','mouseenter', function(e) {

			var $img = $(this).find('.thumb');
			$img.stop();
			
			var val_attr = $img.attr('mgom_zoom');
			var curr_pos = (typeof(val_attr) != 'undefined') ? parseFloat($img.attr('mgom_zoom')) : 1;

			$img.animate({mgomZoom : curr_pos}, 0).animate({mgomZoom : (1 + perc)}, {	
				easing: 'swing',
				duration: duration,
				step: function(now, fx) {
					$img.attr('mgom_zoom', now);

					var css_val = 'scale('+ now +','+ now +')';
					$img.css('transform', css_val).css('-ms-transform', css_val).css('-webkit-transform', css_val);
				}
			});
			
		}).
		delegate('.mg_box:not(.mg_item_no_ol)','mouseleave', function(e) {

			var $img = $(this).find('.thumb');
			$img.stop();
			var curr_pos =  parseFloat($img.attr('mgom_zoom'));
			
			$img.animate({mgomZoom : curr_pos}, 0).animate({mgomZoom : 1}, {	
				easing: 'swing',
				duration: duration,
				step: function(now, fx) {
					$img.attr('mgom_zoom', now);
					
					var css_val = 'scale('+ now +','+ now +')';
					$img.css('transform', css_val).css('-ms-transform', css_val).css('-webkit-transform', css_val);
				}
			});
		});	
	}
	
	/* GRAYSCALE */
	var mgom_grayscale_fx = function($subj, duration) {
		
		// create and append grayscale image
		$subj.find('.mg_box:not(.mg_item_no_ol, .mg_pag_hide)').find('img.thumb').not('.mgom_grayscale').each(function() {
			var img = new Image();
			img.onload = function() {
				Pixastic.process(img, "desaturate", {average : false});
			}
			
			jQuery(img).addClass('thumb mgom_grayscale');
			jQuery(this).after(img);
			img.src = jQuery(this).attr('src');
		});
		
		// mouse hover opacity
		$subj.delegate('.mg_box:not(.mg_item_no_ol)','mouseenter touchstart', function(e) {
			jQuery(this).find('.mgom_grayscale').stop().animate({opacity: 0}, duration);
		}).
		delegate('.mg_box:not(.mg_item_no_ol)','mouseleave touchend', function(e) {
			jQuery(this).find('.mgom_grayscale').stop().animate({opacity: 1}, duration);
		});	
	}
	
	/* BLUR */
	var mgom_blur_fx = function($subj, duration) {
		
		// create and append blurred image
		$subj.find('.mg_box:not(.mg_item_no_ol, .mg_pag_hide)').find('img.thumb').not('.mgom_blur').each(function() {
			var img = new Image();
			img.onload = function() {
				Pixastic.process(img, "blurfast", {amount:0.2});
				
				setTimeout(function() {
					jQuery('.mgom_blur').css('opacity', 0).css('filter', 'alpha(opacity=0)');
				}, 50);
			}
			
			jQuery(img).addClass('thumb mgom_blur');
			jQuery(this).after(img);
			img.src = jQuery(this).attr('src');
		});
		
		// mouse hover opacity
		$subj.delegate('.mg_box:not(.mg_item_no_ol)','mouseenter touchstart', function(e) {
			jQuery(this).find('.mgom_blur').stop().animate({opacity: 1}, duration);
		}).
		delegate('.mg_box:not(.mg_item_no_ol)','mouseleave touchend', function(e) {
			jQuery(this).find('.mgom_blur').stop().animate({opacity: 0}, duration);
		});	
	}
	
	/* NO-BORDER */
	var mgom_no_border_fx = function($subj, orig_padd, duration) {
		$subj.delegate('.mg_box:not(.mg_item_no_ol, .mg_inl_slider, .mg_inl_audio)','mouseenter', function(e) {		
			if($(this).find('.thumb').size() > 0) {
				$(this).find('.img_wrap').clearQueue().animate({'padding' : 0}, duration);	
			}
		}).
		delegate('.mg_box:not(.mg_item_no_ol, .mg_inl_slider, .mg_inl_audio)','mouseleave', function(e) {
			if($(this).find('.thumb').size() > 0) {
				$(this).find('.img_wrap').clearQueue().animate({'padding' : orig_padd}, duration);	
			}
		});	
	}
	
	
	/* -- -- */
	
	// overlays visibility on small sizes
	var mgom_small_cell_fix = function() {
		// find every item with custom overlays
		jQuery('.mg_box .overlays > .mgom_layer').parents('.mg_container').find('.img_wrap').each(function() {
			var box_w = jQuery(this).width();
			var box_h = jQuery(this).height();
			
			// hide corners
			if( box_w < 90 || box_h < 90 ) { 
				jQuery(this).find('.mgom_corner_triangle, .mgom_corner_square, .mgom_corner_circle').hide(); 
			}
			else {
				jQuery(this).find('.mgom_corner_triangle, .mgom_corner_square, .mgom_corner_circle').show();
			}
			
			// hide text layer and shapes
			if( box_w < 60 || box_h < 60 ) { 
				jQuery(this).find('.mgom_txt_wrap, .mgom_circle, .mgom_exagon, .mgom_octagon').hide(); 
			}
			else {jQuery(this).find('.mgom_txt_wrap, .mgom_circle, .mgom_exagon, .mgom_octagon').show();}
		});	
	}
	
	jQuery(window).resize(function() {
		if(typeof(mgom_graphic_resize) != 'undefined') {clearTimeout(mgom_graphic_resize);}
		mgom_graphic_resize = setTimeout(function() {
			mgom_small_cell_fix();
		}, 70);
	});
	
	
	
	/* *** TEXT LAYER EFFECTS *** */

	// default state - prepare attributes
	var mgom_txt_vis = function(grid_id) {

		if(typeof(grid_id) != 'undefined') {
			$subj = jQuery('#mg_grid_'+grid_id+'.mgom_txt_vis:not(.mgom_show_all) .mg_box:not(.mg_pag_hide) .mgom_txt_wrap');
			
			// IE 8/9 fix - be sure everything is shown
			if( (navigator.appVersion.indexOf("MSIE 8.") != -1 || navigator.appVersion.indexOf("MSIE 9.") != -1 ) && $subj.size() > 0 ) {
				$subj.show();
			}
		} else {
			$subj = jQuery('.mgom_txt_vis:not(.mgom_show_all) .mg_box:not(.mg_pag_hide) .mgom_txt_wrap');	
		}

		$wrapper = $subj.parents('.mg_container');

		$subj.each(function() {
	   
			// show title + rest on hover with contents height
			if(($wrapper.hasClass('mgom_show_title') || $wrapper.hasClass('mgom_show_title_fh')) && jQuery(this).find('.mgom_title').size() > 0) {
				
				// keep showing the title 
				var tit_h = jQuery(this).find('.mgom_title').outerHeight();
				var flap_h = parseInt(jQuery(this).css('padding-top')) + tit_h;
				var item_h = jQuery(this).parents('.overlays').outerHeight(true);
				
				// recursive check to avoid zero values - wait for the grid to be displayed
				// if title exists and wrong height is got
				if(item_h < 5 || ( jQuery.trim(jQuery(this).find('.mgom_title')) != '' && tit_h < 5 )) {
					setTimeout(function() {
						mgom_txt_vis();
					}, 50);
					return false;
				}
				
				var init_top = Math.round(item_h - flap_h);
				if(init_top < 0) {init_top = 0;}
				
				jQuery(this).css('top', init_top);
			}
			else {
				var init_top = '100%';
				jQuery(this).css('top', init_top);
			}
			jQuery(this).attr('mgom_init_top', init_top);
			

			// set top position on hover - for contents height
			if($wrapper.hasClass('mgom_show_title') || $wrapper.hasClass('mgom_hide_all')) {				
				var item_h = jQuery(this).parents('.overlays').outerHeight(true);
				
				// recursive check to avoid zero values - wait for the grid to be displayed
				if(item_h < 5) {
					setTimeout(function() {
						mgom_txt_vis();
					}, 50);
					return false;
				}
				
				var tot_h = parseInt(jQuery(this).css('padding-top')) + parseInt(jQuery(this).css('padding-bottom'));
				
				if($wrapper.hasClass('mgom_show_title')) {
					tot_h = tot_h + parseInt(jQuery(this).find('.mgom_title').outerHeight(true)) - parseInt(jQuery(this).find('.mgom_title').css('padding-bottom'));	
				}
				
				var exclude_tit = ($wrapper.hasClass('mgom_show_title')) ? ', .mgom_title' : ''; 
				jQuery(this).find('> *').not('.mgom_txt_block' + exclude_tit).each(function() {
					tot_h = tot_h + jQuery(this).outerHeight(true);   
				});
				
				var hover_top = Math.ceil(item_h - tot_h);
				if(hover_top < 0) {hover_top = 0;}
			}
			else {
				var hover_top = 0;
			}
			jQuery(this).attr('mgom_hover_top', hover_top);
			
        });	
		
		 mgom_txt_vis_hover();
	}


	// hover effects
	var mgom_txt_vis_hover = function() {
		jQuery('.mgom_txt_vis').delegate('.mg_box:not(.mg_pag_hide)','mouseenter', function(e) {
			
			if(!jQuery(this).parents('.mg_container').hasClass('mgom_curtain')) { 
				var val = parseInt(jQuery(this).find('.mgom_txt_wrap').attr('mgom_hover_top'));
				jQuery(this).find('.mgom_txt_wrap').css('top', val);
			}
			else {
				// curtain
				var val = jQuery(this).find('.mgom_txt_wrap').outerHeight() * -1;
				jQuery(this).find('.mgom_graphic_wrap, .thumb').clearQueue().animate({'top' : val}, 400);
			}
		}).
		delegate('.mg_box:not(.mg_pag_hide)','mouseleave', function(e) {
			
			if(!jQuery(this).parents('.mg_container').hasClass('mgom_curtain')) { 
			
				var val = jQuery(this).find('.mgom_txt_wrap').attr('mgom_init_top');
				if(typeof(val) != 'undefined') {
					if(val.indexOf('%') === -1) {val = val + 'px';}
					jQuery(this).find('.mgom_txt_wrap').css('top', val);
				}
			}
			else {
				// curtain
				jQuery(this).find('.mgom_graphic_wrap, .thumb').clearQueue().animate({'top' : 0}, 400);	
			}
		});	
	}
	
	
	// center vertically
	var mgom_vc_txt = function(grid_id) {
		$subj = jQuery('#mg_grid_'+grid_id+'.mgom_vc_txt');
		
		// global array to get the original padding
		if(typeof(grid_id) != 'undefined') {
			if(typeof(mgom_vc_paddings) == 'undefined') {mgom_vc_paddings = jQuery.makeArray();}
			mgom_vc_paddings[grid_id] = parseInt($subj.find('.mgom_txt_wrap').css('padding-top'));
		}

		$subj.find('.mg_box:not(.mg_pag_hide) .mgom_txt_wrap').each(function() {
			if( jQuery(this).find('.mgom_wrap_vc_txt').size() == 0) {
				jQuery(this).wrapInner('<div class="mgom_wrap_vc_txt" />'); 
			
				var clone = jQuery(this).find('.mgom_wrap_vc_txt .mgom_txt_block').clone();
				jQuery(this).append(clone);
				jQuery(this).find('.mgom_wrap_vc_txt .mgom_txt_block').remove();
			}
		});
	}


	// text shortening
	mgom_txt_shortening = function(grid_id) {
		var $grid = jQuery('#mg_grid_'+grid_id);
		var $subj = $grid.find('.mg_box:not(.mg_pag_hide) .mgom_descr');	

		if($subj.size() > 0) {
			$subj.each(function() {
				
				// be sure the grid sizes are executed
				if(jQuery(this).parent().height() < 5) {
					setTimeout(function() {
						mgom_txt_shortening(grid_id);
					}, 50);
					return false;
				}
				
				
                // find max-height
				if( $grid.find('.mg_title_under').size() > 0) { // text under items
					var max_h = 150;
				}
				else {
					var max_h = jQuery(this).parents('.mgom_txt_wrap').height();
					max_h = max_h - parseInt(jQuery(this).css('margin-top')) - parseInt(jQuery(this).css('margin-bottom'));
					
					jQuery(this).parent().find('> *').not('.mgom_descr, .mgom_txt_block, p').each(function() {
                    	 max_h = max_h - jQuery(this).outerHeight(true); 
                    });
					
					// title always visible - remove extra padding
					if(($grid.hasClass('mgom_show_title_fh') || $grid.hasClass('mgom_show_title')) && jQuery(this).parent().find('.mgom_title') > 0) {
						max_h = max_h + parseInt(jQuery(this).parent().find('.mgom_title').css('padding-bottom'));	
					}
				}

				jQuery(this).lcweb_txt_shortener('..', max_h);
            });	
			
			// if some has been shortened - trigger resize to adjust masonry placement
			if($grid.find('.mg_title_under .lcnb_shorten').size() > 0) {
				jQuery(window).trigger('resize');	
			}	
		}
	}
	
	
	// on resize
	jQuery(window).resize(function() {
		if(typeof(mgom_on_resize) != 'undefined') {
			clearTimeout(mgom_on_resize);
		}
		if(typeof(mgom_on_resize_masonry) != 'undefined') {
			clearTimeout(mgom_on_resize_masonry);
		}
		
		mgom_on_resize = setTimeout(function() {
			mgom_txt_vis();
			
			// adjust text after isotope animation 
			mgom_on_resize_masonry = setTimeout(function() {
				mgom_txt_vis();
				
				jQuery('.mg_container').each(function() {
					var gid = this.id.substr(8);
					mgom_txt_shortening(gid);
				});
			}, 710);
		}, 50);
	});
	
	
	// on pagination
	jQuery(document).delegate('.mg_grid_wrap', 'mg_did_pag', function() {   
		var gid = this.id.substr(8);
		
		setTimeout(function() {
			mgom_img_fx(gid);
			mgom_txt_vis(gid);
			mgom_txt_shortening(gid);
		}, 100);
	});
	
})(jQuery);


//////////////////////////////////////////////

/* ------------------------------------------------------------------------
	* LCweb text shortener
	*
	* @version: 	1.1
	* @requires:	jQuery v1.5 or later
	* @author:		Luca Montanari (LCweb) (http://projects.lcweb.it)
	
	* Licensed under the MIT license
------------------------------------------------------------------------- */

if( eval("typeof lcweb_txt_shortener != 'function'") ) {
	
(function(a){a.fn.lcweb_txt_shortener=function(g,d,p,q){var n=a(this);"undefined"==typeof lcts_orig_texts&&(lcts_orig_texts=a.makeArray());"undefined"==typeof g&&(g="..");a.fn.lcts_remove_all_attr=function(){return this.each(function(){var b=a.map(this.attributes,function(a){return a.name}),c=a(this);a.each(b,function(a,b){"href"!=b&&"target"!=b&&c.removeAttr(b)})})};return function(){n.each(function(){var b=a(this),c=a(this).attr("lcts-id");"undefined"==typeof c?(c=Math.random().toString(36).substr(2, 9),a(this).attr("lcts-id",c),lcts_orig_texts[c]=b.html()):(b.html(lcts_orig_texts[c]),b.removeClass("lcts_shorten"));b.find("*:empty").not("br, img, i").remove();var c=b.outerHeight(!0),e="undefined"!=typeof d&&d?parseInt(d):b.parent().height();"undefined"!=typeof d&&parseInt(d)>e&&(e=parseInt(d));if(e<c){b.html();b.addClass("lcts_shorten");b.find("*").lcts_remove_all_attr();b.find("*").not("a, p, br, i:empty").each(function(){var b=a(this).contents();a(this).replaceWith(b)});for(var l=b.html().split(" "), h="",f="",k=c=0;c<e&&k<l.length;)if("undefined"!=typeof l[k]){f=h;h=h+l[k]+" ";for(b.html(h+' <span class="lcts_end_txt">'+g+"</span>");-1!=b.html().indexOf('<br> <span class="lcts_end_txt">');)b.find(".lcts_end_txt").prev().remove();c=b.outerHeight(!0);k++}var m=["a","p","i"];a.each(m,function(a,c){var d=f.match("<"+c,"g"),e=f.match("</"+c,"g");null!=d&&(null!=d&&null==e||d.length>e.length)&&(f=f+"</"+c+">");if(a==m.length-1)for(b.html(f+'<span class="lcts_end_txt">'+g+"</span>"),b.find("*:empty").not("br").remove();-1!= b.html().indexOf('<br> <span class="lcts_end_txt">');)b.find(".lcts_end_txt").prev().remove()});b.find("p").last().css("display","inline")}})}()};a.fn.lcts_reset=function(){a(this).each(function(){"undefined"!=typeof lcts_orig_texts&&"undefined"!=typeof a(this).attr("lcts-id")&&(a(this).removeClass("lcts_shorten"),a(this).html(lcts_orig_texts[a(this).attr("lcts-id")]))})};a.fn.lcts_destroy=function(){a(this).each(function(){"undefined"!=typeof lcts_orig_texts&&"undefined"!=typeof a(this).attr("lcts-id")&& (a(this).removeClass("lcts_shorten"),a(this).html(lcts_orig_texts[a(this).attr("lcts-id")]),a(this).removeAttr("lcts-id"),lcts_orig_texts.splice(a(this).attr("lcts-id"),1))})}})(jQuery);

}



/**
 * jquery.hoverdir.js v1.1.0
 *
 * Licensed under the MIT license.
 * http://www.opensource.org/licenses/mit-license.php
 * 
 * Copyright 2012, Codrops
 * LCweb tweaked to work with Overlay Manager add-on - March 2015
 * http://www.codrops.com - http://www.lcweb.it
 */
 
(function(d,f,m){d.lc_HoverDir=function(a,b){this.$el=d(b);this._init(a)};d.lc_HoverDir.defaults={speed:400,easing:"ease",hoverDelay:0,inverse:!1,overlaySelector:"div",noTransitionClass:"no_transition"};d.lc_HoverDir.prototype={_supportsTransitions:function(){var a=(document.body||document.documentElement).style,b="transition";if("string"==typeof a[b])return!0;for(var c="Moz webkit Webkit Khtml O ms".split(" "),b=b.charAt(0).toUpperCase()+b.substr(1),e=0;e<c.length;e++)if("string"==typeof a[c[e]+
b])return!0;return!1},_init:function(a){this.options=d.extend(!0,{},d.lc_HoverDir.defaults,a);this.support=this._supportsTransitions();this._loadEvents()},_loadEvents:function(){var a=this;a.$el.on("mouseenter.lc_hoverdir, mouseleave.lc_hoverdir",function(b){var c=d(this),e=c.find(a.options.overlaySelector),c=a._getDir(c,{x:b.pageX,y:b.pageY}),g=a._getStyle(c);"mouseenter"===b.type?(a.support&&e.addClass(a.options.noTransitionClass),e.hide().css(g.from),clearTimeout(a.tmhover),a.tmhover=setTimeout(function(){a.support&&
e.removeClass(a.options.noTransitionClass);e.show(0,function(){var b=d(this);a._applyAnimation(b,g.to,a.options.speed)})},a.options.hoverDelay)):(clearTimeout(a.tmhover),a._applyAnimation(e,g.from,a.options.speed))})},_getDir:function(a,b){var c=a.width(),e=a.height(),d=(b.x-a.offset().left-c/2)*(c>e?e/c:1),c=(b.y-a.offset().top-e/2)*(e>c?c/e:1);return Math.round((Math.atan2(c,d)*(180/Math.PI)+180)/90+3)%4},_getStyle:function(a){var b,c,e={left:"0px",top:"-100%"},d={left:"0px",top:"100%"},f={left:"-100%",
top:"0px"},h={left:"100%",top:"0px"},k={top:"0px"},l={left:"0px"};switch(a){case 0:b=this.options.inverse?d:e;c=k;break;case 1:b=this.options.inverse?f:h;c=l;break;case 2:b=this.options.inverse?e:d;c=k;break;case 3:b=this.options.inverse?h:f,c=l}return{from:b,to:c}},_applyAnimation:function(a,b,c){this.support?a.stop().css(b):a.stop().animate(b,d.extend(!0,[],{duration:c+"ms"}))}};d.fn.lc_hoverdir=function(a){var b=d.data(this,"lc_hoverdir");if("string"===typeof a){var c=Array.prototype.slice.call(arguments,
1);if(!b){f.console&&f.console.error("cannot call methods on hoverdir prior to initialization; attempted to call method '"+a+"'");return}if(!d.isFunction(b[a])||"_"===a.charAt(0)){f.console&&f.console.error("no such method '"+a+"' for hoverdir instance");return}b[a].apply(b,c)}else b?b._init():b=d.data(this,"lc_hoverdir",new d.lc_HoverDir(a,this));return b}})(jQuery,window);



/*
 * Pixastic - desaturate and fast blur only
 * Copyright (c) 2008 Jacob Seidelin - MIT License [http://www.pixastic.com/lib/license.txt]
 */

if( eval("typeof Pixastic != 'function'") ) {
	
var Pixastic=function(){function b(a,g,f){a.addEventListener?a.addEventListener(g,f,!1):a.attachEvent&&a.attachEvent("on"+g,f)}function m(a){var g=!1,f=function(){g||(g=!0,a())};document.write('<script defer src="//:" id="__onload_ie_pixastic__">\x3c/script>');var c=document.getElementById("__onload_ie_pixastic__");c.onreadystatechange=function(){"complete"==c.readyState&&(c.parentNode.removeChild(c),f())};document.addEventListener&&document.addEventListener("DOMContentLoaded",f,!1);b(window,"load",
f)}function d(){for(var a=e("pixastic",null,"img"),g=e("pixastic",null,"canvas"),f=a.concat(g),c=0;c<f.length;c++)(function(){for(var a=f[c],g=[],b=a.className.split(" "),d=0;d<b.length;d++){var e=b[d];"pixastic-"==e.substring(0,9)&&(e=e.substring(9),""!=e&&g.push(e))}if(g.length)if("img"==a.tagName.toLowerCase())if(b=new Image,b.src=a.src,b.complete)for(b=0;b<g.length;b++)(d=Pixastic.applyAction(a,a,g[b],null))&&(a=d);else b.onload=function(){for(var c=0;c<g.length;c++){var b=Pixastic.applyAction(a,
a,g[c],null);b&&(a=b)}};else setTimeout(function(){for(var c=0;c<g.length;c++){var b=Pixastic.applyAction(a,a,g[c],null);b&&(a=b)}},1)})()}function e(a,g,b){var c=[];null==g&&(g=document);null==b&&(b="*");g=g.getElementsByTagName(b);b=g.length;a=RegExp("(^|\\s)"+a+"(\\s|$)");for(j=i=0;i<b;i++)a.test(g[i].className)&&(c[j]=g[i],j++);return c}function l(a,b){if(Pixastic.debug)try{switch(b){case "warn":console.warn("Pixastic:",a);break;case "error":console.error("Pixastic:",a);break;default:console.log("Pixastic:",
a)}}catch(f){}}"undefined"!=typeof pixastic_parseonload&&pixastic_parseonload&&m(d);var k=function(){var a=document.createElement("canvas"),b=!1;try{b=!("function"!=typeof a.getContext||!a.getContext("2d"))}catch(f){}return function(){return b}}(),p=function(){var a=document.createElement("canvas"),b=!1,f;try{"function"==typeof a.getContext&&(f=a.getContext("2d"))&&(b="function"==typeof f.getImageData)}catch(c){}return function(){return b}}(),q=function(){var a=!1,b=document.createElement("canvas");
if(k()&&p()){b.width=b.height=1;b=b.getContext("2d");b.fillStyle="rgb(255,0,0)";b.fillRect(0,0,1,1);var f=document.createElement("canvas");f.width=f.height=1;var c=f.getContext("2d");c.fillStyle="rgb(0,0,255)";c.fillRect(0,0,1,1);b.globalAlpha=0.5;b.drawImage(f,0,0);a=255!=b.getImageData(0,0,1,1).data[2]}return function(){return a}}();return{parseOnLoad:!1,debug:!1,applyAction:function(a,b,f,c){c=c||{};var d="canvas"==a.tagName.toLowerCase();if(d&&Pixastic.Client.isIE())return Pixastic.debug&&l("Tried to process a canvas element but browser is IE."),
!1;var h,e,m=!1;Pixastic.Client.hasCanvas()&&(m=!!c.resultCanvas,h=c.resultCanvas||document.createElement("canvas"),e=h.getContext("2d"));var k=a.offsetWidth,r=a.offsetHeight;d&&(k=a.width,r=a.height);if(0==k||0==r)if(null==a.parentNode){var n=a.style.position,p=a.style.left;a.style.position="absolute";a.style.left="-9999px";document.body.appendChild(a);k=a.offsetWidth;r=a.offsetHeight;document.body.removeChild(a);a.style.position=n;a.style.left=p}else{Pixastic.debug&&l("Image has 0 width and/or height.");
return}if(-1<f.indexOf("(")&&(n=f,f=n.substr(0,n.indexOf("(")),n=n.match(/\((.*?)\)/),n[1]))for(n=n[1].split(";"),p=0;p<n.length;p++)if(thisArg=n[p].split("="),2==thisArg.length)if("rect"==thisArg[0]){var q=thisArg[1].split(",");c[thisArg[0]]={left:parseInt(q[0],10)||0,top:parseInt(q[1],10)||0,width:parseInt(q[2],10)||0,height:parseInt(q[3],10)||0}}else c[thisArg[0]]=thisArg[1];c.rect?(c.rect.left=Math.round(c.rect.left),c.rect.top=Math.round(c.rect.top),c.rect.width=Math.round(c.rect.width),c.rect.height=
Math.round(c.rect.height)):c.rect={left:0,top:0,width:k,height:r};n=!1;Pixastic.Actions[f]&&"function"==typeof Pixastic.Actions[f].process&&(n=!0);if(!n)return Pixastic.debug&&l('Invalid action "'+f+'". Maybe file not included?'),!1;if(!Pixastic.Actions[f].checkSupport())return Pixastic.debug&&l('Action "'+f+'" not supported by this browser.'),!1;Pixastic.Client.hasCanvas()?(h!==a&&(h.width=k,h.height=r),m||(h.style.width=k+"px",h.style.height=r+"px"),e.drawImage(b,0,0,k,r),a.__pixastic_org_image?
(h.__pixastic_org_image=a.__pixastic_org_image,h.__pixastic_org_width=a.__pixastic_org_width,h.__pixastic_org_height=a.__pixastic_org_height):(h.__pixastic_org_image=a,h.__pixastic_org_width=k,h.__pixastic_org_height=r)):Pixastic.Client.isIE()&&"undefined"==typeof a.__pixastic_org_style&&(a.__pixastic_org_style=a.style.cssText);b={image:a,canvas:h,width:k,height:r,useData:!0,options:c};return Pixastic.Actions[f].process(b)?Pixastic.Client.hasCanvas()?(b.useData&&Pixastic.Client.hasCanvasImageData()&&
(h.getContext("2d").putImageData(b.canvasData,c.rect.left,c.rect.top),h.getContext("2d").fillRect(0,0,0,0)),c.leaveDOM||(h.title=a.title,h.imgsrc=a.imgsrc,d||(h.alt=a.alt),d||(h.imgsrc=a.src),h.className=a.className,h.style.cssText=a.style.cssText,h.name=a.name,h.tabIndex=a.tabIndex,h.id=a.id,a.parentNode&&a.parentNode.replaceChild&&a.parentNode.replaceChild(h,a)),c.resultCanvas=h):a:!1},prepareData:function(a,b){var f=a.canvas.getContext("2d"),c=a.options.rect,f=f.getImageData(c.left,c.top,c.width,
c.height),c=f.data;b||(a.canvasData=f);return c},process:function(a,b,f,c){if("img"==a.tagName.toLowerCase()){var d=new Image;d.src=a.src;if(d.complete){var e=Pixastic.applyAction(a,d,b,f);c&&c(e);return e}d.onload=function(){var e=Pixastic.applyAction(a,d,b,f);c&&c(e)}}if("canvas"==a.tagName.toLowerCase())return e=Pixastic.applyAction(a,a,b,f),c&&c(e),e},revert:function(a){if(Pixastic.Client.hasCanvas()){if("canvas"==a.tagName.toLowerCase()&&a.__pixastic_org_image)return a.width=a.__pixastic_org_width,
a.height=a.__pixastic_org_height,a.getContext("2d").drawImage(a.__pixastic_org_image,0,0),a.parentNode&&a.parentNode.replaceChild&&a.parentNode.replaceChild(a.__pixastic_org_image,a),a}else Pixastic.Client.isIE()&&"undefined"!=typeof a.__pixastic_org_style&&(a.style.cssText=a.__pixastic_org_style)},Client:{hasCanvas:k,hasCanvasImageData:p,hasGlobalAlpha:q,isIE:function(){return!!document.all&&!!window.attachEvent&&!window.opera}},Actions:{}}}();
"undefined"!=typeof jQuery&&jQuery&&jQuery.fn&&(jQuery.fn.pixastic=function(b,m){var d=[];this.each(function(){if("img"!=this.tagName.toLowerCase()||this.complete){var e=Pixastic.process(this,b,m);e&&d.push(e)}});return 0<d.length?jQuery(d):this});
Pixastic.Actions.blurfast={process:function(b){var m=parseFloat(b.options.amount)||0,d=!(!b.options.clear||"false"==b.options.clear),m=Math.max(0,Math.min(5,m));if(Pixastic.Client.hasCanvas()){var e=b.options.rect,l=b.canvas.getContext("2d");l.save();l.beginPath();l.rect(e.left,e.top,e.width,e.height);l.clip();var k=Math.round(b.width/2),p=Math.round(b.height/2),q=document.createElement("canvas");q.width=k;q.height=p;for(var d=!1,m=Math.round(20*m),a=q.getContext("2d"),g=0;g<m;g++){var f=Math.max(1,
Math.round(k-g)),c=Math.max(1,Math.round(p-g));a.clearRect(0,0,k,p);a.drawImage(b.canvas,0,0,b.width,b.height,0,0,f,c);d&&l.clearRect(e.left,e.top,e.width,e.height);l.drawImage(q,0,0,f,c,0,0,b.width,b.height)}l.restore();b.useData=!1;return!0}if(Pixastic.Client.isIE())return d=10*m,b.image.style.filter+=" progid:DXImageTransform.Microsoft.Blur(pixelradius="+d+")",b.image.style.marginLeft=(parseInt(b.image.style.marginLeft,10)||0)-Math.round(d)+"px",b.image.style.marginTop=(parseInt(b.image.style.marginTop,
10)||0)-Math.round(d)+"px",!0},checkSupport:function(){return Pixastic.Client.hasCanvas()||Pixastic.Client.isIE()}};
Pixastic.Actions.desaturate={process:function(b){var m=!(!b.options.average||"false"==b.options.average);if(Pixastic.Client.hasCanvasImageData()){var d=Pixastic.prepareData(b);b=b.options.rect;b=b.width*b.height;var e=4*b,l,k;if(m)for(;b--;)d[e-=4]=d[l=e+1]=d[k=e+2]=(d[e]+d[l]+d[k])/3;else for(;b--;)d[e-=4]=d[l=e+1]=d[k=e+2]=0.3*d[e]+0.59*d[l]+0.11*d[k];return!0}if(Pixastic.Client.isIE())return b.image.style.filter+=" gray",!0},checkSupport:function(){return Pixastic.Client.hasCanvasImageData()||
Pixastic.Client.isIE()}};

}