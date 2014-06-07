<?php
class W4_Post_list
{
	function __construct()
	{
	}

	function add_filters()
	{
		$shortcodes = apply_filters( 'w4pl/get_shortcodes', array() );
		foreach( $shortcodes as $tag => $attr ){
			add_filter( 'w4pl/shortcode/'. $tag, array(&$this, $attr['func']), 10, 2 );
		}
	}

	function remove_filters()
	{
		$shortcodes = apply_filters( 'w4pl/get_shortcodes', array() );
		foreach( $shortcodes as $tag => $attr ){
			remove_filter( 'w4pl/shortcode/'. $tag, array(&$this, $attr['func']), 10, 2 );
		}
	}


	function get_shortcode_regex()
	{
		$tagnames = array_keys( apply_filters( 'w4pl/get_shortcodes', array() ) );
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
		if ( $m[1] == '[' && $m[6] == ']' )
			return substr($m[0], 1, -1);

		$tag = $m[2];
		$attr = shortcode_parse_atts( $m[3] );

		$shortcodes = apply_filters( 'w4pl/get_shortcodes', array() );
		$callback = $shortcodes[$tag]['func'];

		$content = isset( $m[5] ) ? $m[5] : null;

		if( !empty($callback) )
			return $m[1] . call_user_func( array(&$this, $shortcodes[$tag]['func']), $attr, $content ) . $m[6];
		else
			return $m[1] . $content . $m[6];
	}



	function prepare( $options )
	{
		static $w4pl_lists;
		if( !isset($w4pl_lists) || !is_array($w4pl_lists) )
			$w4pl_lists = array();

		if( !isset($options['id']) )
			return new WP_Error('error', 'Invalid list id');

		if( in_array($options['id'], $w4pl_lists) )
			return new WP_Error('list_loaded', 'A list can load only one.');

		$w4pl_lists[] = $options['id'];

		$this->options 			= $options;
		$this->id 				= $this->options['id'];

		$this->terms_args 		= array();
		$this->terms_query 		= array();
		$this->current_term 	= '';


		$this->posts_query 		= array();
		$this->posts 			= array();
		$this->current_post		= '';

		$this->wp_query 		= array();
		$this->groups 			= array();


		$this->css  			= '';
		$this->js  				= '';
		$this->html 			= '';


		// let helper class extend/modify this class
		do_action_ref_array( 'w4pl/pre_get_list', array( &$this ) );
	}


	function display()
	{
		// create attern based on available tags
		$pattern = $this->get_shortcode_regex();

		// main template
		$template = $this->options['template'];
		$terms_template = '';
		$posts_template = '';
		$groups_template = '';
		$template_nav = '';

		// match [groups]
		if( preg_match('/\[terms\](.*?)\[\/terms\]/sm', $template, $terms_match) )
		{
			$terms_template = $terms_match['1'];
		}
		#print_r($groups_template);
		// match the loop template [posts]
		if( preg_match('/\[posts\](.*?)\[\/posts\]/sm', $template, $posts_match) )
		{
			$posts_template = $posts_match['1'];
		}
		// match [groups]
		if( preg_match('/\[groups\](.*?)\[\/groups\]/sm', $template, $groups_match) )
		{
			$groups_template = $groups_match['1'];
		}
		// parse navigation
		if( preg_match( "/\[nav(.*?)\]/", $template, $nav_match) )
		{
			$template_nav = $nav_match[0];
		}


		// posts & terms.posts
		if( in_array($this->options['list_type'], array('posts', 'terms.posts') ) )
		{
			// push default options to query var
			foreach( array(
				'post_type', 
				'orderby', 
				'order', 
				'offset',
				'posts_per_page'
			) as $option_name )
			{
				if( !empty($this->options[$option_name]) )
					$this->posts_query[$option_name] = $this->options[$option_name];
			}

			// array
			foreach( array(
				'post_mime_type', 
				'post_status'
			) as $option_name )
			{
				if( !empty($this->options[$option_name]) )
					$this->posts_query[$option_name] = $this->options[$option_name];
			}


			// comma separated ids
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
						$this->posts_query[$option_name] = $opt;
				}
			}

