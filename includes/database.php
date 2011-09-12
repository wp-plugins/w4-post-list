<?php
/* We resave all the data upon activation/reactivation. As we change our data 
** structure it is important to resave the options and update the database once if available.
*/
function w4pl_database_update(){
	global $wpdb;
	w4pl_db_install( true );

	if( !get_option( '_w4pl_db_version' ))
		add_option( '_w4pl_db_version', '1' );
}
	
function w4pl_db_install( $force = false ){
	global $wpdb;

	if( w4pl_table_exists() && !$force )
		return;

	if( !empty ( $wpdb->charset ))
		$charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";

	if( !empty ( $wpdb->collate))
		$charset_collate .= " COLLATE {$wpdb->collate}";

	$sql[] = "CREATE TABLE $wpdb->post_list(
		  list_id bigint(20) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
		  list_title varchar(200) NOT NULL DEFAULT '',
		  list_option longtext NOT NULL,
		  user_id bigint(20) NOT NULL DEFAULT '0',
		  UNIQUE  KEY  list_id (list_id)
	){$charset_collate};";

	require_once( ABSPATH . 'wp-admin/upgrade-functions.php' );

	if( dbDelta( $sql ))
		update_option( '_w4pl_db_version', W4PL_DB_VERSION );
}

function w4ld_db_remove(){
	global $wpdb;

	// Remove the database version first
	delete_option( '_w4pl_db_version' );

	// Get Existing data @ array format
	$lists = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM  $wpdb->post_list ORDER BY list_id ASC" ), ARRAY_A );

	// Create an array to export list data
	$export_lists = array();
	
	if ( !empty( $lists )){
		delete_option( '_w4pl_temp_option' );
		update_option( '_w4pl_temp_option', $lists );
	}
	return $wpdb->query( "DROP TABLE IF EXISTS $wpdb->post_list" );
}

// Check our plugin table exists or not
function w4pl_table_exists(){
	global $wpdb;
	return strtolower( $wpdb->get_var( "SHOW TABLES LIKE '$wpdb->post_list'" )) == strtolower( $wpdb->post_list );
}
?>