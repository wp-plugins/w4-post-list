<?php
/**
 * @package W4 Post List
 * @author Shazzad Hossain Khan
 * @url http://w4dev.com/plugins/w4-post-list
**/


class W4PL_Core 
{
	function __construct()
	{
		add_action( 'plugins_loaded', 							array($this, 'load_plugin_textdomain') );


		// register lists post type (w4pl)
		add_action( 'init', 									array($this, 'register_post_type'));


		// register shortcode (postlist)
		add_shortcode( 'postlist', 								array($this, 'shortcode'), 6 );


		// register css/js
		add_action( 'wp_enqueue_scripts', 						array($this, 'register_scripts'), 2 );
		add_action( 'admin_enqueue_scripts', 					array($this, 'register_scripts'), 2 );


		// allow shortcode for text widget content
		add_filter( 'widget_text', 								'do_shortcode');


		// get all available shortcodes
		add_filter( 'w4pl/get_shortcodes', 						array($this, 'get_shortcodes') );


		// display create list page template
		add_filter( 'w4pl/list_options_template', 				array($this, 'list_options_template') );


		// filter option before saving them
		add_filter( 'w4pl/pre_save_options', 					array($this, 'pre_save_options') );


		// filter list options
		add_filter( 'w4pl/pre_get_list', 						array($this, 'pre_get_list') );


		// filter list options at higher priority
		add_filter( 'w4pl/pre_get_options', 					array($this, 'pre_get_options'), 5 );


		// load list options template from posted data.
		add_action( 'wp_ajax_w4pl_list_options_template', 		array($this, 'list_options_template_ajax') );


		add_action( 'w4pl/list_options_template_html', 			array($this, 'list_options_template_html'), 5, 3 );


		// display list creation option page scripts, scripts get loaded on the head tag of that page.
		add_action( 'w4pl/list_options_print_scripts', 			array($this, 'list_options_print_scripts') );


		// get shortcode from posted data
		add_action( 'wp_ajax_w4pl_generate_shortcodes', 		array($this, 'w4pl_generate_shortcodes_ajax') );
	}

	public function load_plugin_textdomain()
	{
		load_plugin_textdomain( W4PL_TD, false, basename(dirname(dirname( __FILE__ ))) . '/languages'  );
	}

	/*
	 * Register List Post Type
	*/

	public function register_post_type()
	{
		global $wp, $wp_rewrite, $wp_post_types;

		register_post_type( W4PL_SLUG, array(
			'labels' => array(
				'name' 					=> _x('Lists', 'post type general name'),
				'singular_name' 		=> _x('List', 'post type singular name'),
				'menu_name'				=> W4PL_NAME,
				'all_items'				=> __('All Lists', W4PL_TD),
				'add_new' 				=> _x('Add New', 'note item'),
				'add_new_item' 			=> __('New List'),
				'edit_item' 			=> __('Edit List'),
				'new_item' 				=> __('New List'),
				'view_item' 			=> __('View List'),
				'search_items' 			=> __('Search List'),
				'not_found' 			=> __('No List found'),
				'not_found_in_trash' 	=> __('No List found in Trash'),
				'parent_item_colon' 	=> ''
			),
			'show_ui'  				=> true,
			'public'  				=> false,
			'has_archive'			=> false,
			'delete_with_user'		=> false,
			'show_in_admin_bar'		=> false,
			'supports' 				=> array('title' ),
			'menu_icon'				=> 'dashicons-admin-generic'
		));
	}


	/*
	 * Shortcodes - Top Level ShortCodes
	*/

