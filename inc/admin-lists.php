<?php
/**
 * @package W4 Post List
 * @author Shazzad Hossain Khan
 * @url http://w4dev.com/plugins/w4-post-list
**/


class W4PL_Lists_Admin extends W4PL_Core
{
	function __construct()
	{
		add_action( 'add_meta_boxes_'. W4PL_SLUG, 					array($this, 'add_meta_boxes') );
		add_action( 'save_post_'. W4PL_SLUG,  						array($this, 'save_post'), 10, 3 );

		add_filter( 'w4pl/template_default',  						array($this, 'template_default') );
		add_filter( 'w4pl/template',  								array($this, 'template'), 10, 2 );


		// set update message for our post type, you dont like to use - "post update" !
		add_filter( 'post_updated_messages', 						array($this, 'post_updated_messages'));

		// additional column
		add_filter( 'manage_'. W4PL_SLUG .'_posts_columns', 		array($this, 'manage_posts_columns') );
		add_action( 'manage_'. W4PL_SLUG .'_posts_custom_column', 	array($this, 'manage_posts_custom_column'), 10, 2 );
	}

	// Meta box
	public function add_meta_boxes( $post )
	{
		// add configuration box right after post title, out of metabox
		add_action( 'edit_form_after_title', array($this, 'list_options_meta_box') );

		// add plugin news metabox one right side
		add_meta_box( "w4pl_news_meta_box", "Plugin Updates", array($this, 'news_meta_box'), W4PL_SLUG, "side", 'core');

		// enqueue script files, print css on header and print js on footer
		add_action('admin_head', array( $this, 'admin_head') );
	}

	public function admin_head()
	{
		$options = get_post_meta( get_the_ID(), '_w4pl', true );
		if( ! $options || !is_array($options) )
			$options = array();

		$options['id'] = get_the_ID();

		do_action( 'w4pl/list_options_print_scripts', $options );
	}


	public function list_options_meta_box( $post )
	{
		$options = get_post_meta( $post->ID, '_w4pl', true );
		if( ! $options || !is_array($options) )
			$options = array();

		$options['id'] = $post->ID;

		# echo '<pre>'; print_r($options); echo '</pre>';
		# $options = apply_filters( 'w4pl/pre_get_options', $options );
		# echo '<pre>'; print_r($options); echo '</pre>';
		do_action( 'w4pl/list_options_template', $options );
	}


	public function save_post( $post_ID, $post, $update )
	{
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
			return $post_ID ;

		if( !isset($_POST['w4pl']) )
			return;

		$options = stripslashes_deep($_POST['w4pl']);
		if( !isset($options['id']) )
			$options['id'] = $post_ID;

		$options = apply_filters( 'w4pl/pre_save_options', $options );
		update_post_meta( $post_ID, '_w4pl', $options );
	}

	// default templates
	public function template_default($r)
	{
		return '<ul>[posts]'. "\n" . '<li>'. "\n" . '[title]'. "\n" . '[excerpt wordlimit=20]' . "\n" . '[more]' . "\n".'</li>'. "\n" . '[/posts]</ul>';
	}

	// default templates
	public function template( $template, $opt )
	{
		if( !isset($opt['list_type']) || empty($opt['list_type']) )
			return $template;

		$users 		= '<ul>[users]'. "\n" . '<li>'. "\n" . '<a href="[user_link]">[user_name]</a>' . "\n".'</li>'. "\n" . '[/users]</ul>';
		$terms 		= '<ul>[terms]'. "\n" . '<li>'. "\n" . '<a href="[term_link]">[term_name]</a>' . "\n".'</li>'. "\n" . '[/terms]</ul>';
		$posts 		= '<ul>[posts]'. "\n" . '<li>'. "\n" . '[title]'. "\n" . '[excerpt wordlimit=20]' . "\n" . '[more]' . "\n".'</li>'. "\n" . '[/posts]</ul>';
		$termsposts 	= '<ul>[terms]'. "\n" . '<li>'. "\n" . '<a href="[term_link]">[term_name]</a>' . "\n" . $posts . "\n" . '</li>'. "\n" . '[/terms]</ul>';
		$usersposts 	= '<ul>[users]'. "\n" . '<li>'. "\n" . '<a href="[user_link]">[user_name]</a>' . "\n" . $posts . "\n" . '</li>'. "\n" . '[/users]</ul>';

		$was_default = (bool) ( empty($template) || in_array($template, array($terms, $users, $posts, $termsposts, $usersposts) ) );
		if( ! $was_default )
			return $template;

		if( 'terms' == $opt['list_type'] ){
			$template = $terms;
		}
		elseif( 'users' == $opt['list_type'] ){
			$template = $users;
		}
		elseif( 'posts' == $opt['list_type'] ){
			$template = $posts;
		}
		elseif( 'terms.posts' == $opt['list_type'] ){
			$template = $termsposts;
		}
		elseif( 'users.posts' == $opt['list_type'] ){
			$template = $usersposts;
		}
		return $template;
	}




