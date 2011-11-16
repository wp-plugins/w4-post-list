function w4pl_toogle(){
	var cat_link = jQuery(this).attr('href');
	var cat_title = jQuery(this).attr('alt');
	var ref_id = jQuery(this).attr('ref');
	
	var post_list = jQuery('#term_posts_' + ref_id);

	if( post_list.is(":hidden")){
		post_list.slideDown();
		jQuery(this).attr({'title':'Hide Posts from ' + cat_title});
		jQuery(this).removeClass('list_inactive');
		jQuery(this).addClass('list_active');
	}else{
		post_list.slideUp();
		jQuery(this).attr({'title':'View Posts from ' + cat_title});
		jQuery(this).removeClass('list_active');
		jQuery(this).addClass('list_inactive');
	}
	return false;
}
jQuery('.list_effect_handler').click(w4pl_toogle);