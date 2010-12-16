(function($){
$(document).ready(function(){
	//var w4tabs = $('#tab_area' ).tabs() ;
	//mytabs.tabs( "rotate" , 3000 , true ) ;
	var effect1 = $('.tabset_effect_1' ).tabs() ;
	var effect2 = $('.tabset_effect_2' ).tabs() ;
	var effect3 = $('.tabset_effect_3' ).tabs() ;
	
	effect2.tabs( "option", "fx", { height: 'toggle', duration : 300 }) ;
	effect3.tabs( "option", "fx", { opacity : 'toggle', duration : 400 }) ;
	
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
		if( confirm( "Are you sure you want to reset Tabber option to default ??" )){
			return true ;
		}
		return false ;
	});
});
})(jQuery);