	public static function get_shortcodes( $shortcodes )
	{
		// Shortcodes
		$core_shortcodes = array(
			'posts' => array(
				'group' => 'Main', 
				'code' => '[posts]'. "\n\n" .'[/posts]', 
				'desc' => '<strong>return</strong> the posts template'
			),
			'terms' => array(
				'group' => 'Main', 
				'code' => '[terms]'. "\n\n" .'[/terms]', 
				'desc' => '<strong>return</strong> the terms template'
			),
			'users' => array(
				'group' => 'Main', 
				'code' => '[users]'. "\n\n" .'[/users]', 
				'desc' => '<strong>return</strong> the users template'
			),
			'groups' => array(
				'group' => 'Main', 
				'code' => '[groups]'. "\n\n" .'[/groups]', 
				'desc' => '<strong>return</strong> the groups template'
			),
			'nav' => array(
				'group' => 'Main', 
				'code' => '[nav type="plain" ajax="1" prev_text="" next_text=""]', 
				'desc' => '<strong>return</strong> pagination for the list
            <br><br><strong>Attributes</strong>:
            <br><strong>type</strong> = (text) allowed values  - plain, list, nav
            <br><strong>ajax</strong> = (0|1) use pagination with ajax
            <br><strong>prev_text</strong> = pagination next button text
            <br><strong>next_text</strong> = pagination prev button text'
			),
		);

		return array_merge( $shortcodes, $core_shortcodes );
	}



	/*
	 * Display List Using Shortcode
	 * @param (array)
	 * @return (string)
	*/
	public function shortcode( $attr )
	{
		if( isset($attr['options']) ){
			$options = maybe_unserialize( base64_decode( str_replace( ' ', '', $attr['options'] ) ) );
		}
		elseif( isset($attr['id']) ){
			$options = get_post_meta( $attr['id'], '_w4pl', true );
			$options['id'] = $attr['id'];
		}
		else{
			if( !is_array($attr) )
				$attr = array($attr);

			$list_id = array_shift( $attr );
			$list_id = (int) $list_id;

			if( $list_id ){
				$options = get_post_meta( $list_id, '_w4pl', true );
				$options['id'] = $list_id;
			}
		}

		if( empty($options) )
			return '';

		$options = apply_filters( 'w4pl/pre_get_options', $options );

		return self::the_list( $options );
	}


	/*
	 * The List
	 * @param (int), list id
	 * @return (string)
	*/
	public static function the_list( $options )
	{
		$w4_post_list = new W4_Post_list();
		$list = $w4_post_list->prepare( $options );
		if( is_wp_error($list) )
		{
			if( is_user_logged_in() && current_user_can( 'delete_users') ){
				return '<p>
					<strong>W4 Post List Error:</strong> <span style="color:#FF0000">'. $list->get_error_message() .'</span>
					<br /><small>*** This error is only visible to admins and won\'t effect in search engine.</small>
				</p>';
			}
			return '<!--W4_Post_list_Error: '. $list->get_error_message() .'-->';
		}

		return "\n<!--list Created by W4 Post List Ver ". W4PL_VERSION ."-->\n" . $w4_post_list->display();
	}


	/*
	 * List Options Template
	 * @param $options (array)
	 * @echo (string)
	*/

