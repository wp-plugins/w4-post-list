<?php
// Load js/css scripts
function w4pl_load_scripts(){
	global $wpdb;
	$wpdb->post_list = $wpdb->prefix . 'post_list';

	if( is_admin()){
		wp_enqueue_script( 'w4pl-admin-js', W4PL_URL.'js/admin_js.js', array( 'jquery','jquery-ui-core','jquery-ui-tabs' ), W4PL_VERSION ,false );
		wp_enqueue_style( 'w4pl-admin-css', W4PL_URL . 'css/admin-style.css', '', W4PL_VERSION );

	}
	else{
		wp_enqueue_script( 'w4pl-js', W4PL_URL . 'js/js.js', array( 'jquery', 'jquery-ui-core' ), W4PL_VERSION ,true );
		wp_enqueue_style( 'w4pl-css', W4PL_URL . 'css/style.css', '', W4PL_VERSION );
	}
}
add_action( 'plugins_loaded', 'w4pl_load_scripts' );

// Parse Shortcode Inside Text Widget
function w4pl_text_widget_replace_callback( $text ){
	$pattern = '/\[\s*postlist(.*?)\]/sm';
	return preg_replace_callback( $pattern,'w4pl_text_widget_replace', $text);
}
add_filter( 'widget_text', 'w4pl_text_widget_replace_callback');

function w4pl_text_widget_replace( $matches ){
	$attr = shortcode_parse_atts( $matches[1] );
	return w4pl_do_shortcode( $attr );
}

// Add [postlist] Shortcode
function w4pl_do_shortcode( $attr){

	if( !is_array( $attr ))
		$attr = array($attr);

	$list_id = array_shift( $attr );
	$list_id = (int) $list_id;
	
	return get_w4_post_list( $list_id);
}
add_shortcode( 'postlist', 'w4pl_do_shortcode');

/* Intializer functions */
function w4_post_list( $list_id, $echo = true ){
	$output = get_w4_post_list( $list_id);

	if( $echo )
		echo $output;

	else
		return $output;
}

function get_w4_post_list( $list_id ){
	$w4_post_list = new W4_Post_list();

	$list = $w4_post_list->prepare( $list_id );

	if( is_wp_error( $list )){
		$w4pl_caps = get_option( 'w4pl_options');

		if( is_user_logged_in() && current_user_can( $w4pl_caps['manage_cap']))
			return '<p><strong>W4 post list Error:</strong> <span style="color:#FF0000">'.$list->get_error_message().'</span><br /><small>* this error is only visible for post list moderators and wont effect in search engine.</small></p>';

		return '<!-- W4 post list Error: '. $list->get_error_message() .'-->';
	}

	return "<!-- Post list Created by W4 post list WordPress Plugin @ http://w4dev.com/w4-plugin/w4-post-list -->\n" . $w4_post_list->display();
}

// Retrieve list data
function w4pl_get_list( $list_id = '', $col = null ){
	global $wpdb;

	$list_id = (int) $list_id;

	if( !$list_id )
		return false;

	$query = $wpdb->prepare( "SELECT * FROM  $wpdb->post_list WHERE list_id = %d", $list_id );

	if ( !$row = $wpdb->get_row( $query, ARRAY_A ))
		return false;

	$row['list_option'] = maybe_unserialize( $row['list_option'] );
	$row = apply_filters( 'w4pl_sanitize_list_option', $row );

	if( isset( $col ) && in_array( $col, array_keys( $row )))
		return $row[$col];

	return $row;
}

function w4pl_trim_excerpt( $text, $length = 0 ){
	if( !$length || $length < 1)
		return $text;
	
	$content = array();
	$words = preg_split( "/[\n\r\t ]+/", $text, $length + 1, PREG_SPLIT_NO_EMPTY );
	if ( count( $words) > 1 ){
		array_pop( $words );
		$content = implode(' ', $words);
	} else {
		$content = implode(' ', $words);
	}
	return $content;
}

/* Plugin Page Url Functions */
function w4pl_add_url( $echo = false ){
	$link = add_query_arg( 'new_list', 'true' , w4pl_plugin_page_url());

	if( $echo )
		echo $link;
	
	else
		return $link;
}
function w4pl_plugin_page_url( $echo = false ){
	$link = add_query_arg( 'page', W4PL_SLUG, admin_url( 'admin.php'));

	if( $echo )
		echo $link;
	
	else
		return $link;
}
function w4pl_option_page_url( $echo = false ){
	$link = add_query_arg( 'page', W4PL_SLUG. '-options', admin_url( 'admin.php'));

	if( $echo )
		echo $link;
	
	else
		return $link;
}

