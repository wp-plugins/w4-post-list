<?php
/**
 * @package W4 Post List
 * @author Shazzad Hossain Khan
 * @url http://w4dev.com/w4-plugin/w4-post-list
**/


class W4PL_Core 
{
	function __construct()
	{
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


		// filter list before getting them
		add_filter( 'w4pl/pre_get_options', 					array($this, 'pre_get_options'), 5 );


		// load list options template from posted data.
		add_action( 'wp_ajax_w4pl_list_options_template', 		array($this, 'list_options_template_ajax') );


		add_action( 'w4pl/list_options_template_html', 			array($this, 'list_options_template_html'), 5, 3 );


		// display list creation option page scripts, scripts get loaded on the head tag of that page.
		add_action( 'w4pl/list_options_print_scripts', 			array($this, 'list_options_print_scripts') );


		// get shortcode from posted data
		add_action( 'wp_ajax_w4pl_generate_shortcodes', 		array($this, 'w4pl_generate_shortcodes_ajax') );
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
				'all_items'				=> __('All Lists', W4PL_TXT_DOMAIN),
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
	 * Shortcodes
	*/

	public static function get_shortcodes()
	{
		// Shortcodes
		return array(
			'posts' => array(
				'group' => 'Main', 
				'code' => '[posts]'. "\n\n" .'[/posts]', 
				'func' => '',
				'desc' => '<strong>return</strong> the posts template'
			),
			'groups' => array(
				'group' => 'Main', 
				'code' => '[groups]'. "\n\n" .'[/groups]', 
				'func' => '',
				'desc' => '<strong>return</strong> the groups template'
			),
			'nav' => array(
				'group' => 'Main', 
				'code' => '[nav ajax=""]', 
				'func' => '',
				'desc' => '<strong>return</strong> pagination for the list
            <br><br><strong>Attributes</strong>:
            <br><strong>type</strong> = (text) allowed values  - plain, list, nav
            <br><strong>ajax</strong> = (0|1) use pagination with ajax'
			),
			'id' => array(
				'group' => 'Post', 
				'func' => 'post_id', 
				'desc' => '<strong>Output</strong>: post id'
			),
			'ID' => array(
				'group' => 'Post', 
				'func' => 'post_id', 
				'desc' => '<strong>Output</strong>: post id'
			),
			'post_id' => array(
				'group' => 'Post', 
				'func' => 'post_id', 
				'desc' => '<strong>Output</strong>: post id'
			),
			'post_number' => array(
				'group' => 'Post', 
				'func' => 'post_number', 
				'desc' => '<strong>Output</strong>: post item number, starting from 1'
			),
			'post_permalink' => array(
				'group' => 'Post', 
				'func' => 'post_permalink', 
				'desc' => '<strong>Output</strong>: post url/link'
			),
			'post_class' => array(
				'group' => 'Post', 
				'func' => 'post_class', 
				'desc' => '<strong>Output</strong>: post html classes'
			),
			'post_title' => array(
				'group' => 'Post', 
				'func' => 'post_title', 
				'desc' => '<strong>Output</strong>: post title
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>wordlimit</strong> = (number), limit number of words to display'
			),
			'post_comment_url' => array(
				'group' => 'Post', 
				'func' => 'post_comment_url', 
				'desc' => '<strong>Output</strong>: post comment form link/url'
			),
			'post_comment_count'=> array(
				'group' => 'Post', 
				'func' => 'post_comment_count', 
				'desc' => '<strong>Output</strong>: (numeric) amount of approved comments'
			),
			'post_date' => array(
				'group' => 'Post', 
				'code' => '[post_date format="'. get_option('date_format') .'"]', 
				'func' => 'post_date', 
				'desc' => '<strong>Output</strong>: post date (date formatted)
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>format</strong> = php datetime format'
			),
			'post_time' => array(
				'group' => 'Post', 
				'code' => '[post_time format="'. get_option('time_format') .'"]', 
				'func' => 'post_time', 
				'desc' => '<strong>Output</strong>: post date (time formatted)
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>format</strong> = php datetime format'
			),
			'post_modified_date' => array(
				'group' => 'Post', 
				'code' => '[post_modified_date format="'. get_option('date_format') .'"]', 
				'func' => 'post_modified_date', 
				'desc' => '<strong>Output</strong>: post modified date (date formatted)
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>format</strong> = php datetime format'
			),
			'post_modified_time'=> array(
				'group' => 'Post', 
				'code' => '[post_modified_time format="'. get_option('time_format') .'"]', 
				'func' => 'post_modified_time', 
				'desc' => '<strong>Output</strong>: post modified date (time formatted)
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>format</strong> = php datetime format'
			),
			'post_author_name' => array(
				'group' => 'Post', 
				'func' => 'post_author_name', 
				'desc' => '<strong>Output</strong>: post author name'
			),
			'post_author_url'	=> array(
				'group' => 'Post', 
				'func' => 'post_author_url', 
				'desc' => '<strong>Output</strong>: post author name url'
			),
			'post_author_email'	=> array(
				'group' => 'Post', 
				'func' => 'post_author_email', 
				'desc' => '<strong>Output</strong>: post author email address'
			),
			'post_author_avatar'=> array(
				'group' => 'Post', 
				'code' => '[post_author_avatar size=""]', 
				'func' => 'post_author_avatar', 
				'desc' => '<strong>Output</strong>: post author avatar
				<br /><br /><strong>attributes:</strong>
				<br /><strong>size</strong> = (number), avatar image size'
			),
			'post_excerpt' => array(
				'group' => 'Post', 
				'code' => '[post_excerpt wordlimit=""]', 
				'func' => 'post_excerpt', 
				'desc' => '<strong>Output</strong>: post excerpt/short description
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>wordlimit</strong> = (number), limit number of words to display'
			),
			'post_content'		=> array(
				'group' => 'Post', 
				'func' => 'post_content', 
				'desc' => '<strong>Output</strong>: post content'
			),
			'post_thumbnail' => array(
				'group' => 'Post', 
				'code' => '[post_thumbnail size="" return=""]', 
				'func' => 'post_thumbnail', 
				'desc' => '<strong>Output</strong>: (text|number) based on the rerurn attribute & only if the post has a thumbnail assigned
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>return</strong> = (text|number), 
				<br />----"src" - will return src of the image, 
				<br />----"id" - will return id of the image, 
				<br />----by default it will return image html
				<br /><strong>size</strong> = (string), post_thumbnail size

				<br /><strong>width</strong> = (number), post_thumbnail width
				<br /><strong>height</strong> = (number), post_thumbnail height'
			),
			'post_meta' => array(
				'group' => 'Post', 
				'code' => '[post_meta key="" multiple="0"]', 
				'func' => 'post_meta', 
				'desc' => '<strong>Output</strong>: post meta value. if return value is an array, it will be migrated to string by using explode function
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>key</strong> = (text|number), meta key name
				<br /><strong>multiple</strong> = (0|1), display meta value at multiple occurence
				<br /><strong>sep</strong> = (text), separate array meta value into string'
			),
			'post_terms' => array(
				'group' => 'Post', 
				'code' => '[post_terms tax="category" sep=", "]', 
				'func' => 'post_terms', 
				'desc' => '<strong>Output</strong>: post type terms. if return value is an array, it will be migrated to string by using explode function
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>tax</strong> = (string), taxonomy name
				<br /><strong>sep</strong> = (string), separate array meta value into string'
			),
			'attachment_thumbnail' => array(
				'group' => 'Media', 
				'code' => '[attachment_thumbnail size=""]', 
				'func' => 'attachment_thumbnail', 
				'desc' => '<strong>Output</strong>: if the post is an attachment, the attached image is displayed as thumbnail
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>size</strong> = (string), image size
				<br /><strong>width</strong> = (number), image width
				<br /><strong>height</strong> = (number), image height'
			),
			'attachment_url' => array(
				'group' => 'Media', 
				'func' => 'attachment_url', 
				'desc' => '<strong>Output</strong>:  if the post is an attachment, the attached image source is returned'
			),
			'group_title' => array(
				'group' => 'Group', 
				'func' => '', 
				'desc' => '<strong>Output</strong>: group name / title'
			),
			'group_url' => array(
				'group' => 'Group', 
				'func' => '', 
				'desc' => '<strong>Output</strong>: group page link'
			),

			'title' => array(
				'group' => 'Standard', 
				'func' => 'template_title', 
				'desc' => '<strong>Output</strong>: title template'
			),
			'meta' => array(
				'group' => 'Standard', 
				'func' => 'template_meta', 
				'desc' => '<strong>Output</strong>: meta template'
			),
			'publish' => array(
				'group' => 'Standard', 
				'func' => 'template_date', 
				'desc' => '<strong>Output</strong>: publish time template'
			),
			'date'				=> array(
				'group' => 'Standard', 
				'func' => 'template_date', 
				'desc' => '<strong>Output</strong>: publish time template'
			),
			'modified' => array(
				'group' => 'Standard', 
				'func' => 'template_modified', 
				'desc' => '<strong>Output</strong>: modified time template'
			),
			'author' => array(
				'group' => 'Standard', 
				'func' => 'template_author', 
				'desc' => '<strong>Output</strong>: author template'
			),
			'excerpt' => array(
				'group' => 'Standard', 
				'func' => 'template_excerpt', 
				'desc' => '<strong>Output</strong>: excerpt template'
			),
			'content' => array(
				'group' => 'Standard', 
				'func' => 'template_content', 
				'desc' => '<strong>Output</strong>: content template'
			),
			'more' => array(
				'group' => 'Standard', 
				'func' => 'template_more',
				'desc' => '<strong>Output</strong>: more link template'
			)
		);
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

		#echo '<pre>'; print_r($options); echo '</pre>';
		#return '';
		$options = apply_filters( 'w4pl/pre_get_options', $options );
		#echo '<pre>'; print_r($options); echo '</pre>';
		
		
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
					<br /><small>*** this error is only visible to admins and won\'t effect in search engine.</small>
				</p>';
			}
			return '<!-- W4 post list Error: '. $list->get_error_message() .'-->';
		}
		return "<!-- Post list Created by W4 post list WordPress Plugin @ http://w4dev.com/w4-plugin/w4-post-list -->\n" . $w4_post_list->display();
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

