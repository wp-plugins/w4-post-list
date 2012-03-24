<?php
function w4pl_template_wrapper( $input = '' ){
	global $w4_post_list;

	if( !is_object( $w4_post_list ))
		$w4_post_list = new W4_Post_list();

	if( !isset( $w4_post_list->default_templates['wrapper'] ))
		return $input;

	$default = $w4_post_list->default_templates['wrapper'];

	if( empty( $input ))
		return $default;
	
	// Version 1.5.7 Template Tag Changes.
	if( preg_match( '/\%\%postlist\%\%/', $input ))
		$input = preg_replace( '/\%\%postlist\%\%/', '[postlist]', $input );

	if( !preg_match( '/\[postlist\]/', $input ))
		return $default;

	return $input;
}

function w4pl_post_template_wrapper( $input = '' ){
	global $w4_post_list;

	if( !is_object( $w4_post_list ))
		$w4_post_list = new W4_Post_list();

	if( !isset( $w4_post_list->default_templates['wrapper_post'] ))
		return $input;

	$default = $w4_post_list->default_templates['wrapper_post'];

	if( empty( $input ))
		return $default;
	
	// Version 1.5.7 Template Tag Changes.
	if( preg_match( '/\%\%postloop\%\%/', $input ))
		$input = preg_replace( '/\%\%postloop\%\%/', '[postloop]', $input );

	if( !preg_match( '/\[postloop\]/', $input ))
		return $default;

	return $input;
}

function w4pl_post_template_loop( $input = '' ){
	global $w4_post_list;

	if( !is_object( $w4_post_list ))
		$w4_post_list = new W4_Post_list();

	if( !isset( $w4_post_list->default_templates['loop_post'] ))
		return $input;

	$default = $w4_post_list->default_templates['loop_post'];

	if( empty( $input ))
		return $default;
	
	if( !isset( $w4_post_list->post_template_fields ))
		$postloop;

	$allowed = join( '|', array_keys( $w4_post_list->post_template_fields ));

	// Version 1.5.7 Template Tag Changes.
	if( preg_match( '/\%\%('. $allowed .')\%\%/', $input ))
		$input = preg_replace( '/\%\%(.*?)\%\%/', '[\1]', $input );

	if( !preg_match( '/\[('. $allowed .')\]/', $input ))
		return $default;

	return $input;
}

function w4pl_category_template_wrapper( $input = '' ){
	global $w4_post_list;

	if( !is_object( $w4_post_list ))
		$w4_post_list = new W4_Post_list();

	if( !isset( $w4_post_list->default_templates['wrapper_category'] ))
		return $input;

	$default = $w4_post_list->default_templates['wrapper_category'];

	if( empty( $input ))
		return $default;
	
	// Version 1.5.7 Template Tag Changes.
	if( preg_match( '/\%\%catloop\%\%/', $input ))
		$input = preg_replace( '/\%\%catloop\%\%/', '[catloop]', $input );

	if( !preg_match( '/\[catloop\]/', $input ))
		return $default;

	return $input;
}

function w4pl_category_template_loop( $input = '' ){
	global $w4_post_list;

	if( !is_object( $w4_post_list ))
		$w4_post_list = new W4_Post_list();

	if( !isset( $w4_post_list->default_templates['loop_category'] ))
		return $input;

	$default = $w4_post_list->default_templates['loop_category'];

	if( empty( $input ))
		return $default;
	
	if( !isset( $w4_post_list->category_template_fields ))
		$postloop;

	$allowed = join( '|', array_keys( $w4_post_list->category_template_fields ));
	
	// Version 1.5.7 Template Tag Changes.
	if( preg_match( '/\%\%('. $allowed .')\%\%/', $input ))
		$input = preg_replace( '/\%\%(.*?)\%\%/', '[\1]', $input );

	if( !preg_match( '/\[('. $allowed .')\]/', $input ))
		return $default;

	return $input;
}
?>