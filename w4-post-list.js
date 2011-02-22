(function($){
function w4pl_admin_toogle(){
	if ($(this).is(':checked')){
		$(this).parent().parent().parent().children("div.w4c_inside").show();
	} else {
		$(this).parent().parent().parent().children("div.w4c_inside").hide();
	}
}

function w4pl_toogle(){
	if($(this).parent().find('.w4pl_sub').is(":hidden")){
		$(this).parent().addClass('open');
		$(this).parent().find('.w4pl_sub').slideDown();
		$(this).attr({'title':'Hide list'});
	}else{
		$(this).parent().removeClass('open');
		$(this).parent().find('.w4pl_sub').slideUp();
		$(this).attr({'title':'Show list'});
	}
}

$(function() {
		$("ul.sortable").sortable({ opacity: 0.6, cursor: 'move'});
	});

$(document).ready(function($){
	$(".w4pl_cat_checkbox").click(w4pl_admin_toogle);
	$('span.showhide_w4pl').click(w4pl_toogle);
	
	$("#post_list_form .option").bind({
		mouseover: function(){
			$(".option input.save_list_option").remove();
			$(this).append('<input type="submit" name="save_w4_post_list_options" class="save_list_option" value="Save option" />');
		//},
		//mouseout: function(){
		//	$(this).children("input.save_list_option").remove();
		}
	});
});
})(jQuery) ;