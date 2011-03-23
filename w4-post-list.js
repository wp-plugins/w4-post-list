(function($){
function w4pl_admin_toogle(){
	if ($(this).is(':checked')){
		$(this).parent().parent().parent().children("div.w4c_inside").show();
	} else {
		$(this).parent().parent().parent().children("div.w4c_inside").hide();
	}
}

function w4pl_toogle(){
	//$(this).preventDefault();
	var cat_link = $(this).attr('href');
	var cat_title = $(this).attr('title');
	var more = '<a class="more" href="'+ cat_link +'" title="'+ cat_title +'">View all</a>';
	var cat_list = $(this).parent();
	var post_list = cat_list.children('.w4pl_posts');
	var marker = cat_list.children('.marker');
	

	
	if(post_list.is(":hidden")){
		cat_list.addClass('open');
		post_list.slideDown();
		$(this).attr({'title':'Hide list'});
		marker.attr({'title':'Hide list'});
		//cat_list.append(more);
	}else{
		cat_list.removeClass('open');
		post_list.slideUp();
		$(this).attr({'title':'Show list'});
		marker.attr({'title':'Show list'});
		//cat_list.children('.more').remove();
	}
	
	return false;
	//$(this).preventDefault();
}

$(document).ready(function($){
	$('li.close ul.w4pl_posts').hide();
	//$(".w4pl_cat_checkbox").click(w4pl_admin_toogle);
	
	$(".category_post_handle").click(function(){
		$('#' + $(this).attr('rel')).slideToggle();
	});
	
	$('.list_effect .w4pl_handler').click(w4pl_toogle);
	
	$("#post_list_form .option").bind({
		mouseover: function(){
			$(".option input.save_list_option").remove();
			$(this).append('<input type="submit" name="save_w4_post_list_options" class="save_list_option" value="Save option" />');
			//$(".w4_help").remove();
			//$(this).append('<div class="w4_help"></div>');
		}
	});
	
	$('a#delete_list').click(function(){
		var name = $(this).attr('rel');
		if( confirm( "Are you sure you want to delete '" + name + "' ?" )){
			return true ;
		}
		return false ;
	});
	
	$("#post_list_form input[name='list_type']").change(function(){
		var pre = $("#post_list_form input[name='list_type']:checked").val();
		$('.hide_if_'+ pre).hide();
		$('.show_if_'+ pre).show();
	});
	
	$("#post_list_form input[name='post_content']").change(function(){
		var post_content = $("#post_list_form input[name='post_content']:checked").val();
		$('.hide_if_post_content_' + post_content).slideUp();
		$('.show_if_post_content_' + post_content).slideDown();
		//alert(post_content);
	});
});
})(jQuery) ;