<?php
/*
Plugin Name: W4 content tabset
Plugin URI: http://w4dev.com/w4-plugin/post-page-custom-tabset-shortcode/
Description: Lets you embed tabset in your post or page content area. Also capable to show your custom field values in a post or page content area by your selection.
Version: 1.4
Author: sajib1223, Shazzad Hossain Khan
Author URI: http://w4dev.com/
*/

/*  Copyright 2011  Shazzad Hossain Khan  (email : sajib1223@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

define( 'W4CT_DIR', plugin_dir_path(__FILE__));
define( 'W4CT_URL', plugin_dir_url(__FILE__));
define( 'W4CT_BASENAME', plugin_basename( __FILE__ ));
define( 'W4CT_VERSION', '1.4' );
define( 'W4CT_NAME', 'W4 content tabset' );
define( 'W4CT_SLUG', strtolower(str_replace(' ', '-', W4CT_NAME )));

global $wpdb;
$w4_tabset_table = $wpdb->prefix . 'content_tabset';

if( file_exists( W4CT_DIR . '/functions.php'))
	include( W4CT_DIR . '/functions.php');

if( is_admin() && file_exists( W4CT_DIR . '/admin.php'))
	include( W4CT_DIR . '/admin.php');

// Filters
add_filter('widget_text', 'w4_tabset_replace_callback' );

add_filter('the_content', 'w4_tabset_replace_callback' );
add_filter('the_excerpt', 'w4_tabset_replace_callback' );
add_filter('the_content', 'w4_tabset_custom_field_replace_callback' );
add_filter('the_excerpt', 'w4_tabset_custom_field_replace_callback' );

function w4_tabset_replace( $matches){
	$pattern = '/\[\s*tabs\s*tabname\s*=\s*[\'\"](.*?)[\'\"]\s*\]?(.*?)\[\s*\/\s*tabs\s*\]/sm' ;
	if( !preg_match_all( $pattern, $matches[2], $tabs, PREG_SET_ORDER ))
		return false;
	
	if( get_option('w4_content_tabset_default') && w4_get_tabset( get_option('w4_content_tabset_default')))
		$options = w4_get_tabset( get_option('w4_content_tabset_default'), 'tabset_option');
		
	else
		$options = w4_tabset_default_options();
	
	extract( $options);
	
	$stylesheet = '';
	$tabset_unique = '';
	$tabset_style = '';
	
	if( $matches[1]){
		extract(shortcode_parse_atts($matches[1]), EXTR_SKIP);
		if($id)	$tabset_id = $id;
		if($Id)	$tabset_id = $Id;
		if($iD)	$tabset_id = $iD;
		if($ID)	$tabset_id = $ID;
		
		if( $style){
			$style = sanitize_title( $style);
			if( w4_get_tabset_by_title( $style))
				$options = w4_get_tabset_by_title( $style, 'tabset_option');
			
			if( w4_get_tabset( $style))
				$options = w4_get_tabset( $style, 'tabset_option');
			
			if( $options){
				$stylesheet = w4_tabset_stylesheet( $style);
				$tabset_effect = $options['tabset_effect'];
				$tabset_event = $options['tabset_event'];
			}
		}
		
		if($tabset_id)
			$tabset_unique = 'tabset_'.$tabset_id.'-';
	}



	$i = 0 ;
	foreach( $tabs as $tab){
		$i++;
		$tab_name = $tab[1] ;
		$tab_id = $tabset_unique . sanitize_title( $tab_name."-".$i ) ;
		
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
	
	$content = $stylesheet . $content;
	$class 		= "w4_content_tabset $tabset_style tabset_effect_{$tabset_effect} $tabset_event";
	$links 		= "<ul class=\"tab_links\">$tab_links</ul>\n";
	$content 	= "<div class=\"$class\">$links<div class=\"tab_content_wrapper\">$content</div>\n</div>";

	return $content;
}

//Retrive and Replace the tabset shortcode
function w4_tabset_replace_callback($text){
	$pattern = '/\[\s*tabset(.*?)\](.*?)\[\s*\/\s*tabset\s*\]/sm';
	return preg_replace_callback($pattern,'w4_tabset_replace',$text);
}

//Show your custom field value in post/page
function w4_tabset_custom_field_replace( $matches ) {
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

function w4_tabset_custom_field_replace_callback($text){
	$pattern = '/\[\s*custom\s*key\s*=\s*[\'\"](.*?)[\'\"]\s*\]/sm' ;
	return preg_replace_callback( $pattern, 'w4_tabset_custom_field_replace', $text ) ;
}

add_action( 'plugins_loaded', 'w4_tabset_loaded');
function w4_tabset_loaded(){
	if( is_admin() && $_GET['page'] == 'w4-content-tabset' ){
		wp_enqueue_style( 'w4_tabset_admin_css', W4CT_URL . 'admin_style.css', '', W4CT_VERSION );
		wp_enqueue_script( 'color_picker', W4CT_URL . 'colorpicker/jscolor.js', array( 'jquery','jquery-ui-core' ), W4CT_VERSION, true);
	}

	add_action( 'wp_head', 'w4_tabset_stylesheet_default');
	wp_enqueue_script( 'w4_tabset_js', W4CT_URL . 'js.js', array( 'jquery' , 'jquery-ui-core', 'jquery-ui-tabs' ), W4CT_VERSION, true );
}
?>