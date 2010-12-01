<?php
/*
Plugin Name: Post/Page content tabset
Plugin URI: http://w4dev.com/w4-plugin/post-page-custom-tabset-shortcode
Description: Lets you embed tabset in your post/page area and also show your desired custom field values in a post/page area
Version: 1.3.1
Author: Shazzad Hossain Khan
Author URI: http://w4dev.com/
*/
define( 'TABSET_DIR', WP_PLUGIN_DIR.'/tabset' ) ;
define( 'TABSET_URL', WP_PLUGIN_URL.'/tabset' ) ;
define( 'TABSET_VERSION', '1.3.1' ) ;
require( TABSET_DIR . '/tabset_admin.php' ) ;

//Retrive and replace the tabs
function tabset_replace( $matches ){
	$pattern = '/\[\s*tabs\s*tabname\s*=\s*[\'\"](.*?)[\'\"]\s*\]?(.*?)\[\s*\/\s*tabs\s*\]/sm' ;
	if( !preg_match_all( $pattern, $matches[1], $tabs, PREG_SET_ORDER ))
		return false ;

	$i = 0 ;
	foreach( $tabs as $tab ){
		$i++ ;
		$tab_name = $tab[1] ;
		$tab_id = sanitize_title( $tab_name."-".$i ) ;
		$tab_links .= "<a title=\"$tab_name\" class=\"$tab_id\" href=\"#$tab_id\">$tab_name<span></span></a> " ;
		$tabs_content[$tab_id] = $tab[2] ;
	}

	foreach( $tabs_content as $tab_key => $tab_cont ){
		$content .= "<div class=\"tab_container\" id=\"$tab_key\">" ;
		$content .= "<div class=\"tab_links\">$tab_links</div>" ;
		$content .= "<div class=\"tab_content\">$tab_cont</div>" ;
		$content .= "</div>" ;
	}
	
	if( $content )
		return "<div id=\"tab_area\">$content</div>" ;
}

//Retrive and Replace the tabset shortcode
function tabset_replace_callback($text){
	$pattern = '/\[\s*tabset\s*\](.*?)\[\s*\/\s*tabset\s*\]/sm' ;
	return preg_replace_callback( $pattern, 'tabset_replace', $text ) ;
}

//Show your custom field value in post/page
function tabset_custom_field_replace( $matches ) {
	if( isset( $GLOBALS['post'] ))
		$post = $GLOBALS['post'] ;
	
	if( !$post && is_page()){
		$post->ID = get_query_var('page_id') ;
		$post = get_post( $post->ID ) ;
	}
	if( !$matches[1] )
		return false ;
	
	
	if( $custom = get_post_meta( $post->ID, $matches[1], true ))
		return $custom ;
}

function tabset_custom_field_replace_callback($text){
	$pattern = '/\[\s*custom\s*key\s*=\s*[\'\"](.*?)[\'\"]\s*\]/sm' ;
	return preg_replace_callback( $pattern, 'tabset_custom_field_replace', $text ) ;
}


add_filter('the_content', 'tabset_replace_callback' ) ;
add_filter('the_excerpt', 'tabset_replace_callback' ) ;
add_filter('the_content', 'tabset_custom_field_replace_callback' ) ;
add_filter('the_excerpt', 'tabset_custom_field_replace_callback' ) ;

?>
