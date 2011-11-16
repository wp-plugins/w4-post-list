<?php
// Post list Error handler Class W4PL_Error @
class W4PL_Error {

	var $errors;
	var $output;

	function W4PL_Error(){
		global $wp_error;
		
		$this->errors = $wp_error;

		if ( empty( $this->errors ))
			$this->errors = new WP_Error();
		
		$this->default_errors();
	}

	function default_errors(){

		// Incase a plugin uses $error rather than the $errors object
		if ( !empty( $error )){
			$this->add_error( $error, 'error' );
			unset( $error );
		}

		// Incase a plugin uses $error rather than the $errors object
		if ( !empty( $message )){
			$this->add_message( $message, 'message' );
			unset($message);
		}

		#print_r($this->errors);
	}
	
	function add_error( $error, $code = '' ){

		if( !empty( $error )){
			$code = !empty( $code ) ? $code : 'error';
			$code = $this->unique_code( $code );
			
			$this->errors->add( $code, $error, 'error' );
		}
	}

	function add_message( $message, $code = '' ){

		if( !empty( $message )){
			$code = !empty( $code ) ? $code : 'error';
			$code = $this->unique_code( $code );
			
			$this->errors->add( $code, $message, 'message' );
		}
	}
	
	function unique_code( $code ){
		$codes = $this->errors->get_error_codes();

		if( !empty( $codes ) && in_array( $code, $codes )){
			$check = in_array( $code, $codes );

			if ( $check ){
				$suffix = 2;
				do {
					$alt_code = $code . "-$suffix";
					$check = in_array( $code, $codes );
					$suffix++;
				} while ( $check );
				$code = $alt_code;
			}
		}
		
		return $code;
	}
	
	function preview(){
	
		if ( is_wp_error( $this->errors )){
			if ( $this->errors->get_error_code()){
				$errors = '';
				$messages = '';
				$this->output = '';
				foreach ( $this->errors->get_error_codes() as $code ) {
					$severity = $this->errors->get_error_data( $code );
					foreach ( $this->errors->get_error_messages( $code ) as $error ){
						if ( 'message' == $severity )
							echo '<div class="w4_updated">' . $error . "</div>\n";
						else
							echo '<div class="w4_error">' . $error . "</div>\n";
					}
				}
			}
		}
		#echo $this->output;
	}
}

global $w4pl_error;
$w4pl_error = new W4PL_Error();

function w4pl_add_error( $error, $code = '' ){
	global $w4pl_error;
	
	if ( empty( $w4pl_error ))
		$w4pl_error = new W4PL_Error();
	
	return $w4pl_error->add_error( $error, $code );
}

function w4pl_add_message( $message, $code = '' ){
	global $w4pl_error;
	
	if ( empty( $w4pl_error ))
		$w4pl_error = new W4PL_Error();
	
	return $w4pl_error->add_message( $message, $code );
}

function w4pl_display_notice(){
	global $w4pl_error;
	
	if ( empty( $w4pl_error ))
		$w4pl_error = new W4PL_Error();
	
	return $w4pl_error->preview();
}

function w4pl_include_message(){

	$key = isset( $_GET['message'] ) ? $_GET['message'] : '';
	if( empty( $key )){
		$key2 = isset( $_GET['error'] ) ? $_GET['error'] : '';
		if( empty( $key2 ))
			return;
	
		$error = array(
			'list_not_saved'	=> __( 'Unable to save. There may be a database connection error or this list may not have been exists or you do 
			not have capabilities to manage this list.'),
			'list_not_deleted'	=> __( 'Unable to delete this list now. There may be a database 
			connection error or this list may not have been exists or you do not have capabilities to delete this.'),
			'no_list_found'		=> __( 'List not found.'),
			'no_permission'		=> __( 'You dont have no permission to manage other users list.')
		);
	
		if( array_key_exists( $key2, $error ) && !empty( $error[$key2] ))
			return w4pl_add_error( $error[$key2], $key2 );
	}
	else{

		$msg = array(
			'list_inserted'			=> __( 'New post list created. now change options below and save to prepare your list.', 'w4-post-list'),
			'list_updated'			=> __( 'Post list options updated.', 'w4-post-list'),
			'list_deleted'			=> __( 'One post list has been deleted.', 'w4-post-list'),
		
			'option_updated'		=> __( 'Option Updted.', 'w4-post-list'),
			'db_updated' 			=> __( 'Informations updated. You can get the on main page. However, the list id and list shortcode may have been changed.', 
			'w4-post-list'),
			'db_dropped' 			=> __( 'Database table dropped.', 'w4-post-list'),
			'db_installed' 			=> __( 'Database installed sucessfully..', 'w4-post-list'),
			'old_option_cleared'	=> __( 'Old Information cleared.', 'w4-post-list')
		);
	
		if( array_key_exists( $key, $msg ) && !empty( $msg[$key] ))
			return w4pl_add_message( $msg[$key], $key );
	}
	return;
}
?>