/* Sanitize Post list Data */
function w4pl_sanitize_list_option( $option ){

	$list_option = isset( $option['list_option'] ) ? $option['list_option'] : array();

	extract( $list_option );
	$yn_array = array( 'yes', 'no');

	if( !in_array( $list_type, array( 'pc', 'op', 'oc', 'op_by_cat' ))){
		$list_type = 'pc';
	}

	if( !in_array( $list_effect, array( 'yes', 'no', 'extended' ))){
		$list_effect = 'no';
	}

	if( !$post_order_method || empty( $post_order_method ))
		$post_order_method = 'newest';


	if( !in_array( $show_future_posts, $yn_array ))
		$show_future_posts = 'no';

	if( !is_array( $post_ids ))
		$post_ids = ( array ) $post_ids;

	if( !is_array( $posts_not_in ))
		$posts_not_in = ( array ) $posts_not_in;

	// Handle category posts/ categories/ and posts by categories
	if( in_array( $list_type, array( 'pc', 'oc', 'op_by_cat' ))):

		$post_ids = array();
		$posts_not_in = array();

		foreach( $categories as $category_id => $category_option ){
			$category_obj = get_category( $category_id);

			// if the cat doesnt exists
			if( !$category_obj)
				continue;

			$category_option['post_ids'] = (array) $category_option['post_ids'];
			$category_option['posts_not_in'] = (array) $category_option['posts_not_in'];
			$category_option['post_order_method'] = empty( $category_option['post_order_method'] ) ? 'newest' : $category_option['post_order_method'];
			$category_option['max'] = intval( $category_option['max'] );

			if( !in_array( $category_option['show_future_posts'], $yn_array ))
				$category_option['show_future_posts'] = 'no';

			$_posts = w4pl_term_posts( $category_id );

			// Future posts is on. First take out the not selected post_ids from $_posts and make that selected posts. Then do same for the post_not_in fields
			if( 'yes' == $category_option['show_future_posts'] ){

				$category_option['post_ids'] = $_posts;
				foreach( $category_option['posts_not_in'] as $_post_id ){
					if( $keys = array_keys( $category_option['post_ids'], $_post_id )){
						foreach( $keys as $k){
							unset( $category_option['post_ids'][$k] );
						}
					}
				}
				$category_option['post_ids'] = array_unique( $category_option['post_ids'] );

				$category_option['posts_not_in'] = $_posts;
				foreach( $category_option['post_ids'] as $_post_id ){
					if( $keys = array_keys( $category_option['posts_not_in'], $_post_id )){
						foreach( $keys as $k ){
							unset( $category_option['posts_not_in'][$k] );
						}
					}
				}
				$category_option['posts_not_in'] = array_unique( $category_option['posts_not_in'] );

			}
			// Future posts is off. First take out the selected post_ids from $_posts and make that selected posts_not_in. Then do same for the post_ids fields
			elseif( 'no' == $category_option['show_future_posts']){
				$category_option['posts_not_in'] = $_posts;

				foreach( $category_option['post_ids'] as $post_id ){
					if( $keys = array_keys( $category_option['posts_not_in'], $post_id )){
						foreach($keys as $k){
							unset( $category_option['posts_not_in'][$k] );
						}
					}
				}
				$category_option['posts_not_in'] = array_unique( $category_option['posts_not_in'] );

				$category_option['post_ids'] = $_posts;
				foreach( $category_option['posts_not_in'] as $post_id ){
					if( $keys = array_keys( $category_option['post_ids'], $post_id )){
						foreach($keys as $k){
							unset( $category_option['post_ids'][$k] );
						}
					}
				}
				$category_option['post_ids'] = array_unique( $category_option['post_ids'] );
			}
			
			// Post by terms
			if( 'op_by_cat' == $list_type ){
				$temp_post_ids = $category_option['post_ids'];
				$temp_posts_not_in = $category_option['posts_not_in'];

				if( $category_option["max"] > 0 && count( $temp_post_ids ) > $category_option["max"] )
					$temp_post_ids = array_slice( $temp_post_ids, 0, $category_option["max"] );

				$post_ids = wp_parse_args( $post_ids, $temp_post_ids );
				$posts_not_in = wp_parse_args( $posts_not_in, $temp_posts_not_in );

				$post_ids = array_unique( $post_ids );
				$posts_not_in = array_unique( $posts_not_in );
			}

			$categories[$category_id] = $category_option;
		}

	elseif( in_array( $list_type, array( 'op' ))):

		$all_post_ids = w4pl_all_posts_id();

		if( 'yes' == $show_future_posts ){

			$post_ids = $all_post_ids;
			foreach( $posts_not_in as $post_id ){
				if( $keys = array_keys( $post_ids, $post_id )){
					foreach( $keys as $k ){
						unset( $post_ids[$k] );
					}
				}
			}
			$post_ids = array_merge( $post_ids, array());

			$posts_not_in = $all_post_ids;
			foreach( $post_ids as $post_id){
				if( $keys = array_keys($posts_not_in, $post_id)){
					foreach( $keys as $k ){
						unset( $posts_not_in[$k] );
					}
				}
			}
			$posts_not_in = array_merge( $posts_not_in, array());
		}
		
		if( 'no' == $show_future_posts ){

			$posts_not_in = $all_post_ids;
			foreach( $post_ids as $post_id){
				if( $keys = array_keys( $posts_not_in, $post_id )){
					foreach($keys as $k){
						unset($posts_not_in[$k]);
					}
				}
			}
			$posts_not_in = array_merge( $posts_not_in, array());

			$post_ids = $all_post_ids;
			foreach( $posts_not_in as $post_id){
				if( $keys = array_keys($post_ids, $post_id)){
					foreach($keys as $k){
						unset($post_ids[$k]);
					}
				}
			}
			$post_ids = array_merge( $post_ids, array());
		}
	endif;

	$html_template['wrapper'] 			= w4pl_template_wrapper( $html_template['wrapper'] );
	$html_template['wrapper_post']		= w4pl_post_template_wrapper( $html_template['wrapper_post'] );
	$html_template['loop_post'] 		= w4pl_post_template_loop( $html_template['loop_post'] );
	$html_template['wrapper_category']	= w4pl_category_template_wrapper( $html_template['wrapper_category'] );
	$html_template['loop_category']		= w4pl_category_template_loop( $html_template['loop_category'] );

	$list_option = compact(
			'list_type',
			'list_effect',

			'post_max',
			'post_order_method',
			'show_future_posts',
			'read_more_text',
			'excerpt_length',

			'post_ids',
			'posts_not_in',
			'categories',
			'html_template'
		);
	
	$option['list_option'] = $list_option;
	return $option;
}
add_filter( 'w4pl_sanitize_list_option', 'w4pl_sanitize_list_option');