		/* GROUP 1 */
		$fields['before_field_group_query'] = array(
			'position'		=> '5',
			'html' 			=> '<div id="w4pl_field_group_query" class="w4pl_field_group">
								<div class="w4pl_group_title">Post Query</div>
								<div class="w4pl_group_fields">'
		);

		$fields['post_type'] = array(
			'position'		=> '6',
			'option_name' 	=> 'post_type',
			'name' 			=> 'w4pl[post_type]',
			'label' 		=> 'Post Type',
			'type' 			=> 'select',
			'option' 		=> self::post_type_options(),
			'input_after'	=> '<span class="spinner" style="position:relative; float:none; left:10px; top:5px; margin: 0; height:19px;"></span>'
		);

		// mime type field
		if( $mime_type_options = self::post_mime_type_options($options['post_type']) )
		{
			$fields['post_mime_type'] = array(
				'position' 		=> '8',
				'option_name' 	=> 'post_mime_type',
				'name' 			=> 'w4pl[post_mime_type]',
				'label' 		=> 'Post Mime Type',
				'type' 			=> 'checkbox',
				'option' 		=> $mime_type_options,
				'desc' 			=> 'if displaying attachment, choose mime type to restrcit result to specific file types.'
			);
		}

		if( 'attachment' != $options['post_type'] )
		{
			$fields['post_status'] = array(
				'position'		=> '10',
				'option_name' 	=> 'post_status',
				'name' 			=> 'w4pl[post_status]',
				'label' 		=> 'Post Status',
				'type' 			=> 'checkbox',
				'option' 		=> array('any' => 'Any', 'publish' => 'Publish', 'pending' => 'Pending', 'future' => 'Future', 'inherit' => 'Inherit')
			);
		}

