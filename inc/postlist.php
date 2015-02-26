<?php
/**
 * @package W4 Post List
 * @author Shazzad Hossain Khan
 * @url http://w4dev.com/plugins/w4-post-list
**/


class W4_Post_list
{
	function __construct(){}

	function prepare( $options )
	{
		static $w4pl_lists;
		if( !isset($w4pl_lists) || !is_array($w4pl_lists) )
			$w4pl_lists = array();

		if( !isset($options['id']) )
			return new WP_Error('error', 'Invalid list id');

		if( in_array($options['id'], $w4pl_lists) )
			return new WP_Error('list_loaded', 'A list can load only once on the same page');

		$w4pl_lists[] = $options['id'];

		$this->options 			= $options;
		$this->id 				= $this->options['id'];

		$this->terms_args 		= array();
		$this->terms_query 		= array();
		$this->current_term 	= '';

		$this->users_args 		= array();
		$this->users_query 		= array();
		$this->current_user 	= '';

		$this->posts_args 		= array();
		$this->posts_query 		= array();
		$this->current_post		= '';

		$this->groups 			= array();
		$this->current_group	= '';


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
		$template = isset($this->options['template']) ? $this->options['template'] : '';
		$terms_template = '';
		$users_template = '';
		$posts_template = '';
		$groups_template = '';
		$template_nav = '';

		// match [groups]
		if( preg_match('/\[terms\](.*?)\[\/terms\]/sm', $template, $terms_match) )
		{
			$terms_template = $terms_match['1'];
		}
		if( preg_match('/\[users\](.*?)\[\/users\]/sm', $template, $users_match) )
		{
			$users_template = $users_match['1'];
		}
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


		// let helper class extend/modify this class
		do_action_ref_array( 'w4pl/parse_query_args', array( &$this ) );




		$paged = isset($_REQUEST['page'. $this->id]) ? $_REQUEST['page'. $this->id] : 1;


		#echo '<pre>'; print_r($this->options); echo '</pre>';


		// terms
		if( in_array($this->options['list_type'], array('terms', 'terms.posts') ) )
		{
			$this->terms_query = new W4PL_Terms_Query( $this->terms_args );
			$this->terms_query->query();


			if( empty($terms_match) || ! $this->terms_query->get_results() )
			{
				$template = '';
			}
			else
			{
				$terms_loop = '';
				foreach( $this->terms_query->get_results() as $term )
				{
					$terms_template_clone = $terms_template; // clone the group template
					$term_posts_loop = '';

					$this->current_term = $term;

					// term posts
					if( in_array($this->options['list_type'], array('terms.posts') ) )
					{
						$this->posts_args['paged'] = 1;
						$this->posts_args['posts_per_page'] = isset($this->options['limit']) && $this->options['limit'] ? (int) $this->options['limit'] : -1;
						$this->posts_args['tax_query'] = array(
							'relation' => 'OR',
							array(
								'taxonomy' 	=> $this->options['terms_taxonomy'],
								'field' 	=> 'term_id',
								'terms' 	=> $this->current_term->term_id,
							)
						);
						$this->posts_query = new WP_Query( $this->posts_args );
	
						#echo '<pre>'; print_r($this->posts_args); echo '</pre>';
	
	
						// post loop
						if( $this->posts_query->have_posts() )
						{
							while( $this->posts_query->have_posts() )
							{
								$this->posts_query->the_post();
								$term_posts_loop .= preg_replace_callback( "/$pattern/s", array(&$this, 'do_shortcode_tag'), $posts_template );
							}
						}

						// reset postdata back to normal.
						wp_reset_postdata();

					} // end term posts


					// replace [posts]
					if( isset($posts_match) && isset($posts_match['0']) ){
						$terms_template_clone = str_replace( $posts_match['0'], $term_posts_loop, $terms_template_clone );
					}
	
					$terms_loop .= preg_replace_callback( "/$pattern/s", array(&$this, 'do_shortcode_tag'), $terms_template_clone );
				}

				// replace [terms]
				$template = str_replace( $terms_match[0], $terms_loop, $template );
			}

			// replace [nav]
			// template will be empty if there's no results
			if( !empty($template_nav) && !empty($template) )
			{
				if( isset($this->options['terms_max']) && !empty($this->options['terms_max']) && $this->options['terms_max'] < ($this->options['terms_limit'] * $paged) )
					$max_num_pages = $paged;
				else
					$max_num_pages = $this->terms_query->max_num_pages;

				$navigation = $this->navigation( $max_num_pages, $paged, shortcode_parse_atts($nav_match[1]) );
				$template = str_replace( $nav_match[0], $navigation, $template );
			}
		}


		// users
		if( in_array($this->options['list_type'], array('users', 'users.posts') ) )
		{
			$this->users_query = new W4PL_Users_Query( $this->users_args );
			$this->users_query->query();

			#echo '<pre>'; print_r($this->users_query); echo '</pre>';
			#$this->users_query = get_users( $this->options['users_taxonomy'], $this->users_args );


			if( empty($users_match) || ! $this->users_query->get_results() )
			{
				$template = '';
			}
			else
			{
				$users_loop = '';
				foreach( $this->users_query->get_results() as $user )
				{
					$users_template_clone = $users_template; // clone the group template
					$user_posts_loop = '';
	
					$this->current_user = $user;
	
					// term posts
					if( in_array($this->options['list_type'], array('users.posts') ) )
					{
						$this->posts_args['paged'] = 1;
						$this->posts_args['posts_per_page'] = isset($this->options['limit']) ? (int) $this->options['limit'] : -1;
						$this->posts_args['author'] = $this->current_user->ID;
						$this->posts_query = new WP_Query( $this->posts_args );
	
						#echo '<pre>'; print_r($this->posts_query); echo '</pre>';
	
	
						// post loop
						if( $this->posts_query->have_posts() )
						{
							while( $this->posts_query->have_posts() )
							{
								$this->posts_query->the_post();
								$user_posts_loop .= preg_replace_callback( "/$pattern/s", array(&$this, 'do_shortcode_tag'), $posts_template );
							}
						}
	
						// reset postdata back to normal.
						wp_reset_postdata();
	
					} // end term posts
	
	
					// replace [posts]
					if( isset($posts_match) && isset($posts_match['0']) ){
						$users_template_clone = str_replace( $posts_match['0'], $user_posts_loop, $users_template_clone );
					}
	
	
					$users_loop .= preg_replace_callback( "/$pattern/s", array(&$this, 'do_shortcode_tag'), $users_template_clone );
				}
	
				// replace [users]
				$template = str_replace( $users_match[0], $users_loop, $template );
			}

			// replace [nav]
			// template will be empty if there's no results
			if( !empty($template_nav) && !empty($template) )
			{
				if( isset($this->options['users_max']) && !empty($this->options['users_max']) && $this->options['users_max'] < ($this->options['users_limit'] * $paged) )
					$max_num_pages = $paged;
				else
					$max_num_pages = $this->users_query->max_num_pages;

				$navigation = $this->navigation( $max_num_pages, $paged, shortcode_parse_atts($nav_match[1]) );
				$template = str_replace( $nav_match[0], $navigation, $template );
			}
		}


		// posts
		if( in_array($this->options['list_type'], array('posts') ) && !empty($posts_match) )
		{
			# echo '<pre>'; print_r($this->posts_args); echo '</pre>';

			// do query posts
			$this->posts_query = new WP_Query( $this->posts_args );

			# echo '<pre>'; print_r($this->posts_query); echo '</pre>';

			// if using groups
			if( isset($this->options['groupby']) && !empty($this->options['groupby']) && !empty($groups_template) ) :
				$this->init_posts_groups();

			// remove the group block
			elseif( !empty($groups_template) ) :
				$template = str_replace( $groups_match[0], $posts_match['0'], $template );
			endif;


			// found posts
			if( $this->posts_query->have_posts() ):

				if( !empty($this->groups) )
				{
					$groups_loop = '';
					foreach( $this->groups as $group)
					{
						$this->current_group = $group;

						$group_posts_loop = '';
						$groups_template_clone = $groups_template; // clone the group template

						// post loop
						while( $this->posts_query->have_posts() )
						{
							$this->posts_query->the_post();
							if( in_array( get_the_ID(), $group['post_ids']) ){
								$group_posts_loop .= preg_replace_callback( "/$pattern/s", array(&$this, 'do_shortcode_tag'), $posts_template );
							}
						}

						// replace [posts]
						$groups_template_clone = str_replace( $posts_match['0'], $group_posts_loop, $groups_template_clone );

						$groups_loop .= preg_replace_callback( "/$pattern/s", array(&$this, 'do_shortcode_tag'), $groups_template_clone );
					}

					// replace [groups]
					$template = str_replace( $groups_match[0], $groups_loop, $template );
				}
				else
				{
					$posts_loop = '';
					// post loop
					while( $this->posts_query->have_posts() )
					{
						$this->posts_query->the_post();
						$posts_loop .= preg_replace_callback( "/$pattern/s", array(&$this, 'do_shortcode_tag'), $posts_template );
					}

					// replace [posts]
					$template = str_replace( $posts_match[0], $posts_loop, $template );
				}

			// no posts
			else :

				// replace [posts]
				$template = '';

			endif;


			// reset postdata back to normal.
			wp_reset_postdata();


			// replace [nav]
			// template will be empty if there's no results
			if( !empty($template_nav) && !empty($template) )
			{
				if( isset($this->options['limit']) && !empty($this->options['limit']) && (int) $this->options['limit'] < ($this->options['posts_per_page'] * $paged) )
					$max_num_pages = $paged;
				else
					$max_num_pages = $this->posts_query->max_num_pages;

				$navigation = $this->navigation( $max_num_pages, $paged, shortcode_parse_atts($nav_match[1]) );
				$template = str_replace( $nav_match[0], $navigation, $template );
			}
		} // end posts


		$this->template = trim($template);

		// html
		$this->html  = '';
		$this->html .= '<div id="w4pl-list-'. $this->id .'">'. "\n\t" .'<div id="w4pl-inner-'. $this->id .'" class="w4pl-inner">';
		if( !empty($this->template) )
		{ $this->html .= "\n\t\t" . $this->template . "\n\t"; }
		$this->html .= '</div><!--#w4pl-inner-'. $this->id .'-->'. "\n" .'</div><!--#w4pl-'. $this->id .'-->';


		// let helper classes extend or modify this class
		do_action_ref_array( 'w4pl/parse_html', array(&$this) );


		// return the template
		return "<!--W4_Post_list_{$this->id}-->\n" . $this->html . "\n<!--END_W4_Post_list_{$this->id}-->\n";
	}