	public static function list_options_template( $options )
	{
		$options = apply_filters( 'w4pl/pre_get_options', $options );

		$fields = array();

		// this wrap the whole fields are
		$fields['before_list_options'] = array(
			'position'		=> '0',
			'html' 			=> '<div id="w4pl_list_options">'
		);
		$fields['id'] = array(
			'position'		=> '1.1',
			'option_name' 	=> 'id',
			'name' 			=> 'w4pl[id]',
			'type' 			=> 'hidden'
		);
		$fields['tab_id'] = array(
			'position'		=> '1.2',
			'option_name' 	=> 'tab_id',
			'name' 			=> 'w4pl[tab_id]',
			'type' 			=> 'hidden'
		);


		/* Field Group - List Type */
		$fields['before_field_group_type'] = array(
			'position'		=> '2',
			'html' 			=> '<div id="w4pl_field_group_type" class="w4pl_field_group">
								<div class="w4pl_group_title">'. __('List Type', W4PL_TD) .'</div>
								<div class="w4pl_group_fields">'
		);
		$fields['list_type'] = array(
			'position'		=> '3',
			'option_name' 	=> 'list_type',
			'name' 			=> 'w4pl[list_type]',
			'label' 		=> __('List Type', W4PL_TD),
			'type' 			=> 'radio',
			'option' 		=> self::list_type_options(),
			'input_class'	=> 'w4pl_onchange_lfr'
		);
		$fields['after_field_group_type'] = array(
			'position'		=> '4',
			'html' 			=> '</div><!--.w4pl_group_fields--></div><!--#w4pl_field_group_type-->'
		);


		/* Field Group - Template */
		$fields['before_field_group_template'] = array(
			'position'		=> '150',
			'html' 			=> '<div id="w4pl_field_group_template" class="w4pl_field_group">
				<div class="w4pl_group_title">'. __('Template', W4PL_TD) .'</div>
				<div class="w4pl_group_fields">'
		);

		$template_html = '
		<div class="wffw wffwi_w4pl_template wffwt_textarea">
			<p style="margin-top:0px;">
				<a href="#" class="button w4pl_toggler" data-target="#w4pl_template_examples">'. __('Template Example', W4PL_TD) .'</a>
				<a href="#" class="button w4pl_toggler" data-target="#w4pl_template_buttons">'. __('Shortcodes', W4PL_TD) .'</a>
			</p>
			<div id="w4pl_template_examples" class="csshide">'
			. "<pre style='width:auto'>\n[groups]\n\t[group_title]\n\t[posts]\n\t\t[post_title]\n\t[/posts]\n[/groups]\n[nav]</pre>"
			. "<br />without group, a simple template should be like -"
			. "<pre style='width:auto'>[posts]\n\t[post_title]\n[/posts]\n[nav]</pre>"
			. '</div>';


		$shortcodes = apply_filters( 'w4pl/get_shortcodes', array() );
		$shortcode_groups = array();
		foreach( $shortcodes as $shortcode => $attr ){
			$group = $attr['group'];
			if( !isset($shortcode_groups[$group]) || !is_array($shortcode_groups[$group]) )
				$shortcode_groups[$group] = array();
			$shortcode_groups[$group][] = $shortcode;
		}

		$template_html .= '<div id="w4pl_template_buttons" class="csshide">';
		foreach( $shortcode_groups as $shortcode_group => $scodes ){
			$template_html .= sprintf(' <div class="w4pl_button_group"><span class="w4pl_button_group_title">%1$s</span>', $shortcode_group );
			foreach( $scodes as $shortcode ){
				$attr = $shortcodes[$shortcode];
				if( isset($attr['code']) )
					$code = $attr['code'];
				else
					$code = '['. $shortcode . ']';
				$template_html .= sprintf(' <a href="#%1$s" data-code="%2$s">%1$s</a>', $shortcode, esc_attr($code) );
			}
			$template_html .= '</div>';
		}
		$template_html .= '</div>';

		/*
		$template_html .= '
		<div class="wfflw wfflwi_w4pl_template wfflwt_textarea">
			<label for="w4pl_template" class="wffl wffli_w4pl_template wfflt_textarea">Template</label>
		</div>';
		*/
		$template_html .= w4pl_form_child_field_html( array(
			'id' 			=> 'w4pl_template',
			'name' 			=> 'w4pl[template]',
			'input_class' 	=> 'wff wffi_w4pl_template wfft_textarea widefat',
			'type' 			=> 'textarea',
			'default' 		=> apply_filters('w4pl/template_default', '' ),
			'value'			=> isset($options['template']) ? $options['template'] : ''
		));
		$template_html .= '</div>';

		$fields['template1'] = array(
			'position'		=> '155',
			'html' 			=> $template_html
		);

		$fields['after_field_group_template'] = array(
			'position'		=> '160',
			'html' 			=> '</div><!--.w4pl_group_fields--></div><!--#w4pl_field_group_template-->'
		);



		$fields['after_list_options'] = array(
			'position'		=> '999',
			'type' 			=> 'html',
			'html' 			=> '</div><!--after_list_options-->'
		);

		# echo '<pre>'; print_r($fields); echo '</pre>';


		$form_args = array(
			'no_form' 		=> true,
			'button_after' 	=> false
		);


		// let helper class extend/modify this class
		$fields = apply_filters( 'w4pl/admin_list_fields', $fields, $options );


		// order by position
		uasort( $fields, array( get_class(), 'order_by_position') );

		# echo '<pre>'; print_r($fields); echo '</pre>';

		$output = w4pl_form_fields( $fields, $options, $form_args );

		// filter the output
		$output = apply_filters( 'w4pl/list_options_template_html', $output, $fields, $options );

		echo $output;
	}

