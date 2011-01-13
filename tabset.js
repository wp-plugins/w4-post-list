(function($){
$(document).ready(function(){
	//var w4tabs = $('#tab_area' ).tabs() ;
	//mytabs.tabs( "rotate" , 3000 , true ) ;
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
	//Make the tabset colapsible on click
	//effect1.tabs("option", "collapsible", true);
	
	//For admin section only
	$("#tabset_help").click(function() {
		$("#contextual-help-wrap").slideToggle({ duration: 'fast' });
		$("#contextual-help-wrap").css({ 'background-color':'#FFFFE0', 'border-style': 'solid', 'border:color':'#E6DB55', 'border-width':'medium'});
		$('#contextual-help-link-wrap').toggle() ;
		return false;
	});
	
	$('#tabset-form input, #tabset-form textarea, #tabset-form select').keyup(function() {
		$('#save-tabset-options').val('Save now...');
	});
	
	$('#reset-tabset-options').click(function(){
		if( confirm( "Are you sure you want to reset \"W4 content tabset\" option to default ??" )){
			return true ;
		}
		return false ;
	});
});
})(jQuery);