		$fields['orderby'] = array(
			'position'		=> '12',
			'option_name' 	=> 'orderby',
			'name' 			=> 'w4pl[orderby]',
			'label' 		=> 'Orderby',
			'type' 			=> 'select',
			'option' 		=> self::post_orderby_options($options['post_type']),
			'input_after'	=> '<div id="orderby_meta_key_wrap">Meta key: <input name="w4pl[orderby_meta_key]" type="text" value="'
				. (isset($options['orderby_meta_key']) ? esc_attr($options['orderby_meta_key']) : '') .'" /></div>'
		);
		$fields['order'] = array(
			'position'		=> '14',
			'option_name' 	=> 'order',
			'name' 			=> 'w4pl[order]',
			'label' 		=> 'Order',
			'type' 			=> 'radio',
			'option' 		=> array('ASC' => 'ASC', 'DESC' => 'DESC')
		);

		$fields['post__in'] = array(
			'position'		=> '15',
			'option_name' 	=> 'post__in',
			'name' 			=> 'w4pl[post__in]',
			'label' 		=> 'Include posts',
			'type' 			=> 'text',
			'input_class' 	=> 'widefat',
			'desc' 			=> 'comma separated post id'
		);
		$fields['post__not_in'] = array(
			'position'		=> '16',
			'option_name' 	=> 'post__not_in',
			'name' 			=> 'w4pl[post__not_in]',
			'label' 		=> 'Exclude posts',
			'type' 			=> 'text',
			'input_class' 	=> 'widefat',
			'desc' 			=> 'comma separated post id'
		);
		$fields['post_parent__in'] = array(
			'position'		=> '20',
			'option_name' 	=> 'post_parent__in',
			'name' 			=> 'w4pl[post_parent__in]',
			'label' 		=> 'Post parent',
			'type' 			=> 'text',
			'input_class' 	=> 'widefat',
			'desc' 			=> 'comma separated parent post ids'
		);
		$fields['author__in'] = array(
			'position'		=> '25',
			'option_name' 	=> 'author__in',
			'name' 			=> 'w4pl[author__in]',
			'label' 		=> 'Post author',
			'type' 			=> 'text',
			'input_class' 	=> 'widefat',
			'desc' 			=> 'comma separated user/author ids'
		);

