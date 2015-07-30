<?php
/**
 * @package W4 Post List
 * @author Shazzad Hossain Khan
 * @url http://w4dev.com/plugins/w4-post-list
**/


class W4PL_Tinymce
{
	function __construct()
	{
		add_action( 'init', __CLASS__ . '::register_tinymce_button' );
	}

	public static function register_tinymce_button()
	{
		if( ( current_user_can( 'edit_posts' ) || current_user_can( 'edit_pages' )) && get_user_option( 'rich_editing' ) == 'true'){

			add_filter( 'mce_external_plugins', __CLASS__ . '::mce_external_plugins' );
			add_filter( 'mce_buttons', 			__CLASS__ . '::mce_buttons' );
		}
	}

	public static function mce_buttons( $buttons )
	{
		array_push( $buttons, "|", "w4pl" );
		return $buttons;
	}

	public static function mce_external_plugins( $plugins )
	{
		$plugins['w4pl'] = W4PL_URL. 'tinymice/plugin.js';
		return $plugins;
	}
}

	new W4PL_Tinymce;


	# remove_action( 'init', 'W4PL_Tinymce::register_tinymce_button' );
?>