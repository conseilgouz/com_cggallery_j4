/**
* CG Gallery Component  - Joomla Module 
* Version			: 2.4.5
* Package			: Joomla 4.x/5.x
* copyright 		: Copyright (C) 2023 ConseilGouz. All rights reserved.
* license    		: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/
jQuery(document).ready(function($) {
$('.cg_gallery').each(function() {
	var options;
	var $this = $(this);
	var myid = $this.attr("data");
	if (typeof Joomla === 'undefined' || typeof Joomla.getOptions === 'undefined') {
		console.error('Joomla.getOptions not found!\nThe Joomla core.js file is not being loaded.');
		return false;
	}
	options = Joomla.getOptions('cg_gallery_'+myid);
	if (typeof options === 'undefined' ) {return false}
	go_gallery(myid,options); 
})
})
function go_gallery (myid,options) {
	var me = "#cg_gallery_"+myid+" ";
	$params = {	gallery_images_preload_type:"minimal",tile_enable_shadow: true,	lightbox_type: "compact"};
	if (options.ug_lightbox == "false") {
		if (options.ug_link == "true") {
			jQuery.extend($params,{tile_as_link:true});
		} else { 
			jQuery.extend($params,{tile_enable_icons:false});
		}
	}else {
		if (options.ug_link == "true") {		
			jQuery.extend($params,{tile_show_link_icon:true,tile_link_newpage: false});
		}
	}
	if ((options.ug_texte == "fixe") || (options.ug_texte == "true"))  { 
		jQuery.extend($params,{tile_enable_textpanel: true,tile_textpanel_source: "desc",lightbox_show_textpanel: true});
		if (options.ug_texte == "fixe") {
			jQuery.extend($params,{tile_textpanel_always_on: true});
		}
	} 
	if (options.ug_type == "grid") { 
		jQuery.extend($params,{gallery_theme:options.ug_type,grid_space_between_rows:parseInt(options.ug_space_between_rows),grid_space_between_cols:parseInt(options.ug_space_between_cols),slider_textpanel_enable_title: false,grid_num_cols:1, theme_panel_position : options.ug_grid_thumbs_pos});
		if (options.ug_lightbox == "false") { // 1.1.4 : zoom
			jQuery.extend($params,{slider_enable_fullscreen_button: false});
		} else {
			jQuery.extend($params,{slider_enable_fullscreen_button: true});
		}
		if (options.ug_zoom == "false") {
			jQuery.extend($params,{slider_control_zoom: false});
			jQuery.extend($params,{slider_enable_zoom_panel: false});
		} else {
			jQuery.extend($params,{slider_control_zoom: true});
			jQuery.extend($params,{slider_enable_zoom_panel: true});
		}
		if (optionsoptions.ug_grid_show_icons == 'false') {
			jQuery.extend($params,{slider_enable_arrows:false,slider_enable_progress_indicator:false,slider_enable_play_button:false,slider_enable_fullscreen_button:false,slider_enable_zoom_panel:false,	strippanel_enable_handle:false,	gridpanel_enable_handle:false});
		}
	}
	if (options.ug_type == "carousel") {
		jQuery.extend($params,{gallery_theme:options.ug_type,theme_navigation_margin: 5,tile_width:parseInt(options.ug_tile_width),tile_height:parseInt(options.ug_tile_height),
		carousel_space_between_rows:parseInt(options.ug_space_between_rows),carousel_space_between_tiles:parseInt(options.ug_space_between_cols),carousel_scroll_duration:parseInt(options.ug_carousel_scroll_duration),carousel_autoplay_timeout:parseInt(options.ug_carousel_autoplay_timeout)});
		if (options.ug_link == "true") {
			jQuery.extend($params,{tile_as_link:true,tile_link_newpage: true});
		}		
	}
	if (options.ug_type == "slider") { 
		jQuery.extend($params,{gallery_theme:options.ug_type,gallery_height:parseInt(options.ug_tile_height),gallery_width:parseInt(options.ug_tile_width),carousel_space_between_tiles:parseInt(options.ug_space_between_cols),slider_transition_speed:parseInt(options.ug_carousel_scroll_duration),gallery_play_interval:parseInt(options.ug_carousel_autoplay_timeout),slider_scale_mode: "fill",slider_enable_fullscreen_button: true});
		if (options.ug_lightbox == "false") {
			jQuery.extend($params,{slider_enable_fullscreen_button: false});
		} else {
			jQuery.extend($params,{slider_enable_fullscreen_button: true});
		}
		if (options.ug_zoom == "false") {
			jQuery.extend($params,{slider_control_zoom: false});
		} else {
			jQuery.extend($params,{slider_control_zoom: true});
		}
		if (options.ug_texte != "false") {
			jQuery.extend($params,{slider_enable_text_panel:true,slider_textpanel_enable_title: false,slider_textpanel_desc_text_align: "center",slider_enable_bullets:false});
			if (options.ug_texte == "true") { // survol
				jQuery.extend($params,{slider_textpanel_always_on: false});
			} 
		} 
	}
	if (options.ug_type == "tiles") { 
		if (options.ug_tiles_type == "column") { 
			jQuery.extend($params,{tiles_space_between_rows:parseInt(options.ug_space_between_rows),tiles_space_between_cols:parseInt(options.ug_space_between_cols),tiles_min_columns:parseInt(options.ug_min_columns)});
		}
		if (options.ug_tiles_type == "tilesgrid") { 
			jQuery.extend($params,{gallery_theme: "tilesgrid",grid_num_rows:parseInt(options.ug_grid_num_rows),tile_height:parseInt(options.ug_tile_height),grid_space_between_rows:parseInt(options.ug_space_between_rows),grid_space_between_cols:parseInt(options.ug_space_between_cols)});
		}
		if (options.ug_tiles_type == "nested") {
			jQuery.extend($params,{gallery_theme: "tiles",tiles_type: "nested",tiles_nested_optimal_tile_width:parseInt(options.ug_tile_width),tiles_space_between_rows:parseInt(options.ug_space_between_rows),tiles_space_between_cols:parseInt(options.ug_space_between_cols)});
		}
		if (options.ug_tiles_type == "justified") {  
			jQuery.extend($params,{gallery_theme: "tiles",tiles_type: "justified",tiles_justified_row_height:parseInt(options.ug_tile_height),tiles_justified_space_between:parseInt(options.ug_space_between_cols),tiles_space_between_rows:parseInt(options.ug_space_between_rows),tiles_space_between_cols:parseInt(options.ug_space_between_cols)});
		}
	}
	jQuery(me).unitegallery($params); 
		
}	