	public static function list_options_template_html( $output, $fields, $options )
	{
		if( isset($options['tab_id']) )
			$output = str_replace('id="'. $options['tab_id'] .'" class="', 'id="'. $options['tab_id'] .'" class="w4pl_active ', $output);

		return $output;
	}

	public static function list_options_template_ajax()
	{
		// parsing from tinymce input
		if( isset($_POST['selection']) )
		{
			$selection = isset($_POST['selection']) ? stripslashes($_POST['selection']) : '';
			if( preg_match( "/\[postlist options=[\"\'](.*?)[\"\']/sm", $selection, $selection_match) )
			{
				$options = maybe_unserialize( base64_decode( str_replace( ' ', '', $selection_match['1']) ) );
				if( is_object($options) ){
					$options = get_object_vars($options);
				}

				if( !empty($options) ){
					do_action( 'w4pl/list_options_template', $options );
				}
			}
			elseif( preg_match( "/\[postlist id=[\"\'](.*?)[\"\']/sm", $selection, $selection_match) )
			{
				$list_id = preg_replace('/[^0-9]/', '', $selection_match['1']);
				if( $list_id ){
					$options = get_post_meta( $list_id, '_w4pl', true );
					$options['id'] = $list_id;
					$options = apply_filters( 'w4pl/pre_get_options', $options );
					do_action( 'w4pl/list_options_template', $options );
				}
			}
			elseif( preg_match( "/\[postlist (.*?)]/sm", $selection, $selection_match) )
			{
				$list_id = preg_replace('/[^0-9]/', '', $selection_match['1']);
				if( $list_id ){
					$options = get_post_meta( $list_id, '_w4pl', true );
					$options['id'] = $list_id;
					$options = apply_filters( 'w4pl/pre_get_options', $options );
					do_action( 'w4pl/list_options_template', $options );
				}
			}
		}
		elseif( isset($_POST['w4pl']) )
		{
			$options = stripslashes_deep( $_POST['w4pl'] );
			if( is_object($options) ){
				$options = get_object_vars($options);
			}
			if( !empty($options) ){
				do_action( 'w4pl/list_options_template', $options );
			}
		}

		die('');
	}

	public function pre_save_options( $options )
	{
		if( isset($options['post_type']) && 'attachment' == $options['post_type'] ){
			unset( $options['post_status'] );
		}
		if( isset($options['template']) ){
			$options['template'] = apply_filters('w4pl/template', $options['template'], $options );
		}

		return $options;
	}


	public function pre_get_options($options)
	{
		if( !isset($options) || !is_array($options) )
			$options = array();


		/* Version 1.6.7 List Compat */
		if( isset($options['template_loop']) && !empty($options['template_loop']) ){
			if( isset($options['template']) 
				&& ! preg_match('/\[posts\](.*?)\[\/posts\]/sm', $options['template']) 
				&& preg_match('/\[loop\]/sm', $options['template'], $match ) 
			){
				$options['template'] = str_replace( $match[0], '[posts]'. $options['template_loop'] .'[/posts]', $options['template'] );
			}
			elseif( empty($options['template']) )
			{
				$options['template'] = str_replace( '[loop]', '[posts]'. $options['template_loop'] .'[/posts]', $options['template'] );
			}

			unset($options['template_loop']);
		} // end


		$options = wp_parse_args( $options, array(
			'id' 				=> md5( microtime() . rand() ), 
			'tab_id' 			=> 'w4pl_field_group_type', 
			'list_type' 		=> 'posts'
		));

		if( isset($options['template']) ){
			$options['template'] = apply_filters( 'w4pl/template', $options['template'], $options );
		}

		return $options;
	}

