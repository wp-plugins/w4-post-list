<?php
class W4PL_Admin 
{
	function __construct()
	{
		add_filter( 'post_updated_messages', 				array($this, 'post_updated_messages'));
		add_filter( 'plugin_action_links_' . W4PL_BASENAME, array($this, 'plugin_action_links') );

		add_action( 'add_meta_boxes', 						array($this, 'add_meta_boxes') );
		add_action( 'save_post_'. W4PL_SLUG,  				array($this, 'save_post'), 10, 3 );

		add_action( 'wp_ajax_w4pl_get_post_type_fields', 	array($this, 'get_post_type_fields_ajax') );

		add_filter( 'w4pl/template_default',  				array($this, 'template_default') );
		add_filter( 'w4pl/template_loop_default',  			array($this, 'template_loop_default') );
	}

	public function post_updated_messages( $messages )
	{
		global $post_ID, $post;

		$messages[W4PL_SLUG] = array(
			 1 => sprintf( __('List updated. Use Shortcode <input value="[postlist %d]" type="text" size="9" />'), $post_ID ),
			 2 => '',
			 3 => '',
			 4 => __('List updated.'),
			 5 => '',
			 6 => sprintf( __('List published. Use Shortcode <input value="[postlist %d]" type="text" size="9" />'), $post_ID ),
			 7 => __('List saved.'),
			 8 => sprintf( __('List submitted. Use Shortcode <input value="[postlist %d]" type="text" size="9" />'), $post_ID ),
			 9 => sprintf( __('List scheduled. Use Shortcode <input value="[postlist %d]" type="text" size="9" />'), $post_ID ),
			10 => ''
		);
		return $messages;
	}

	// Meta box
	public function add_meta_boxes( $post_type )
	{
		// this seems better :)
		if( $post_type == W4PL_SLUG ){
			add_action( 'edit_form_after_title', array($this, 'list_options_meta_box') );
		}

		add_meta_box( "w4pl_news_meta_box", "Plugin Updates", array($this, 'news_meta_box'), W4PL_SLUG, "side", 'core');

		if( $post_type == W4PL_SLUG )
		{
			add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts') );
			add_action('admin_head', array($this, 'admin_head') );
			add_action('admin_print_footer_scripts', array($this, 'admin_print_footer_scripts') );
		}
	}


	public function admin_enqueue_scripts($hook)
	{
		wp_enqueue_style(  'w4pl_form', W4PL_URL . 'assets/form.css' );
		wp_enqueue_script( 'w4pl_form', W4PL_URL . 'assets/form.js', array( 'jquery', 'plupload-handlers', 'jquery-ui-autocomplete') );
	}


	public function admin_head()
	{
		?>
        <style>
/*W4 post list admin*/
#w4pl_template_before, #w4pl_template_after, #w4pl_template_loop{
	width:99%;
	height:50px;
	}
#w4pl_template_loop, #w4pl_css{
	height:250px;
	}
#minor-publishing-actions, #misc-publishing-actions{
	display:none;
}
#shortcode_hint_toggle{
	position:relative;
	margin:10px 0;
	float:left;
	clear:left;
}
.tax_query .wffew{
	max-height:100px;
	overflow:hidden;
	overflow-y:auto;
}
.wffw{
	margin:15px 0;
	padding-top:15px;
	padding-bottom:15px;
	border-width: 0 0 0 5px;
	box-shadow:0 0 1px #AAAAAA;
}
.wfflw,
.wffdw {
	width:200px;
	float:left;
	clear:left;
}
.wffew {
	margin-left:220px;
}
.wffl{
	font-size:13px;
}
        </style>
		<script type="text/javascript">
