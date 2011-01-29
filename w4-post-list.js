(function($){
function w4pl_admin_toogle(){
	if ($(this).is(':checked')){
		$(this).parent().parent().next().show();
	} else {
		$(this).parent().parent().next().hide();
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

$(document).ready(function($){
	$(".w4pl_cat_checkbox").click(w4pl_admin_toogle);
	$('span.showhide_w4pl').click(w4pl_toogle);
});
})(jQuery) ;