		$fields['after_field_group_query'] = array(
			'position'		=> '50',
			'html' 			=> '</div><!--.w4pl_group_fields--></div><!--#w4pl_field_group_query-->'
		);


		/* GROUP 2 */
		/* GROUP 3 */
		/* GROUP 4 */


		/* GROUP 5 */
		$fields['before_field_group_limits'] = array(
			'position'		=> '75',
			'html' 			=> '<div id="w4pl_field_group_limits" class="w4pl_field_group"><div class="w4pl_group_title">Limit</div><div class="w4pl_group_fields">'
		);
		$fields['posts_per_page'] = array(
			'position'		=> '80',
			'option_name' 	=> 'posts_per_page',
			'name' 			=> 'w4pl[posts_per_page]',
			'label' 		=> 'Items per page',
			'type' 			=> 'text',
			'desc' 			=> 'number of items to show per page'
		);
		$fields['limit'] = array(
			'position'		=> '85',
			'option_name' 	=> 'limit',
			'name' 			=> 'w4pl[limit]',
			'label' 		=> 'Maximum items to display',
			'type' 			=> 'text',
			'desc' 			=> 'maximum results to display in total'
		);
		$fields['offset'] = array(
			'position'		=> '90',
			'option_name' 	=> 'offset',
			'name' 			=> 'w4pl[offset]',
			'label' 		=> 'Offset',
			'type' 			=> 'text',
			'desc' 			=> 'skip given number of posts from beginning'
		);
		$fields['after_field_group_limits'] = array(
			'position'		=> '95',
			'html' 			=> '</div><!--.w4pl_group_fields--></div><!--#w4pl_field_group_limits-->'
		);


