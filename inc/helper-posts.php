<?php
/**
 * @package W4 Post List
 * @author Shazzad Hossain Khan
 * @url http://w4dev.com/w4-plugin/w4-post-list
**/


class W4PL_Helper_Posts extends W4PL_Core
{
	function __construct()
	{
		/* Register User Shortcodes */
		add_filter( 'w4pl/get_shortcodes', 			array($this, 'get_shortcodes'), 21 );

		/* Filer Option */
		add_filter( 'w4pl/pre_get_options', 		array($this, 'pre_get_options') );

		/* Option Page Fields */
		add_filter( 'w4pl/admin_list_fields', 		array($this, 'admin_list_fields'), 10, 2 );

		/* Parse List Query Args */
		add_filter( 'w4pl/parse_query_args', 		array($this, 'parse_query_args'), 10 );
	}


	/* Register User Shortcodes */

	public static function get_shortcodes( $shortcodes )
	{
		$_shortcodes = array(
			'id' => array(
				'group' 	=> 'Post', 
				'callback' 	=> array('W4PL_Helper_Posts', 'post_id'),
				'desc' 		=> '<strong>Output</strong>: post id'
			),
			'ID' => array(
				'group' 	=> 'Post', 
				'callback' 	=> array('W4PL_Helper_Posts', 'post_id'),
				'desc' 		=> '<strong>Output</strong>: post id'
			),
			'post_id' => array(
				'group' 	=> 'Post', 
				'callback' 	=> array('W4PL_Helper_Posts', 'post_id'),
				'desc' 		=> '<strong>Output</strong>: post id'
			),
			'post_number' => array(
				'group' 	=> 'Post', 
				'callback' 	=> array('W4PL_Helper_Posts', 'post_number'),
				'desc' 		=> '<strong>Output</strong>: post item number, starting from 1'
			),
			'post_permalink' => array(
				'group' 	=> 'Post', 
				'callback' 	=> array('W4PL_Helper_Posts', 'post_permalink'),
				'desc' 		=> '<strong>Output</strong>: post url/link'
			),
			'post_class' => array(
				'group' 	=> 'Post', 
				'callback' 	=> array('W4PL_Helper_Posts', 'post_class'),
				'desc' 		=> '<strong>Output</strong>: post html classes'
			),
			'post_title' => array(
				'group' 	=> 'Post', 
				'callback' 	=> array('W4PL_Helper_Posts', 'post_title'),
				'desc' 		=> '<strong>Output</strong>: post title
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>wordlimit</strong> = (number), limit number of words to display'
			),
			'post_comment_url' => array(
				'group' 	=> 'Post', 
				'callback' 	=> array('W4PL_Helper_Posts', 'post_comment_url'),
				'desc' 		=> '<strong>Output</strong>: post comment form link/url'
			),
			'post_comment_count'=> array(
				'group' 	=> 'Post', 
				'callback' 	=> array('W4PL_Helper_Posts', 'post_comment_count'),
				'desc' 		=> '<strong>Output</strong>: (numeric) amount of approved comments'
			),
			'post_the_date' => array(
				'group' 	=> 'Post', 
				'code' 		=> '[post_the_date format="'. get_option('date_format') .'" before="" after=""]', 
				'callback' 	=> array('W4PL_Helper_Posts', 'post_the_date'),
				'desc' 		=> '<strong>Output</strong>: unique post date, ignored on current item if previous post date and curent post date is same (date formatted)
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>format</strong> = php datetime format
				<br /><strong>before</strong> = before date
				<br /><strong>after</strong> = after date'
			),
			'post_date' => array(
				'group' 	=> 'Post', 
				'code' 		=> '[post_date format="'. get_option('date_format') .'"]', 
				'callback' 	=> array('W4PL_Helper_Posts', 'post_date'),
				'desc' 		=> '<strong>Output</strong>: post date (date formatted)
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>format</strong> = php datetime format'
			),
			'post_time' => array(
				'group' 	=> 'Post', 
				'code' 		=> '[post_time format="'. get_option('time_format') .'"]', 
				'callback' 	=> array('W4PL_Helper_Posts', 'post_time'),
				'desc' 		=> '<strong>Output</strong>: post date (time formatted)
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>format</strong> = php datetime format'
			),
			'post_modified_date' => array(
				'group' 	=> 'Post', 
				'code' 		=> '[post_modified_date format="'. get_option('date_format') .'"]', 
				'callback' 	=> array('W4PL_Helper_Posts', 'post_modified_date'),
				'desc' 		=> '<strong>Output</strong>: post modified date (date formatted)
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>format</strong> = php datetime format'
			),
			'post_modified_time'=> array(
				'group' 	=> 'Post', 
				'code' 		=> '[post_modified_time format="'. get_option('time_format') .'"]', 
				'callback' 	=> array('W4PL_Helper_Posts', 'post_modified_time'),
				'desc' 		=> '<strong>Output</strong>: post modified date (time formatted)
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>format</strong> = php datetime format'
			),
			'post_author_meta' => array(
				'group' 	=> 'Post', 
				'code' 		=> '[post_author_meta name=""]', 
				'callback' 	=> array('W4PL_Helper_Posts', 'post_author_meta'),
				'desc' 		=> '<strong>Output</strong>: post author meta value
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>name</strong> = ex: display_name, bio, user_email etc'
			),
			'post_author_name' => array(
				'group' 	=> 'Post', 
				'callback' 	=> array('W4PL_Helper_Posts', 'post_author_name'),
				'desc' 		=> '<strong>Output</strong>: post author name'
			),
			'post_author_url'	=> array(
				'group' 	=> 'Post', 
				'callback' 	=> array('W4PL_Helper_Posts', 'post_author_url'),
				'desc' 		=> '<strong>Output</strong>: post author name url'
			),
			'post_author_email'	=> array(
				'group' 	=> 'Post', 
				'callback' 	=> array('W4PL_Helper_Posts', 'post_author_email'),
				'desc' 		=> '<strong>Output</strong>: post author email address'
			),
			'post_author_avatar'=> array(
				'group' 	=> 'Post', 
				'code' 		=> '[post_author_avatar size=""]', 
				'callback' 	=> array('W4PL_Helper_Posts', 'post_author_avatar'),
				'desc' 		=> '<strong>Output</strong>: post author avatar
				<br /><br /><strong>attributes:</strong>
				<br /><strong>size</strong> = (number), avatar image size'
			),
			'post_excerpt' => array(
				'group' 	=> 'Post', 
				'code' 		=> '[post_excerpt wordlimit=""]', 
				'callback' 	=> array('W4PL_Helper_Posts', 'post_excerpt'),
				'desc' 		=> '<strong>Output</strong>: post excerpt/short description
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>wordlimit</strong> = (number), limit number of words to display'
			),
			'post_content'		=> array(
				'group' 	=> 'Post', 
				'callback' 	=> array('W4PL_Helper_Posts', 'post_content'),
				'desc' 		=> '<strong>Output</strong>: post content'
			),
			'post_thumbnail' => array(
				'group' 	=> 'Post', 
				'code' 		=> '[post_thumbnail size="" return=""]', 
				'callback' 	=> array('W4PL_Helper_Posts', 'post_thumbnail'),
				'desc' 		=> '<strong>Output</strong>: (text|number) based on the rerurn attribute & only if the post has a thumbnail assigned
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
				'group' 	=> 'Post', 
				'code' 		=> '[post_meta key="" multiple="0"]', 
				'callback' 	=> array('W4PL_Helper_Posts', 'post_meta'),
				'desc' 		=> '<strong>Output</strong>: post meta value. if return value is an array, it will be migrated to string by using explode function
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>key</strong> = (text|number), meta key name
				<br /><strong>multiple</strong> = (0|1), display meta value at multiple occurence
				<br /><strong>sep</strong> = (text), separate array meta value into string'
			),
			'post_terms' => array(
				'group' 	=> 'Post', 
				'code' 		=> '[post_terms tax="category" sep=", "]', 
				'callback' 	=> array('W4PL_Helper_Posts', 'post_terms'),
				'desc' 		=> '<strong>Output</strong>: post type terms. if return value is an array, it will be migrated to string by using explode function
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>tax</strong> = (string), taxonomy name
				<br /><strong>sep</strong> = (string), separate array meta value into string'
			),
			'attachment_thumbnail' => array(
				'group' 	=> 'Post', 
				'code' 		=> '[attachment_thumbnail size=""]', 
				'callback' 	=> array('W4PL_Helper_Posts', 'attachment_thumbnail'),
				'desc' 		=> '<strong>Output</strong>: if the post type is attachment, the attached file thumb is displayed.
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>id</strong> = (string), attachment id
				<br /><strong>meta_key</strong> = (string), retrieve attachment id from meta value
				<br /><strong>size</strong> = (string), image size
				<br /><strong>width</strong> = (number), image width
				<br /><strong>height</strong> = (number), image height'
			),
			'attachment_url' => array(
				'group' 	=> 'Post', 
				'callback' 	=> array('W4PL_Helper_Posts', 'attachment_url'),
				'desc' 		=> '<strong>Output</strong>:  if the post is an attachment, the attached image source is returned'
			),

			'parent_permalink' => array(
				'group' 	=> 'Post', 
				'code' 		=> '[parent_permalink self=1]', 
				'callback' 	=> array('W4PL_Helper_Posts', 'parent_permalink'),
				'desc' 		=> '<strong>Output</strong>: if the post type is hierarchical, it\'s parent post permalink is returned
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>self</strong> = (int), if no parent item exist, return the self permalink'
			),

			'title' => array(
				'group' 	=> 'Post', 
				'callback' 	=> array('W4PL_Helper_Posts', 'template_title'),
				'desc' 		=> '<strong>Output</strong>: title template'
			),
			'meta' => array(
				'group' 	=> 'Post', 
				'callback' 	=> array('W4PL_Helper_Posts', 'template_meta'),
				'desc' 		=> '<strong>Output</strong>: meta template'
			),
			'publish' => array(
				'group' 	=> 'Post', 
				'callback'	=> array('W4PL_Helper_Posts', 'template_date'),
				'desc' 		=> '<strong>Output</strong>: publish time template'
			),
			'date' => array(
				'group' 	=> 'Post', 
				'callback' 	=> array('W4PL_Helper_Posts', 'template_date'),
				'desc' 		=> '<strong>Output</strong>: publish time template'
			),
			'modified' => array(
				'group' 	=> 'Post', 
				'callback' 	=> array('W4PL_Helper_Posts', 'template_modified'),
				'desc' 		=> '<strong>Output</strong>: modified time template'
			),
			'author' => array(
				'group' 	=> 'Post', 
				'callback' 	=> array('W4PL_Helper_Posts', 'template_author'),
				'desc' 		=> '<strong>Output</strong>: author template'
			),
			'excerpt' => array(
				'group' 	=> 'Post', 
				'callback' 	=> array('W4PL_Helper_Posts', 'template_excerpt'),
				'desc' 		=> '<strong>Output</strong>: excerpt template'
			),
			'content' => array(
				'group' 	=> 'Post', 
				'callback' 	=> array('W4PL_Helper_Posts', 'template_content'),
				'desc' 		=> '<strong>Output</strong>: content template'
			),
			'more' => array(
				'group' 	=> 'Post', 
				'code' 		=> '[more text="Continue Reading"]', 
				'callback' 	=> array('W4PL_Helper_Posts', 'template_more'),
				'desc' 		=> '<strong>Output</strong>: more link template
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>text</strong> = (string), text to be displayed'
			),

			'group_id' => array(
				'group' 	=> 'Group', 
				'callback' 	=> array('W4PL_Helper_Posts', 'post_group_id'),
				'desc' 		=> '<strong>Output</strong>: group name / title'
			),
			'group_title' => array(
				'group' 	=> 'Group', 
				'callback' 	=> array('W4PL_Helper_Posts', 'post_group_title'),
				'desc' 		=> '<strong>Output</strong>: group name / title'
			),
			'group_url' => array(
				'group' 	=> 'Group', 
				'callback' 	=> array('W4PL_Helper_Posts', 'post_group_url'),
				'desc' 		=> '<strong>Output</strong>: group page link'
			)
		);

		return array_merge( $shortcodes, $_shortcodes );
	}

