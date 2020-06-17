var umol_mobile_width = 600;

var umol_splash = [
	{ img: 'uma.jpg', color: '#8DA0CF', position: 'right top', size: '100% auto', title: 'UMass Amherst campus' },
	{ img: 'umb.jpg', color: '#82A1C2', position: 'center bottom', size: '100% auto', title: 'UMass Boston campus' },
	{ img: 'umd.jpg', color: '#F5F5F5', position: 'right bottom', size: 'auto 100%', title: 'UMass Dartmouth Undergraduate Commencement' },
	{ img: 'uml.jpg', color: '#5F6492', position: 'center bottom', size: '100% auto', title: 'UMass Lowell Rec Center' }
];

var umol_comparo_range = {
	min: 1,
	max: 3
};

var umol_comparo_open = false;

function umol_get_header_height() {
	var header_height = jQuery('#header').height();
	
	if (jQuery('body').hasClass('admin-menu')) {
		header_height += 30;
	}
	
	return header_height;
}

function umol_get_main_width() {
	return jQuery('#main').width();
}

function umol_is_mobile() {
	var main_width = umol_get_main_width();

	return (main_width <= umol_mobile_width) ? true : false;
}

function umol_set_vertical_offset() {	
	try {
		var hash = window.location.hash;
		if (hash.length > 1) {
			var header_height = umol_get_header_height();
			var offset = jQuery(hash).offset();
			var top = offset.top - header_height;
			jQuery("html, body").animate({ scrollTop: top });
		}
	} catch (e) {
		//
	}
}

function umol_animate_to(anchor_name) {
	var element_scroll_to = jQuery('#' + anchor_name);
	var header_height = umol_get_header_height();
		
	jQuery("html, body").animate({ scrollTop: element_scroll_to.offset().top - header_height }, 500);
}

function umol_set_splash_background() {
	var splash_length = umol_splash.length;
	var splash_index = Math.floor(Math.random() * splash_length);
	
	//temp
	//splash_index = 2;
	
	var splash_bg_img = '/sites/all/themes/umol/images/splash/' + umol_splash[splash_index].img;
	var splash_bg_color = umol_splash[splash_index].color;
	var splash_bg_position = umol_splash[splash_index].position;
	var splash_bg_size = umol_splash[splash_index].size;
	var splash_bg_img_attribute = '';
		
	jQuery('#splash').css('background-color', splash_bg_color);
	jQuery('#splash').css('background-position', splash_bg_position);
	jQuery('#splash').css('background-size', splash_bg_size);
	
	if (umol_is_mobile()) {
		splash_bg_img_attribute = 'none';
		splash_text = '';
	} else {
		splash_bg_img_attribute = 'url(' + splash_bg_img + ')';
		splash_text = umol_splash[splash_index].title;
	}
	
	jQuery('#splash').css('background-image', splash_bg_img_attribute );
	//jQuery('#splash-hilite').text(splash_text);
	jQuery('#splash-hilite').text('');
}

function umol_set_hero_background() {
	var hero_length = umol_splash.length;
	var hero_index = Math.floor(Math.random() * hero_length);
	
	var hero_bg_img = '/sites/all/themes/umol/images/splash/' + umol_splash[hero_index].img;
	var hero_bg_color = umol_splash[hero_index].color;
	var hero_bg_position = umol_splash[hero_index].position;
	var hero_bg_size = umol_splash[hero_index].size;
	var hero_bg_img_attribute = '';
		
	jQuery('#hero').css('background-color', hero_bg_color);
	jQuery('#hero').css('background-position', hero_bg_position);
	jQuery('#hero').css('background-size', hero_bg_size);
	
	if (umol_is_mobile()) {
		hero_bg_img_attribute = 'none';
		hero_text = '';
	} else {
		hero_bg_img_attribute = 'url(' + hero_bg_img + ')';
		hero_text = umol_splash[hero_index].title;
	}
	
	jQuery('#hero').css('background-image', hero_bg_img_attribute );

}

