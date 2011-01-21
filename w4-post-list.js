(function($){
function w4pl_toogle(){
	if ($(this).is(':checked')){
		//alert('checking');
		$(this).parent().parent().next().show();
	} else {
		$(this).parent().parent().next().hide();
	}
}

$(document).ready(function($){
	$(".w4pl_cat_checkbox").click(w4pl_toogle);
	
	$('a.showhide_w4pl').click(function(){
		if($(this).next().is(":hidden")){
			$(this).next().slideDown();
			//$(this).text('hide');
			$(this).addClass('open');
		}else{
			$(this).next().slideUp();
			$(this).removeClass('open');
		}
	});
	});

});
})(jQuery) ;