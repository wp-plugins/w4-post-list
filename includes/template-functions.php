<?php
function w4pl_template_wrapper( $input = '' ){
	global $w4_post_list;

	if( !is_object( $w4_post_list ))
		$w4_post_list = new W4_Post_list();

	if( !isset( $w4_post_list->default_template ))
		$input;

	$default = $w4_post_list->default_template['wrapper'];

	if( empty( $input ))
		return $default;
	
	if( !preg_match( '/\%\%postlist\%\%/', $input ))
		return $default;

	return $input;
}

function w4pl_post_template_wrapper( $input = '' ){
	global $w4_post_list;

	if( !is_object( $w4_post_list ))
		$w4_post_list = new W4_Post_list();

	if( !isset( $w4_post_list->default_template ))
		$input;

	$default = $w4_post_list->default_template['wrapper_post'];

	if( empty( $input ))
		return $default;
	
	if( !preg_match( '/\%\%postloop\%\%/', $input ))
		return $default;

	return $input;
}

function w4pl_post_template_loop( $input = '' ){
	global $w4_post_list;

	if( !is_object( $w4_post_list ))
		$w4_post_list = new W4_Post_list();

	if( !isset( $w4_post_list->default_template ))
		$input;

	$default = $w4_post_list->default_template['loop_post'];

	if( empty( $input ))
		return $default;
	
	if( !isset( $w4_post_list->post_template_fields ))
		$postloop;

	$allowed = join( '|', array_keys( $w4_post_list->post_template_fields ));

	if( !preg_match( '/\%\%('. $allowed .')\%\%/', $input ))
		return $default;

	return $input;
}

function w4pl_category_template_wrapper( $input = '' ){
	global $w4_post_list;

	if( !is_object( $w4_post_list ))
		$w4_post_list = new W4_Post_list();

	if( !isset( $w4_post_list->default_template ))
		$input;

	$default = $w4_post_list->default_template['wrapper_category'];

	if( empty( $input ))
		return $default;
	
	if( !preg_match( '/\%\%catloop\%\%/', $input ))
		return $default;

	return $input;
}

function w4pl_category_template_loop( $input = '' ){
	global $w4_post_list;

	if( !is_object( $w4_post_list ))
		$w4_post_list = new W4_Post_list();

	if( !isset( $w4_post_list->default_template ))
		$input;

	$default = $w4_post_list->default_template['loop_category'];

	if( empty( $input ))
		return $default;
	
	if( !isset( $w4_post_list->category_template_fields ))
		$postloop;

	$allowed = join( '|', array_keys( $w4_post_list->category_template_fields ));

	if( !preg_match( '/\%\%('. $allowed .')\%\%/', $input ))
		return $default;

	return $input;
}
?>