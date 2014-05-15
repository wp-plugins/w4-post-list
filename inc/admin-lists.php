<?php
class W4PL_Lists_Admin 
{
	function __construct()
	{
		add_action( 'add_meta_boxes_'. W4PL_SLUG, 					array($this, 'add_meta_boxes') );
		add_action( 'save_post_'. W4PL_SLUG,  						array($this, 'save_post'), 10, 3 );

		add_action( 'wp_ajax_w4pl_get_post_type_fields', 			array($this, 'get_post_type_fields_ajax') );

		add_filter( 'w4pl/template_default',  						array($this, 'template_default') );


		// set update message for our post type, you dont like to use - "post update" !
		add_filter( 'post_updated_messages', 						array($this, 'post_updated_messages'));

		// additional column
		add_filter( 'manage_'. W4PL_SLUG .'_posts_columns', 		array($this, 'manage_posts_columns') );
		add_action( 'manage_'. W4PL_SLUG .'_posts_custom_column', 	array($this, 'manage_posts_custom_column'), 10, 2 );

		// add lists link to plugin links, so one can navigate quickly
		add_filter( 'plugin_action_links_' . W4PL_BASENAME, 		array($this, 'plugin_action_links') );
	}

	// Meta box
	public function add_meta_boxes( $post )
	{
		// add configuration box right after post title, out of metabox
		add_action( 'edit_form_after_title', array($this, 'list_options_meta_box') );

		// add plugin news metabox one right side
		add_meta_box( "w4pl_news_meta_box", "Plugin Updates", array($this, 'news_meta_box'), W4PL_SLUG, "side", 'core');

		// enqueue script files, print css on header and print js on footer
		add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts') );
		add_action('admin_head', array($this, 'admin_head') );
		add_action('admin_print_footer_scripts', array($this, 'admin_print_footer_scripts') );
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
/*W4 Post List Admin*/
#w4pl_template_before, #w4pl_template_after, #w4pl_template{width:99%;height:50px;}
#w4pl_template{height:250px;}
#minor-publishing-actions, #misc-publishing-actions{display:none;}
#shortcode_hint_toggle{position:relative;margin:10px 0;float:left;clear:left;}
.tax_query .wffew{max-height:100px;overflow:hidden;overflow-y:auto;}
.wffw{margin:15px 0;padding-top:15px;padding-bottom:15px;border-width: 0 0 0 5px;box-shadow:0 0 1px #AAAAAA;box-sizing: border-box;-moz-box-sizing: border-box;-webkit-box-sizing: border-box;overflow:hidden;}
.wfflw, .wffdw {width:200px;float:left;clear:left;}
.wffew {margin-left:220px;}
.wffl{font-size:13px;}
#w4pl_post_type_options{position:relative;}
#w4pl_post_type_options:after{ background:url(images/loading.gif) no-repeat; width:30px; height:30px; display:block;}
#w4pl_template_buttons a{ padding:4px 8px; display:inline-block; border:1px solid #DDD; background-color:#EEE; line-height:12px; font-size:12px; margin:0 2px 2px 0; text-decoration:none; border-radius: 3px; -moz-border-radius:3px; -webkit-border-radius:3px;}
.w4pl_button_group{ padding:0 0 10px;}
.w4pl_button_group_title{ display:block;}
<?php do_action( 'w4pl/admin_print_css' ); ?>
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
			$('.wffwi_w4pl_post_type .spinner').css('display', 'inline-block');
			$('#publish').hide();

			$.post(ajaxurl, 'action=w4pl_get_post_type_fields&post_id='+ $('#post_ID').val() +'&post_type='+ $(this).val(), function(r){
				$('#w4pl_post_type_options').html(r);
				$('.wffwi_w4pl_post_type .spinner').css('display', 'none');
				$('#publish').show();
				
				return false;
			})
		});

