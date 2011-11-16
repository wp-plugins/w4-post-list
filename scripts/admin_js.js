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
		jQuery( '.toogle_help').hide();
		jQuery( '.toogle_helper').click(function(){
			jQuery(this).parent().parent().find('.toogle_help').toggle();
			return false;
		});

		$(".category_post_handle").click(function(){
			$(this).parent().parent().children("div.w4c_inside").toggle();
		});
		$('input:checkbox[name="selector"]').change(jqCheckAll);
		$(".w4pl_form .option").bind({
			mouseover: function(){
				$(".option input.w4pl_update_button").remove();
				$(this).append('<input type="submit" class="w4pl_update_button" value="Update" />');
			}
		});
		$('a.delete_list').click(function(){
			var name = $(this).attr('rel');
			if( confirm( "Are you sure you want to delete '" + name + "' ?" )){
				return true ;
			}
			return false ;
		});
		$('a#remove_w4ldb').click(function(){
			if( confirm( "* Are you sure you want to Drop plugin table ?\n\r* This process cant be undone. However, we will still keep your old post list informations, which can be imported later" )){
				return true ;
			}
			return false ;
		});
		$(".w4pl_form input[name='list_type']").change(function(){
			var list_type = $(".w4pl_form input[name='list_type']:checked").val();
			$('.hide_if_'+ list_type).hide();
			$('.show_if_'+ list_type).show();
			//load_category_post();
		});
		$('.w4pl_tip_handle').hover(function(){
			var ch = $(this).find('span').toggle();
		});
	});
})(jQuery) ;