	/* Post Shortcode Callbacks */

	public static function post_id($attr, $cont){ return get_the_ID(); }
	public static function post_number($attr, $cont, $list){ return (int) ($list->posts_query->current_post + 1); }
	public static function post_permalink($attr, $cont){ return get_permalink(); }
	public static function post_class($attr, $cont){ return join( ' ', get_post_class() ); }
	public static function post_title($attr, $cont)
	{
		$return = get_the_title();
		if( isset($attr['wordlimit']) ){
			$wordlimit = $attr['wordlimit'];
			$return = wp_trim_words( $return, $wordlimit );
		}
		return $return;
	}

	public static function post_comment_url($attr, $cont){ return get_permalink() . "#comments"; }
	public static function post_comment_count($attr, $cont){ global $post; return (int) $post->comment_count; }

	public static function post_the_date($attr, $cont)
	{
		$format = $before = $after = '';
		if( isset($attr['format']) )
			$format = $attr['format'];
		if( isset($attr['before']) )
			$before = $attr['before'];
		if( isset($attr['after']) )
			$after = $attr['after'];

		return the_date( $format, $before, $after, false );
	}
	public static function post_date($attr, $cont)
	{
		$format = get_option('date_format');
		if( isset($attr['format']) ){
			$format = $attr['format'];
		}
		return get_the_date( $format );
	}
	public static function post_time($attr, $cont)
	{
		$format = get_option('time_format');
		if( isset($attr['format']) ){
			$format = $attr['format'];
		}
		return get_the_time($format);
	}
	public static function post_modified_date($attr, $cont)
	{
		$format = get_option('date_format');
		if( isset($attr['format']) ){
			$format = $attr['format'];
		}
		return get_post_modified_time($format);
	}
	public static function post_modified_time($attr, $cont)
	{
		$format = get_option('time_format');
		if( isset($attr['format']) ){
			$format = $attr['format'];
		}
		return get_post_modified_time($format);
	}
	public static function post_author_meta( $attr, $cont)
	{
		if( isset($attr) && !is_array($attr) && is_string($attr) ){
			$name = trim($attr);
			$attr = array();
		}
		elseif( isset($attr['name']) ){
			$name = $attr['name'];
		}
		if( empty($name) || in_array($name, array('pass', 'user_pass')))
			return;

		return get_the_author_meta( $name, get_the_author_meta('ID') );
	}

