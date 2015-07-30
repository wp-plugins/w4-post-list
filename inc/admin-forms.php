<?php
/**
 * @package W4 Post List
 * @author Shazzad Hossain Khan
 * @url http://w4dev.com/plugins/w4-post-list
**/


function w4pl_form_fields( $fields, $values = array(), $form_args = array() )
{
	if( !is_array( $fields ))
		$fields = array();

	if( !is_array( $values ))
		$values = array();


	if( !is_array($form_args) )
		$form_args = array();

	if( empty($form_args['qv']) )
		$form_args['qv'] = array();

	if( empty( $form_args['method'] ))
		$form_args['method'] = 'POST';

	if( empty( $form_args['class'] ))
		$form_args['class'] = 'form-wrap w4pl_form';

	if( empty( $form_args['action']) )
	{
		$schema = is_ssl() ? 'https://' : 'http://';
		$form_args['action'] = $schema . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	}

	if( !empty($form_args['qv']) )
	{
		$query_vars = array();
		foreach( $form_args['qv'] as $q)
		{
			if( isset($_GET[$q]) && $_GET[$q] != '' )
			{
				$query_vars[$q] = trim($_GET[$q]);
			}
		}

		if( !empty( $query_vars ))
		{
			$form_args['action'] = add_query_arg( $query_vars, $form_args['action'] );
		}
	}

	if( empty( $form_args['button_text'] ))
		$form_args['button_text'] = 'Update';


	$html = '';

	if( !isset( $form_args['no_form'] ))
	{
		$html .= '<form';
		$attr_keys = array('class', 'id', 'name', 'title', 'enctype', 'method', 'action');
		foreach( $form_args as $name => $attr )
		{
			if( !empty($name) && in_array($name, $attr_keys) )
			{
				$html .= ' '. $name .'="'. esc_attr($attr) .'"';
			}
		}
		$html .= '>';
	}

	if( !empty( $form_args['after_tag'] ))
		$html .= $form_args['after_tag'];

	if( isset( $form_args['button_before'] ) &&  $form_args['button_before'] === true )
		$html .= "<p class='form_button_container button_container_top'><input type='submit' value='". $form_args['button_text'] ."' class='form_button button_top'></p>";


	foreach( $fields as $field ):
		if( isset($field['name']) && $field['name'] != '' )
		{
			if( isset($field['option_name']) )
				$field['value'] = isset($values[$field['option_name']]) ? $values[$field['option_name']] : '';
			else
				$field['value'] = isset($values[$field['name']]) ? $values[$field['name']] : '';
		}

		$html .= w4pl_form_field_html( $field );
	endforeach;

	if( !isset( $form_args['button_after'] ) ||  $form_args['button_after'] !== false )
		$html .= "<p class='form_button_container button_container_bottom'><input type='submit' value='". $form_args['button_text'] ."' class='button-primary form_button button_bottom'></p>";

	if( !empty( $form_args['form_closing'] ))
		$html .= $form_args['form_closing'];

	if( !isset( $form_args['no_form'] ))
	{
		$html .= '</form>';
	}

	return $html;
}

function w4pl_form_child_field_html( $args = array() ){

	$args['label'] = '';
	$args['field_wrap'] = false;
	$args['label_wrap'] = false;
	$args['input_wrap'] = false;

	return w4pl_form_field_html( $args );
}

