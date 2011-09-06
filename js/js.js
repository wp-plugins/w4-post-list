function w4pl_toogle(){
	var cat_link = jQuery(this).attr('href');
	var cat_title = jQuery(this).attr('alt');
	var post_list = jQuery(this).parent().find('.category_posts');

	if( post_list.is(":hidden")){
		post_list.slideDown();
		jQuery(this).attr({'title':'Hide Posts from ' + cat_title});
		jQuery(this).removeClass('list_closed');
		jQuery(this).addClass('list_open');
	}else{
		post_list.slideUp();
		jQuery(this).attr({'title':'View Posts from ' + cat_title});
		jQuery(this).removeClass('list_open');
		jQuery(this).addClass('list_closed');
	}
	return false;
}
jQuery('li.close ul.w4pl_posts').hide();
jQuery('.list_closed').parent().find('.category_posts').hide();
jQuery('.category_effect_handler').click(w4pl_toogle);