		$('#w4pl_template_buttons a').click(function(e){
			insertAtCaret( 'w4pl_template', $(this).data('code') );
			return false;
		});
	});

	<?php do_action( 'w4pl/admin_print_js' ); ?>

})(jQuery);

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
			'position'		=> '5',
			'before'		=> '<h2>Query</h2>',
			'option_name' 	=> 'post_type',
			'name' 			=> 'w4pl[post_type]',
			'label' 		=> 'Post Type',
			'type' 			=> 'select',
			'option' 		=> self::post_type_options(),
			'input_after'	=> '<span class="spinner" style="position:relative; float:none; left:10px; top:5px; margin: 0; height:19px;"></span>'
		);
		$fields['post_status'] = array(
			'position'		=> '10',
			'option_name' 	=> 'post_status',
			'name' 			=> 'w4pl[post_status]',
			'label' 		=> 'Post Status',
			'type' 			=> 'checkbox',
			'option' 		=> array('any' => 'Any', 'publish' => 'Publish', 'pending' => 'Pending', 'future' => 'Future', 'inherit' => 'Inherit')
		);
		$fields['before_post_type_options'] = array(
			'position'		=> '15',
			'type' 			=> 'html',
			'html' 			=> '<div id="w4pl_post_type_options">'
		);
		// intialize post type fields
		self::post_type_fields($fields, $post_data);
		$fields['after_post_type_options'] = array(
			'position'		=> '35',
			'type' 			=> 'html',
			'html' 			=> '</div><!--w4pl_post_type_options-->'
		);

		#echo '<pre>';	print_r($post_data);	echo '</pre>';


		$fields['post__in'] = array(
			'position'		=> '40',
			'option_name' 	=> 'post__in',
			'name' 			=> 'w4pl[post__in]',
			'label' 		=> 'Include post by ids',
			'type' 			=> 'text',
			'desc' 			=> 'comma separated post id'
		);
		$fields['post__not_in'] = array(
			'position'		=> '45',
			'option_name' 	=> 'post__not_in',
			'name' 			=> 'w4pl[post__not_in]',
			'label' 		=> 'Exclude post by ids',
			'type' 			=> 'text',
			'desc' 			=> 'comma separated post id'
		);
		$fields['post_parent__in'] = array(
			'position'		=> '50',
			'option_name' 	=> 'post_parent__in',
			'name' 			=> 'w4pl[post_parent__in]',
			'label' 		=> 'Post parent ids',
			'type' 			=> 'text',
			'desc' 			=> 'comma separated post parent id'
		);
		$fields['author__in'] = array(
			'position'		=> '55',
			'option_name' 	=> 'author__in',
			'name' 			=> 'w4pl[author__in]',
			'label' 		=> 'Post author ids',
			'type' 			=> 'text',
			'desc' 			=> 'comma separated author id'
		);




		$fields['orderby'] = array(
			'position'		=> '65',
			'before'		=> '<h2>Order</h2>',
			'option_name' 	=> 'orderby',
			'name' 			=> 'w4pl[orderby]',
			'label' 		=> 'Orderby',
			'type' 			=> 'select',
			'option' 		=> self::post_orderby_options($post_data['post_type']),
			'input_after'	=> '<div id="orderby_meta_key_wrap">Meta key: <input name="w4pl[orderby_meta_key]" type="text" value="'
				. (isset($post_data['orderby_meta_key']) ? esc_attr($post_data['orderby_meta_key']) : '') .'" /></div>'
		);
		$fields['order'] = array(
			'position'		=> '70',
			'option_name' 	=> 'order',
			'name' 			=> 'w4pl[order]',
			'label' 		=> 'Order',
			'type' 			=> 'radio',
			'option' 		=> array('ASC' => 'ASC', 'DESC' => 'DESC')
		);


		$fields['posts_per_page'] = array(
			'position'		=> '75',
			'before'		=> '<h2>Limit</h2>',
			'option_name' 	=> 'posts_per_page',
			'name' 			=> 'w4pl[posts_per_page]',
			'label' 		=> 'Items per page',
			'type' 			=> 'text',
			'desc' 			=> 'number of items to show per page'
		);
		$fields['limit'] = array(
			'position'		=> '80',
			'option_name' 	=> 'limit',
			'name' 			=> 'w4pl[limit]',
			'label' 		=> 'Maximum items to display',
			'type' 			=> 'text',
			'desc' 			=> 'maximum results to display in total'
		);
		$fields['offset'] = array(
			'position'		=> '95',
			'option_name' 	=> 'offset',
			'name' 			=> 'w4pl[offset]',
			'label' 		=> 'Offset',
			'type' 			=> 'text',
			'desc' 			=> 'skip given number of posts from beginning'
		);


		$fields['template'] = array(
			'position'		=> '100',
			'before'		=> '<h2>Template</h2>',
			'option_name' 	=> 'template',
			'name' 			=> 'w4pl[template]',
			'label' 		=> '',
			'type' 			=> 'textarea',
			'input_class' 	=> 'widefat',
			'default' 		=> apply_filters('w4pl/template_default', ''),
			'desc' 			=> 'top level shortcodes are [nav], [groups][/groups], [posts][/posts]. while using group by option, posts should be nested in groups tag. example:'
			. "<pre style='width:auto'>
[groups]
  [group_title]
  [posts]
    [post_title]
  [/posts]
[/groups]
[nav]
</pre>"
			. '<br />without group, a simple template should be like -'
			. "<pre style='width:auto'>
[posts]
  [post_title]
[/posts]
[nav]
</pre>"
		);


		$shortcodes = W4PL_Core::get_shortcodes();
		$shortcode_groups = array();
		foreach( $shortcodes as $shortcode => $attr ){
			$group = $attr['group'];
			if( !isset($shortcode_groups[$group]) || !is_array($shortcode_groups[$group]) )
				$shortcode_groups[$group] = array();

			#if( ! in_array($attr['group'], $shortcode_groups) )
			$shortcode_groups[$group][] = $shortcode;
		}

		#print_r($shortcode_groups);


		$input_before = '<div id="w4pl_template_buttons">';
		foreach( $shortcode_groups as $shortcode_group => $scodes ){
			$input_before .= sprintf(' <div class="w4pl_button_group"><span class="w4pl_button_group_title">%1$s</span>', $shortcode_group );
			foreach( $scodes as $shortcode ){
				$attr = $shortcodes[$shortcode];
				if( isset($attr['code']) )
					$code = $attr['code'];
				else
					$code = '['. $shortcode . ']';
				$input_before .= sprintf(' <a href="#%1$s" data-code="%2$s">%1$s</a>', $shortcode, esc_attr($code) );
			}
			$input_before .= '</div>';
		}
		$input_before .= '</div>';


		$fields['template']['input_before'] = $input_before;
		$fields['template']['input_wrap_before'] = self::get_shortcode_hint_html();


		/* Migration procedure */
		if( isset($post_data['template_loop']) && !empty($post_data['template_loop']) )
		{
			$post_data['template'] = str_replace( '[loop]', '[posts]'. $post_data['template_loop'] .'[/posts]', $post_data['template'] );
			unset($post_data['template_loop']);
		}

		if( isset($post_data['template']) && ! preg_match('/\[posts\](.*?)\[\/posts\]/sm', $post_data['template']) && preg_match('/\[loop\]/sm', $post_data['template'], $match ) )
		{
			$post_data['template'] = str_replace( $match[0], '[posts]'. $post_data['template_loop'] .'[/posts]', $post_data['template'] );
		}



		# echo '<pre>'; print_r($fields); echo '</pre>';


		$form_args = array(
			'no_form' 		=> true,
			'button_after' 	=> false
		);


		// let helper class extend/modify this class
		$fields = apply_filters( 'w4pl/admin_list_fields', $fields, $post_data, $form_args );


		// order by position
		uasort( $fields, array($this, 'order_by_position') );


		echo w4pl_form_fields( $fields, $post_data, $form_args );
	}

	public function order_by_position( $a, $b )
	{
		if( !isset($a['position']) || !isset($b['position']) )
			return -1;

		if( $a['position'] == $b['position'] )
	        return 0;

	    return ($a['position'] < $b['position']) ? -1 : 1;
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
		self::post_type_fields( $fields, $post_data );

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
				'position' 		=> '20',
				'option_name' 	=> 'post_mime_type',
				'name' 			=> 'w4pl[post_mime_type]',
				'label' 		=> 'Post Mime Type',
				'type' 			=> 'checkbox',
				'option' 		=> $mime_type_options,
				'desc' 			=> 'if displaying attachment, choose mime type to restrcit result to specific file types.'
			);
		}

		$fields['groupby'] = array(
			'position' 		=> '25',
			'option_name' 	=> 'groupby',
			'name' 			=> 'w4pl[groupby]',
			'label' 		=> 'Group By',
			'type' 			=> 'select',
			'option' 		=> self::post_groupby_options($post_type)
		);
		$fields['group_order'] = array(
			'position' 		=> '26',
			'option_name' 	=> 'group_order',
			'name' 			=> 'w4pl[group_order]',
			'label' 		=> 'Group Order',
			'type' 			=> 'radio',
			'option' 		=> array('' => 'None', 'ASC' => 'ASC', 'DESC' => 'DESC')
		);


		// post format fields
		if ( post_type_supports( $post_type, 'post-formats' ) )
		{
			#global $_wp_post_type_features;
			#$formats = $_wp_post_type_features[$post_type]['post-formats'];

			#print_r( $formats );
			#die();

			if( $formats = get_post_format_strings() )
			{
				// TODO - push to top
				$new_formats = array('' => __('Any') );
				foreach( $formats as $k => $v ){
					if( 'standard' != $k )
					{
						$new_formats["post-format-$k"] = $v;
					}
				}

				$fields['post_format'] = array(
					'position' 		=> '32',
					'option_name' 	=> 'post_format',
					'name' 			=> 'w4pl[post_format]',
					'label' 		=> 'Post Format',
					'type' 			=> 'select',
					'option' 		=> $new_formats
				);
			}
		}


		$index = 0;
		foreach( self::post_type_taxonomies_options($post_type) as $taxonomy => $label )
		{
			if( $terms = get_terms( $taxonomy, array( 'fields' => 'id=>name', 'hide_empty' => false ) ) )
			{
				$fields['tax_query_' . $taxonomy] = array(
					'position' 		=> '34.'. $index,
					'option_name' 	=> 'tax_query_'. $taxonomy,
					'name' 			=> 'w4pl[tax_query_'. $taxonomy .']',
					'label' 		=> 'Taxonomy - ' . $label,
					'type' 			=> 'checkbox',
					'class' 		=> 'tax_query',
					'option' 		=> $terms
				);
				++$index;
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
		#die( print_r($_POST) );

		if( isset($options) )
		{
			update_post_meta( $post_ID, '_w4pl', $options );
		}
	}

	// default templates
	public function template_default($r){
		return '<ul>[posts]'. "\n" . '<li>'. "\n" . '[title]'. "\n" . '[post_thumbnail]'. "\n" . '[excerpt]' . "\n" . '[more]' . "\n".'</li>'. "\n" . '[/posts]</ul>';
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


	public static function post_groupby_options( $post_type )
	{
		$return = array(
			'' 			=> 'None',
			'year' 		=> 'Year',
			'month' 	=> 'Month',
			'yearmonth' => 'Year Months',
			'author' 	=> 'Author',
			'parent' 	=> 'Parent'
		);
		foreach( get_object_taxonomies($post_type, 'all') as $taxonomy => $taxonomy_object ){
			if( $taxonomy == 'post_format' || !$taxonomy_object->public )
				continue;
			$return['tax_'. $taxonomy] = $taxonomy_object->labels->name;
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

	public function get_shortcode_hint_html()
	{
		$shortcodes = W4PL_Core::get_shortcodes();
		$return = '<a id="shortcode_hint_toggle" class="button">shortcodes details</a>';
		$return .= '<table id="shortcode_hint" class="widefat" style="display:none;">';
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


	public function post_updated_messages( $messages )
	{
		global $post_ID, $post;

		$input_attr = 'type="text" size="9" onfocus="this.select();" onclick="this.select();" readonly="readonly"';

		$messages[W4PL_SLUG] = array(
			 1 => sprintf( __('List updated. Use Shortcode <input value="[postlist %1$d]" %2$s />'), $post_ID, $input_attr ),
			 2 => '',
			 3 => '',
			 4 => __('List updated.'),
			 5 => '',
			 6 => sprintf( __('List published. Use Shortcode <input value="[postlist %1$d]" %2$s />'), $post_ID, $input_attr ),
			 7 => __('List saved.'),
			 8 => sprintf( __('List submitted. Use Shortcode <input value="[postlist %1$d]" %2$s />'), $post_ID, $input_attr ),
			 9 => sprintf( __('List scheduled. Use Shortcode <input value="[postlist %1$d]" %2$s />'), $post_ID, $input_attr ),
			10 => ''
		);
		return $messages;
	}

	public function manage_posts_columns( $columns )
	{
		$date = false;
		if( isset($columns['date']) ){
			$date = $columns['date'];
			unset($columns['date']);
		}
		$columns['shortcode'] = __('Shortcode');

		if( $date ){
			$columns['date'] = $date;
		}

		return $columns;
	}

	public function manage_posts_custom_column( $column_name, $post_ID )
	{
		if( 'shortcode' == $column_name ){
			printf( '<input value="[postlist %d]" type="text" size="9" onfocus="this.select();" onclick="this.select();" readonly="readonly" />', $post_ID );
		}
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
			else
			{
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

	new W4PL_Lists_Admin;
?>