<?php
class W4_Post_list
{
	var $id 		= array();
	var $query 		= array();
	var $wp_query 	= array();
	var $options 	= array();
	var $css		= '';
	var $js			= '';

	function __construct()
	{
		$shortcodes = self::get_shortcodes();
		foreach( $shortcodes as $tag => $attr ){
			if( ! has_filter( 'w4pl/shortcode/'. $tag, array(&$this, $attr['func']) ) )
				add_filter( 'w4pl/shortcode/'. $tag, array(&$this, $attr['func']), 10, 2 );
		}
	}

	public static function get_shortcodes()
	{
		// Shortcodes # [Shortcode] => Callback
		return array(
			'id' => array(
				'func' => 'post_id', 
				'desc' => '<strong>Output</strong>: post id'
			),
			'ID' => array(
				'func' => 'post_id', 
				'desc' => '<strong>Output</strong>: post id'
			),
			'post_id' => array(
				'func' => 'post_id', 
				'desc' => '<strong>Output</strong>: post id'
			),
			'post_permalink' => array(
				'func' => 'post_permalink', 
				'desc' => '<strong>Output</strong>: post url/link'
			),
			'post_class' => array(
				'func' => 'post_class', 
				'desc' => '<strong>Output</strong>: post html classes'
			),
			'post_title' => array(
				'func' => 'post_title', 
				'desc' => '<strong>Output</strong>: post title
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>wordlimit</strong> = (number), limit number of words to display'
			),
			'post_comment_url' => array(
				'func' => 'post_comment_url', 
				'desc' => '<strong>Output</strong>: post comment form link/url'
			),
			'post_comment_count'=> array(
				'func' => 'post_comment_count', 
				'desc' => '<strong>Output</strong>: (numeric) amount of approved comments'
			),
			'post_date' => array(
				'func' => 'post_date', 
				'desc' => '<strong>Output</strong>: post date (date formatted)
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>format</strong> = php datetime format'
			),
			'post_time' => array(
				'func' => 'post_time', 
				'desc' => '<strong>Output</strong>: post date (time formatted)
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>format</strong> = php datetime format'
			),
			'post_modified_date' => array(
				'func' => 'post_modified_date', 
				'desc' => '<strong>Output</strong>: post modified date (date formatted)
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>format</strong> = php datetime format'
			),
			'post_modified_time'=> array(
				'func' => 'post_modified_time', 
				'desc' => '<strong>Output</strong>: post modified date (time formatted)
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>format</strong> = php datetime format'
			),
			'post_author_name' => array(
				'func' => 'post_author_name', 
				'desc' => '<strong>Output</strong>: post author name'
			),
			'post_author_url'	=> array(
				'func' => 'post_author_url', 
				'desc' => '<strong>Output</strong>: post author name url'
			),
			'post_author_avatar'=> array(
				'code' => '[post_author_avatar size=""]', 
				'func' => 'post_author_avatar', 
				'desc' => '<strong>Output</strong>: post author avatar
				<br /><br /><strong>attributes:</strong>
				<br /><strong>size</strong> = (number), avatar image size'
			),
			'post_excerpt' => array(
				'code' => '[post_excerpt wordlimit=""]', 
				'func' => 'post_excerpt', 
				'desc' => '<strong>Output</strong>: post excerpt/short description
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>wordlimit</strong> = (number), limit number of words to display'
			),
			'post_content'		=> array(
				'func' => 'post_content', 
				'desc' => '<strong>Output</strong>: post content'
			),
			'post_thumbnail' => array(
				'code' => '[post_thumbnail size=""]', 
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
			'attachment_thumbnail' => array(
				'code' => '[attachment_thumbnail size=""]', 
				'func' => 'attachment_thumbnail', 
				'desc' => '<strong>Output</strong>: if the post is an attachment, the attached image is displayed as thumbnail
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>size</strong> = (string), image size
				<br /><strong>width</strong> = (number), image width
				<br /><strong>height</strong> = (number), image height'
			),
			'attachment_url' => array(
				'func' => 'attachment_url', 
				'desc' => '<strong>Output</strong>:  if the post is an attachment, the attached image source is returned'
			),
			'post_meta' => array(
				'code' => '[post_meta key="" multiple="0"]', 
				'func' => 'post_meta', 
				'desc' => '<strong>Output</strong>: post meta value. if return value is an array, it will be migrated to string by using explode function
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>key</strong> = (text|number), meta key name
				<br /><strong>multiple</strong> = (0|1), display meta value at multiple occurence
				<br /><strong>sep</strong> = (text), separate array meta value into string'
			),
			'post_terms' => array(
				'code' => '[post_terms tax="category" sep=", "]', 
				'func' => 'post_terms', 
				'desc' => '<strong>Output</strong>: post type terms. if return value is an array, it will be migrated to string by using explode function
				<br /><br /><strong>Attributes:</strong>
				<br /><strong>tax</strong> = (string), taxonomy name
				<br /><strong>sep</strong> = (string), separate array meta value into string'
			),

			'title' => array(
				'func' => 'template_title', 
				'desc' => '<strong>Output</strong>: title template'
			),
			'meta' => array(
				'func' => 'template_meta', 
				'desc' => '<strong>Output</strong>: meta template'
			),
			'publish' => array(
				'func' => 'template_date', 
				'desc' => '<strong>Output</strong>: publish time template'
			),
			'date'				=> array(
				'func' => 'template_date', 
				'desc' => '<strong>Output</strong>: publish time template'
			),
			'modified' => array(
				'func' => 'template_modified', 
				'desc' => '<strong>Output</strong>: modified time template'
			),
			'author' => array(
				'func' => 'template_author', 
				'desc' => '<strong>Output</strong>: author template'
			),
			'excerpt' => array(
				'func' => 'template_excerpt', 
				'desc' => '<strong>Output</strong>: excerpt template'
			),
			'content' => array(
				'func' => 'template_content', 
				'desc' => '<strong>Output</strong>: content template'
			),
			'more' => array(
				'func' => 'template_more',
				'desc' => '<strong>Output</strong>: more link template'
			)
		);
	}

	function prepare( $list_id )
	{
		if( W4PL_SLUG != get_post_type($list_id) )
			return new WP_Error( 'postlist_not_found', 
			sprintf( __( 'List not found with id - %1$s', W4PL_TXT_DOMAIN ), $list_id ) );


		static $w4pl_loaded;
		if( !isset($w4pl_loaded) || !is_array($w4pl_loaded) )
			$w4pl_loaded = array();


		if( in_array($list_id, $w4pl_loaded) )
			return new WP_Error('list_loaded', 'A list can load only one.');

		$w4pl_loaded[] = $list_id;

		$this->id 				= $list_id;
		$this->query 			= array();
		$this->wp_query 		= '';
		$this->options 			= get_post_meta( $list_id, '_w4pl', true );
	}


	function display()
	{
		// push default options to query var
		foreach( array(
			'post_type', 
			'post_mime_type', 
			'post_status', 
			'orderby', 
			'order', 
			'posts_per_page'
		) as $option_name )
		{
			if( !empty($this->options[$option_name]) )
				$this->query[$option_name] = $this->options[$option_name];
		}

		// push default options to query var
		foreach( array(
			'post__in', 
			'post__not_in', 
			'post_parent__in', 
			'author__in',
		) as $option_name )
		{
			if( !empty($this->options[$option_name]) ){
				$opt = wp_parse_id_list($this->options[$option_name]);
				if( !empty($opt) )
					$this->query[$option_name] = $opt;
			}
		}

		// orderby meta key/value
		if( $this->options['orderby'] == 'meta_value' || $this->options['orderby'] == 'meta_value_num' )
		{
			$this->query['meta_key'] = $this->options['orderby_meta_key'];
		}

		# print_r($this->options);


		// build taxonoomy query
		$this->query['tax_query'] = array();
		foreach( $this->options as $option_name => $option_val )
		{
			if( !empty($option_val) && 0 === strpos($option_name, 'tax_query_') )
			{
				$this->query['tax_query'][] = array(
					'taxonomy' 			=> str_replace('tax_query_', '', $option_name),
					'terms' 			=> $option_val,
					'operator' 			=> 'IN',
					'field' 			=> 'term_id'
				);
			}
		}

		# print_r($this->query);

		// we catch paged query using a non-pretty query var
		$paged = isset($_REQUEST['page'. $this->id]) ? $_REQUEST['page'. $this->id] : 1;


		$defaults = array(
			'post_status' 	=> 'publish',
			'post_type' 	=> 'post',
			'paged'			=> $paged
		);

		$this->query = wp_parse_args( $this->query, $defaults );

		// while maximum limit is set, we only fetch till the maximum post
		if( isset($this->options['limit']) && !empty($this->options['limit']) && $this->options['limit'] < ($this->options['posts_per_page'] * $paged) )
		{
			$this->query['offset'] = ($paged - 1) * $this->options['posts_per_page'];
			$this->query['posts_per_page'] = $this->options['limit'] - ( $this->options['posts_per_page'] * ($paged-1) );
		}


		$this->wp_query = new WP_Query( $this->query );


		#print_r($this->wp_query->request);

		// the post loop template
		$template_loop = $this->options['template_loop'];
		// create attern based on available tags
		$pattern = $this->get_shortcode_regex();


		// the very first wrapper html
		#$return .= $this->options['template_before'];


		#$return .= $this->options['template_loop_before'];
		$the_loop = '';
		if( $this->wp_query->have_posts() ):
			while( $this->wp_query->have_posts() ): 
				$this->wp_query->the_post();
				$the_loop .= preg_replace_callback( "/$pattern/s", array(&$this, 'do_shortcode_tag'), $template_loop );
			endwhile;
		endif;
		#$return .= $this->options['template_loop_after'];


		$template = $this->options['template'];

		// parse navigation
		if( preg_match( "/\[nav(.*?)\]/", $template, $m) )
		{
			$nav = $this->get_navigation( shortcode_parse_atts($m[1]), null );
			$template = preg_replace( '/\[nav.*?\]/', $nav, $template );
		}

		// replace the loop content
		$template = str_replace( '[loop]', $the_loop, $template );


		// unique list class
		$class = trim('w4pl ' . $this->options['class']);

		if( !empty($this->options['css']) )
			$this->css .= str_replace( '[listid]', $this->id, $this->options['css'] );
		if( !empty($this->options['js']) )
			$this->js .= str_replace( '[listid]', $this->id, $this->options['js'] );

		$return  = '';

		// css push
		if( !empty($this->css) )
			$return .= '<style id="w4pl-css-'. $this->id .'" type="text/css">' . $this->css . '</style>' . "\n";


		// main template
		$return .= '<div id="w4pl-list-'. $this->id .'" class="'. $class .'"><div id="w4pl-inner-'. $this->id .'" class="w4pl-inner">';
		$return .= $template;
		$return .= '</div><!--#w4pl-inner-'. $this->id .'--></div><!--#w4pl-'. $this->id .'-->';


		// js push
		if( !empty($this->js) )
			$return .= "\n" . '<script id="w4pl-js-'. $this->id .'" type="text/javascript">' . $this->js . '</script>' . "\n";


		// reset postdata back to normal.
		wp_reset_postdata();


		// return the template
		return "<!--W4_Post_list_{$this->id}-->\n" . $return . "\n\n";
	}



	function get_navigation( $attr = array() )
	{
		$paged = isset($_REQUEST['page'. $this->id]) ? $_REQUEST['page'. $this->id] : 1;
		$return = '';

		if( $this->wp_query->max_num_pages > 1 )
		{
			if( isset($attr['type']) && 'plain' == $attr['type'] ){
				$big = 10;
				$max_num_pages = $this->wp_query->max_num_pages;
				if( isset($this->options['limit']) && !empty($this->options['limit']) ){
					if( $this->options['limit'] > $this->options['posts_per_page'] ){
						$max_pages = ceil($this->options['limit'] / $this->options['posts_per_page']);
					}
					if( $max_pages && $max_pages < $max_num_pages )
						$max_num_pages = $max_pages;
				}
				$return .= paginate_links( array(
					'type' 		=> 'plain',
					'base' 		=> '?page'. $this->id .'=%#%',
					'format' 	=> '?page'. $this->id .'=%#%',
					'current' 	=> $paged,
					'total' 	=> $max_num_pages,
					'end_size' 	=> 2,
					'mid_size' 	=> 2,
					'prev_text' => 'Previous',
					'next_text' => 'Next'
				));
			}

			elseif( isset($attr['type']) && 'list' == $attr['type'] )
			{
				$big = 10;
				$max_num_pages = $this->wp_query->max_num_pages;
				if( isset($this->options['limit']) && !empty($this->options['limit']) ){
					if( $this->options['limit'] > $this->options['posts_per_page'] ){
						$max_pages = ceil($this->options['limit'] / $this->options['posts_per_page']);
					}
					if( $max_pages && $max_pages < $max_num_pages )
						$max_num_pages = $max_pages;
				}
				$return .= paginate_links( array(
					'type' 		=> 'list',
					'base' 		=> '?page'. $this->id .'=%#%',
					'format' 	=> '?page'. $this->id .'=%#%',
					'current' 	=> $paged,
					'total' 	=> $max_num_pages,
					'end_size' 	=> 2,
					'mid_size' 	=> 2,
					'prev_text' => 'Previous',
					'next_text' => 'Next'
				));
			}

			else
			{
				$big = 10;
				$max_num_pages = $this->wp_query->max_num_pages;
				if( isset($this->options['limit']) && !empty($this->options['limit']) ){
					if( $this->options['limit'] > $this->options['posts_per_page'] ){
						$max_pages = ceil($this->options['limit'] / $this->options['posts_per_page']);
					}
					if( $max_pages && $max_pages < $max_num_pages )
						$max_num_pages = $max_pages;
				}

				if( $paged == 2 )
					$return .= '<a href="'. remove_query_arg(array('page'. $this->id)) .'" class="prev page-numbers">Prev</a>';
				elseif( $paged > 2 )
					$return .= '<a href="'. add_query_arg( 'page'. $this->id, ($paged - 1) ) .'" class="prev page-numbers">Prev</a>';

				if( $max_num_pages > $paged )
					$return .= '<a href="'. add_query_arg( 'page'. $this->id, ($paged + 1) ) .'" class="next page-numbers">Next</a>';
			}
		}

		if( !empty($return) )
		{
			$class = 'navigation';
			if( isset($attr['ajax']) && ( (bool) $attr['ajax'] ) ){
				$class .= ' ajax-navigation';

				$this->js .= '(function($){$(document).ready(function(){$("#w4pl-list-'. $this->id 
				. ' .navigation a.page-numbers").live("click", function(){var that = $(this), parent = $("#w4pl-list-'. $this->id 
				. '");parent.load( that.attr("href") + " #" + parent.attr("id") + " .w4pl-inner", function(e){});return false;});});})(jQuery) ;';

			}

			$return = '<div class="'. $class .'">'. $return . '</div>';
		}

		return $return;
	}

	function get_shortcode_regex()
	{
		$tagnames = array_keys( $this->get_shortcodes() );
		$tagregexp = join( '|', array_map('preg_quote', $tagnames) );
	
		return
			  '\\['                              // Opening bracket
			. '(\\[?)'                           // 1: Optional second opening bracket for escaping shortcodes: [[tag]]
			. "($tagregexp)"                     // 2: Shortcode name
			. '(?![\\w-])'                       // Not followed by word character or hyphen
			. '('                                // 3: Unroll the loop: Inside the opening shortcode tag
			.     '[^\\]\\/]*'                   // Not a closing bracket or forward slash
			.     '(?:'
			.         '\\/(?!\\])'               // A forward slash not followed by a closing bracket
			.         '[^\\]\\/]*'               // Not a closing bracket or forward slash
			.     ')*?'
			. ')'
			. '(?:'
			.     '(\\/)'                        // 4: Self closing tag ...
			.     '\\]'                          // ... and closing bracket
			. '|'
			.     '\\]'                          // Closing bracket
			.     '(?:'
			.         '('                        // 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags
			.             '[^\\[]*+'             // Not an opening bracket
			.             '(?:'
			.                 '\\[(?!\\/\\2\\])' // An opening bracket not followed by the closing shortcode tag
			.                 '[^\\[]*+'         // Not an opening bracket
			.             ')*+'
			.         ')'
			.         '\\[\\/\\2\\]'             // Closing shortcode tag
			.     ')?'
			. ')'
			. '(\\]?)';                          // 6: Optional second closing brocket for escaping shortcodes: [[tag]]
	}

	function do_shortcode_tag( $m )
	{
		if ( $m[1] == '[' && $m[6] == ']' ) {
			return substr($m[0], 1, -1);
		}
		$tag = $m[2];
		$attr = shortcode_parse_atts( $m[3] );
		if ( isset( $m[5] ) ){
			return $m[1] . apply_filters( 'w4pl/shortcode/'. $tag, $attr, '', $m[5] ) . $m[6];
		} else {
			return $m[1] . apply_filters( 'w4pl/shortcode/'. $tag, $attr, '', null ) . $m[6];
		}
	}


	// Callback Functions - Post
	function post_id($attr, $cont){ return get_the_ID(); }
	function post_permalink($attr, $cont){ return get_permalink(); }
	function post_class($attr, $cont){ return join( ' ', get_post_class() ); }
	function post_title($attr, $cont)
	{
		$return = get_the_title();
		if( isset($attr['wordlimit']) ){
			$wordlimit = $attr['wordlimit'];
			$return = wp_trim_words( $return, $wordlimit );
		}
		return $return;
	}

	function post_comment_url($attr, $cont){ return get_permalink() . "#comments"; }
	function post_comment_count($attr, $cont){ global $post; return (int) $post->comment_count; }

	function post_date($attr, $cont)
	{
		$format = get_option('date_format');
		if( isset($attr['format']) ){
			$format = $attr['format'];
		}
		return get_the_time($format);
	}
	function post_time($attr, $cont)
	{
		$format = get_option('time_format');
		if( isset($attr['format']) ){
			$format = $attr['format'];
		}
		return get_the_time($format);
	}
	function post_modified_date($attr, $cont)
	{
		$format = get_option('date_format');
		if( isset($attr['format']) ){
			$format = $attr['format'];
		}
		return get_post_modified_time($format);
	}
	function post_modified_time($attr, $cont)
	{
		$format = get_option('time_format');
		if( isset($attr['format']) ){
			$format = $attr['format'];
		}
		return get_post_modified_time($format);
	}

	function post_author_name($attr, $cont){ return get_the_author_meta('display_name'); }
	function post_author_url($attr, $cont){ return get_author_posts_url( get_the_author_meta('ID') ); }
	function post_author_avatar($attr, $cont)
	{
		$size = 32;
		if( isset($attr['size']) ){
			$size = $attr['size'];
		}
		return get_avatar( get_the_author_meta('user_email'), $size );
	}

	function post_excerpt($attr, $cont)
	{
		$post = get_post();
		$excerpt = $post->post_excerpt;
		if ( '' == $excerpt )
			$excerpt = $post->post_content;

		$excerpt = wp_strip_all_tags( $excerpt );

		if( isset($attr['wordlimit']) ){
			$wordlimit = $attr['wordlimit'];
			$excerpt = wp_trim_words( $excerpt, $wordlimit );
		}

		return $excerpt;
	}
	function post_content($attr, $cont)
	{
		global $post;
		// Post content without wrapper --
		$content = apply_filters( 'the_content', get_the_content() );
		$content = str_replace(']]>', ']]&gt;', $content);
		return $content;
	}
	function post_thumbnail($attr, $cont)
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
		else{
			$size = 'post-thumbnail';
		}

		$post_id = get_the_ID();
		$post_thumbnail_id = get_post_thumbnail_id( $post_id );

		if( isset($attr['return']) && 'id' == $attr['return'] ){
			return (int) $post_thumbnail_id;
		}
		elseif( isset($attr['return']) && 'src' == $attr['return'] ){
			$img = wp_get_attachment_image_src( $post_thumbnail_id );
			return isset($img[0]) ? $img[0] : '';
		}
		elseif ( $post_thumbnail_id ) {
			return wp_get_attachment_image( $post_thumbnail_id, $size );
		}

		return '';
	}

