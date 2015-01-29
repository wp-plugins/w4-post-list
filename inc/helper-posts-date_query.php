<?php
/**
 * Date Query Implementation
 * @package WordPress
 * @subpackage W4 Post List
 * @author Shazzad Hossain Khan
 * @url http://w4dev.com/plugins/w4-post-list
**/


class W4PL_Helper_Date_Query extends W4PL_Core
{
	function __construct()
	{
		add_filter( 'w4pl/admin_list_fields', array($this, 'admin_list_fields'), 10, 2 );

		add_filter( 'w4pl/pre_save_options', array($this, 'pre_save_options') );

		add_filter( 'w4pl/pre_get_options', array($this, 'pre_get_options') );

		add_filter( 'w4pl/parse_query_args', array($this, 'parse_query_args'), 18 );
	}



	/* Meta box */
	public function admin_list_fields( $fields, $post_data )
	{
		$list_type = $post_data['list_type'];
		if( ! in_array($list_type, array('posts', 'terms.posts', 'users.posts') ) )
			return $fields;


		/* Date Query */
		$fields['before_field_group_date_query'] = array(
			'position'		=> '130',
			'html' 			=> '<div id="w4pl_field_group_date_query" class="w4pl_field_group">
								<div class="w4pl_group_title">Posts: Date Query</div>
								<div class="w4pl_group_fields">'
		);

		$fields['year'] = array(
			'position'		=> '132',
			'option_name' 	=> 'year',
			'name' 			=> 'w4pl[year]',
			'label' 		=> __('Year', W4PL_TD),
			'type' 			=> 'text',
			'desc' 			=> '4 digit year'
		);
		$fields['monthnum'] = array(
			'position'		=> '134',
			'option_name' 	=> 'monthnum',
			'name' 			=> 'w4pl[monthnum]',
			'label' 		=> __('Month', W4PL_TD),
			'type' 			=> 'text',
			'desc' 			=> 'numeric representation of months. ex: 5 for may, 11 for november.'
		);
		$fields['day'] = array(
			'position'		=> '136',
			'option_name' 	=> 'day',
			'name' 			=> 'w4pl[day]',
			'label' 		=> __('Day', W4PL_TD),
			'type' 			=> 'text'
		);

		$fields['after_field_group_date_query'] = array(
			'position'		=> '139',
			'html' 			=> '</div><!--.w4pl_group_fields--></div><!--#before_field_group_date_query-->'
		);

		/* ========================================= */

		return $fields;
	}


	public function pre_save_options($options)
	{
		return $options;
	}

	public function pre_get_options($options)
	{
		return $options;
	}


	public function parse_query_args( $list )
	{
		if( in_array($list->options['list_type'], array('posts', 'terms.posts', 'users.posts') ) )
		{
			// meta query
			foreach( array(
				'year', 
				'monthnum', 
				'day'
			) as $option_name )
			{
				if( !empty($list->options[$option_name]) )
					$list->posts_args[$option_name] = intval( $list->options[$option_name] );
			}
		}
		# echo '<pre>'; print_r($list->posts_args); echo '</pre>';
	}
}

	new W4PL_Helper_Date_Query;
?>