function umol_render_field_sections() {
	var tabs = '<div class="umol-field-section-tabs margin-bot-25">';
	tabs += '<ul>';
	
	jQuery('.umol-field-sections').children('.field').each( function(index) {
		var this_label_row = jQuery(this).children('.field-label').html();
		var this_label = '';		
		var this_id = '';
		
		this_label = this_label_row.replace(':', '');
		this_label = this_label.replace('&nbsp;', ' ');
		this_label = this_label.trim();
		this_label = this_label.replace(' ', '&nbsp;');
		
		this_id = this_label.toLowerCase();		
		this_id = this_id.replace(/[^A-Za-z]/g, '');
		
		// update field label
		jQuery(this).children('.field-label').html(this_label);
		
		// add id attribute to field
		jQuery(this).children('.field-label').parent('.field').attr('id', this_id);
		
		tabs += '<li><a href="#' + this_id + '" class="scroll cta grey large nocaps">' + this_label + '</a></li>';
	});
	
	tabs += '</ul>';
	tabs += '</div>';
	tabs += '<div class="clear"></div>';
	
	jQuery('.umol-field-sections').prepend(tabs);
}



/* DEPRECATED */
function umol_render_field_sections_full_width() {
	var tabs = '<div class="umol-field-section-tabs margin-bot-50">';
	
	jQuery('.umol-field-sections').children('.field').each( function(index) {
		var this_label_row = jQuery(this).children('.field-label').html();
		var this_label = '';		
		var this_id = '';
		
		this_label = this_label_row.replace(':', '');
		this_label = this_label.replace('&nbsp;', ' ');
		
		this_id = this_label.toLowerCase();		
		this_id = this_id.replace(/[^A-Za-z]/g, '');
		
		// update field label
		jQuery(this).children('.field-label').html(this_label);
		
		// add id attribute to field
		jQuery(this).children('.field-label').parent('.field').attr('id', this_id);
		
		tabs += '<div>';
		tabs += '<a href="#' + this_id + '" class="scroll cta grey large full nocaps">:: ' + this_label + ' ::</a>';
		tabs += '</div>';
	});
	
	tabs += '</div>';
	
	jQuery('.umol-field-sections').prepend(tabs);
}

function umol_render_quicktabs(qtid) {
	var content = '';
	var tabs = '';
	var pages = '';
	var tab_limit = 4;
	
	jQuery('#umol_section_content').children('.field').each( function(index) {
		if (index < tab_limit) {
			var this_label_row = jQuery(this).children('.field-label').html();
			var this_label_array = this_label_row.split(':');
			var this_label = this_label_array[0];		
			var this_data = jQuery(this).children('.field-items').children('.field-item').html();
			var this_tab_class = (index == 0) ? 'active' : '';		
			var this_page_class = (index == 0) ? '' : 'quicktabs-hide';
		
			tabs += '<li class="' + this_tab_class + '">';
			tabs += '  <a id="quicktabs-tab-' + qtid + '-' + index + '" class="quicktabs-umol ' + this_tab_class + '">' + this_label + '</a>';
			tabs += '</li>';
		
			pages += '<div id="quicktabs-tabpage-' + qtid + '-' + index + '" class="quicktabs-tabpage ' + this_page_class + '">';
			pages += '  <div class="content">' + this_data + '</div>';
			pages += '</div>';
		}
	});
	
	content += '<div id="quicktabs-' + qtid + '" class="quicktabs-wrapper quicktabs-style-umol">';
	content += '  <div class="item-list">';
	content += '    <ul class="quicktabs-tabs quicktabs-style-umol">' + tabs + '</ul>';
	content += '  </div>';
	content += '  <div id="quicktabs-container-' + qtid + '" class="quicktabs_main quicktabs-style-umol">';
	content += '    ' + pages;
	content += '  </div>';
	content += '</div>';	
	
	jQuery('#umol_section_tabs').html(content);
}

function umol_get_quicktab(id) {
	var id_array = id.split('-');
	var id_array_count = id_array.length;
	var tab_index = id_array[id_array_count - 1];
	
	jQuery('#' + id).parent('li').parent('ul').children('li').each( function(index, element) {
		if (index == tab_index) {
			jQuery(element).addClass('active');
			jQuery(element).children('a').addClass('active');
		} else {
			jQuery(element).removeClass('active');
			jQuery(element).children('a').removeClass('active');		
		}
	});
	
	jQuery('#' + id).parent('li').parent('ul').parent('.item-list').parent('.quicktabs-wrapper').children('.quicktabs_main').children('.quicktabs-tabpage').each( function(index, element) {
		if (index == tab_index) {
			jQuery(element).removeClass('quicktabs-hide');
		} else {
			jQuery(element).addClass('quicktabs-hide');		
		}
	});
}

