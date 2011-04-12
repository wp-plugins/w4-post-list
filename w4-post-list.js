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

function jqCheckAll(){
	var name = $(this).val();
	var box = $('input:checkbox[name="'+ name +'"]');
	if($(this).is(':checked')){
		box.attr('checked', true);
	}
	else{
		box.attr('checked', false);
	}
	//$("input[name=" + name + "[]]").attr('checked', true);
}

function load_category_post(){
	var update_div = $('.select_category_posts');
	var list_id = $('input[name=list_id]').val();
	
	$.ajax({
		type: 'POST',
		url: ajaxurl,
		beforeSend: function( XMLHttpRequest){
			update_div.html('Loading');
		},
		data: $("form#w4_post_list_form").serialize(),
		success: function(data){
			update_div.hide();
			update_div.html(data).slideDown();
			update_div.addClass('loaded');
		}
	});
}
$(document).ready(function($){
	$('li.close ul.w4pl_posts').hide();
	$(".category_post_handle").click(function(){
		$(this).parent().parent().children("div.w4c_inside").slideToggle();
	});
	
	$('.list_effect .w4pl_handler').click(w4pl_toogle);
	$('input:checkbox[name="selector"]').change(jqCheckAll);
	
	$("#w4_post_list_form .option").bind({
		mouseover: function(){
			$(".option input.save_w4_post_list_options").remove();
			$(this).append('<input type="submit" name="save_w4_post_list_options" class="save_w4_post_list_options" value="Save option" />');
		}
	});
	$('a#delete_list').click(function(){
		var name = $(this).attr('rel');
		if( confirm( "Are you sure you want to delete '" + name + "' ?" )){
			return true ;
		}
		return false ;
	});
	$("#w4_post_list_form input[name='list_type']").change(function(){
		var list_type = $("#w4_post_list_form input[name='list_type']:checked").val();
		$('.hide_if_'+ list_type).hide();
		$('.show_if_'+ list_type).show();
		//load_category_post();
		
	});
	$("#w4_post_list_form input[name='post_content']").change(function(){
		var post_content = $("#w4_post_list_form input[name='post_content']:checked").val();
		$('.hide_if_post_content_' + post_content).slideUp();
		$('.show_if_post_content_' + post_content).slideDown();
		//alert(post_content);
	});
});
})(jQuery) ;