		/* GROUP 5 */
		$fields['before_field_group_groupby'] = array(
			'position'		=> '100',
			'html' 			=> '<div id="w4pl_field_group_groupby" class="w4pl_field_group"><div class="w4pl_group_title">Group</div><div class="w4pl_group_fields">'
		);
		$fields['groupby'] = array(
			'position' 		=> '105',
			'option_name' 	=> 'groupby',
			'name' 			=> 'w4pl[groupby]',
			'label' 		=> 'Group By',
			'type' 			=> 'select',
			'option' 		=> self::post_groupby_options($options['post_type'])
		);
		$fields['group_order'] = array(
			'position' 		=> '106',
			'option_name' 	=> 'group_order',
			'name' 			=> 'w4pl[group_order]',
			'label' 		=> 'Group Order',
			'type' 			=> 'radio',
			'option' 		=> array('' => 'None', 'ASC' => 'ASC', 'DESC' => 'DESC')
		);
		$fields['after_field_group_groupby'] = array(
			'position'		=> '110',
			'html' 			=> '</div><!--.w4pl_group_fields--></div><!--#w4pl_field_group_groupby-->'
		);


		/* GROUP 6 */
		$fields['before_field_group_template'] = array(
			'position'		=> '150',
			'html' 			=> '<div id="w4pl_field_group_template" class="w4pl_field_group"><div class="w4pl_group_title">Template</div><div class="w4pl_group_fields">'
		);

		$template_html = '<div class="wffw wffwi_w4pl_template wffwt_textarea">';

		$template_html .= '<p style="margin-top:0px;">
			<a href="#" class="button w4pl_toggler" data-target="#w4pl_template_examples">Template Example</a>
			<a href="#" class="button w4pl_toggler" data-target="#w4pl_template_buttons">Shortcodes</a>
		</p>';

		$template_html .= '<div id="w4pl_template_examples" class="csshide">'
		. "<pre style='width:auto'>\n[groups]\n\t[group_title]\n\t[posts]\n\t\t[post_title]\n\t[/posts]\n[/groups]\n[nav]</pre>"
		. "<br />without group, a simple template should be like -"
		. "<pre style='width:auto'>[posts]\n\t[post_title]\n[/posts]\n[nav]</pre>"
		. '</div>';


		$shortcodes = self::get_shortcodes();
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


