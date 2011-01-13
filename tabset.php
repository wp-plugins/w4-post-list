<?php
/*
Plugin Name: Post/Page content tabset
Plugin URI: http://w4dev.com/w4-plugin/post-page-custom-tabset-shortcode
Description: Lets you embed tabset in your post/page area and also show your desired custom field values in a post/page area
Version: 1.3.6
Author: sajib1223, Shazzad Hossain Khan
Author URI: http://w4dev.com/
*/
define( 'TABSET_DIR', plugin_dir_path(__FILE__)) ;
define( 'TABSET_URL', plugin_dir_url(__FILE__)) ;
define( 'TABSET_VERSION', '1.3.6' ) ;
define( 'TABSET_BASENAME', plugin_basename( __FILE__ )) ;
require( TABSET_DIR . 'tabset_admin.php' ) ;

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
		$tab_links .= "\t<li><a title=\"$tab_name\" class=\"$tab_id\" href=\"#$tab_id\">$tab_name</a></li>\n" ;
		$tabs_content[$tab_id] = $tab[2] ;
	}

	foreach( $tabs_content as $tab_key => $tab_cont ){
		$content .= "<div class=\"tab_container\" id=\"$tab_key\">" ;
		$content .= "<div class=\"tab_content\">$tab_cont</div>" ;
		$content .= "</div>" ;
	}
	
	if( !$content )
		return false;
	
	$class = ' tabset_effect_'. w4_tabset_get_option( 'tabset_effect');
	$class .= ' '. w4_tabset_get_option( 'tabset_event');
	$links = "<ul class=\"tab_links\">$tab_links</ul>\n";
	$content = "<div id=\"tab_area\" class=\"$class\">$links<div id=\"tab_content_wrapper\">$content</div>\n</div>";

	return $content;
	return false;
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