function umol_set_view_loader() {
	if (jQuery('.view-content').length) {
		jQuery('.view-content').html(jQuery('#loader').html());
	} else if (jQuery('.view-empty').length) {
		jQuery('.view-empty').html(jQuery('#loader').html());
	}
	
	jQuery('.view-header,.view-footer,ul.pager').html('');
		
	umol_animate_to('headline');
	
	if (jQuery('.view-course-search-new').length) {
		umol_set_filter_buttons();
	}
	
	if (jQuery('.view-programs').length) {
		//umol_set_filter_buttons();
	}
}

function umol_set_grad_level_visibility() {
  var level_selection = jQuery('#edit-field-category-value').val();
  
  if (level_selection == '1') {
    jQuery('#edit-field-level-value-wrapper').show();
  } else {
    jQuery('#edit-field-level-value-wrapper').hide();
  }
}

// convert multi-select boxes to single-select on mobile
// hide filters and add button
function umol_set_filters() {	
  umol_set_grad_level_visibility();
  
  var level_selection = jQuery('#edit-field-category-value').val();
  
  if (level_selection == '1') {
    jQuery('#edit-field-level-value-wrapper').show();
  } else {
    jQuery('#edit-field-level-value-wrapper').hide();
  }
  
	if (umol_is_mobile()) {		
		jQuery('.view-filters').wrap('<div class="view-filters-container" style="display:none"></div>');
		jQuery('#filters-switch').remove();
		jQuery('.view-filters-container').before('<div id="filters-switch"><a href="#" class="cta medium blue">Filters</a></div>');
	}
}

function umol_set_register_buttons() {
	jQuery('.views-row').each( function(index, element) {
		if (jQuery(this).children('.views-field-field-registration-url').children('.field-content').children('div').hasClass('reg-status-Open')) {	
			jQuery(this).children('.views-field-field-registration-url').show();
			jQuery(this).children('.views-field-field-url').attr('note', 'hidden').hide();
		} else {
			jQuery(this).children('.views-field-field-url').show();
			jQuery(this).children('.views-field-field-registration-url').hide();
		}
	});
}

function umol_set_filter_buttons() {
	// remove existing filters
	jQuery('#view-filter-buttons').remove();
	
	var filter_buttons = '';
	var this_val;
	var this_label;
	var this_text;
	var this_id;
	
	jQuery('.view-filters .views-exposed-widget .views-widget .form-item select').each( function(index, element) {
		this_val = jQuery(this).val();
		this_label = jQuery(this).parent('.form-item').parent('.views-widget').parent('.views-exposed-widget').children('label').html().trim();
		this_text = jQuery(this).children('option[selected]').text();
		this_id = jQuery(this).attr('id');
		
		if (this_val != 'All') {
			filter_buttons += '<a fid="' + this_id + '">' + this_label + ': ' + this_text + '</a>';
		} 
	});
	
	jQuery('.view-filters .views-exposed-widget .views-widget .form-item input').each( function(index, element) {
		this_val = jQuery(this).val();
		this_label = jQuery(this).parent('.form-item').parent('.views-widget').parent('.views-exposed-widget').children('label').html().trim();
		this_text = this_val;
		this_id = jQuery(this).attr('id');
		
		if (this_val != '') {
			filter_buttons += '<a fid="' + this_id + '">' + this_label + ': ' + this_text + '</a>';
		}
	});
	
	
	if (filter_buttons.length > 0) {
		filter_buttons = '<div id="view-filter-buttons" class="margin-bot-25">' + filter_buttons + '</div>';
	
		jQuery('#block-system-main').prepend(filter_buttons);
	}
}

function umol_get_checked_comparo_items() {
	var items = [];
	var nid;
	
	jQuery('.comparo-check').each( function(index, element) {
		if (jQuery(this).hasClass('checked')) {
			nid = parseInt(jQuery(this).attr('nid'));
			
			items.push(nid);
		}
	});
	
	return items;
}