	function attachment_thumbnail($attr, $cont)
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
		else{
			$size = 'post-thumbnail';
		}

		if( isset($attr['id']) )
			$post_id = (int) $attr['id'];
		else
			$post_id = get_the_ID();


		if( 'attachment' != get_post_type($post_id) )
			return '';


		$icon = false;
		if( ! wp_attachment_is_image($post_id) )
			$icon = true;

		if ( $post_id ) {
			$html = wp_get_attachment_image( $post_id, $size, $icon );
		} else {
			$html = '';
		}

		return $html;
	}
	function attachment_url($attr, $cont)
	{
		if( isset($attr['id']) )
			$post_id = (int) $attr['id'];
		else
			$post_id = get_the_ID();

		if( 'attachment' != get_post_type($post_id) )
			return '';

		return wp_get_attachment_url($post_id);
	}

	function post_meta($attr, $cont)
	{
		if( isset($attr['key']) ){
			$meta_key = $attr['key'];
		}
		elseif( isset($attr['meta_key']) ){
			$meta_key = $attr['meta_key'];
		}
		if( ! $meta_key )
			return;

		$single = true;
		if( array_key_exists('multiple', $attr) ){
			$single = ! ( (bool) $attr['multiple'] );
		}

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
				$return = implode($sep, $new);
			else
				$return = '';
		}

		return $return;
	}
	function post_terms($attr, $cont)
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

	// Tempate functions - Post
	function template_title($attr, $cont){
		return sprintf( 
			'<a class="post_title w4pl_post_title" href="%1$s" title="View %2$s">%2$s</a>', 
			get_permalink(), 
			get_the_title() 
		);
	}
	function template_meta($attr, $cont){
		return sprintf( 
			__("Posted on:", W4PL_TXT_DOMAIN). ' <abbr class="published post-date" title="%1$s">%2$s</abbr> <span class="post_author">by %3$s</span>', 
			get_the_time( get_option('time_format') ), 
			get_the_time( get_option('date_format') ), 
			get_the_author()
		);
	}
	function template_date($attr, $cont){
		return sprintf( 
			'<abbr class="published post-date" title="%1$s"><strong>' . __(" Published:", W4PL_TXT_DOMAIN).'</strong> %2$s</abbr>',
			get_the_time( get_option('time_format') ), 
			get_the_time( get_option('date_format') )
		);
	}
	function template_modified($attr, $cont){
		return sprintf( 
			'<abbr class="modified post-modified" title="%1$s"><strong>' . __( "Updated:", W4PL_TXT_DOMAIN ) . '</strong> %2$s</abbr>',
			get_post_modified_time( get_option('time_format')), 
			get_post_modified_time( get_option('date_format'))
		);
	}
	function template_author($attr, $cont){
		return sprintf( 
			'<a href="%1$s" title="View all posts by %2$s" rel="author">%2$s</a>', 
			get_author_posts_url( get_the_author_meta('ID') ), 
			get_the_author() 
		);
	}
	function template_excerpt($attr, $cont){
		return sprintf( 
			'<div class="post-excerpt">%s</div>',
			$this->post_excerpt($attr, $cont)
		);
	}
	function template_content($attr, $cont){
		return sprintf( 
			'<div class="post-excerpt">%s</div>',
			$this->post_content($attr, $cont)
		);
	}
	function template_more($attr, $cont){
		$read_more = !empty( $attr['text'] ) ? $attr['text'] : __( 'Continue reading &raquo;', W4PL_TXT_DOMAIN );
		return sprintf( 
			'<a class="read_more" href="%1$s" title="Cotinue reading %2$s">%3$s</a>', 
			get_permalink(), 
			get_the_title(), 
			$read_more 
		);
	}
}


?>