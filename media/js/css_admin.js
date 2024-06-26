jQuery(document).ready(function(){
	if (jQuery("label[for='jform_params_iso_entree1']").hasClass("active")) { // initial value
		jQuery( "#jform_params_introtext_limit-lbl" ).parent(".control-label").parent(".control-group").css({"float":"left","width":"50%"});
		jQuery( "#jform_params_iso_count-lbl" ).parent(".control-label").parent(".control-group").css({"float":"left","width":"50%"});
		if (jQuery("label[for='jform_params_pagination0']").hasClass("active")) {
			jQuery( "#jform_params_pagination-lbl" ).parent(".control-label").parent(".control-group").css({"float":"left","width":"50%"});
		}
	}
	if (jQuery("label[for='jform_params_introtext_img0']").hasClass("active")) {
		jQuery( "#jform_params_introtext_img-lbl" ).parent(".control-label").parent(".control-group").css({"float":"left","width":"50%"});
	}
	jQuery("[id^='jform_params_iso_entree']").click(function(){ // click on Information type button
	if (this.id == "jform_params_iso_entree1") { // Articles
		jQuery( "#jform_params_introtext_limit-lbl" ).parent(".control-label").parent(".control-group").css({"float":"left", "width":"50%"});
		jQuery( "#jform_params_iso_count-lbl" ).parent(".control-label").parent(".control-group").css({"float":"left","width":"50%"});
		if (jQuery("label[for='jform_params_pagination0']").hasClass("active")) {
			jQuery( "#jform_params_pagination-lbl" ).parent(".control-label").parent(".control-group").css({"float":"left","width":"50%"});
		} else {
			jQuery( "#jform_params_pagination-lbl" ).parent(".control-label").parent(".control-group").css({"float":"none"});
		}
	} else if ((this.id == "jform_params_iso_entree0") || (this.id == "jform_params_iso_entree2")) { // weblink 
		jQuery( "#jform_params_introtext_limit-lbl" ).parent(".control-label").parent(".control-group").css("float","none");
		jQuery( "#jform_params_iso_count-lbl" ).parent(".control-label").parent(".control-group").css("float","none");
		jQuery( "#jform_params_pagination-lbl" ).parent(".control-label").parent(".control-group").css({"float":"none"});
	}
	});
	jQuery("[id^='jform_params_pagination']").click(function(){ // click on pagination  button
		if (jQuery("label[for='jform_params_pagination0']").hasClass("active")) {
			jQuery( "#jform_params_pagination-lbl" ).parent(".control-label").parent(".control-group").css({"float":"left","width":"50%"});
		} else {
			jQuery( "#jform_params_pagination-lbl" ).parent(".control-label").parent(".control-group").css({"float":"none"});
		}
	});
	jQuery("[id^='jform_params_introtext_img']").click(function(){ // click on show image button
		if (jQuery("label[for='jform_params_introtext_img0']").hasClass("active")) {
			jQuery( "#jform_params_introtext_img-lbl" ).parent(".control-label").parent(".control-group").css({"float":"left","width":"50%"});
		} else {
			jQuery( "#jform_params_introtext_img-lbl" ).parent(".control-label").parent(".control-group").css({"float":"none"});
		}
	});

});