function umol_toggle_comparo_button() {
	var items = umol_get_checked_comparo_items();
	var launcher = '<a class="cta large blue off comparo-launcher">View Selected</a>';

	if ( !(jQuery('a.comparo-launcher').length) ) {
		jQuery('.view-content').prepend('<div class="margin-bot-25">' + launcher + '</div>');
		jQuery('.view-content').append('<div class="margin-top-25">' + launcher + '</div>');
	}
  
	if (umol_comparo_is_launchable(items)) {
		jQuery('.comparo-launcher').removeClass('off');
	} else {
		jQuery('.comparo-launcher').addClass('off');
	}
}

function umol_comparo_is_launchable(items) {
	if (items.length >= umol_comparo_range.min && items.length <= umol_comparo_range.max) {
		return true;
	} else {
		return false;
	}
}

function umol_set_field_validation(identifier, is_valid=true) {
	if (is_valid) {
		jQuery(identifier).addClass('valid').removeClass('invalid');
	} else {
		jQuery(identifier).removeClass('valid').addClass('invalid');
	}
}

function umol_validate_homeshortrfi() {
	var is_valid = true;
	var firstname = '#homeshortrfi input[name="firstname"]';
	var lastname = '#homeshortrfi input[name="lastname"]';
	var email = '#homeshortrfi input[name="email"]';
  var phone = '#homeshortrfi input[name="phone"]';
  
	if (jQuery(firstname).val() == '') {
		umol_set_field_validation(firstname, false);
		is_valid = false;
	} else {
		umol_set_field_validation(firstname, true);
	}
	
	if (jQuery(lastname).val() == '') {
		umol_set_field_validation(lastname, false);
		is_valid = false;
	} else {
		umol_set_field_validation(lastname, true);
	}
	
	if (jQuery(email).val() == '') {
		umol_set_field_validation(email, false);
		is_valid = false;
	} else {
		umol_set_field_validation(email, true);
	}

  if (jQuery(phone).val() == '') {
		umol_set_field_validation(phone, false);
		is_valid = false;
	} else {
		umol_set_field_validation(phone, true);
	}
	
	return is_valid;
}

function umol_validate_landingpageshortrfi() {
  var lp_short = {
    'firstname': jQuery('#lp-firstname').val(),
    'lastname': jQuery('#lp-lastname').val(),
    'email': jQuery('#lp-email').val()
  };

  jQuery('#rfi-landingpageform #edit-firstname').val(lp_short.firstname);
  jQuery('#rfi-landingpageform #edit-lastname').val(lp_short.lastname);
  jQuery('#rfi-landingpageform #edit-email').val(lp_short.email);
  
  umol_animate_to('block-rfi-rfi-landingpageform');
}


function umol_align_module_boxes() {
	var b_width = jQuery('body').width();
	var h_max = 0;
	var h_this = 0;
	var m_this = 0;
		
	if (b_width >= 960) {		
		jQuery('.module-boxes .module-box-description').each( function() {
			h_this = jQuery(this).height();
			
			if (h_this > h_max) {
				h_max = h_this;
			}
		});
		
		jQuery('.module-boxes .module-box-description').each( function() {
			h_this = jQuery(this).height();
			
			if (h_this < h_max) {
				m_this = h_max - h_this;
				jQuery(this).css('margin-bottom', m_this + 'px');
			}
		});
	} else {
		jQuery('.module-box-description').css('margin-bottom', 'auto');
	}
}

