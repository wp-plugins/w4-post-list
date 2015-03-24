<?php
/***
 * Plugin Name: W4 Post List
 * Plugin URI: http://w4dev.com/plugins/w4-post-list
 * Description: This plugin lets you create a list of - Posts, Terms, Users, Terms + Posts and Users + Posts. Outputs are completely customizable using Shortcode, HTML & CSS. Read documentation plugin usage.
 * Version: 2.0.5
 * Author: Shazzad Hossain Khan
 * Author URI: http://w4dev.com/about
**/

/***
 * Copyright 2011  Shazzad Hossain Khan  (email : sajib1223@gmail.com)
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.

 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
***/



	/* Plugins Global Constant */

	define( 'W4PL_DIR', 			plugin_dir_path(__FILE__) );
	define( 'W4PL_URL', 			plugin_dir_url(__FILE__) );
	define( 'W4PL_BASENAME', 		plugin_basename( __FILE__ ));
	define( 'W4PL_NAME', 			'W4 Post List' );
	define( 'W4PL_SLUG', 			'w4pl' );
	define( 'W4PL_VERSION', 		'2.0.5' );
	define( 'W4PL_TD', 				'w4pl' );
	define( 'W4PL_INC', 			W4PL_DIR . 'inc' );


	/* Required Files */

	include( W4PL_INC .'/core.php');
	include( W4PL_INC .'/query.php');
	include( W4PL_INC .'/postlist.php');
	include( W4PL_INC .'/widget.php');


	/* Modules */

	/* posts */
	include( W4PL_INC .'/helper-posts.php');
	include( W4PL_INC .'/helper-posts-tax_query.php');
	include( W4PL_INC .'/helper-posts-meta_query.php');
	include( W4PL_INC .'/helper-posts-date_query.php');

	/* terms */
	include( W4PL_INC .'/helper-terms.php');

	/* users */
	include( W4PL_INC .'/helper-users.php');

	/* template css, js */
	include( W4PL_INC .'/helper-style.php');

	/* preset */
	include( W4PL_INC .'/helper-presets.php');

	/* no items found */
	include( W4PL_INC .'/helper-no-items.php');

	/* misc */
	include( W4PL_INC .'/helper-shortcodes.php');


	/* Tinymce */
	include( W4PL_DIR .'/tinymice/tinymice.php');


	/* Admin required files */
	include( W4PL_INC .'/admin-forms.php');
	include( W4PL_INC .'/admin-lists.php');
	include( W4PL_INC .'/admin-docs.php');


	/* on-unload */
	do_action( 'w4pl/loaded' );

?>