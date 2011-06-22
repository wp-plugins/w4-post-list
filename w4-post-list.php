<?php
/*
Plugin Name: W4 post list
Plugin URI: http://w4dev.com/w4-plugin/w4-post-list
Description: Lists wordpress posts, categories and posts with categories by W4 post list plugin. Show/Hide post list with jquery slide effect. Multi-lingual supported.
Version: 1.4.1
Author: Shazzad Hossain Khan
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

/*	Few  argument have been changed in version 1.3.1. Please if you feel 
	difficulties after upgrading to latest, go to your post list admin 
	setting page and save the options again.
*/

define( 'W4PL_DIR', plugin_dir_path(__FILE__));
define( 'W4PL_URL', plugin_dir_url(__FILE__));
define( 'W4PL_BASENAME', plugin_basename( __FILE__ ));
define( 'W4PL_VERSION', '1.4.1' );
define( 'W4PL_DB_VERSION', '2' );
define( 'W4PL_NAME', 'W4 post list' );
define( 'W4PL_SLUG', strtolower( str_replace( ' ', '-', W4PL_NAME )));

register_activation_hook( __FILE__, 'w4pl_database_update' );

if( file_exists( W4PL_DIR . 'includes/class.php'))
	include( W4PL_DIR . 'includes/class.php');

if( file_exists( W4PL_DIR . 'includes/widgets.php'))
	include( W4PL_DIR . 'includes/widgets.php');

if( file_exists( W4PL_DIR . 'includes/functions.php' ))
	include( W4PL_DIR . 'includes/functions.php');

// Load admin scripts
if( is_admin()):
	if( file_exists( W4PL_DIR . 'includes/admin.php'))
	include( W4PL_DIR . 'includes/admin.php');

	if( file_exists( W4PL_DIR . 'includes/forms.php'))
	include( W4PL_DIR . 'includes/forms.php');
endif;
?>