<?php
class W4PL_Helper_Posts extends W4PL_Core
{
	function __construct()
	{
		add_filter( 'w4pl/admin_list_fields', array($this, 'admin_list_fields'), 10, 2 );
	}


	/* Meta box */
	public function admin_list_fields( $fields, $options )
	{
		$list_type = $options['list_type'];
		if( ! in_array($list_type, array('posts', 'terms.posts') ) )
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
		$fields['post_parent__in'] = array(
			'position'		=> '67',
			'option_name' 	=> 'post_parent__in',
			'name' 			=> 'w4pl[post_parent__in]',
			'label' 		=> 'Post parent',
			'type' 			=> 'text',
			'input_class' 	=> 'widefat',
			'desc' 			=> 'comma separated parent post ids'
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
			'desc' 			=> 'maximum results to display in total, default all found'
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
				'desc' 			=> 'number of items to show per page'
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
}

	new W4PL_Helper_Posts;
?>