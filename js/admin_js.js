(function($){
	function w4pl_admin_toogle(){
		if ($(this).is(':checked')){
			$(this).parent().parent().parent().children("div.w4c_inside").show();
		} else {
			$(this).parent().parent().parent().children("div.w4c_inside").hide();
		}
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
	}
	$(document).ready(function($){
		$(".category_post_handle").click(function(){
			$(this).parent().parent().children("div.w4c_inside").toggle();
		});
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
		$('a#remove_w4ldb').click(function(){
			if( confirm( "Are you sure you want to Drop plugin table ? This process cant be undone. We will still hold your information, which you can import later" )){
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
		$('.w4pl_tip_handle').hover(function(){
			var ch = $(this).find('span').toggle();
		});
	});
})(jQuery) ;