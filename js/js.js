(function($){
function w4pl_toogle(){
	//$(this).preventDefault();
	var cat_link = $(this).attr('href');
	var cat_title = $(this).attr('alt');
	var post_list = $(this).parent().find('.category_posts');

	if( post_list.is(":hidden")){
		post_list.slideDown();
		$(this).attr({'title':'Hide Posts from ' + cat_title});
		$(this).removeClass('list_closed');
		$(this).addClass('list_open');
	}else{
		post_list.slideUp();
		$(this).attr({'title':'View Posts from ' + cat_title});
		$(this).removeClass('list_open');
		$(this).addClass('list_closed');
	}
	return false;
	//$(this).preventDefault();
}


$(document).ready(function($){
	$('li.close ul.w4pl_posts').hide();
	$('.list_closed').parent().find('.category_posts').hide();
	$('.category_effect_handler').click(w4pl_toogle);
});

})(jQuery) ;