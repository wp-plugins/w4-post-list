<?php
// Post list Class W4PL_CORE @

class W4_Post_list {
	
	var $options = array();
	var $post_template_fields = array();
	var $category_template_fields = array();

	function __construct(){
		// Shortcode template fields # tag id => Callback
		$this->post_template_fields = array(
			'title'				=> 'template_title',
			'meta'				=> 'template_meta',
			'publish'			=> 'template_publish',
			'date'				=> 'template_publish',
			'modified'			=> 'template_modified',
			'author'			=> 'template_author',
			'excerpt'			=> 'template_excerpt',
			'content'			=> 'template_content',
			'more' 				=> 'template_more',

			'id' 				=> 'post_id',
			'post_comment_url'	=> 'post_comment_url',
			'ID' 				=> 'post_id',
			'link'				=> 'post_permalink',
			'post_permalink'	=> 'post_permalink',
			'post_title'		=> 'post_title',
			'post_date'			=> 'post_date',
			'post_date_time'	=> 'post_date_time',
			'post_modified'		=> 'post_modified',
			'post_modified_time'=> 'post_modified_time',
			'post_comment_count'=> 'post_comment_count',
			'post_author'		=> 'post_author',
			'post_author_url'	=> 'post_author_url',
			'post_excerpt'		=> 'post_excerpt',
			'post_content'		=> 'post_content'
		);

		$this->category_template_fields = array( 
			'category_title'	=> 'template_category_title',
			'category_count'	=> 'template_category_count',
			'category_posts'	=> 'template_category_posts',

			'cat_desc'			=> 'cat_desc',
			'cat_link'			=> 'cat_link',
			'cat_count'			=> 'cat_count',
			'cat_name'			=> 'cat_name'
		);
		
		$this->default_template = array( 
			'wrapper' 			=> "<div class='w4_post_list'>\n%%postlist%%\n</div>",
			'wrapper_post'		=> "<ul>\n%%postloop%%\n</ul>",
			'loop_post' 		=> "<li>\n%%title%%\n%%publish%%\n%%modified%%\n%%excerpt%%\n%%more%%\n</li>",
			'wrapper_category'	=> "<ul>\n%%catloop%%\n</ul>",
			'loop_category'		=> "<li>\n%%category_title%%\n%%category_count%%\n%%category_posts%%\n</li>"
		);
	}

	function prepare( $list_id ){

		if( ! w4pl_get_list( $list_id ))
			return new WP_Error( 'postlist_not_found', 
			sprintf( __( 'List not found with [%1$s]. <a class="button" href="%2$s">Create a new list</a></p>',	'w4-post-list' ), $list_id, w4pl_add_url()));

		$this->id 				= $list_id;
		$this->query 			= array();
		$this->options 			= w4pl_get_list( $this->id);
		$this->list_option 		= $this->options['list_option'];

		// List Options
		$this->list_type 		= $this->list_option['list_type'];
		$this->categories 		= $this->list_option['categories'];

		if( in_array( $this->list_type, array( 'oc', 'pc' )) && !$this->categories )
				return new WP_Error( 'no_attribute', __( 'No category selected. Please select one to show here.', 'w4-post-list' ));

		elseif( in_array( $this->list_type, array( 'op', 'op_by_cat' )) && count( $this->list_option['post_ids'] ) < 1 )
				return new WP_Error( 'no_attribute', __( 'No post selected. Please select one to show here.', 'w4-post-list' ));

		$this->list_effect 		= $this->list_option['list_effect'];
		$this->read_more	 	= $this->list_option['read_more_text'];
		$this->excerpt_length 	= $this->list_option['excerpt_length'];

		// List Template
		$this->template 			= $this->list_option['html_template'];
	}

	function display(){

		if( in_array( $this->list_type, array( 'oc', 'pc' ))){
			$postlist = $this->generate_category_list();
		}

		elseif( in_array( $this->list_type, array( 'op', 'op_by_cat' ))){

			$post_order = w4pl_sanitize_post_order_method( $this->list_option['post_order_method']);
			$this->query = array(
				'post__in' 			=> $this->list_option['post_ids'],
				'order' 			=> $post_order['order'],
				'orderby' 			=> $post_order['orderby'],
				'posts_per_page'	=> intval( $this->list_option["post_max"]) > 0 ? intval( $this->list_option["post_max"]) : '-1'
			);
			$this->query['showposts'] = $this->query['posts_per_page'];

			$postlist = $this->generate_posts_list();
		}
		return "<!-- Post list ID - $this->id-->\n" . preg_replace( '/\%\%postlist\%\%/', $postlist, $this->template['wrapper'] );
	}