/* Post order key to array() */
function w4pl_sanitize_post_order_method( $order = 'newest' ){
	$array = array(
		'newest'		=> array( 'orderby' => 'date', 'order' => 'DESC'),
		'oldest'		=> array( 'orderby' => 'date', 'order' => 'ASC'),
		'most_popular'	=> array( 'orderby' => 'comment_count', 'order' => 'DESC'),
		'less_popular'	=> array( 'orderby' => 'comment_count', 'order' => 'ASC'),
		'a_title'		=> array( 'orderby' => 'title', 'order' => 'ASC'),
		'z_title'		=> array( 'orderby' => 'title', 'order' => 'DESC'),
		'random'		=> array( 'orderby' => 'rand', 'order' => 'ASC'),
	);
	return $array[$order];
}

/* All Published post ids */
function w4pl_all_posts_id(){
	global $wpdb;
	$results = $wpdb->get_col( "SELECT ID FROM $wpdb->posts WHERE post_type = 'post' AND post_status = 'publish'" );
	return $results;
}

/* All Published post ids inside a category */
function w4pl_term_posts( $term_id, $taxonomy = 'category', $post_type = 'post' ){
	global $wpdb;

	$posts = $wpdb->get_col( "SELECT 
	p.ID FROM $wpdb->posts as p 
	INNER JOIN $wpdb->term_relationships as tr 
		ON p.ID = tr.object_id 
	INNER JOIN $wpdb->term_taxonomy AS tt 
		ON tr.term_taxonomy_id = tt.term_taxonomy_id 
	WHERE 
		tt.taxonomy = '$taxonomy' 
	AND 
		tt.term_id = '$term_id' 
	AND 
		p.post_type = '$post_type' 
	AND 
		p.post_status = 'publish'" );

	if( !$posts )
		return array();

	return $posts;
}
?>