	public static function post_author_name($attr, $cont){ return get_the_author_meta('display_name'); }
	public static function post_author_url($attr, $cont){ return get_author_posts_url( get_the_author_meta('ID') ); }
	public static function post_author_email($attr, $cont){ return get_the_author_meta('user_email'); }
	public static function post_author_avatar($attr, $cont)
	{
		$size = 32;
		if( isset($attr['size']) ){
			$size = $attr['size'];
		}
		return get_avatar( get_the_author_meta('user_email'), $size );
	}

	public static function post_excerpt( $attr, $cont )
	{
		$post = get_post();
		$excerpt = $post->post_excerpt;
		if ( '' == $excerpt )
			$excerpt = $post->post_content;

		if( isset($attr['wordlimit']) ){
			$wordlimit = (int) $attr['wordlimit'];
			$excerpt = wp_trim_words( wp_strip_all_tags($excerpt), $wordlimit );
		}

		return $excerpt;
	}
	public static function post_content($attr, $cont)
	{
		global $post;
		// Post content without wrapper --
		$content = apply_filters( 'the_content', get_the_content() );
		$content = str_replace(']]>', ']]&gt;', $content);
		return $content;
	}
	public static function post_thumbnail($attr, $cont)
	{
		if( isset($attr['size']) ){
			$size = $attr['size'];
		}
		elseif( isset($attr['width']) ){
			if( isset($attr['height']) ){
				$height = $attr['height'];
			}
			else{
				$height = 9999;
			}
			$size = array($attr['width'], $height);
		}
		elseif( isset($attr['height']) )
		{
			if( isset($attr['width']) ){
				$width = $attr['width'];
			}
			else{
				$width = 9999;
			}
			$size = array($width, $attr['height']);
		}
		else
		{
			$size = 'post-thumbnail';
		}


		$post_id = get_the_ID();
		$post_thumbnail_id = (int) get_post_thumbnail_id( $post_id );


		if( isset($attr['return']) && 'id' == $attr['return'] )
		{
			return $post_thumbnail_id;
		}
		elseif( isset($attr['return']) && 'src' == $attr['return'] )
		{
			$img = wp_get_attachment_image_src( $post_thumbnail_id, $size );
			return isset($img[0]) ? $img[0] : '';
		}
		elseif ( $post_thumbnail_id )
		{
			return wp_get_attachment_image( $post_thumbnail_id, $size );
		}

		return '';
	}

