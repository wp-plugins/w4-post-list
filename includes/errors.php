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
							echo '<div class="updated fade">' . $error . "</div>\n";
						else
							echo '<div class="error fade">' . $error . "</div>\n";
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

function w4pl_display_error(){
	global $w4pl_error;
	
	if ( empty( $w4pl_error ))
		$w4pl_error = new W4PL_Error();
	
	return $w4pl_error->preview();
}
?>