<?php
/*
Plugin Name: W4 post list
Plugin URI: http://w4dev.com/w4-plugin/w4-post-list
Description: Lists wordpress posts, categories and posts with categories by W4 post list plugin. Show/Hide post list with jquery slide effect. The Most Customizable Post list Plugin u ever used..
Version: 1.5.2
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

# Plugins Global Constant
define( 'W4PL_DIR', plugin_dir_path(__FILE__));
define( 'W4PL_URL', plugin_dir_url(__FILE__));
define( 'W4PL_BASENAME', plugin_basename( __FILE__ ));
define( 'W4PL_VERSION', '1.5.2' );
define( 'W4PL_DB_VERSION', '2' );
define( 'W4PL_NAME', 'W4 post list' );
define( 'W4PL_SLUG', strtolower( str_replace( ' ', '-', W4PL_NAME )));
define( 'W4PL_INC', W4PL_DIR . 'includes' );

# Hook called when Plugin is Activated or updated
register_activation_hook( __FILE__, 'w4pl_database_update' );

function w4pl_file_check(){
	$w4pl_plugin_files = array( 
		'functions.php',
		'template-functions.php',
		'database.php',
		'errors.php',
		'class.php',
		'admin-misc.php',
		'admin.php',
		'management-page.php',
		'forms.php',
		'widgets.php'
	);

	foreach( $w4pl_plugin_files as $w4pl_plugin_file ){
		if( !file_exists( W4PL_INC .'/'. $w4pl_plugin_file ))
			return false;
	}
	return true;
}
function w4pl_admin_notice(){
	echo "<div class='error'><p>W4 post list plugin found some file missing. You are recommended to comppletely uninstall and delete this plugin, then reinstall a fresh copy.</p></div>";
}

if( w4pl_file_check()){
	include( W4PL_INC .'/functions.php');
	include( W4PL_INC .'/template-functions.php');
	include( W4PL_INC .'/database.php');
	include( W4PL_INC .'/errors.php');
	include( W4PL_INC .'/class.php');
	include( W4PL_INC .'/widgets.php');

	// Load admin files when viewing admin page
	if( is_admin()){
		include( W4PL_INC .'/admin-misc.php');
		include( W4PL_INC .'/admin.php');
		include( W4PL_INC . '/management-page.php');
		include( W4PL_INC .'/forms.php');
	}
}else{
	add_action( 'admin_notices', 'w4pl_admin_notice');
}
?>