	public static function post_meta($attr, $cont)
	{
		if( isset($attr) && !is_array($attr) && is_string($attr) ){
			$meta_key = trim($attr);
			$attr = array();
		}

		if( isset($attr['key']) ){

			$meta_key = $attr['key'];
		}
		elseif( isset($attr['meta_key']) ){
			$meta_key = $attr['meta_key'];
		}
		if( ! $meta_key )
			return;

		$single = ! ( isset($attr) && is_array($attr) && array_key_exists('multiple', $attr) ?  (bool) $attr['multiple'] : true );

		$sep = ', ';
		if( isset($attr['sep']) ){
			$sep = $attr['sep'];
		}

		$return = get_post_meta( get_the_ID(), $meta_key, $single );

		if( is_array($return) ){
			$new = array();
			foreach( $return as $r => $d ){
				if( !is_array($d) ){
					$new[] = $d;
				}
			}
			if( $new )
				$return = implode( $sep, $new );
			else
				$return = '';
		}

		return $return;
	}
	public static function post_terms($attr, $cont)
	{
		if( isset($attr['tax']) ){
			$taxonomy = $attr['tax'];
		}
		elseif( isset($attr['taxonomy']) ){
			$taxonomy = $attr['taxonomy'];
		}
		if( ! isset($taxonomy) || ! taxonomy_exists($taxonomy) )
			return;

		$sep = ', ';
		if( isset($attr['sep']) ){
			$sep = $attr['sep'];
		}

		return get_the_term_list( get_the_ID(), $taxonomy, '', $sep );
	}


