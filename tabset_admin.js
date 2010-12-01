(function($) {
$(document).ready(function(){
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
})(jQuery)