	function generate_category_list(){

		$output = '';
		foreach( (array)$this->categories as $cat_id => $cat_option ){
			// Categort template..
			$category_template = $this->category_template( $cat_id );

			if( !$category_template )
				continue;

			$output .= $category_template;
		}
		return preg_replace( '/\%\%catloop\%\%/', $output, $this->template['wrapper_category'] );
	}

	function category_template( $cat_id ){
		$this->category_obj = get_category( $cat_id );

		#print_r( $this->category_obj );

		if( !$this->category_obj )
			return false;

		$this->category_class = '';
		$this->category_link_class = '';
		$this->show_post_list = false;
		$this->list_has_effect = false;
		$this->query = array();

		$this->cat_max = intval( $this->categories[$this->category_obj->term_id]["max"] ) > 0 ? intval($this->categories[$this->category_obj->term_id]["max"]) : '-1';

		if( 'pc' == $this->list_type && count( $this->categories[$this->category_obj->term_id]['post_ids']) > 0)
			$this->show_post_list = true;

		if( 'pc' == $this->list_type && in_array( $this->list_effect, array( 'extended', 'yes' )))
			$this->list_has_effect = true;

		$template = $this->template['loop_category'];
		foreach ( $this->category_template_fields as $field => $callback ){
			if( preg_match( "/\%\%{$field}\%\%/", $template ) && is_callable( array( &$this, $callback )))
				$template = preg_replace( "/\%\%{$field}\%\%/", call_user_func( array( &$this, $callback )), $template );
		}

		return $template;
	}

	// Category template tag parsers
	function cat_count(){
			return (int) $this->category_obj->count;
	}

	function cat_name(){
			return (string) $this->category_obj->name;
	}

	function cat_desc(){
			return !empty( $this->category_obj->category_description ) ? trim( $this->category_obj->category_description ) : '';
	}

	function cat_link(){
			return get_category_link( $this->category_obj->term_id );
	}

	function template_category_posts(){
		if( $this->show_post_list ){

			$post_order = w4pl_sanitize_post_order_method( $this->categories[$this->category_obj->term_id]['post_order_method']);
			$this->query = array(
				'post__in' 			=> $this->categories[$this->category_obj->term_id]['post_ids'],
				'cat' 				=> $this->category_obj->term_id,
				'order' 			=> $post_order['order'],
				'orderby' 			=> $post_order['orderby'],
				'posts_per_page'	=> $this->cat_max,
				'showposts'			=> $this->cat_max
			);

			$postlist =  $this->generate_posts_list();

			if( 'yes' == $this->list_effect )
				$postlist = "<div class='term_posts' id='term_posts_".$this->category_obj->term_id."'>" . $postlist . "</div>";

			elseif( 'extended' == $this->list_effect )
				$postlist = "<div class='term_posts' id='term_posts_".$this->category_obj->term_id."' style='display:none;'>" . $postlist . "</div>";

			return $postlist;
		}
		return '';
	}

	function template_category_title(){
		if( $this->list_has_effect ){
			$this->category_link_class = "list_effect_enabled";

			if( $this->show_post_list ){
				$this->category_link_class .= " list_effect_handler";

				if( 'extended' == $this->list_effect )
					$this->category_link_class .= " list_inactive";
				
				else
					$this->category_link_class .= " list_active";
			}
		}

		return sprintf( '<a class="%1$s" alt="%3$s" ref="%4$s" href="%2$s" title="'. __( 'View posts from %3$s', 'w4-post-list' ) .'">%3$s</a>',
		$this->category_link_class, '%%cat_link%%', '%%cat_name%%', $this->category_obj->term_id );
	}

	function template_category_count(){
			return '<abbr class="item_count" title="'. sprintf( '%1$s '.__( 'Posts in', 'w4-post-list').' %2$s', '%%cat_count%%',
			'%%cat_name%%' ) .'">(%%cat_count%%)</abbr>';
	}