	// Attachment
	public static function attachment_thumbnail($attr, $cont)
	{
		if( isset($attr['size']) ){
			$size = $attr['size'];
		}
		elseif( isset($attr['width']) ){
			if( isset($attr['height']) ){
				$height = $attr['height'];
			}
			else{
				$height = 9999;
			}
			$size = array($attr['width'], $height);
		}
		elseif( isset($attr['height']) )
		{
			if( isset($attr['width']) ){
				$width = $attr['width'];
			}
			else{
				$width = 9999;
			}
			$size = array($width, $attr['height']);
		}
		else
		{
			$size = 'post-thumbnail';
		}


		if( isset($attr['id']) ){
			$attachment_id = (int) $attr['id'];
		}
		elseif( isset($attr['meta_key']) ){
			$attachment_id = get_post_meta(get_the_ID(), $attr['meta_key'], true);
		}
		else{
			$attachment_id = get_the_ID();
		}


		if( 'attachment' != get_post_type($attachment_id) )
			return '';


		$icon = false;
		if( ! wp_attachment_is_image($attachment_id) ){
			$icon = true;
		}

		if ( $attachment_id ) {
			$html = wp_get_attachment_image( $attachment_id, $size, $icon );
		} else {
			$html = '';
		}

		return $html;
	}
	public static function attachment_url($attr, $cont)
	{
		if( isset($attr['id']) )
			$post_id = (int) $attr['id'];
		else
			$post_id = get_the_ID();

		if( 'attachment' != get_post_type($post_id) )
			return '';

		return wp_get_attachment_url($post_id);
	}

	// Parent
	public static function parent_permalink($attr, $cont)
	{
		$post = get_post();
		$parent = ( $post->post_parent > 0 && $post->post_parent != $post->ID ) ? get_post( $post->post_parent ) : false;
		if( $parent )
			return get_permalink( $parent );
		elseif( isset($attr['self']) && $attr['self'] )
			return get_permalink( $post );
		else
			return '#';
	}


