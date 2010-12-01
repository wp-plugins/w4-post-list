(function($){
$(document).ready( function(){
	$('#tab_area .tab_container:first' ).addClass('active') ;
	$('.tab_links a:first' ).addClass('active') ;
	$(".tab_links a").bind("click",function(){
			var ID = $(this).attr("class") ;
			$('.tab_container').removeClass('active') ;
			$('.tab_links a').removeClass('active') ;
			$('div#'+ID).addClass('active') ;
			$('div.active .tab_links a.'+ID ).addClass('active') ;
	});
});
})(jQuery);