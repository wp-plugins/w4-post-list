<?php
class W4PL_Helper_Terms extends W4PL_Core
{
	function __construct()
	{
		add_filter( 'w4pl/admin_list_fields', 					array($this, 'admin_list_fields'), 10, 2 );

		// filter list before getting them
		add_filter( 'w4pl/pre_get_options', 					array($this, 'pre_get_options') );
	}


	/* Meta box */
	public function admin_list_fields( $fields, $options )
	{
		$list_type = $options['list_type'];
		if( ! in_array($list_type, array('terms', 'terms.posts') ) )
			return $fields;

		$fields['before_field_group_terms_query'] = array(
			'position'		=> '5',
			'html' 			=> '<div id="w4pl_field_group_terms_query" class="w4pl_field_group"><div class="w4pl_group_title">Terms</div><div class="w4pl_group_fields">'
		);
		$fields['terms_taxonomy'] = array(
			'position'		=> '10',
			'option_name' 	=> 'terms_taxonomy',
			'name' 			=> 'w4pl[terms_taxonomy]',
			'label' 		=> 'Taxonomy',
			'type' 			=> 'radio',
			'option'		=> self::taxonomies_options(),
			'input_class'	=> 'w4pl_onchange_lfr'
		);
		$fields['terms__in'] = array(
			'position'		=> '11',
			'option_name' 	=> 'terms__in',
			'name' 			=> 'w4pl[terms__in]',
			'label' 		=> 'Include terms',
			'type' 			=> 'text',
			'input_class' 	=> 'widefat',
			'desc' 			=> 'comma separated term id'
		);
		$fields['terms__not_in'] = array(
			'position'		=> '12',
			'option_name' 	=> 'terms__not_in',
			'name' 			=> 'w4pl[terms__not_in]',
			'label' 		=> 'Exclude terms',
			'type' 			=> 'text',
			'input_class' 	=> 'widefat',
			'desc' 			=> 'comma separated term id'
		);

		$fields['terms_name__like'] = array(
			'position'		=> '15',
			'option_name' 	=> 'terms_name__like',
			'name' 			=> 'w4pl[terms_name__like]',
			'label' 		=> 'Name search',
			'type' 			=> 'text',
			'desc' 			=> 'enter text that will be used to search terms by name &amp; slug'
		);
		$fields['terms_slug__like'] = array(
			'position'		=> '16',
			'option_name' 	=> 'terms_slug__like',
			'name' 			=> 'w4pl[terms_slug__like]',
			'label' 		=> 'Slug search',
			'type' 			=> 'text',
			'desc' 			=> 'enter text that will be used to search terms by name'
		);
		$fields['terms_description__like'] = array(
			'position'		=> '17',
			'option_name' 	=> 'terms_description__like',
			'name' 			=> 'w4pl[terms_description__like]',
			'label' 		=> 'Description search',
			'type' 			=> 'text',
			'desc' 			=> 'enter text that will be used to search terms by description'
		);
		$fields['terms_count__min'] = array(
			'position'		=> '18',
			'option_name' 	=> 'terms_count__min',
			'name' 			=> 'w4pl[terms_count__min]',
			'label' 		=> 'Having min posts',
			'type' 			=> 'text'
		);

		$fields['terms_orderby'] = array(
			'position'		=> '21',
			'option_name' 	=> 'terms_orderby',
			'name' 			=> 'w4pl[terms_orderby]',
			'label' 		=> 'Orderby',
			'type' 			=> 'select',
			'option' 		=> self::terms_orderby_options( $options['terms_taxonomy'] )
		);
		$fields['terms_order'] = array(
			'position'		=> '22',
			'option_name' 	=> 'terms_order',
			'name' 			=> 'w4pl[terms_order]',
			'label' 		=> 'Order',
			'type' 			=> 'radio',
			'option' 		=> array('ASC' => 'ASC', 'DESC' => 'DESC')
		);
		$fields['terms_offset'] = array(
			'position'		=> '31',
			'option_name' 	=> 'terms_offset',
			'name' 			=> 'w4pl[terms_offset]',
			'label' 		=> 'Offset',
			'type' 			=> 'text',
			'desc' 			=> 'skip given number of terms from beginning'
		);
		$fields['terms_limit'] = array(
			'position'		=> '32',
			'option_name' 	=> 'terms_limit',
			'name' 			=> 'w4pl[terms_limit]',
			'label' 		=> 'Items per page',
			'type' 			=> 'text',
			'desc' 			=> 'number of items to show per page'
		);
		$fields['terms_max'] = array(
			'position'		=> '33',
			'option_name' 	=> 'terms_max',
			'name' 			=> 'w4pl[terms_max]',
			'label' 		=> 'Maximum items',
			'type' 			=> 'text',
			'desc' 			=> 'maximum results to display in total, default all found'
		);

		$fields['after_field_group_terms_query'] = array(
			'position'		=> '50',
			'html' 			=> '</div><!--.w4pl_group_fields--></div><!--#w4pl_field_group_terms_query-->'
		);

		return $fields;
	}

	public function pre_get_options($options)
	{
		if( !isset($options) || !is_array($options) )
			$options = array();

		$options = wp_parse_args( $options, array(
			'terms_taxonomy' 			=> 'category', 
			'terms__in' 				=> '', 
			'terms__not_in' 			=> '', 
			'terms_name__like'			=> '',
			'terms_slug__like'			=> '',
			'terms_description__like'	=> '',
			'terms_count__min'			=> '',
			'terms_offset'				=> '',
			'terms_limit'				=> '',
			'terms_max'					=> '',
			'terms_orderby'				=> 'count',
			'terms_order'				=> 'DESC'
		));

		return $options;
	}

	public static function taxonomies_options()
	{
		global $wp_taxonomies;
		$return = array();
		foreach( $wp_taxonomies as $t => $attr){
			if( $attr->public )
			{
				$return[$t] = $attr->label;
			}
		}
		return $return;
	}

	public static function terms_orderby_options($taxonomy)
	{
		$return = array(
			'term_id'			=> __( 'ID', 					W4PL_TXT_DOMAIN),
			'name'				=> __( 'Name', 					W4PL_TXT_DOMAIN),
			'slug'				=> __( 'Slug', 					W4PL_TXT_DOMAIN),
			'count'				=> __( 'Count', 				W4PL_TXT_DOMAIN)
		);

		return $return;
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
		if ( $m[1] == '[' && $m[6] == ']' ) {
			return substr($m[0], 1, -1);
		}
		$tag = $m[2];
		$attr = shortcode_parse_atts( $m[3] );
		#if ( isset( $m[5] ) ){
		#	return $m[1] . apply_filters( 'w4pl/shortcode/'. $tag, $attr, '', $m[5] ) . $m[6];
		#} else {
			return $m[1] . apply_filters( 'w4pl/shortcode/'. $tag, $attr, '', $this ) . $m[6];
		#}
	}

}

	new W4PL_Helper_Terms;
?>