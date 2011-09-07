<?php
// Post list Class W4PL_CORE @

class W4_Post_list {
	
	var $options = array();
	var $post_template_fields = array();

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
		
		// Shortcode template fields # tag id => Callback
		$this->post_template_fields = array(
			'title'				=> 'title',
			'meta'				=> 'meta',
			'publish'			=> 'publish',
			'date'				=> 'publish',
			'modified'			=> 'modified',
			'author'			=> 'author',
			'excerpt'			=> 'excerpt',
			'content'			=> 'content',
			'more' 				=> 'more',

			'id' 				=> 'post_id',
			'ID' 				=> 'post_id',
			'link'				=> 'post_permalink',
			'post_permalink'	=> 'post_permalink',
			'post_title'		=> 'post_title',
			'post_date'			=> 'post_date',
			'post_date_time'	=> 'post_date_time',
			'post_modified'		=> 'post_modified',
			'post_modified_time'=> 'post_modified_time',
			'post_author'		=> 'post_author',
			'post_author_url'	=> 'post_author_url',
			'post_excerpt'		=> 'post_excerpt',
			'post_content'		=> 'post_content'
		);

		$this->category_template_fields = array( 'category_title', 'category_count', 'category_posts');
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
		return preg_replace( '/\%\%postlist\%\%/', $postlist, $this->template['wrapper'] );
	}
	
	function generate_category_list(){

		$output = '';
		foreach( (array)$this->categories as $cat_id => $cat_option){
			// Categort template..
			$category_template = $this->category_template( $cat_id);

			if( !$category_template )
				continue;

			$output .= $category_template;
		}
		return preg_replace( '/\%\%catloop\%\%/', $output, $this->template['wrapper_category'] );
	}

	function category_template( $cat_id ){
		$this->category_obj = get_category( $cat_id);
		if( !$this->category_obj )
			return false;

		$this->category_class = '';
		$this->category_link_class = '';
		$this->show_count = false;
		$this->show_post_list = false;
		$this->list_has_effect = false;
		$this->query = array();
		$this->cat_template = $this->template['loop_category'];
		$this->cat_max = intval( $this->categories[$this->category_obj->term_id]["max"]) > 0 ? intval($this->categories[$this->category_obj->term_id]["max"]) : '-1';

		if( 'pc' == $this->list_type && count( $this->categories[$this->category_obj->term_id]['post_ids']) > 0)
			$this->show_post_list = true;

		if( 'pc' == $this->list_type && in_array( $this->list_effect, array( 'extended', 'yes')))
			$this->list_has_effect = true;

		if( 'all' == $this->list_option['show_category_posts_count'] && $this->category_obj->count > 0 )
			$this->show_count = true;

		$post_order = w4pl_sanitize_post_order_method( $this->categories[$this->category_obj->term_id]['post_order_method']);
		$this->query = array(
			'post__in' 			=> $this->categories[$this->category_obj->term_id]['post_ids'],
	#		'post__not_in' 		=> $this->categories[$this->category_obj->term_id]['posts_not_in'],
			'cat' 				=> $this->category_obj->term_id,
			'order' 			=> $post_order['order'],
			'orderby' 			=> $post_order['orderby'],
			'posts_per_page'	=> $this->cat_max,
			'showposts'			=> $this->cat_max
		);

		foreach ( $this->category_template_fields as $field ){
			if( preg_match( "/\%\%{$field}\%\%/", $this->cat_template ))
				$this->cat_template = preg_replace( "/\%\%{$field}\%\%/", $this->$field(), $this->cat_template );
		}

		return $this->cat_template;
	}

	function category_posts(){
		if( $this->show_post_list ){
			$postlist =  $this->generate_posts_list();

			if( 'yes' == $this->list_effect )
				$postlist = "<div class='category_posts'>" . $postlist . "</div>";
					
			elseif( 'extended' == $this->list_effect )
				$postlist = "<div class='category_posts' style='display:none;'>" . $postlist . "</div>";
					
			return $postlist;
		}
		return '';
	}

	function category_title(){
		if( $this->list_has_effect ){
			$this->category_link_class = "list_effect_enabled";

			if( $this->show_post_list ){
				$this->category_link_class .= " category_effect_handler";

				if( 'extended' == $this->list_effect )
					$this->category_link_class .= " list_closed";
			}
		}

		return sprintf( '<a class="%1$s" alt="%3$s" href="%2$s" title="'. __( 'View posts from %3$s', 'w4-post-list' ) .'">%3$s</a>',
		$this->category_link_class, get_category_link( $this->category_obj->term_id ), $this->category_obj->name );
	}

	function category_count(){
		if( $this->show_count )
			return '<abbr class="item_count" title="'. sprintf( '%1$s '.__( 'Posts in', 'w4-post-list').' %2$s', $this->category_obj->count,
			$this->category_obj->name ) .'">('. $this->category_obj->count .')</abbr>';
	}

	function generate_posts_list(){
		#global $post, $wp_query;

		#$old_post = $post;
		#$old_wp_query = $wp_query;

		$defaults = array( 'post_status' => 'publish', 'post_type' => 'post' );
		$this->query = wp_parse_args((array) $this->query, $defaults );

		#query_posts( $this->query );
		$query = new WP_Query( $this->query );
		$postloop = '';
		//Checking post
		if( $query->have_posts()):
			while( $query->have_posts()): $query->the_post();
				$postloop .= call_user_func( array( &$this, 'post_template' ));
			endwhile;
			#wp_reset_query();
		endif; //End-if( have_posts()):
		wp_reset_postdata();
		
		#$post = $old_post;
		#$wp_query = $old_wp_query;

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

	// Post template tag parser
	function post_id(){
		return get_the_ID();
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

	function title(){
		return sprintf( '<a class="w4pl_post_title" href="%1$s" title="View %2$s">%2$s</a>', '%%post_permalink%%', '%%post_title%%' );
	}

	function meta(){
		return sprintf( 'Posted on <abbr class="published post_date" title="%2$s">%3$s</abbr> <span class="post_author">by %1$s</span>', '%%author%%', '%%post_date_time%%', '%%post_date%%' );
	}

	function publish(){
		return sprintf( '<abbr class="published post_date" title="%2$s"><strong>' . __(" Published:", "w4-post-list").'</strong> %1$s</abbr>',
		'%%post_date%%', '%%post_date_time%%' );
	}

	function modified(){
		return sprintf( '<abbr class="modified post_modified" title="%2$s"><strong>' . __( "Updated:", "w4-post-list" ) . '</strong> %1$s</abbr>',
		'%%post_modified%%', '%%post_modified_time%%' );
	}

	function author(){
		return sprintf( '<a href="%1$s" title="View all posts by %2$s" rel="author">%2$s</a>', '%%post_author_url%%', '%%post_author%%' );
	}

	function excerpt(){
		return "<div class=\"post_excerpt\">%%post_excerpt%%</div>";
	}

	function content(){
		// Post content--
		return "<div class=\"post_content\">%%post_content%%</div>";
	}

	function more(){
		$read_more = !empty( $this->read_more ) ? $this->read_more : __( 'Continue reading &raquo;', 'w4-post-list' );
		return sprintf( '<a href="%1$s" title="Cotinue reading %2$s">%3$s</a>', '%%post_permalink%%', '%%post_title%%',  $read_more );
	}
}
//use function w4_post_list() as template tag to show a post list anywhere in your theme
$w4_post_list = new W4_Post_list();
?>