	// Tempate
	public static function template_title($attr, $cont){
		return sprintf( 
			'<a class="post_title w4pl_post_title" href="%1$s" title="View %2$s">%2$s</a>', 
			get_permalink(), 
			get_the_title() 
		);
	}
	public static function template_meta($attr, $cont){
		return sprintf( 
			__("Posted on:", W4PL_TXT_DOMAIN). ' <abbr class="published post-date" title="%1$s">%2$s</abbr> <span class="post_author">by %3$s</span>', 
			get_the_time( get_option('time_format') ), 
			get_the_time( get_option('date_format') ), 
			get_the_author()
		);
	}
	public static function template_date($attr, $cont){
		return sprintf( 
			'<abbr class="published post-date" title="%1$s"><strong>' . __(" Published:", W4PL_TXT_DOMAIN).'</strong> %2$s</abbr>',
			get_the_time( get_option('time_format') ), 
			get_the_time( get_option('date_format') )
		);
	}
	public static function template_modified($attr, $cont){
		return sprintf( 
			'<abbr class="modified post-modified" title="%1$s"><strong>' . __( "Updated:", W4PL_TXT_DOMAIN ) . '</strong> %2$s</abbr>',
			get_post_modified_time( get_option('time_format')), 
			get_post_modified_time( get_option('date_format'))
		);
	}
	public static function template_author($attr, $cont){
		return sprintf( 
			'<a href="%1$s" title="View all posts by %2$s" rel="author">%2$s</a>', 
			get_author_posts_url( get_the_author_meta('ID') ), 
			get_the_author() 
		);
	}
	public static function template_excerpt($attr, $cont, $list){
		return sprintf( 
			'<div class="post-excerpt">%s</div>',
			self::post_excerpt($attr, $cont, $list)
		);
	}
	public static function template_content($attr, $cont){
		return sprintf( 
			'<div class="post-excerpt">%s</div>',
			$this->post_content($attr, $cont)
		);
	}
	public static function template_more($attr, $cont){
		$read_more = !empty( $attr['text'] ) ? $attr['text'] : __( 'Continue reading &raquo;', W4PL_TXT_DOMAIN );
		return sprintf( 
			'<a class="read_more" href="%1$s" title="%3$s %2$s">%3$s</a>', 
			get_permalink(), 
			get_the_title(), 
			esc_attr( $read_more )
		);
	}


	public static function post_group_id( $attr, $cont, $list )
	{
		return isset($list->current_group) ? $list->current_group['id'] : 0;
	}
	public static function post_group_title( $attr, $cont, $list )
	{
		return isset($list->current_group) ? $list->current_group['title'] : '';
	}
	public static function post_group_link( $attr, $cont, $list )
	{
		return isset($list->current_group) ? $list->current_group['url'] : '';
	}


	/* Filer Option */