	public function post_updated_messages( $messages )
	{
		global $post_ID, $post;

		$input_attr = sprintf( 
			'<input value="[postlist id=&quot;%d&quot;]" type="text" size="20" onfocus="this.select();" onclick="this.select();" readonly="readonly />"', 
			$post_ID 
		);

		$messages[W4PL_SLUG] = array(
			 1 => sprintf( __('List updated. Shortcode %2$s'), $post_ID, $input_attr ),
			 2 => '',
			 3 => '',
			 4 => __('List updated.'),
			 5 => '',
			 6 => sprintf( __('List published. Shortcode %2$s"]" %2$s />'), $post_ID, $input_attr ),
			 7 => __('List saved.'),
			 8 => sprintf( __('List submitted. Shortcode %2$s" %2$s />'), $post_ID, $input_attr ),
			 9 => sprintf( __('List scheduled. Shortcode %2$s'), $post_ID, $input_attr ),
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

		$columns['list_type'] = __('List Type');
		$columns['shortcode'] = __('Shortcode');

		if( $date ){
			# $columns['date'] = $date;
		}

		return $columns;
	}

	public function manage_posts_custom_column( $column_name, $post_ID )
	{
		if( 'list_type' == $column_name )
		{
			echo self::list_type_label($post_ID);
		}
		else if( 'shortcode' == $column_name ){
			printf( 
				'<input value="[postlist id=&quot;%d&quot;]" type="text" size="20" onfocus="this.select();" onclick="this.select();" readonly="readonly" />', 
				$post_ID 
			);
		}
	}

	/**
	 * Prints a friendly list type of a given post list
	 * used on admin lists table
	 */

	public function list_type_label( $post_ID )
	{
		// this is really odd to get information like this
		$options = get_post_meta( $post_ID, '_w4pl', true );
		$options['id'] = $post_ID;
		$options = apply_filters( 'w4pl/pre_get_options', $options );

		$lt = $options['list_type'];


		$return = '';

		if( 'terms.posts' == $lt )
		{
			$tax_obj = get_taxonomy($options['terms_taxonomy']);
			$post_obj = get_post_type_object($options['post_type']);
			$return = $tax_obj->label . ' & ' . $post_obj->labels->name;
		}
		else if( 'users.posts' == $lt )
		{
			$post_obj = get_post_type_object($options['post_type']);
			$return = 'Users' . ' & ' . $post_obj->labels->name;
		}
		else if( 'posts' == $lt )
		{
			$post_obj = get_post_type_object($options['post_type']);
			$return = $post_obj->labels->name;
		}
		else if( 'terms' == $lt )
		{
			$tax_obj = get_taxonomy($options['terms_taxonomy']);
			$return = $tax_obj->label;
		}
		else if( 'users' == $lt )
		{
			$return = 'Users';
		}

		if( empty($return) ){
			$lt_options = self::list_type_options();
			if( !empty($lt) && isset($lt_options[$lt]) )
				$return = $lt_options[$lt];
			else
				$return = '-';
		}
		
		return $return;
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
			$request = wp_remote_request('http://w4dev.com/w4pl.txt');
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