	public function pre_get_list($list)
	{
		return $list;
	}


	/*
	 * Display List Shortcode - Ajax
	**/

	public static function w4pl_generate_shortcodes_ajax()
	{
		$options = isset($_POST) ? stripslashes_deep($_POST) : array();
		if( isset($options['w4pl']) )
			$options = $options['w4pl'];

		if( empty($options) )
			die('');


		// if a list exists, we save the data and return the short with id
		if( is_numeric($options['id']) && get_post($options['id']) )
		{
			// pass options through callback
			$options = apply_filters( 'w4pl/pre_save_options', $options );
			// update into post meta
			update_post_meta( $options['id'], '_w4pl', $options );

			printf( '[postlist id="%d"]', $options['id']);
		}

		else
		{
			// filter options, remove default values
			$options = apply_filters( 'w4pl/pre_save_options', $options );
			// encode options, split string by 100 characters to avoid 
			$encode = chunk_split( base64_encode( maybe_serialize($options) ), 100, ' ');
	
			printf( '[postlist options="%s"]', trim($encode) );
		}

		die();
	}


	/*
	 * Register Scripts
	**/

	public static function register_scripts()
	{
		wp_register_style(  'w4pl_form', 				W4PL_URL . 'assets/form/form.css' );
		wp_register_script( 'w4pl_form', 				W4PL_URL . 'assets/form/form.js', array('jquery', 'jquery-ui-core') );
		wp_register_script( 'w4pl_tinymce_popup', 		W4PL_URL . 'assets/tinymce/tinymce_popup.js' );
	}



