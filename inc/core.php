<?php
class W4PL_Core 
{
	function __construct()
	{
		// register post list
		add_action( 'init', 					array($this, 'register_post_type'));


		// add postlist shortcode
		add_shortcode( 'postlist', 				array($this, 'shortcode') );


		// allow shortcode for widget text
		add_filter( 'widget_text', 				'do_shortcode');


		// allow shortcode for widget text
		add_filter( 'w4pl/get_shortcodes', 		array($this, 'get_shortcodes') );
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
			'supports' 				=> array('title' ),
			'menu_icon'				=> W4PL_URL .'assets/menu.png'
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
				'group' => 'Template', 
				'func' => 'template_title', 
				'desc' => '<strong>Output</strong>: title template'
			),
			'meta' => array(
				'group' => 'Template', 
				'func' => 'template_meta', 
				'desc' => '<strong>Output</strong>: meta template'
			),
			'publish' => array(
				'group' => 'Template', 
				'func' => 'template_date', 
				'desc' => '<strong>Output</strong>: publish time template'
			),
			'date'				=> array(
				'group' => 'Template', 
				'func' => 'template_date', 
				'desc' => '<strong>Output</strong>: publish time template'
			),
			'modified' => array(
				'group' => 'Template', 
				'func' => 'template_modified', 
				'desc' => '<strong>Output</strong>: modified time template'
			),
			'author' => array(
				'group' => 'Template', 
				'func' => 'template_author', 
				'desc' => '<strong>Output</strong>: author template'
			),
			'excerpt' => array(
				'group' => 'Template', 
				'func' => 'template_excerpt', 
				'desc' => '<strong>Output</strong>: excerpt template'
			),
			'content' => array(
				'group' => 'Template', 
				'func' => 'template_content', 
				'desc' => '<strong>Output</strong>: content template'
			),
			'more' => array(
				'group' => 'Template', 
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
	public function shortcode( $attr)
	{
		if( !is_array( $attr ))
			$attr = array($attr);
		$list_id = array_shift( $attr );
		$list_id = (int) $list_id;

		return self::the_list($list_id);
	}


	/*
	 * The List
	 * @param (int), list id
	 * @return (string)
	*/
	public static function the_list( $id )
	{
		$w4_post_list = new W4_Post_list();
		$list = $w4_post_list->prepare( $id );
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
}

	new W4PL_Core;
?>