	/**
	 * Create Navigation
	 * @package w4-post-list

	 * @param int $max_num_pages Maximum number of navigation pages
	 * @param int $paged Current page
	 * @param array $attr Mixed attributes
	 * @return string The navigation HTML
	**/

	function navigation( $max_num_pages, $paged = 1, $attr = array() )
	{
		$nav_type = isset($attr['type']) ? $attr['type'] : '';
		$prev_text = isset($attr['prev_text']) && !empty($attr['prev_text']) ? $attr['prev_text'] : __('Previous');
		$next_text = isset($attr['next_text']) && !empty($attr['next_text']) ? $attr['next_text'] : __('Next');

		$return = '';


		$paged_qp = 'page'. $this->id; // the query parameter for pagination
		$base = remove_query_arg($paged_qp, get_pagenum_link()) . '%_%'; // remove current lists query parameter from base, other lists qr will be kept
		// if base already have a query parameter, use &.
		if( strpos($base, '?' ) )
		{ $format = '&'. $paged_qp . '=%#%'; }
		else
		{ $format = '?'. $paged_qp . '=%#%'; }


		if( in_array( $nav_type, array('plain', 'list') ) )
		{
			$big = 10;
			$pag_args = array(
				'type' 		=> $nav_type,
				'base' 		=> $base,
				'format' 	=> $format,
				'current' 	=> $paged,
				'total' 	=> $max_num_pages,
				'end_size' 	=> 2,
				'mid_size' 	=> 2,
				'prev_text' => $prev_text,
				'next_text' => $next_text,
				'add_args'	=> false // stop wp to add query arguments
			);

			$return = paginate_links( $pag_args );
		}

		// default navigation
		else
		{
			if( $paged == 2 )
				$return .= '<a href="'. remove_query_arg( $paged_qp ) .'" class="prev page-numbers prev_text">'. $prev_text . '</a>';
			elseif( $paged > 2 )
				$return .= '<a href="'. add_query_arg( $paged_qp, ($paged - 1) ) .'" class="prev page-numbers prev_text">'. $prev_text . '</a>';
			if( $max_num_pages > $paged )
				$return .= '<a href="'. add_query_arg( $paged_qp, ($paged + 1) ) .'" class="next page-numbers next_text">'. $next_text . '</a>';
		}

		if( !empty($return) )
		{
			$class = 'navigation';
			$use_ajax = isset($attr['ajax']) ? (bool) $attr['ajax'] : false;
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


	function init_posts_groups()
	{
		$groupby = $this->options['groupby'];
		$this->groups = array();

		// new @ 1.9.9.6
		// allow group using modified date
		if( in_array($groupby, array('year', 'month', 'yearmonth') ) && !in_array($this->options['groupby_time'], array('post_date', 'post_modified')) )
		{ $groupby_time = 'post_date'; }
		else
		{ $groupby_time = $this->options['groupby_time']; }

		// post parent
		if( 'parent' == $groupby )
		{
			foreach( $this->posts_query->posts as $index => $post )
			{
				if( $post->post_parent )
				{
					$parent = get_post( $post->post_parent );
					$group_id = $parent->ID;
					if( !isset($this->groups[$group_id]) ){
						$this->groups[$group_id] = array(
							'id' 	=> $group_id,
							'title' => $parent->post_title,
							'url' 	=> get_permalink($parent->ID)
						);
					}
				}
				else
				{
					$group_id = 0;
					if( !isset($this->groups[$group_id]) ){
						$this->groups[$group_id] = array(
							'id' 	=> $group_id,
							'title' => 'Unknown',
							'url' 	=> ''
						);
					}
				}

				if( !isset($this->groups[$group_id]['post_ids']) )
					$this->groups[$group_id]['post_ids'] = array();

				$this->groups[$group_id]['post_ids'][] = $post->ID;
			}
		}

		// terms
		elseif( 0 === strpos($groupby, 'tax_') )
		{
			$tax = str_replace('tax_', '', $groupby);
			foreach( $this->posts_query->posts as $index => $post )
			{
				if( $terms = get_the_terms($post, $tax) )
				{
					$term = array_shift($terms);
					$group_id = $term->term_id;
					if( !isset($this->groups[$group_id]) ){
						$this->groups[$group_id] = array(
							'id' 	=> $group_id,
							'title' => $term->name,
							'url' 	=> get_term_link($term)
						);
					}
				}
				else
				{
					$group_id = 0;
					if( !isset($this->groups[$group_id]) ){
						$this->groups[$group_id] = array(
							'id' 	=> $group_id,
							'title' => 'Unknown',
							'url' 	=> ''
						);
					}
				}

				if( !isset($this->groups[$group_id]['post_ids']) )
					$this->groups[$group_id]['post_ids'] = array();

				$this->groups[$group_id]['post_ids'][] = $post->ID;
			}
		}

		elseif( 'author' == $groupby )
		{
			foreach( $this->posts_query->posts as $index => $post )
			{
				if( $post->post_author )
				{
					$parent = get_userdata( $post->post_author );
					$group_id = $parent->ID;
					if( !isset($this->groups[$group_id]) ){
						$this->groups[$group_id] = array(
							'id' 	=> $group_id,
							'title' => $parent->display_name,
							'url' 	=> get_author_posts_url($parent->ID)
						);
					}
				}
				else
				{
					$group_id = 0;
					if( !isset($this->groups[$group_id]) ){
						$this->groups[$group_id] = array(
							'id' 	=> $group_id,
							'title' => 'Unknown',
							'url' 	=> ''
						);
					}
				}

				if( !isset($this->groups[$group_id]['post_ids']) )
					$this->groups[$group_id]['post_ids'] = array();

				$this->groups[$group_id]['post_ids'][] = $post->ID;
			}
		}

		// year
		elseif( 'year' == $groupby )
		{
			foreach( $this->posts_query->posts as $index => $post )
			{
				if( $year = mysql2date( 'Y', $post->{$groupby_time} ) )
				{
					$group_id = $year;
					if( !isset($this->groups[$group_id]) ){
						$this->groups[$group_id] = array(
							'id' 	=> $group_id,
							'title' => $year,
							'url' 	=> get_year_link($year)
						);
					}
				}
				else
				{
					$group_id = 0;
					if( !isset($this->groups[$group_id]) ){
						$this->groups[$group_id] = array(
							'id' 	=> $group_id,
							'title' => 'Unknown',
							'url' 	=> ''
						);
					}
				}

				if( !isset($this->groups[$group_id]['post_ids']) )
					$this->groups[$group_id]['post_ids'] = array();

				$this->groups[$group_id]['post_ids'][] = $post->ID;
			}
		}


		// month
		elseif( 'month' == $groupby )
		{
			foreach( $this->posts_query->posts as $index => $post )
			{
				$month = mysql2date( 'm', $post->{$groupby_time} );
				$year = mysql2date( 'Y', $post->{$groupby_time} );

				if( $month && $year )
				{
					$group_id = $month;
					if( !isset($this->groups[$group_id]) ){
						$this->groups[$group_id] = array(
							'id' 	=> $group_id,
							'title' => mysql2date( 'F', $post->{$groupby_time} ),
							'url' 	=> get_month_link( $year, $month )
						);
					}
				}
				else
				{
					$group_id = 0;
					if( !isset($this->groups[$group_id]) ){
						$this->groups[$group_id] = array(
							'id' 	=> $group_id,
							'title' => 'Unknown',
							'url' 	=> ''
						);
					}
				}

				if( !isset($this->groups[$group_id]['post_ids']) )
					$this->groups[$group_id]['post_ids'] = array();

				$this->groups[$group_id]['post_ids'][] = $post->ID;
			}
		}

		// month
		elseif( 'yearmonth' == $groupby )
		{
			foreach( $this->posts_query->posts as $index => $post )
			{
				$month = mysql2date( 'm', $post->{$groupby_time} );
				$year = mysql2date( 'Y', $post->{$groupby_time} );

				if( $year && $month )
				{
					$group_id = $year . '-' . $month;
					if( !isset($this->groups[$group_id]) ){
						$this->groups[$group_id] = array(
							'id' 	=> $group_id,
							'title' => mysql2date( 'Y, F', $post->{$groupby_time} ),
							'url' 	=> get_month_link( $year, $month )
						);
					}
				}
				else
				{
					$group_id = 0;
					if( !isset($this->groups[$group_id]) ){
						$this->groups[$group_id] = array(
							'id' 	=> $group_id,
							'title' => 'Unknown',
							'url' 	=> ''
						);
					}
				}

				if( !isset($this->groups[$group_id]['post_ids']) )
					$this->groups[$group_id]['post_ids'] = array();

				$this->groups[$group_id]['post_ids'][] = $post->ID;
			}
		}


		// meta_value
		elseif( 'meta_value' == $groupby )
		{
			$groupby_meta_key = $this->options['groupby_meta_key'];
			foreach( $this->posts_query->posts as $index => $post )
			{
				if( $value = get_post_meta($post->ID, $groupby_meta_key, true) )
				{
					$group_id = $value;
					if( !isset($this->groups[$group_id]) ){
						$this->groups[$group_id] = array(
							'id' 	=> $group_id,
							'title' => $value,
							'url' 	=> ''
						);
					}
				}
				else
				{
					$group_id = 0;
					if( !isset($this->groups[$group_id]) ){
						$this->groups[$group_id] = array(
							'id' 	=> $group_id,
							'title' => 'Unknown',
							'url' 	=> ''
						);
					}
				}

				if( !isset($this->groups[$group_id]['post_ids']) )
					$this->groups[$group_id]['post_ids'] = array();

				$this->groups[$group_id]['post_ids'][] = $post->ID;
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
		$callback = $shortcodes[$tag]['callback'];

		$content = isset( $m[5] ) ? $m[5] : null;

		if( !empty($callback) )
			return $m[1] . call_user_func( $callback, $attr, $content, $this ) . $m[6];
		else
			return $m[1] . $content . $m[6];
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
}
?>