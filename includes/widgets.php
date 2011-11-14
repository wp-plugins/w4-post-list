<?php
// W4 post list Widget
class W4PL_Widget extends WP_Widget {

	function W4PL_Widget() {
		$widget_ops = array(
			'classname' => 'w4_post_list',
			'description' => __( 'List your selected posts or categories or both of them together...', 'w4-post-list' )
		);

		$control_ops = array('width' => 200, 'height' => 400);
		$this->WP_Widget( 'w4_post_list', 'W4 post list', $widget_ops, $control_ops );
		$this->alt_option_name = 'w4_post_list';
	}

	function widget( $args, $instance){
		global $w4_post_list;
		extract( $args);
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? 'W4 post list' : __( $instance['title'], 'w4-post-list' ), $instance, $this->id_base );

		$list = get_w4_post_list( $instance['PL_ID'] );
		if( is_wp_error( $list )){
			if( current_user_can( 'edit_plugins')){
				echo $before_widget;
					if( $title )
				echo $before_title . $title . $after_title;
				echo $list->get_error_message();
				echo $after_widget;
			}
			return;
		}
		
		echo $before_widget;
			if( $title )
		echo $before_title . $title . $after_title;
		w4_post_list( $instance['PL_ID'] );
        echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance 								= $old_instance;
		$instance['title'] 						= strip_tags( $new_instance['title']);
		$instance['PL_ID']				 		= (int) $new_instance['PL_ID'];
		return $instance;
	}

	function form( $instance ){
		$title 						= isset($instance['title']) ? esc_attr($instance['title']) : 'Hit list:';
		$PL_ID				 		= isset($instance['PL_ID']) ? (int)($instance['PL_ID']) : 0;
		
		?>
		<div id="w4pl_widget_admin">
            <p><strong><?php _e( 'Title:', 'w4-post-list'); ?></strong><br />
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" 
            value="<?php echo $title; ?>" /></p>
            
            
			<?php echo dropdown_post_list_selector( $this->get_field_name('PL_ID'), $this->get_field_id('PL_ID'), $PL_ID); ?>

            <div class="w4-post-list-support">
            <?php printf( __( '<a target="_blank" href="%s">Let us know</a> if you face any problem or what additional functions you want from this plugin.', 
			'w4-post-list'), 'http://w4dev.com/w4-plugin/w4-post-list/?ref=widgets#comments' ); ?>
            <?php printf( __( 'You can Rate this on <a target="_blank" href="%s">Wordpress plugin page</a>', 'w4-post-list' ), 
			'http://wordpress.org/extend/plugins/w4-post-list/'); ?>
            </div>
		</div>
		<?php
	}
}
//load Widget==============================
add_action('widgets_init', 'W4PL_Widget_Init');
function W4PL_Widget_Init(){
	register_widget('W4PL_Widget');
}

// For widget
function dropdown_post_list_selector( $select_name, $select_id, $selected = 0){
	global $wpdb;
	$query = $wpdb->prepare( "SELECT * FROM  $wpdb->post_list" );
		
	if ( ! $lists = $wpdb->get_results( $query ))
		return '<p class="red">'. sprintf( __( 'No post list yet. <a href="%s" class="button">Create now &#8212;</a>', 'w4-post-list' ), w4pl_add_url()) .'</strong>';

	$selected = (int) $selected;
	
	$all_post_list .= "<p>". __( 'Select a post list:', 'w4-post-list'). "<br />";
	
	$all_post_list .= "<select name=\"$select_name\" id=\"$select_id\">\n";

	foreach($lists as $list){
		$sel = ($selected == $list->list_id) ? 'selected="selected"' : '';
		$title = empty($list->list_title) ? 'List#' . $list->list_id : $list->list_title;
		$all_post_list .= "<option value=\"$list->list_id\" $sel >$title</option>\n";
	}
	$all_post_list .= "</select>";
	$all_post_list .= sprintf( __( 'or <a class="button" href="%s">create new</a>', 'w4-post-list' ), w4pl_add_url()). "</p>";

	return $all_post_list;
}
?>