(function($){
	$(document).ready(function(){
		$('#shortcode_hint_toggle').click(function(){
			$('#shortcode_hint').toggle();
			return false;
		});

		$('#w4pl_orderby').change(function(){
			if( 'meta_value' == $(this).val() || 'meta_value_num' == $(this).val() ){
				$('#orderby_meta_key_wrap').show();
			}
			else{
				$('#orderby_meta_key_wrap').hide();
			}
		});
		$('#w4pl_orderby').trigger('change');
		

		$('#w4pl_post_type').change(function(){
			$.post(ajaxurl, 'action=w4pl_get_post_type_fields&post_id='+ $('#post_ID').val() +'&post_type='+ $(this).val(), function(r){
				$('#w4pl_post_type_options').html(r);
				return false;
			})
		});
	});
})(jQuery) ;
		</script>
        <?php
	}

	public function admin_print_footer_scripts(){}


	public function list_options_meta_box( $post )
	{
		$post_ID = $post->ID;
		$post_data = get_post_meta( $post_ID, '_w4pl', true );

		if( ! $post_data || !is_array($post_data) )
			$post_data = array();

		if( ! isset($post_data['post_type']) )
			$post_data['post_type'] = 'post';

		if( ! isset($post_data['post_status']) )
			$post_data['post_status'] = 'publish';

		$fields = array();
		$fields['post_type'] = array(
			'option_name' 	=> 'post_type',
			'name' 			=> 'w4pl[post_type]',
			'label' 		=> 'Post Type',
			'type' 			=> 'select',
			'option' 		=> self::post_type_options()
		);
		$fields['post_status'] = array(
			'option_name' 	=> 'post_status',
			'name' 			=> 'w4pl[post_status]',
			'label' 		=> 'Post Status',
			'type' 			=> 'checkbox',
			'option' 		=> array('any' => 'Any', 'inherit' => 'Inherit', 'pending' => 'Pending', 'publish' => 'Publish', 'future' => 'Future')
		);
		$fields['before_post_type_options'] = array(
			'type' 			=> 'html',
			'html' 			=> '<div id="w4pl_post_type_options">'
		);

		// intialize post type fields
		self::post_type_fields($fields, $post_data);


		$fields['after_post_type_options'] = array(
			'type' 			=> 'html',
			'html' 			=> '</div><!--w4pl_post_type_options-->'
		);


		$fields['orderby'] = array(
			'option_name' 	=> 'orderby',
			'name' 			=> 'w4pl[orderby]',
			'label' 		=> 'Orderby',
			'type' 			=> 'select',
			'option' 		=> self::post_orderby_options($post_data['post_type']),
			'input_after'	=> '<div id="orderby_meta_key_wrap">Meta key: <input name="w4pl[orderby_meta_key]" type="text" value="'. (isset($post_data['orderby_meta_key']) ? esc_attr($post_data['orderby_meta_key']) : '') .'" /></div>'
		);
		$fields['order'] = array(
			'option_name' 	=> 'order',
			'name' 			=> 'w4pl[order]',
			'label' 		=> 'Order',
			'type' 			=> 'select',
			'option' 		=> array('ASC' => 'ASC', 'DESC' => 'DESC')
		);

		$fields['post__in'] = array(
			'option_name' 	=> 'post__in',
			'name' 			=> 'w4pl[post__in]',
			'label' 		=> 'Include post by ids',
			'type' 			=> 'text',
			'desc' 			=> 'comma separate id'
		);
		$fields['post__not_in'] = array(
			'option_name' 	=> 'post__not_in',
			'name' 			=> 'w4pl[post__not_in]',
			'label' 		=> 'Exclude post by ids',
			'type' 			=> 'text',
			'desc' 			=> 'comma separate id'
		);
		$fields['post_parent__in'] = array(
			'option_name' 	=> 'post_parent__in',
			'name' 			=> 'w4pl[post_parent__in]',
			'label' 		=> 'Post parent ids',
			'type' 			=> 'text',
			'desc' 			=> 'comma separate id'
		);
		$fields['author__in'] = array(
			'option_name' 	=> 'author__in',
			'name' 			=> 'w4pl[author__in]',
			'label' 		=> 'Post author ids',
			'type' 			=> 'text',
			'desc' 			=> 'comma separate id'
		);

		$fields['posts_per_page'] = array(
			'option_name' 	=> 'posts_per_page',
			'name' 			=> 'w4pl[posts_per_page]',
			'label' 		=> 'Items per page',
			'type' 			=> 'text',
			'desc' 			=> 'number of items to show per page'
		);
		$fields['limit'] = array(
			'option_name' 	=> 'limit',
			'name' 			=> 'w4pl[limit]',
			'label' 		=> 'Maximum items to display',
			'type' 			=> 'text',
			'desc' 			=> 'maximum results to display in total'
		);

		$fields['template'] = array(
			'option_name' 	=> 'template',
			'name' 			=> 'w4pl[template]',
			'label' 		=> 'Main template',
			'type' 			=> 'textarea',
			'input_class' 	=> 'widefat',
			'default' 		=> apply_filters('w4pl/template_default', ''),
			'desc' 			=> 'the main template. this template should have the [loop] shortcode somewhere.'
		);
		$fields['template_loop'] = array(
			'option_name' 	=> 'template_loop',
			'name' 			=> 'w4pl[template_loop]',
			'label' 		=> 'Loop Template',
			'type' 			=> 'textarea',
			'input_class' 	=> 'widefat',
			'default' 		=> apply_filters('w4pl/template_loop_default', ''),
			'desc' 			=> 'click on the button below to see all available shortcodes'
		);


		$shortcodes = W4_Post_list::get_shortcodes();
		$shortcode_hint_html = '<a id="shortcode_hint_toggle" class="button">View Shortcodes</a>';
		$shortcode_hint_html .= '<table id="shortcode_hint" class="widefat" style="display:none;">';
		$shortcode_hint_html .= '<thead><tr><th style="text-align: right;">Tag</th><th>Details</th></tr></thead><tbody>';
		foreach( $shortcodes as $shortcode => $attr ){ 
			$rc = isset($rc) && $rc == '' ? $rc = 'alt' : '';
			$shortcode_hint_html .= '<tr class="'. $rc .'">';
			$shortcode_hint_html .= '<th valign="top" style="text-align: right; font-size:12px; line-height: 1.3em;"><code>['. $shortcode. ']</code></th>';
			$shortcode_hint_html .= '<td style="font-size:12px; line-height: 1.3em;">'. $attr['desc'] . '</td>';
			$shortcode_hint_html .= '</tr>';
		}
			$shortcode_hint_html .= '<tr class="'. $rc .'">';
			$shortcode_hint_html .= '<th valign="top" style="text-align: right; font-size:12px; line-height: 1.3em;"><code>[nav]</code></th>';
			$shortcode_hint_html .= '<td style="font-size:12px; line-height: 1.3em;"><strong>return</strong> pagination for the list
            <br /><br /><strong>Attributes</strong>:
            <br /><strong>type</strong> = (text) allowed values  - plain, list, nav
            <br /><strong>ajax</strong> = (0|1) use pagination with ajax</td>';
			$shortcode_hint_html .= '</tr>';
		$shortcode_hint_html .= '</tbody></table>';

		$fields['template_loop']['input_wrap_before'] = $shortcode_hint_html;

		$fields['class'] = array(
			'option_name' 	=> 'class',
			'name' 			=> 'w4pl[class]',
			'label' 		=> 'List class',
			'type' 			=> 'text',
			'input_class' 	=> 'widefat',
			'desc' 			=> 'add html class to the list'
		);
		$fields['css'] = array(
			'option_name' 	=> 'css',
			'name' 			=> 'w4pl[css]',
			'label' 		=> 'Custom css',
			'type' 			=> 'textarea',
			'input_class' 	=> 'widefat',
			'desc' 			=> 'this css loaded just before the list template. to make the style unique just for this list, use <code>#w4pl-list-'. get_the_ID() . '</code> as parent selector. Alternatively, you can use <code>#w4pl-[listid]</code> which will do the same thing.'
		);
		$fields['js'] = array(
			'option_name' 	=> 'js',
			'name' 			=> 'w4pl[js]',
			'label' 		=> 'JavaScript',
			'type' 			=> 'textarea',
			'input_class' 	=> 'widefat',
			'desc' 			=> 'this js loaded just after the list template.'
		);

		$form_args = array(
			'no_form' 		=> true,
			'button_after' 	=> false
		);

		echo w4pl_form_fields( $fields, $post_data, $form_args );
	}

	public static function get_post_type_fields_ajax()
	{
		$post_ID = isset($_POST['post_id']) ? $_POST['post_id'] : 0;
		$post_data = get_post_meta( $post_ID, '_w4pl', true );
		if( ! $post_data || !is_array($post_data) )
			$post_data = array();

		if( isset($_POST['post_type']) )
			$post_data['post_type'] = $_POST['post_type'];

		$post_type = $post_data['post_type'];

		$fields = array();
		self::post_type_fields($fields, $post_data);

		if( empty($fields) ){
			echo '';
			die();
		}

		$form_args = array(
			'no_form' 		=> true,
			'button_after' 	=> false
		);
		echo w4pl_form_fields( $fields, $post_data, $form_args );
		die();
	}


	public static function post_type_fields( &$fields, $post_data )
	{
		$post_type = $post_data['post_type'];
		// mime type field
		if( $mime_type_options = self::post_mime_type_options($post_type) )
		{
			$fields['post_mime_type'] = array(
				'option_name' 	=> 'post_mime_type',
				'name' 			=> 'w4pl[post_mime_type]',
				'label' 		=> 'Post Mime Type',
				'type' 			=> 'select',
				'option' 		=> $mime_type_options
			);
		}

		foreach( self::post_type_taxonomies_options($post_type) as $taxonomy => $label )
		{
			if( $terms = get_terms( $taxonomy, array( 'fields' => 'id=>name', 'hide_empty' => false ) ) )
			{
				$fields['tax_query_' . $taxonomy] = array(
					'option_name' 	=> 'tax_query_'. $taxonomy,
					'name' 			=> 'w4pl[tax_query_'. $taxonomy .']',
					'label' 		=> 'Post: ' . $label,
					'type' 			=> 'checkbox',
					'class' 		=> 'tax_query',
					'option' 		=> $terms
				);
			}
		}
		return $fields;
	}


	public function save_post( $post_ID, $post, $update )
	{
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
			return $post_ID ;

		if( !isset($_POST['w4pl']) )
			return;

		$options = stripslashes_deep($_POST['w4pl']);
		if( isset($options) )
		{
			update_post_meta( $post_ID, '_w4pl', $options );
		}
	}

	// default templates
	public function template_default($r){
		return '<ul>[loop]</ul>';
	}
	public function template_loop_default($r){
		return '<li>'. "\n".'[title]'. "\n".'[post_thumbnail]'. "\n".'[excerpt]'. "\n".'[more]'. "\n".'</li>';
	}



	public static function post_type_options()
	{
		global $wp_post_types;

		$return = array();
		foreach( $wp_post_types as $post_type => $post_type_object ){
			if(	!$post_type_object->public )
				continue;

			$return[$post_type] = $post_type_object->labels->name;
		}
	
		return $return;
	}

	public static function post_mime_type_options($post_type = 'post')
	{
		global $wpdb;
		$mime_types = $wpdb->get_col( $wpdb->prepare( 
			"SELECT DISTINCT post_mime_type FROM $wpdb->posts WHERE post_status != 'trash' AND post_type=%s AND post_mime_type <> ''", $post_type
		));

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

	public static function post_type_taxonomies_options( $post_type )
	{
		$return = array();
		foreach( get_object_taxonomies($post_type, 'all') as $taxonomy => $taxonomy_object ){
			if( $taxonomy == 'post_format' || !$taxonomy_object->public )
				continue;

			$return[$taxonomy] = $taxonomy_object->labels->name;
		}
		return $return;
	}

	public static function post_orderby_options( $post_type )
	{
		$return = array(
			'ID'				=> __( 'ID', 					W4PL_TXT_DOMAIN),
			'title'				=> __( 'Title', 				W4PL_TXT_DOMAIN),
			'name'				=> __( 'Name', 					W4PL_TXT_DOMAIN),
			'date'				=> __( 'Publish Date', 			W4PL_TXT_DOMAIN),
			'modified'			=> __( 'Modified Date', 		W4PL_TXT_DOMAIN),
			'menu_order'		=> __( 'Menu Order', 			W4PL_TXT_DOMAIN),
			'meta_value'		=> __( 'Meta value', 			W4PL_TXT_DOMAIN),
			'meta_value_num'	=> __( 'Meta numeric value', 	W4PL_TXT_DOMAIN),
			'rand'				=> __( 'Random', 				W4PL_TXT_DOMAIN),
		);
		if( post_type_supports($post_type, 'comments') )
			$return['comment_count'] = __( 'Comment Count',W4PL_TXT_DOMAIN);

		return $return;
	}

	public static function plugin_action_links( $links )
	{
		$readme_link['doc'] = '<a href="'. 'edit.php?post_type='. W4PL_SLUG . '-docs">' . __( 'Docs', W4PL_TXT_DOMAIN ). '</a>';
		return array_merge( $links, $readme_link );
	}

	public static function news_meta_box()
	{
		self::plugin_news();
	}

	/* Retrive latest updates about Post List plugin */
	public static function plugin_news( $echo = true, $refresh = false )
	{
		$transient = 'w4pl_plugin_news';
		$transient_old = $transient . '_old';
		$expiration = 7200;
	
		$output = get_transient( $transient );

		if( $refresh || !$output || empty( $output ))
		{
			$request = wp_remote_request('http://w4dev.com/wp-admin/admin-ajax.php?action=w4_ajax&action_call=plugin_news');
			$content = wp_remote_retrieve_body($request);

			if( is_wp_error( $content ) ){
				$output = get_option( $transient_old );
			}
			else{
				$output = $content;
				// Save last new forever if a newer is not available..
				update_option( $transient_old, $output );
			}

			set_transient( $transient, $output, $expiration );
		}

		$output = preg_replace( '/[\n]/', '<br />', $output );

		if( !$echo )
			return $output;
		else
			echo $output;
	}
}

	new W4PL_Admin;




?>