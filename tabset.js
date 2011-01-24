(function($){
$(document).ready(function(){
	$('#w4_tabset_widget').tabs({ spinner: 'Loading...' });
	var effect1 = $('.tabset_effect_1' ).tabs() ;
	var effect2 = $('.tabset_effect_2' ).tabs() ;
	var effect3 = $('.tabset_effect_3' ).tabs() ;
	effect2.tabs( "option", "fx", { height: 'toggle', duration : 300 }) ;
	effect3.tabs( "option", "fx", { opacity : 'toggle', duration : 400 }) ;
	var events1 = $('.on_click' ).tabs() ;
	var events2 = $('.on_hover' ).tabs() ;
	$( ".on_click" ).tabs( "option", "event", 'click' );
	$( ".on_hover" ).tabs( "option", "event", 'mouseover' );
});
})(jQuery);