	public function pre_get_options($options)
	{
		if( !isset($options) || !is_array($options) )
			$options = array();

		if( isset($options['list_type']) && in_array($options['list_type'], array('posts', 'terms.posts', 'users.posts') ) )
		{
			$options = wp_parse_args( $options, array(
				'post_type' 		=> 'post', 
				'post_status' 		=> array('publish'), 
				'post__in' 			=> '', 
				'post__not_in' 		=> '', 
				'exclude_self'		=> '',
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

			if( 'attachment' == $options['post_type'] )
				$options['post_status'] = array('inherit');
		}

		return $options;
	}


	/* Option Page Fields */

	public function admin_list_fields( $fields, $options )
	{
		$list_type = $options['list_type'];
		if( ! in_array($list_type, array('posts', 'terms.posts', 'users.posts') ) )
			return $fields;

		/* GROUP 2 */
		$fields['before_field_group_query'] = array(
			'position'		=> '51',
			'html' 			=> '<div id="w4pl_field_group_query" class="w4pl_field_group">
								<div class="w4pl_group_title">Posts</div>
								<div class="w4pl_group_fields">'
		);

		$fields['post_type'] = array(
			'position'		=> '55',
			'option_name' 	=> 'post_type',
			'name' 			=> 'w4pl[post_type]',
			'label' 		=> 'Post Type',
			'type' 			=> 'select',
			'option' 		=> self::post_type_options(),
			'input_class'	=> 'w4pl_onchange_lfr'
		);

		// mime type field
		if( $mime_type_options = self::post_mime_type_options($options['post_type']) )
		{
			$fields['post_mime_type'] = array(
				'position' 		=> '56',
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
				'position'		=> '60',
				'option_name' 	=> 'post_status',
				'name' 			=> 'w4pl[post_status]',
				'label' 		=> 'Post Status',
				'type' 			=> 'checkbox',
				'option' 		=> array('any' => 'Any', 'publish' => 'Publish', 'pending' => 'Pending', 'future' => 'Future', 'inherit' => 'Inherit')
			);
		}

		$fields['post__in'] = array(
			'position'		=> '65',
			'option_name' 	=> 'post__in',
			'name' 			=> 'w4pl[post__in]',
			'label' 		=> 'Include posts',
			'type' 			=> 'text',
			'input_class' 	=> 'widefat',
			'desc' 			=> 'comma separated post id'
		);
		$fields['post__not_in'] = array(
			'position'		=> '66',
			'option_name' 	=> 'post__not_in',
			'name' 			=> 'w4pl[post__not_in]',
			'label' 		=> 'Exclude posts',
			'type' 			=> 'text',
			'input_class' 	=> 'widefat',
			'desc' 			=> 'comma separated post id'
		);
		$fields['exclude_self'] = array(
			'position'		=> '66.5',
			'option_name' 	=> 'exclude_self',
			'name' 			=> 'w4pl[exclude_self]',
			'label' 		=> 'Exclude self',
			'type' 			=> 'radio',
			'option' 		=> array('' => 'No', 'yes' => 'Yes')
		);
		$fields['post_parent__in'] = array(
			'position'		=> '67',
			'option_name' 	=> 'post_parent__in',
			'name' 			=> 'w4pl[post_parent__in]',
			'label' 		=> 'Post parent',
			'type' 			=> 'text',
			'input_class' 	=> 'widefat',
			'desc' 			=> 'display child posts of given parent items. comma separated parent post ids'
		);
		$fields['author__in'] = array(
			'position'		=> '68',
			'option_name' 	=> 'author__in',
			'name' 			=> 'w4pl[author__in]',
			'label' 		=> 'Post author',
			'type' 			=> 'text',
			'input_class' 	=> 'widefat',
			'desc' 			=> 'comma separated user/author ids'
		);

		$fields['orderby'] = array(
			'position'		=> '70',
			'option_name' 	=> 'orderby',
			'name' 			=> 'w4pl[orderby]',
			'label' 		=> 'Orderby',
			'type' 			=> 'select',
			'option' 		=> self::post_orderby_options($options['post_type']),
			'input_after'	=> '<div id="orderby_meta_key_wrap">Meta key: <input name="w4pl[orderby_meta_key]" type="text" value="'
				. (isset($options['orderby_meta_key']) ? esc_attr($options['orderby_meta_key']) : '') .'" /></div>'
		);
		$fields['order'] = array(
			'position'		=> '71',
			'option_name' 	=> 'order',
			'name' 			=> 'w4pl[order]',
			'label' 		=> 'Order',
			'type' 			=> 'radio',
			'option' 		=> array('ASC' => 'ASC', 'DESC' => 'DESC')
		);


		$fields['limit'] = array(
			'position'		=> '76',
			'option_name' 	=> 'limit',
			'name' 			=> 'w4pl[limit]',
			'label' 		=> 'Maximum items',
			'type' 			=> 'text',
			'desc' 			=> 'maximum results to display in total'
		);

		if( 'posts' == $options['list_type'] )
		{
			$fields['offset'] = array(
				'position'		=> '77',
				'option_name' 	=> 'offset',
				'name' 			=> 'w4pl[offset]',
				'label' 		=> 'Offset',
				'type' 			=> 'text',
				'desc' 			=> 'skip given number of posts from beginning'
			);
			$fields['posts_per_page'] = array(
				'position'		=> '75',
				'option_name' 	=> 'posts_per_page',
				'name' 			=> 'w4pl[posts_per_page]',
				'label' 		=> 'Items per page',
				'type' 			=> 'text',
				'desc' 			=> sprintf('number of items to show per page<br />use <strong>-1</strong> to display all, default <strong>%d</strong>', get_option('posts_per_page') )
			);
		}

		if( 'posts' == $options['list_type'] )
		{
			$fields['groupby'] = array(
				'position' 		=> '95',
				'option_name' 	=> 'groupby',
				'name' 			=> 'w4pl[groupby]',
				'label' 		=> 'Group By',
				'type' 			=> 'select',
				'option' 		=> self::post_groupby_options($options['post_type'])
			);
			$fields['group_order'] = array(
				'position' 		=> '96',
				'option_name' 	=> 'group_order',
				'name' 			=> 'w4pl[group_order]',
				'label' 		=> 'Group Order',
				'type' 			=> 'radio',
				'option' 		=> array('' => 'None', 'ASC' => 'ASC', 'DESC' => 'DESC')
			);
		}

		$fields['after_field_group_query'] = array(
			'position'		=> '100',
			'html' 			=> '</div><!--.w4pl_group_fields--></div><!--#w4pl_field_group_query-->'
		);

		return $fields;
	}


	/* Parse List Query Args */

	public function parse_query_args( $list )
	{
		if( in_array($list->options['list_type'], array('posts', 'terms.posts', 'users.posts') ) )
		{
			#echo '<pre>';
			#print_r($list->options);
			#echo '</pre>';

			// push default options to query var
			foreach( array(
				'post_type', 
				'orderby', 
				'order', 
				'posts_per_page', 
				'offset'
			) as $option_name )
			{
				if( !empty($list->options[$option_name]) )
					$list->posts_args[$option_name] = $list->options[$option_name];
			}

			// array
			foreach( array(
				'post_mime_type', 
				'post_status'
			) as $option_name )
			{
				if( !empty($list->options[$option_name]) )
					$list->posts_args[$option_name] = $list->options[$option_name];
			}


			// comma separated ids
			foreach( array(
				'post__in', 
				'post__not_in', 
				'post_parent__in', 
				'author__in',
			) as $option_name )
			{
				if( !empty($list->options[$option_name]) ){
					$opt = wp_parse_id_list($list->options[$option_name]);
					if( !empty($opt) )
						$list->posts_args[$option_name] = $opt;
				}
			}

			// exclude current post
			if( is_singular() && isset($list->options['exclude_self']) && 'yes' == $list->options['exclude_self'] ){
				if( !isset($list->posts_args['post__not_in']) ){
					$list->posts_args['post__not_in'] = array( get_the_ID() );
				}
				elseif( is_array($list->posts_args['post__not_in']) ){
					$list->posts_args['post__not_in'][] = get_the_ID();
				}
				elseif( empty($list->posts_args['post__not_in']) ){
					$list->posts_args['post__not_in'] = array( get_the_ID() );
				}
			}

			// orderby meta key/value
			if( $list->options['orderby'] == 'meta_value' || $list->options['orderby'] == 'meta_value_num' )
			{
				$list->posts_args['meta_key'] = $list->options['orderby_meta_key'];
			}

			// we catch paged query using a non-pretty query var
			$paged = isset($_REQUEST['page'. $list->id]) ? $_REQUEST['page'. $list->id] : 1;

			$defaults = array(
				'post_status' 	=> 'publish',
				'post_type' 	=> 'post',
				'paged'			=> $paged
			);

			$list->posts_args = wp_parse_args( $list->posts_args, $defaults );


			// set the posts per page
			if( !isset($list->posts_args['posts_per_page']) || empty($list->posts_args['posts_per_page']) ){
				$list->posts_args['posts_per_page'] = get_option('posts_per_page', 10);
			}


			// while maximum limit is set, we only fetch till the maximum post
			if( !empty($list->options['limit']) && $list->options['limit'] < ($list->posts_args['posts_per_page'] * $paged) )
			{
				$list->posts_args['offset'] = (int) $list->options['offset'] + ( ($paged - 1) * $list->posts_args['posts_per_page'] );
				$list->posts_args['posts_per_page'] = $list->options['limit'] - ( $list->posts_args['posts_per_page'] * ($paged-1) );
			}
			// while maximum limit is set, we only fetch till the maximum post
			elseif( !empty($list->options['offset']) )
			{
				$list->posts_args['offset'] = (int) $list->options['offset'] + ($paged - 1) * $list->posts_args['posts_per_page'];
			}

		}
		// ends post query
	}
}

	new W4PL_Helper_Posts;
?>