		$template_html .= '<div class="wfflw wfflwi_w4pl_template wfflwt_textarea"><label for="w4pl_template" class="wffl wffli_w4pl_template wfflt_textarea">Template</label></div>';
		$template_html .= w4pl_form_child_field_html( array(
			'id' 			=> 'w4pl_template',
			'name' 			=> 'w4pl[template]',
			'input_class' 	=> 'wff wffi_w4pl_template wfft_textarea widefat',
			'type' 			=> 'textarea',
			'default' 		=> apply_filters('w4pl/template_default', ''),
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


		/* Migration procedure */
		if( isset($options['template_loop']) && !empty($options['template_loop']) )
		{
			$options['template'] = str_replace( '[loop]', '[posts]'. $options['template_loop'] .'[/posts]', $options['template'] );
			unset($options['template_loop']);
		}

		if( isset($options['template']) && ! preg_match('/\[posts\](.*?)\[\/posts\]/sm', $options['template']) && preg_match('/\[loop\]/sm', $options['template'], $match ) )
		{
			$options['template'] = str_replace( $match[0], '[posts]'. $options['template_loop'] .'[/posts]', $options['template'] );
		}

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

	public function pre_save_options($options)
	{
		foreach( array(
			'tab_id' 			=> 'w4pl_field_group_query', 
			'post_type' 		=> 'post', 
			'post_status' 		=> array('publish'), 
			'post__in' 			=> '', 
			'post__not_in' 		=> '', 
			'post_parent__in' 	=> '',
			'author__in' 		=> '',
			'posts_per_page'	=> '',
			'limit'				=> '',
			'offset'			=> '',
			'groupby'			=> '',
			'orderby'			=> 'date',
			'order'				=> 'DESC',
			'group_order'		=> ''
		) as $k => $v )
		{
			if( empty($v) && empty($options[$k]) )
				unset($options[$k]);
			elseif( array_key_exists($k, $options) && $v == $options[$k] )
				unset($options[$k]);
		}

		if( 'attachment' == $options['post_type'] ){
			unset( $options['post_status'] );
		}

		return $options;
	}


	public function pre_get_options($options)
	{
		if( !isset($options) || !is_array($options) )
			$options = array();

		if( isset($options['template_loop']) && !empty($options['template_loop']) ){
			if( isset($options['template']) 
				&& ! preg_match('/\[posts\](.*?)\[\/posts\]/sm', $options['template']) 
				&& preg_match('/\[loop\]/sm', $options['template'], $match ) 
			){
				$options['template'] = str_replace( $match[0], '[posts]'. $options['template_loop'] .'[/posts]', $options['template'] );
				unset($options['template_loop']);
			}
		}

		$options = wp_parse_args( $options, array(
			'id' 				=> md5( microtime() . rand() ), 
			'tab_id' 			=> 'w4pl_field_group_query', 
			'post_type' 		=> 'post', 
			'post_status' 		=> array('publish'), 
			'post__in' 			=> '', 
			'post__not_in' 		=> '', 
			'post_parent__in' 	=> '',
			'author__in' 		=> '',
			'posts_per_page'	=> '',
			'limit'				=> '',
			'offset'			=> '',
			'groupby'			=> '',
			'orderby'			=> 'date',
			'order'				=> 'DESC',
			'group_order'		=> ''
		));

		if( 'attachment' == $options['post_type'] ){
			$options['post_status'] = array('inherit');
		}

		return $options;
	}

	public function pre_get_list($list)
	{
		return $list;
	}


	/*
	 * Encoded Shortcode data
	**/

	public static function w4pl_generate_shortcodes_ajax()
	{
		$options = isset($_POST) ? stripslashes_deep($_POST) : array();
		if( isset($options['w4pl']) )
			$options = $options['w4pl'];

		if( empty($options) )
			die('');

		if( is_numeric($options['id']) && get_post($options['id']) )
		{
			$options = apply_filters( 'w4pl/pre_save_options', $options );
			update_post_meta( $options['id'], '_w4pl', $options );

			printf( '[postlist id="%d"]', $options['id']);
			die('');
		}

		// filter options, remove default values
		$options = apply_filters( 'w4pl/pre_save_options', $options );

		// encode options, split string by 100 characters to avoid 
		$encode = chunk_split( base64_encode( maybe_serialize($options) ), 100, ' ');

		printf( '[postlist options="%s"]', trim($encode) );
		die();
	}


	public static function register_scripts()
	{
		wp_register_style(  'w4pl_form', 				W4PL_URL . 'assets/form/form.css' );
		wp_register_script( 'w4pl_form', 				W4PL_URL . 'assets/form/form.js', array('jquery', 'jquery-ui-core') );

		wp_register_style(  'w4pl_jquery_ui_custom', 	W4PL_URL . 'assets/jquery/jquery-ui-1.9.2.custom.css' );
		wp_register_script( 'w4pl_colorpicker', 		W4PL_URL . 'assets/colorpicker/jscolor.js' );
		wp_register_script( 'w4pl_tinymce_popup', 		W4PL_URL . 'assets/tinymce/tinymce_popup.js' );
	}


	public static function list_options_print_scripts( $options )
	{
		$options = apply_filters( 'w4pl/pre_get_options', $options );

		$tab_id = isset($options['tab_id']) ? '#'. $options['tab_id'] : '.w4pl_field_group:first';

		wp_print_styles(  'w4pl_form' );
		wp_print_scripts( 'w4pl_form' );

		?>
		<style>
/*W4 Post List Admin*/
#w4pl_template{height:350px;}
#minor-publishing-actions, #misc-publishing-actions{display:none;}
#w4pl_template_examples{ font-size:12px; color:#999999;}
#shortcode_hint_toggle{position:relative;margin:10px 0;float:left;clear:left;}
.wffw{margin:0;padding-top:8px;padding-bottom:8px;border-width: 0 0 1px 5px;box-sizing: border-box;-moz-box-sizing: border-box;-webkit-box-sizing: border-box;overflow:hidden;}
.wfflw, .wffdw {width:200px;float:left;clear:left;}
.wffew {margin-left:220px;}
.wffl{font-size:13px;}
#w4pl_post_type_options{position:relative;}
#w4pl_post_type_options:after{ background:url(images/loading.gif) no-repeat; width:30px; height:30px; display:block;}
#w4pl_template_buttons a{ padding:4px 8px; display:inline-block; border:1px solid #DDD; background-color:#EEE; line-height:12px; font-size:12px; margin:0 2px 2px 0; text-decoration:none; border-radius: 3px; -moz-border-radius:3px; -webkit-border-radius:3px;}
.w4pl_button_group{ padding:0 0 10px;}
.w4pl_button_group_title{ display:block;}
.wfflwi_w4pl_template, .wffdwi_w4pl_css, .wffdwi_w4pl_js{ float:none; width:auto;}
.wffewi_w4pl_css, .wffewi_w4pl_js{ margin-left:0;}
#w4pl_list_options table.widefat th{ font-size:11px;}

#w4pl_list_options{ position:relative;}
.w4pl_group_title{margin:0; width:20%; padding:8px 10px;border-bottom:1px solid #D1E5EE; background-color:#FFF; font-size:16px; line-height:20px;
box-sizing: border-box;-moz-box-sizing: border-box;-webkit-box-sizing: border-box; font-weight:normal; cursor:pointer;}
.w4pl_field_group:last-child .w4pl_group_title{ border-bottom:1px solid #D1E5EE;}
.w4pl_field_group{}
.w4pl_group_fields{ display:none; position:absolute; left:22%; top:0; width:78%;}
.w4pl_active .w4pl_group_fields{ display:block;}
.w4pl_active .w4pl_group_title, .w4pl_group_title:hover{ background-color:#D1E5EE; box-shadow:0 0 1px #666 inset;}
<?php do_action( 'w4pl/admin_print_css' ); ?>
        </style>

		<script type="text/javascript">
(function($){

	$(document).on('w4pl/form_loaded', function(el){
		// console.log('w4pl/form_loaded');

		//$('#w4pl_list_options').css('minHeight', $('.w4pl_group_fields.w4pl_active').outerHeight() );
		w4pl_adjust_height();

		$('#w4pl_orderby').trigger('change');
	});

	$(document).ready(function(){
		$(document).trigger('w4pl/form_loaded', $('#w4pl_list_options') );
	});


	/* onchange post type, refresh the form */
	$('#w4pl_post_type').live('change', function(){
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
		$('.wffwi_w4pl_post_type .spinner').css('display', 'inline-block');
		$('#publish').hide();

		if( showTab === undefined ){
			showTab = 'w4pl_field_group_query';
		}
		if( data === undefined ){
			var data = $('#w4pl_list_options :input').serialize() + '&action=w4pl_list_options_template';
		}

		$.post( ajaxurl, data, function(r){
			$('#w4pl_list_options').replaceWith(r);

			$('.wffwi_w4pl_post_type .spinner').css('display', 'none');
			$('#publish').show();

			$('#'+ showTab).addClass('w4pl_active');

			w4pl_adjust_height();

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
}

	new W4PL_Core;
?>