	function generate_posts_list(){

		$defaults = array( 'post_status' => 'publish', 'post_type' => 'post' );
		$this->query = wp_parse_args((array) $this->query, $defaults );

		$query = new WP_Query( $this->query );
		$postloop = '';
		
		//Checking post
		if( $query->have_posts()):
			while( $query->have_posts()): $query->the_post();
				$postloop .= call_user_func( array( &$this, 'post_template' ));
			endwhile;
		endif; //End-if( have_posts()):
		
		// Reset postdata back to normal.
		wp_reset_postdata();
		
		return preg_replace( '/\%\%postloop\%\%/', $postloop, $this->template['wrapper_post'] );
	}

	
	function post_template(){

		$template = $this->template['loop_post'];
		foreach ( $this->post_template_fields as $field => $callback ){
			if( preg_match( "/\%\%{$field}\%\%/", $template ) && is_callable( array( &$this, $callback )))
				$template = preg_replace( "/\%\%{$field}\%\%/", call_user_func( array( &$this, $callback )), $template );
		}

		return $template;
	}

	// Callable Basic functions - Post
	function post_id(){
		return get_the_ID();
	}

	function post_comment_url(){
		return "%%post_permalink%%#comments";
	}

	function post_permalink(){
		return get_permalink();
	}

	function post_title(){
		return the_title( '','',false );
	}

	function post_date(){
		return get_the_time('j-m-Y');
	}

	function post_date_time(){
		return get_the_time('g:i a');
	}

	function post_modified(){
		return get_post_modified_time('j-m-Y');
	}

	function post_modified_time(){
		return get_post_modified_time('g:i a');
	}

	function post_comment_count(){
		global $post;
		return (int) $post->comment_count;
	}

	function post_author(){
		return get_the_author();
	}

	function post_author_url(){
		return get_author_posts_url( get_the_author_meta( 'ID' ));
	}

	function post_excerpt(){
		global $post;

		// Post excerpt without wrapper --
		$excerpt = $post->post_excerpt;

		if ( '' == $excerpt )
			$excerpt = $post->post_content;

		$excerpt = wp_strip_all_tags( $excerpt );
		$excerpt = w4pl_trim_excerpt( $excerpt, $this->excerpt_length );

		return $excerpt;
	}

	function post_content(){
		global $post;
		// Post content without wrapper --
		/* Not sure why this dont work. if anyone can get the below 3 line works, please contact me.
			$content = apply_filters( 'the_content', get_the_content());
			$content = str_replace(']]>', ']]&gt;', $content);
			$content = "<div class=\"post_content\">" . $content . "</div>";;
		*/

		# $content = "<div class=\"post_content\">" . wpautop( get_the_content()) . "</div>";

		$content = apply_filters( 'the_content', get_the_content());
		$content = str_replace(']]>', ']]&gt;', $content);

		return $content;
	}

	// Tempate functions - Post
	function template_title(){
		return sprintf( '<a class="w4pl_post_title" href="%1$s" title="View %2$s">%2$s</a>', '%%post_permalink%%', '%%post_title%%' );
	}

	function template_meta(){
		return sprintf( 'Posted on <abbr class="published post_date" title="%2$s">%3$s</abbr> <span class="post_author">by %1$s</span>', '%%author%%', '%%post_date_time%%', '%%post_date%%' );
	}

	function template_publish(){
		return sprintf( '<abbr class="published post_date" title="%2$s"><strong>' . __(" Published:", "w4-post-list").'</strong> %1$s</abbr>',
		'%%post_date%%', '%%post_date_time%%' );
	}

	function template_modified(){
		return sprintf( '<abbr class="modified post_modified" title="%2$s"><strong>' . __( "Updated:", "w4-post-list" ) . '</strong> %1$s</abbr>',
		'%%post_modified%%', '%%post_modified_time%%' );
	}

	function template_author(){
		return sprintf( '<a href="%1$s" title="View all posts by %2$s" rel="author">%2$s</a>', '%%post_author_url%%', '%%post_author%%' );
	}

	function template_excerpt(){
		return "<div class=\"post_excerpt\">%%post_excerpt%%</div>";
	}

	function template_content(){
		// Post content--
		return "<div class=\"post_content\">%%post_content%%</div>";
	}

	function template_more(){
		$read_more = !empty( $this->read_more ) ? $this->read_more : __( 'Continue reading &raquo;', 'w4-post-list' );
		return sprintf( '<a href="%1$s" title="Cotinue reading %2$s">%3$s</a>', '%%post_permalink%%', '%%post_title%%',  $read_more );
	}
}
//use function w4_post_list() as template tag to show a post list anywhere in your theme
$w4_post_list = new W4_Post_list();
?>