			// orderby meta key/value
			if( $this->options['orderby'] == 'meta_value' || $this->options['orderby'] == 'meta_value_num' )
			{
				$this->posts_query['meta_key'] = $this->options['orderby_meta_key'];
			}

			// we catch paged query using a non-pretty query var
			$paged = isset($_REQUEST['page'. $this->id]) ? $_REQUEST['page'. $this->id] : 1;

			$defaults = array(
				'post_status' 	=> 'publish',
				'post_type' 	=> 'post',
				'paged'			=> $paged
			);

			$this->posts_query = wp_parse_args( $this->posts_query, $defaults );

			// while maximum limit is set, we only fetch till the maximum post
			if( isset($this->options['limit']) && !empty($this->options['limit']) && $this->options['limit'] < ($this->options['posts_per_page'] * $paged) )
			{
				$this->posts_query['offset'] = (int) $this->options['offset'] + ($paged - 1) * $this->options['posts_per_page'];
				$this->posts_query['posts_per_page'] = $this->options['limit'] - ( $this->options['posts_per_page'] * ($paged-1) );
			}
		}
		// ends post query



		// terms
		if( in_array($this->options['list_type'], array('terms', 'terms.posts') ) )
		{
			// push default options to query var
			foreach( array(
				'terms_count__min'			=> 'count__min',
				'terms_name__like'			=> 'name__like',
				'terms_slug__like'			=> 'slug__like',
				'terms_description__like'	=> 'description__like',
				'terms_offset'				=> 'offset',
				'terms_limit'				=> 'limit',
				'terms_orderby'				=> 'orderby',
				'terms_order'				=> 'order'
			) as $option => $name )
			{
				if( !empty($this->options[$option]) )
					$this->terms_args[$name] = $this->options[$option];
			}
			#echo '<pre>'; print_r($this->options); echo '</pre>';

			// comma separated ids
			foreach( array(
				'terms__in' 		=> 'term_id__in',
				'terms__not_in' 	=> 'term_id__not_in'
			) as $option => $name )
			{
				if( !empty($this->options[$option]) )
				{
					$opt = wp_parse_id_list( $this->options[$option] );
					if( !empty($opt) )
						$this->terms_args[$name] = $opt;
				}
			}

			$this->terms_args['taxonomy'] = $this->options['terms_taxonomy'];

			$paged = isset($_REQUEST['page'. $this->id]) ? $_REQUEST['page'. $this->id] : 1;

			if( !empty($this->options['terms_limit']) ){
				$this->terms_args['offset'] = (int) $this->options['terms_offset'] + ($paged - 1) * $this->options['terms_limit'];
			}
			if( isset($this->options['terms_max']) && !empty($this->options['terms_max']) && $this->options['terms_max'] < ($this->options['terms_limit'] * $paged) )
			{
				$this->terms_args['limit'] = $this->options['terms_max'] - ( $this->options['terms_limit'] * ($paged-1) );
			}
			// terms query var ends


			$this->terms_query = new W4PL_Terms_Query( $this->terms_args );
			$this->terms_query->query();


			#echo '<pre>'; print_r($this->terms_query); echo '</pre>';
			#$this->terms_query = get_terms( $this->options['terms_taxonomy'], $this->terms_args );


			$terms_loop = '';
			foreach( $this->terms_query->get_results() as $term )
			{
				$terms_template_clone = $terms_template; // clone the group template
				$term_posts_loop = '';

				$this->current_term = $term;

				// term posts
				if( in_array($this->options['list_type'], array('terms.posts') ) )
				{
					$this->posts_query['paged'] = 1;
					$this->posts_query['posts_per_page'] = isset($this->options['limit']) ? (int) $this->options['limit'] : -1;
					$this->posts_query['tax_query'] = array(
						'relation' => 'OR',
						array(
							'taxonomy' 	=> $this->options['terms_taxonomy'],
							'field' 	=> 'term_id',
							'terms' 	=> $this->current_term->term_id,
						)
					);
					$this->wp_query = new WP_Query( $this->posts_query );

					#echo '<pre>'; print_r($this->posts_query); echo '</pre>';


					// post loop
					if( $this->wp_query->have_posts() )
					{
						while( $this->wp_query->have_posts() )
						{
							$this->wp_query->the_post();
							$term_posts_loop .= preg_replace_callback( "/$pattern/s", array(&$this, 'do_shortcode_tag'), $posts_template );
						}
					}

					// reset postdata back to normal.
					wp_reset_postdata();

				} // end term posts


				// replace [posts]
				$terms_template_clone = str_replace( $posts_match['0'], $term_posts_loop, $terms_template_clone );


				$terms_loop .= preg_replace_callback( "/$pattern/s", array(&$this, 'do_shortcode_tag'), $terms_template_clone );
			}

			// replace [terms]
			$template = str_replace( $terms_match[0], $terms_loop, $template );


			// replace [nav]
			if( !empty($template_nav) )
			{
				if( isset($this->options['terms_max']) && !empty($this->options['terms_max']) && $this->options['terms_max'] < ($this->options['terms_limit'] * $paged) )
					$max_num_pages = $paged;
				else
					$max_num_pages = $this->terms_query->max_num_pages;

				$navigation = $this->navigation( $max_num_pages, $paged, '?page'. $this->id .'=%#%', shortcode_parse_atts($nav_match[1]) );
				$template = str_replace( $nav_match[0], $navigation, $template );
			}
		}


		// posts
		if( in_array($this->options['list_type'], array('posts') ) )
		{

			// let helper class extend/modify this class
			do_action_ref_array( 'w4pl/parse_query', array( &$this ) );

			#echo '<pre>'; print_r($template); echo '</pre>';


			$this->wp_query = new WP_Query( $this->posts_query );

			# echo '<pre>'; print_r($this->wp_query); echo '</pre>';

			if( isset($this->options['groupby']) && !empty($this->options['groupby']) && !empty($groups_template) )
			{
				$this->init_groups();
			}
			elseif( !empty($groups_template) )
			{
				// remove the group block
				$template = str_replace( $groups_match[0], $posts_match['0'], $template );
			}


			if( $this->wp_query->have_posts() ):
				if( !empty($this->groups) )
				{
					$groups_loop = '';
					foreach( $this->groups as $group)
					{
						$group_posts_loop = '';
						$groups_template_clone = $groups_template; // clone the group template

						// post loop
						while( $this->wp_query->have_posts() )
						{
							$this->wp_query->the_post();
							if( in_array( get_the_ID(), $group['post_ids']) ){
								$group_posts_loop .= preg_replace_callback( "/$pattern/s", array(&$this, 'do_shortcode_tag'), $posts_template );
							}
						}

						// replace [posts]
						$groups_template_clone = str_replace( $posts_match['0'], $group_posts_loop, $groups_template_clone );

						// replace groups atribute
						$groups_template_clone = str_replace( "[group_title]", $group['title'], $groups_template_clone );
						$groups_template_clone = str_replace( "[group_url]", $group['url'], $groups_template_clone );

						$groups_loop .= $groups_template_clone;
					}

					// replace [groups]
					$template = str_replace( $groups_match[0], $groups_loop, $template );
				}
				else
				{
					$posts_loop = '';
					// post loop
					while( $this->wp_query->have_posts() )
					{
						$this->wp_query->the_post();
						$posts_loop .= preg_replace_callback( "/$pattern/s", array(&$this, 'do_shortcode_tag'), $posts_template );
					}

					// replace [posts]
					$template = str_replace( $posts_match[0], $posts_loop, $template );
				}
			endif;


			// reset postdata back to normal.
			wp_reset_postdata();


			// replace [nav]
			if( !empty($template_nav) )
			{
				if( isset($this->options['limit']) && !empty($this->options['limit']) && (int) $this->options['limit'] < ($this->options['posts_per_page'] * $paged) )
					$max_num_pages = $paged;
				else
					$max_num_pages = $this->wp_query->max_num_pages;

				$navigation = $this->navigation( $max_num_pages, $paged, '?page'. $this->id .'=%#%', shortcode_parse_atts($nav_match[1]) );
				$template = str_replace( $nav_match[0], $navigation, $template );
			}

		} // end posts



		$return  = '';

		// main template
		$return .= '<div id="w4pl-list-'. $this->id .'"><div id="w4pl-inner-'. $this->id .'" class="w4pl-inner">';
		$return .= $template;
		$return .= '</div><!--#w4pl-inner-'. $this->id .'--></div><!--#w4pl-'. $this->id .'-->';


		$this->html = $return;


		// let helper class extend/modify this class
		do_action_ref_array( 'w4pl/parse_html', array( &$this ) );

		// return the template
		return "<!--W4_Post_list_{$this->id}-->\n" . $this->html . "\n\n";
	}


	function navigation( $max_num_pages, $paged = 1, $base = '', $attr = array() )
	{
		$pageq_var = 'page'. $this->id;
		$use_ajax = isset($attr['ajax']) ? (bool) $attr['ajax'] : false;
		$nav_type = isset($attr['type']) ? $attr['type'] : '';

		if( in_array( $nav_type, array('plain', 'list') ) ){
			$big = 10;
			$return = paginate_links( array(
				'type' 		=> $nav_type,
				'base' 		=> $base,
				'format' 	=> $base,
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
			if( $paged == 2 )
				$return .= '<a href="'. remove_query_arg( array($pageq_var) ) .'" class="prev page-numbers">Prev</a>';
			elseif( $paged > 2 )
				$return .= '<a href="'. add_query_arg( $pageq_var, ($paged - 1) ) .'" class="prev page-numbers">Prev</a>';
			if( $max_num_pages > $paged )
				$return .= '<a href="'. add_query_arg( $pageq_var, ($paged + 1) ) .'" class="next page-numbers">Next</a>';
		}

		if( !empty($return) )
		{
			$class = 'navigation';
			if( $use_ajax )
			{
				$class .= ' ajax-navigation';
				$this->js .= '(function($){$(document).ready(function(){$("#w4pl-list-'. $this->id 
				. ' .navigation a.page-numbers").live("click", function(){var that = $(this), parent = $("#w4pl-list-'. $this->id 
				. '");parent.addClass("w4pl-loading");parent.load( that.attr("href") + " #" + parent.attr("id") + " .w4pl-inner", function(e){parent.removeClass("w4pl-loading");});return false;});});})(jQuery) ;';
			}

			$return = '<div class="'. $class .'">'. $return . '</div>';
		}

		return $return;
	}


	function init_groups()
	{
		$groupby = $this->options['groupby'];
		$this->groups = array();

		// post parent
		if( 'parent' == $groupby )
		{
			foreach( $this->wp_query->posts as $index => $post )
			{
				if( $post->post_parent )
				{
					$parent = get_post( $post->post_parent );
					if( !isset($this->groups[$parent->ID]) ){
						$this->groups[$parent->ID] = array(
							'title' => $parent->post_title,
							'url' 	=> get_permalink($parent->ID)
						);
					}
					if( !isset($this->groups[$parent->ID]['post_ids']) ){
						$this->groups[$parent->ID]['post_ids'] = array();
					}
					$this->groups[$parent->ID]['post_ids'][] = $post->ID;
					}
					else{
					if( !isset($this->groups[0]) ){
						$this->groups[0] = array(
							'title' => 'Unknown',
							'url' 	=> ''
						);
					}
					if( !isset($this->groups[0]['post_ids']) ){
						$this->groups[0]['post_ids'] = array();
					}
					$this->groups[0]['post_ids'][] = $post->ID;
				}
			}
		}

		// terms
		elseif( 0 === strpos($groupby, 'tax_') )
		{
			$tax = str_replace('tax_', '', $groupby);
			foreach( $this->wp_query->posts as $index => $post )
			{
				if( $terms = get_the_terms($post, $tax) )
				{
					#print_r($terms);

					$term = array_shift($terms);
					if( !isset($this->groups[$term->term_id]) ){
						$this->groups[$term->term_id] = array(
							'title' => $term->name,
							'url' 	=> get_term_link($term)
						);
					}
					if( !isset($this->groups[$term->term_id]['post_ids']) ){
						$this->groups[$term->term_id]['post_ids'] = array();
					}
					$this->groups[$term->term_id]['post_ids'][] = $post->ID;
					}
					else{
					if( !isset($this->groups[0]) ){
						$this->groups[0] = array(
							'title' => 'Unknown',
							'url' 	=> ''
						);
					}
					if( !isset($this->groups[0]['post_ids']) ){
						$this->groups[0]['post_ids'] = array();
					}
					$this->groups[0]['post_ids'][] = $post->ID;
				}
			}
		}

		elseif( 'author' == $groupby )
		{
			foreach( $this->wp_query->posts as $index => $post )
			{
				if( $post->post_author )
				{
					$parent = get_userdata( $post->post_author );
					if( !isset($this->groups[$parent->ID]) ){
						$this->groups[$parent->ID] = array(
							'title' => $parent->display_name,
							'url' 	=> get_author_posts_url($parent->ID)
						);
					}
					if( !isset($this->groups[$parent->ID]['post_ids']) ){
						$this->groups[$parent->ID]['post_ids'] = array();
					}
					$this->groups[$parent->ID]['post_ids'][] = $post->ID;
				}
				else{
					if( !isset($this->groups[0]) ){
						$this->groups[0] = array(
							'title' => 'Unknown',
							'url' 	=> ''
						);
					}
					if( !isset($this->groups[0]['post_ids']) ){
						$this->groups[0]['post_ids'] = array();
					}
					$this->groups[0]['post_ids'][] = $post->ID;
				}
			}
		}

		// year
		elseif( 'year' == $groupby )
		{
			foreach( $this->wp_query->posts as $index => $post )
			{
				if( $year = mysql2date( 'Y', $post->post_date ) )
				{
					if( !isset($this->groups[$year]) ){
						$this->groups[$year] = array(
							'title' => $year,
							'url' 	=> get_year_link($year)
						);
					}
					if( !isset($this->groups[$year]['post_ids']) ){
						$this->groups[$year]['post_ids'] = array();
					}
					$this->groups[$year]['post_ids'][] = $post->ID;
				}
				else{
					if( !isset($this->groups[0]) ){
						$this->groups[0] = array(
							'title' => 'Unknown',
							'url' 	=> ''
						);
					}
					if( !isset($this->groups[0]['post_ids']) ){
						$this->groups[0]['post_ids'] = array();
					}
					$this->groups[0]['post_ids'][] = $post->ID;
				}
			}
		}


		// month
		elseif( 'month' == $groupby )
		{
			foreach( $this->wp_query->posts as $index => $post )
			{
				$month = mysql2date( 'm', $post->post_date );
				$year = mysql2date( 'Y', $post->post_date );

				if( $month && $year )
				{
					if( !isset($this->groups[$month]) ){
						$this->groups[$month] = array(
							'title' => mysql2date( 'F', $post->post_date ),
							'url' 	=> get_month_link( $year, $month )
						);
					}
					if( !isset($this->groups[$month]['post_ids']) ){
						$this->groups[$month]['post_ids'] = array();
					}
					$this->groups[$month]['post_ids'][] = $post->ID;
				}
				else{
					if( !isset($this->groups[0]) ){
						$this->groups[0] = array(
							'title' => 'Unknown',
							'url' 	=> ''
						);
					}
					if( !isset($this->groups[0]['post_ids']) ){
						$this->groups[0]['post_ids'] = array();
					}
					$this->groups[0]['post_ids'][] = $post->ID;
				}
			}
		}

		// month
		elseif( 'yearmonth' == $groupby )
		{
			foreach( $this->wp_query->posts as $index => $post )
			{
				$month = mysql2date( 'm', $post->post_date );
				$year = mysql2date( 'Y', $post->post_date );

				if( $year && $month )
				{
					if( !isset($this->groups[$year.$month]) ){
						$this->groups[$year.$month] = array(
							'title' => mysql2date( 'Y, F', $post->post_date ),
							'url' 	=> get_month_link( $year, $month )
						);
					}
					if( !isset($this->groups[$year.$month]['post_ids']) ){
						$this->groups[$year.$month]['post_ids'] = array();
					}
					$this->groups[$year.$month]['post_ids'][] = $post->ID;
				}
				else{
					if( !isset($this->groups[0]) ){
						$this->groups[0] = array(
							'title' => 'Unknown',
							'url' 	=> ''
						);
					}
					if( !isset($this->groups[0]['post_ids']) ){
						$this->groups[0]['post_ids'] = array();
					}
					$this->groups[0]['post_ids'][] = $post->ID;
				}
			}
		}


		#print_r( $this->options['group_order'] );

		if( isset($this->options['group_order']) && !empty($this->options['group_order']) )
		{
			if( 'ASC' == $this->options['group_order'] )
			{
				uasort( $this->groups, array($this, 'cmp_asc') );
			}
			elseif( 'DESC' == $this->options['group_order'] )
			{
				uasort( $this->groups, array($this, 'cmp_desc') );
			}
		}

		#echo '<pre>';
		#print_r( $this->groups );
		#echo '</pre>';
	}

	public function cmp_asc($a, $b)
	{
		if ($a == $b) {
			return 0;
		}
		return ($a < $b) ? -1 : 1;
	}
	public function cmp_desc($a, $b)
	{
		if ($a == $b) {
			return 0;
		}
		return ($a > $b) ? -1 : 1;
	}


	// Callback Functions - Post
	function post_id($attr, $cont){ return get_the_ID(); }
	function post_number($attr, $cont){ return $this->wp_query->current_post + 1; }
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
	function post_author_email($attr, $cont){ return get_the_author_meta('user_email'); }
	function post_author_avatar($attr, $cont)
	{
		$size = 32;
		if( isset($attr['size']) ){
			$size = $attr['size'];
		}
		return get_avatar( get_the_author_meta('user_email'), $size );
	}

	function post_excerpt( $attr, $cont )
	{
		$post = get_post();
		$excerpt = $post->post_excerpt;
		if ( '' == $excerpt )
			$excerpt = $post->post_content;

		$excerpt = wp_strip_all_tags( $excerpt );

		if( isset($attr['wordlimit']) ){
			$wordlimit = (int) $attr['wordlimit'];
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


	function post_meta($attr, $cont)
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


	// Attachment
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

	function group_title($attr, $cont){
		$gr = array();
		foreach( $this->groups as $group ){
			if( in_array( get_the_ID(), $group['post_ids']) ){
				$gr = $group;
				break;
			}
		}
		return $gr['title'];
	}

	// Tempate
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

	function term_id( $attr, $cont )
	{
		return isset($this->current_term) ? $this->current_term->term_id : 0;
	}
	function term_name( $attr, $cont )
	{
		return isset($this->current_term) ? $this->current_term->name : '';
	}
	function term_slug( $attr, $cont )
	{
		return isset($this->current_term) ? $this->current_term->slug : '';
	}
	function term_link( $attr, $cont )
	{
		return isset($this->current_term) ? get_term_link($this->current_term) : '';
	}
	function term_count( $attr, $cont )
	{
		return isset($this->current_term) ? $this->current_term->count : 0;
	}
	function term_content( $attr, $cont )
	{
		return isset($this->current_term) ? $this->current_term->description : '';
	}
}


?>