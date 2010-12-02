(function($){
$(document).ready( function(){
	$('.tabset_style_2' ).tabs() ;
	
	$('.tabset_style_1 .tab_container:first' ).addClass('active') ;
	$('.tabset_style_1 .tab_links a:first' ).addClass('active') ;
	$(".tabset_style_1 .tab_links a").bind("click",function(){
			var ID = $(this).attr("class") ;
			$('.tabset_style_1 .tab_container').removeClass('active') ;
			$('.tabset_style_1 .tab_links a').removeClass('active') ;
			$('.tabset_style_1 div#'+ID).addClass('active') ;
			$('.tabset_style_1 div.active .tab_links a.'+ID ).addClass('active') ;
	});
});
})(jQuery);