<?php
/**
 * @package W4 Post List
 * @author Shazzad Hossain Khan
 * @url http://w4dev.com/plugins/w4-post-list
**/


class W4PL_Widget extends WP_Widget
{
	function W4PL_Widget()
	{
		$widget_ops = array(
			'classname' 	=> 'w4_post_list_widget',
			'description' 	=> __( 'List your posts completely customized', W4PL_TD )
		);
		$control_ops = array( 'width' => 200, 'height' => 400);
		$this->WP_Widget( 'w4_post_list', 'W4 Post List', $widget_ops, $control_ops );
		$this->alt_option_name = 'w4_post_list';
	}
	function widget( $args, $instance)
	{
		extract( $args);
		$title = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );

		$options = get_post_meta( $instance['PL_ID'], '_w4pl', true );
		$options['id'] = $instance['PL_ID'];
		$options = apply_filters( 'w4pl/pre_get_options', $options );

		echo $before_widget;
		if( $title )
			echo $before_title . $title . $after_title;
		echo W4PL_Core::the_list( $options );
		echo $after_widget;
	}
	function update( $new_instance, $old_instance ) {
		$instance 					= $old_instance;
		$instance['title'] 			= strip_tags( $new_instance['title']);
		$instance['PL_ID']			= (int) $new_instance['PL_ID'];
		return $instance;
	}
	function form( $instance )
	{
		$title = isset($instance['title']) ? esc_attr($instance['title']) : '';
		$PL_ID = isset($instance['PL_ID']) ? (int)($instance['PL_ID']) : 0;

		?>
		<p>
			<strong><?php _e( 'Title:', W4PL_TD); ?></strong>
            <br /><input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" 
			value="<?php echo $title; ?>" />
		</p>
		<p>
			<strong><?php _e( 'Select list:', W4PL_TD); ?></strong>
            <br /><select id="<?php echo $this->get_field_id('PL_ID'); ?>" name="<?php echo $this->get_field_name('PL_ID'); ?>"><?php
				$lists = get_posts( 'post_status=publish&post_type='. W4PL_SLUG . '&posts_per_page=-1' );
				$PL_ID = (int) $PL_ID;
				foreach( $lists as $list ){
					$sel = ( $PL_ID == $list->ID ) ? 'selected="selected"' : '';
					$title = empty($list->post_title) ? 'List#' . $list->ID : $list->post_title;
					echo "<option value=\"$list->ID\" $sel >$title</option>\n";
				}
			?></select>
			or <a class="button" href="<?php echo esc_url(admin_url('post-new.php?post_type='. W4PL_SLUG)); ?>">create new</a>
		</p>
		<?php
	}
}

// Register Widget ==============================
add_action('widgets_init', 'W4PL_Widget_Init');
function W4PL_Widget_Init(){
	register_widget( 'W4PL_Widget' );
}

?>