jQuery(document).ready(function($) {

	if (jQuery('#splash').length) {
		umol_set_splash_background();
	}
  
  if (jQuery('#hero').length) {
		umol_set_hero_background();
	}
	
	if (jQuery('#hero-levels').length) {
		jQuery('#hero-levels').children('a').click( function() {
			var lid = jQuery(this).attr('lid');
			
			jQuery('#hero-levels').hide();
			
			jQuery('.hero-subject').hide();
			
			jQuery('.hero-subject[lid="' + lid + '"]').show();
		});	
	}
	
	if (jQuery('.view.content-search > .view-filters').length) {
		umol_set_filters();
	}
	
	if (jQuery('.view-course-search-new').length) {
		umol_set_filter_buttons();
		
		umol_set_register_buttons();
	}
	
	if (jQuery('.view-programs').length) {				
		umol_toggle_comparo_button();
	}
	
	if (jQuery('.ds-2col-programs').length) {
		umol_render_field_sections();
	}
	
	if (jQuery('#umol_section_tabs').length && jQuery('#umol_section_content')) {
		var qtid = jQuery('#umol_section_tabs').attr('qtid');
		
		umol_render_quicktabs(qtid);
	}
	
	jQuery('.quicktabs-umol').live('click', function() {
		umol_get_quicktab(jQuery(this).attr('id'));
	});
	
	jQuery('#filters-switch').live('click', function() {
		jQuery('.view-filters-container').toggle('slow');
	});
	
	// anchor with allowance for header height	
	umol_set_vertical_offset();
	
	// scroll to top	
	jQuery('a[href=#top]').click(function(){
		jQuery('html, body').animate({scrollTop:0}, 'slow');
		return false;
	});
	
	// scroll to anchor with allowance for header height	
	jQuery('a.scroll').click(function(e) {
		e.preventDefault();
		var anchor_name = jQuery(this).attr('href');
		
		if (anchor_name.indexOf("/") == 0) {
			anchor_name = anchor_name.replace("/", '');
		}
		
		if (anchor_name.indexOf("#") == 0) {
			anchor_name = anchor_name.replace("#", '');
		}
		
		umol_animate_to(anchor_name);
	});
	
	// if ajax and autosubmit are enabled for views
	jQuery('.views-exposed-widget .views-widget .form-item input, #edit-title').live('change', function() {		
		return false;
	});
	
	jQuery('.form-text').removeClass('ctools-auto-submit-processed');

	
	jQuery('#view-filter-buttons > a').live('click', function() {		
		var field = {};
		
		field.id = jQuery(this).attr('fid');
		field.type = jQuery('#' + field.id).attr('type');
		
		switch (field.type) {
			case 'text': {
				jQuery('#' + field.id).val('');
				
				// text field won't respond to change trigger, so change campus
				jQuery('#edit-field-campus-value').trigger('change');
				
				break;
			}
			default: {
				jQuery('#' + field.id).val('All').trigger('change');
			
				break;
			}
		}
	});
	
	jQuery('#edit-title').live('blur', function() {});
	
	jQuery("#edit-search-block-form--2").autocomplete({
		source: umol_autocomplete_data,
		minLength: 3,
		select: function(event, ui) {
			jQuery(this).val('');
			window.location.href = ui.item.value;
			return false;
		},
		focus: function(event, ui) {
			return false;
		}
	});
	
	jQuery("#edit-keys").autocomplete({
		source: umol_autocomplete_data,
		minLength: 3
	});	
	
	jQuery(".comparo-check").live('click', function() {
		var nid = jQuery(this).attr('nid');
		
		if (jQuery(this).hasClass('checked')) {
			jQuery(this).removeClass('checked');
		} else {
			jQuery(this).addClass('checked');
		}
		
		umol_toggle_comparo_button();	
	});
	
	jQuery(".comparo-launcher").live('click', function() {
		var items = umol_get_checked_comparo_items();
		
		if (umol_comparo_is_launchable(items)) {
			umol_comparo_open = true;

			var url = '/comparo?items=' + items.join(',');
			
			jQuery.fancybox({
				overlayOpacity: 0.8,
				href: url				
			});
			
			umol_comparo_open = false;
		}
	});
	
	jQuery('#homeshortrfi-submit').click( function() {
		if (umol_validate_homeshortrfi()) {
			jQuery('#homeshortrfi').submit();
		}
	});
  
  jQuery('#landingpageshortrfi-submit').click( function() {
		umol_validate_landingpageshortrfi();
	});
	
	if (jQuery('.module-boxes').length) {
		umol_align_module_boxes();
	}  
});

Drupal.behaviors.umol = {
	attach: function(context, settings) {
		// reset register/details buttons after ajax success
		jQuery('#views-exposed-form-course-search-new-page', context).ajaxSuccess(function() {
			umol_set_register_buttons();
		});
		
		jQuery('#views-exposed-form-programs-page', context).ajaxSuccess(function() {
			umol_set_grad_level_visibility();

			umol_toggle_comparo_button();
		});
		
		// remove class from text filters
		jQuery('.form-text').removeClass('ctools-auto-submit-processed');
	}
}