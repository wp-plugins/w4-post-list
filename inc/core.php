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

		# add_action( 'wp_footer', 				array($this, 'wp_footer'));
	}

	public function wp_footer()
	{
		wp_enqueue_script( 'w4pl', W4PL_URL . 'assets/w4-post-list.js', array( 'jquery' ) );
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