	public static function list_options_print_scripts( $options )
	{
		$options = apply_filters( 'w4pl/pre_get_options', $options );

		wp_print_styles(  'w4pl_form' );
		wp_print_scripts( 'w4pl_form' );

		?>
		<style>
/* W4 Post List - Admin List Template CSS */
#titlediv{margin-bottom:30px;}
#w4pl_list_options{ position:relative; font-size:13px; line-height:normal;}
table.widefat{ background-color:#FFF; box-shadow:0 1px 1px rgba(0, 0, 0, 0.04); border:1px solid #e5e5e5; border-spacing:0px; width:100%; clear:both;}
table.widefat th{font-size:11px; padding:8px 10px; font-weight:400;}
table.widefat td{font-size:11px; padding:8px 10px;}
table.widefat thead th{ border-bottom:1px solid #e1e1e1;}
#w4pl_list_options code{border: 1px solid #d1e5ee; color: #666; padding: 1px 5px; font-size:12px;}
#w4pl_template, #w4pl_css, #w4pl_js{height:350px; font-size:14px; line-height:18px; color:#666; width:100%; box-sizing:border-box;}
#w4pl_js{height:150px;}
#w4pl_template_examples{ font-size:12px; color:#999999;}
#shortcode_hint_toggle{position:relative;margin:10px 0;float:left;clear:left;}
#w4pl_post_type_options{position:relative;}
#w4pl_post_type_options:after{ background:url(images/loading.gif) no-repeat; width:30px; height:30px; display:block;}
#w4pl_template_buttons a{ padding:4px 8px; display:inline-block; border:1px solid #DDD; background-color:#EEE; line-height:12px; font-size:12px; margin:0 2px 2px 0; text-decoration:none; border-radius: 3px; -moz-border-radius:3px; -webkit-border-radius:3px; color:#0074a2;}
.w4pl_button_group{ padding:0 0 10px;}
.w4pl_button_group_title{ display:block;}
.wffw{margin:0;padding-top:8px;padding-bottom:8px;border-width: 0 0 1px 5px;box-sizing: border-box;-moz-box-sizing: border-box;-webkit-box-sizing: border-box;overflow:hidden;}
.wfflw, .wffdw {width:200px;float:left;clear:left;}
.wffdw2{ margin-top:10px; margin-bottom:0;}
.wffew {margin-left:220px;}
.wffl{font-size:13px;}
.wfflwi_w4pl_template, .wffdwi_w4pl_css, .wffdwi_w4pl_js{ float:none !important; width:auto;}
.wffewi_w4pl_css, .wffewi_w4pl_js{ margin-left:0;}
.wffewi_w4pl_list_type label, .wffwi_w4pl_terms_taxonomy label{display:block}
.wffewi_w4pl_list_type label small{ color:#999; text-transform:uppercase; font-weight:bold; }
.w4pl_group_title{margin:0; width:20%; padding:8px 10px;border-bottom:1px solid #D1E5EE; background-color:#FFF; font-size:16px; line-height:20px;
box-sizing: border-box;-moz-box-sizing: border-box;-webkit-box-sizing: border-box; font-weight:normal; cursor:pointer;}
.w4pl_field_group:last-child .w4pl_group_title{ border-bottom:1px solid #D1E5EE;}
.w4pl_field_group{}
.w4pl_group_fields{ display:none; position:absolute; left:22%; top:0; width:78%;}

.w4pl_active .w4pl_group_fields{ display:block;}
.w4pl_active .w4pl_group_title, .w4pl_group_title:hover{ background-color:#D1E5EE; box-shadow:0 0 1px #666 inset;}
#w4pl_lo{ width:100%; height:100%; position:absolute; top:0; left:0; background:url(<?php echo admin_url('images/spinner.gif'); ?>) no-repeat center rgba(255,255,255,0.5);}
#minor-publishing-actions, #misc-publishing-actions{display:none;}

body.rtl .w4pl_group_fields{ left:0;}
body.rtl .wffew{ margin-left:0; margin-right:220px;}
body.rtl .wfflw, body.rtl .wffdw{ float:right; clear:right;}
body.rtl .wffewi_w4pl_css, .wffewi_w4pl_js{ margin-right:0 !important;}

<?php do_action( 'w4pl/admin_print_css' ); ?>
        </style>

		<script type="text/javascript">
(function($){

	$(document).on('w4pl/form_loaded', function(el){
		//console.log('w4pl/form_loaded');
		//$('#w4pl_list_options').css('minHeight', $('.w4pl_group_fields.w4pl_active').outerHeight() );
		w4pl_adjust_height();

		$('#w4pl_orderby').trigger('change');
	});

	$(document).ready(function(){
		$(document).trigger('w4pl/form_loaded', $('#w4pl_list_options') );
	});


	/* onchange post type, refresh the form */
	$('.w4pl_onchange_lfr').live('change', function(){
		var id = $(this).parents('.w4pl_field_group').attr('id');
		// console.log( id );
		w4pl_get_form(null, id );
	});
	/* onclick button, display hidden elements */
	$('.w4pl_toggler').live('click', function(){
		$( $(this).data('target') ).toggle();
		w4pl_adjust_height();
		return false;
	});
	/* onchange orderby, toggle meta input */
	$('#w4pl_orderby').live('change', function(){
		if( 'meta_value' == $(this).val() || 'meta_value_num' == $(this).val() ){
			$('#orderby_meta_key_wrap').show();
		}
		else{
			$('#orderby_meta_key_wrap').hide();
		}
	});
	/* show/hide group options */
	$('.w4pl_group_title').live('click', function()
	{
		$('#w4pl_list_options').height('auto');
		$('.w4pl_field_group').removeClass('w4pl_active');
		$(this).parent('.w4pl_field_group').addClass('w4pl_active');

		$('#w4pl_tab_id').val( $(this).parent('.w4pl_field_group').attr('id') );
		w4pl_adjust_height();

		return false;
	});
	/* put selected element code at pointer */
	$('#w4pl_template_buttons a').live('click', function(e){
		insertAtCaret( 'w4pl_template', $(this).data('code') );
		return false;
	});

	// Adjust form height
	function w4pl_adjust_height()
	{
		var miHeight = $('.w4pl_active .w4pl_group_fields').outerHeight();
		$('#w4pl_list_options').css('minHeight', miHeight);
	}

	function w4pl_get_form( data, showTab )
	{
		/* onchange post type, refresh the form */
		$('#publish').addClass('disabled');

		if( showTab === null ){
			showTab = 'w4pl_field_group_type';
		}
		if( data === null ){
			var data = $('#w4pl_list_options :input').serialize() + '&action=w4pl_list_options_template';
		}

		$('#w4pl_list_options').append('<div id="w4pl_lo"></div>');
		//return false;

		$.post( ajaxurl, data, function(r)
		{
			$('#w4pl_list_options').replaceWith(r);

			$('#'+ showTab).addClass('w4pl_active');

			$(document).trigger('w4pl/form_loaded', $('#w4pl_list_options') );

			// $('.wffwi_w4pl_post_type .spinner').css('display', 'none');
			$('#publish').removeClass('disabled');

			return false;
		})
	}

	/*
	 * Similar feature as tinymce quicktag button
	 * This function helps to place shortcode right at the cursor position
	*/
	function insertAtCaret(areaId,text) {
		var txtarea = document.getElementById(areaId);
		var scrollPos = txtarea.scrollTop;
		var strPos = 0;
		var br = ((txtarea.selectionStart || txtarea.selectionStart == '0') ? 
			"ff" : (document.selection ? "ie" : false ) );
		if (br == "ie") { 
			txtarea.focus();
			var range = document.selection.createRange();
			range.moveStart ('character', -txtarea.value.length);
			strPos = range.text.length;
		}
		else if (br == "ff") strPos = txtarea.selectionStart;
	
		var front = (txtarea.value).substring(0,strPos);  
		var back = (txtarea.value).substring(strPos,txtarea.value.length); 
		txtarea.value=front+text+back;
		strPos = strPos + text.length;
		if (br == "ie") { 
			txtarea.focus();
			var range = document.selection.createRange();
			range.moveStart ('character', -txtarea.value.length);
			range.moveStart ('character', strPos);
			range.moveEnd ('character', 0);
			range.select();
		}
		else if (br == "ff") {
			txtarea.selectionStart = strPos;
			txtarea.selectionEnd = strPos;
			txtarea.focus();
		}
		txtarea.scrollTop = scrollPos;
	}

	<?php do_action( 'w4pl/admin_print_js' ); ?>

})(jQuery);
		</script>
        <?php
	}

	public static function list_type_options()
	{
		$return = array(
			'posts' 		=> __('Posts', W4PL_TD) .' - <small>'. implode(', ', self::post_type_options()) .'</small>',
			'terms' 		=> __('Terms', W4PL_TD) .' - <small>'. implode(', ', self::taxonomies_options()) .'</small>',
			'users' 		=> __('Users', W4PL_TD),
			'terms.posts' 	=> __('Terms + Posts', W4PL_TD),
			'users.posts' 	=> __('Users + Posts', W4PL_TD)
		);

		return $return;
	}


	public static function post_type_options()
	{
		global $wp_post_types;

		$return = array();
		foreach( $wp_post_types as $post_type => $post_type_object ){
			// exclude the list post type
			if( !in_array($post_type, array(W4PL_SLUG, 'revision', 'nav_menu_item') ) ){
				$return[$post_type] = $post_type_object->labels->name;
			}
		}
	
		return $return;
	}

	public static function post_mime_type_options($post_types = '')
	{
		global $wpdb;
		if( empty($post_types) ){
			$post_types = array('post');
		}
		elseif( ! is_array($post_types) ){
			$post_types = array($post_types);
		}

		$mime_types = $wpdb->get_col(
			"SELECT DISTINCT post_mime_type FROM $wpdb->posts WHERE post_status != 'trash' AND post_type IN ('" . implode("','", $post_types) ."') AND post_mime_type <> ''"
		);

		if( !empty($mime_types) )
		{
			$return = array('' => 'Any');
			foreach( $mime_types as $mime_type ){
				if( !empty($mime_type) )
					$return[$mime_type] = $mime_type;
			}
			return $return;
		}
		return array();
	}

	public static function post_status_options()
	{
		global $wp_post_statuses;

		$return = array();
		foreach( $wp_post_types as $post_type => $post_type_object ){
			if(	!$post_type_object->public )
				continue;

			$return[$post_type] = $post_type_object->labels->name;
		}

		return $return;
	}

	public static function post_groupby_options( $post_types = array() )
	{
		$return = array(
			'' 				=> __('None', 			W4PL_TD),
			'year' 			=> __('Year', 			W4PL_TD),
			'month' 		=> __('Month', 			W4PL_TD),
			'yearmonth' 	=> __('Year Months', 	W4PL_TD),
			'author' 		=> __('Author',			W4PL_TD),
			'parent' 		=> __('Parent', 		W4PL_TD),
			'meta_value'	=> __('Custom field', 	W4PL_TD)
		);

		if( ! is_array($post_types) ){
			$post_types = array($post_types);
		}
		if( ! empty($post_types) && is_array($post_types) ){
			foreach( $post_types as $post_type ){
				foreach( get_object_taxonomies($post_type, 'all') as $taxonomy => $taxonomy_object ){
					if( $taxonomy == 'post_format' || !$taxonomy_object->public )
						continue;
					$return['tax_'. $taxonomy] = $taxonomy_object->labels->name;
				}
			}
		}

		return $return;
	}

	public static function post_orderby_options( $post_types = array() )
	{
		$return = array(
			'ID'				=> __( 'ID', 					W4PL_TD),
			'title'				=> __( 'Title', 				W4PL_TD),
			'name'				=> __( 'Name', 					W4PL_TD),
			'date'				=> __( 'Publish Date', 			W4PL_TD),
			'modified'			=> __( 'Modified Date', 		W4PL_TD),
			'menu_order'		=> __( 'Menu Order', 			W4PL_TD),
			'meta_value'		=> __( 'Meta value', 			W4PL_TD),
			'meta_value_num'	=> __( 'Meta numeric value', 	W4PL_TD),
			'comment_count'		=> __( 'Comment Count', 		W4PL_TD),
			'rand'				=> __( 'Random', 				W4PL_TD),
			'none'				=> __( 'None', 					W4PL_TD),
			'post__in'			=> __( 'Include posts', 	W4PL_TD),
		);

		return $return;
	}

	public static function taxonomies_options()
	{
		global $wp_taxonomies;
		$return = array();
		foreach( $wp_taxonomies as $t => $attr){
			#if( $attr->public ){
				$return[$t] = $attr->label;
			#}
		}
		return $return;
	}



	public static function get_shortcode_hint_html()
	{
		$shortcodes = W4PL_Core::get_shortcodes();
		$return = '<a target="#shortcode_hint" class="button w4pl_toggler">shortcodes details</a>';
		$return .= '<table id="shortcode_hint" class="widefat csshide">';
		$return .= '<thead><tr><th style="text-align: right;">Tag</th><th>Details</th></tr></thead><tbody>';
		foreach( $shortcodes as $shortcode => $attr ){ 
			$rc = isset($rc) && $rc == '' ? $rc = 'alt' : '';
			$return .= '<tr class="'. $rc .'">';
			$return .= '<th valign="top" style="text-align: right; font-size:12px; line-height: 1.3em;"><code>['. $shortcode. ']</code></th>';
			$return .= '<td style="font-size:12px; line-height: 1.3em;">'. $attr['desc'] . '</td>';
			$return .= '</tr>';
		}
		$return .= '</tbody></table>';
		return $return;
	}


	/*
	 * Order array elements by position
	 * @param (array)
	 * @param (array)
	 * @return (bool)
	*/

	public static function order_by_position( $a, $b )
	{
		if( !isset($a['position']) || !isset($b['position']) )
			return -1;

		if( $a['position'] == $b['position'] )
	        return 0;

	    return ($a['position'] < $b['position']) ? -1 : 1;
	}

	public static function p($a)
	{
		echo '<pre>'; print_r($a); echo '</pre>';
	}
}

	new W4PL_Core;
?>