function w4pl_form_field_html( $args = array()){

	if( !is_array($args) )
		return;

	$defaults = array(
		'label' 		=> '',
		'name' 			=> '',
		'type'			=> 'html',
		'html'			=> '',
		'placeholder'	=> '',
		'input_html'	=> '',
		'input_attr'	=> '',
		'desc'			=> '',
		'desc2'			=> '',
		'default' 		=> '',
		'value' 		=> '',
		'required' 		=> 'n',

		'id' 			=> '',
		'class'			=> '',
		'style' 		=> '',
		'attrs' 		=> array(),

		'before'		=> '',
		'after'			=> '',

		'field_wrap'	=> true,
		'field_before'	=> '',
		'field_after'	=> '',

		'label_wrap'	=> true,
		'label_wrap_before' => '',
		'label_before'	=> '',
		'label_after'	=> '',

		'input_wrap'	=> true,
		'input_wrap_before'	=> '',
		'input_before'	=> '',
		'input_after'	=> '',
		'input_class'	=> ''
	);



	$args = wp_parse_args( $args, $defaults );

	if( empty($args['id']) ){
		$args['id'] = w4pl_form_field_id( $args['name'] );
	}

	extract( $args );

	if( !$value )
		$value = $default;

	$attr = '';
	if( !empty($style) )
	{
		$attrs['style'] = $style;
	}
	foreach( $attrs as $an => $av )
	{
		$attr .= ' '. $an .'="'. esc_attr($av) .'"';
	}

	$html .= $before;

	if( !in_array($type, array('html', 'hidden') ) && $field_wrap ){
		$html .= sprintf( '<div class="%1$s"%2$s>', w4pl_form_pitc_class('wffw', $id, $type, $class), $attr );
	}

	$html .= $field_before;

	switch( $type ):

	case "hidden":
		$html .= sprintf( '<input id="%1$s" name="%2$s" value="%3$s" type="hidden" />', $id, $name, $value );
	break;

	case "text":
	case "text_combo":
	case "html_input":

	case "textarea":
	case "select":
	case "radio":
	case "checkbox":


		// label
		$html .= $label_wrap_before;
		$html .= w4pl_form_field_label( $args );

		// description
		if( !empty($desc) ){
			$html .= sprintf( '<div class="%1$s">%2$s</div>', w4pl_form_pitc_class('wffdw', $id, $type), $desc );
		}

		// input
		$html .= $input_wrap_before;
		if( $input_wrap ){
			$html .= sprintf( '<div class="%1$s">', w4pl_form_pitc_class('wffew', $id, $type) );
		}

		$html .= $input_before;


		if( $type == 'text' ){
			$html .= sprintf( 
				'<input class="%1$s %5$s" id="%2$s" name="%3$s" value="%4$s" type="text" placeholder="%6$s" />', 
				w4pl_form_pitc_class('wff', $id, $type), $id, $name, $value, $input_class, $placeholder
			);
		}

		elseif( $type == 'textarea' ){
			$html .= sprintf( 
				'<textarea class="%1$s %5$s" id="%2$s" name="%3$s" placeholder="%6$s">%4$s</textarea>', 
				w4pl_form_pitc_class('wff', $id, $type), $id, $name, $value, $input_class, $placeholder
			);
		}

		elseif( $type == 'select' ){
			$html .= sprintf( '<select class="%1$s %5$s" id="%2$s" name="%3$s"%4$s>', w4pl_form_pitc_class('wff', $id, $type), $id, $name, $input_attr, $input_class );
			foreach( $option as $k => $l ){
				$_attr = '';
				$sel = $value == $k ? ' selected="selected"' : '';
				if( is_array($l) )
				{
					$_attr = isset($l['attr']) ? $l['attr'] : '';
					$l = $l['label'];
				}

				$html .= sprintf( '<option value="%1$s"%2$s%4$s>%3$s</option>', $k, $sel, $l, $_attr );
			}
			$html .= '</select>';
		}

		elseif( $type == 'radio' ){
			foreach( $option as $k => $l ){
				$sel = $value == $k ? ' checked="checked"' : '';
				$html .= sprintf( 
					'<label><input id="%1$s_%2$s" class="%6$s" name="%3$s" value="%2$s" type="radio"%4$s /> %5$s</label>', $id, $k, $name, $sel, $l, $input_class 
				);
			}
		}

		elseif( $type == 'checkbox' )
		{
			foreach( $option as $k => $l )
			{
				$before = '';
				$sel = is_array($value) && in_array($k, $value) ? ' checked="checked"' : '';
				if( is_array($l) )
				{
					$before = isset($l['before']) ? $l['before'] : '';
					$l = $l['label'];
				}

				$html .= $before;
				$html .= sprintf( '<label><input id="%1$s_%2$s" name="%3$s[]" value="%2$s" type="checkbox"%4$s class="%6$s" /> %5$s</label>', $id, $k, $name, $sel, $l, $input_class );
			}
		}

		elseif( !empty($input_html) ){
			$html .= $input_html;
		}

		$html .= $input_after;

		if( $input_wrap ){
			$html .= '</div>';
		}

		// description
		if( !empty($desc2) ){
			$html .= sprintf( '<div class="%1$s">%2$s</div>', w4pl_form_pitc_class('wffdw2', $id, $type), $desc2 );
		}


	break;

	default:
		if( !empty($callback) && is_callable($callback) ){
			$html .= call_user_func($callback, $args);
		}
	break;

	endswitch;

	$html .= $field_after;

	if( !in_array($type, array('html', 'hidden') ) && $field_wrap ){
		$html .= '</div>';
	}


	return $html;
}

// prefix id type class
function w4pl_form_field_label( $args ){
	extract( $args );
	$html = '';

	if( !empty($label) ){
		if( $label_wrap ){
			$html .= sprintf( '<div class="%1$s">', w4pl_form_pitc_class('wfflw', $id, $type) );
		}
		$html .= $label_before;

		if( $required == 'y' ){
			$label .= '<span class="req">*</span>';
		}
		
		// radio checkbox would use span, not label
		if( in_array($type, array('radio', 'checkbox', 'html_input') ) ){
			$html .= sprintf( '<span class="%1$s">%2$s</span>', w4pl_form_pitc_class('wffl', $id, $type), $label );
		}
		else{
			$html .= sprintf( '<label class="%1$s" for="%2$s">%3$s</label>', w4pl_form_pitc_class('wffl', $id, $type), $id, $label );
		}

		$html .= $label_after;
		if( $label_wrap ){
			$html .= '</div>';
		}
	}

	return $html;
}

// prefix id type class
function w4pl_form_pitc_class( $pref = '', $id = '0', $type = '', $class = '' ){
	return trim( esc_attr("{$pref} {$pref}i_{$id} {$pref}t_{$type} {$class}") );
}

// sanitize id
function w4pl_form_field_id( $raw_id = '' ){
	$sanitized = preg_replace( '|%[a-fA-F0-9][a-fA-F0-9]|', '', $raw_id );
	$sanitized = preg_replace( '/[^A-Za-z0-9_-]/', '_', $sanitized );
	$sanitized = str_replace( '__', '_', $sanitized );
	$sanitized = trim( $sanitized, '_' );
	return $sanitized;
}

?>
