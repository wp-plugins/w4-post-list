function w4pl_toogle(){
	var term_link = jQuery(this).attr('href');
	var term_title = jQuery(this).html();
	var term_ref = jQuery(this).attr('ref');
	
	//alert(term_title);
	//return false;
	var post_list = jQuery('.'+term_ref);
	
	if( post_list ){
		if( post_list.is(":hidden")){
			post_list.slideDown();
			jQuery(this).attr({'title':'Hide Posts from ' + term_title});
			jQuery(this).removeClass('list_inactive');
			jQuery(this).addClass('list_active');
		}else{
			post_list.slideUp();
			jQuery(this).attr({'title' : 'View Posts from ' + term_title});
			jQuery(this).removeClass('list_active');
			jQuery(this).addClass('list_inactive');
		}
		return false;
	}
}
jQuery('.available_posts').click(w4pl_toogle);