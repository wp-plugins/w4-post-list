<?php
/*
Plugin Name: W4 post list
Plugin URI: http://w4dev.com/w4-plugin/w4-post-list
Description: List your wordpress posts as you like in post or page or in sidebar widgets. !!
Version: 1.2
Author: Shazzad Hossain Khan
Author URI: http://w4dev.com/
*/
define( 'W4PL_DIR', plugin_dir_path(__FILE__)) ;
define( 'W4PL_URL', plugin_dir_url(__FILE__)) ;
define( 'W4PL_BASENAME', plugin_basename( __FILE__ )) ;
define( 'W4PL_VERSION', '1.2' ) ;
define( 'W4PL_NAME', 'W4 post list' ) ;
define( 'W4PL_SLUG', strtolower(str_replace(' ', '-', W4PL_NAME ))) ;

//Load our script and css file
class W4PL_CORE {
	function W4PL_CORE(){
		add_action( 'init', array(&$this, 'load_w4pl_scripts')) ;
		add_action( 'admin_menu', array(&$this, 'admin_menu')) ;
		add_action( "admin_init", array(&$this, 'add_post_meta')) ;
		add_action( 'save_post', array(&$this, 'save_details'));
		add_action( 'post_row_actions', array(&$this, 'post_row_actions'));
		add_action( 'plugin_action_links_'.W4PL_BASENAME, array(&$this, 'plugin_action_links' )) ;
	}

	//Load scripts
	function load_w4pl_scripts(){
		//wp_enqueue_script( 'w4pl_js', W4PL_URL . '/w4-post-list.js', array( 'jquery' , 'jquery-ui-core' ), '',false ) ;
		wp_enqueue_style( 'w4pl_css', W4PL_URL . '/w4-post-list.css' ) ;
	}

	function plugin_action_links( $links ){
		$readme_link['readme'] = '<a href="'.esc_attr(admin_url('plugins.php?page='.W4PL_SLUG)).'">'.esc_html( __( 'How to use' )).'</a>';
		return array_merge( $links, $readme_link );
	}

	function post_row_actions($actions){
		global $post;
		$w4pl_post = array();
		$w4pl_post_status = get_post_meta( $post->ID, '_w4pl_post', true );

		if($w4pl_post_status == 0){
			$w4pl_post['w4pl_include'] = '<a href="'.admin_url(sprintf('plugins.php?page=%1$s&amp;action=include&amp;id=%2$d',W4PL_SLUG,$post->ID )).'">' 
			. __('Include in W4 post list') . '</a>' ;
		}else{
			$w4pl_post['w4pl_exclude'] = '<a href="'.admin_url(sprintf('plugins.php?page=%1$s&amp;action=exclude&amp;id=%2$d',W4PL_SLUG,$post->ID )).'">' 
			. __('Exclude from W4 post list') . '</a>' ;
		}

		return array_merge($actions, $w4pl_post);
	}

	function add_post_meta(){
		add_meta_box( "_w4pl_post", "Include in W4 post list ?", array(&$this, 'meta_box'), "post", "normal", "high") ;
	}

	function meta_box() {
		global $post;
		$w4pl_post_status = get_post_meta( $post->ID, '_w4pl_post', true );
		?>
		<p>
		<label><input type="radio" name="_w4pl_post" value="0" <?php if($w4pl_post_status == 0) echo 'checked="checked"'; ?> /> No</label><br />
		<label><input type="radio" name="_w4pl_post" value="1" <?php if($w4pl_post_status == 1) echo 'checked="checked"'; ?> /> Yes</label>
		</p>
		<?php
	}

	
	function save_details( $post_id ){
		global $post;
		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
			return $post_id ;
		if ( isset( $_POST["_w4pl_post"])){
			update_post_meta( $post->ID, "_w4pl_post", $_POST["_w4pl_post"] );
		}
	}
	
	//Add amin page
	function admin_menu(){
		add_plugins_page( W4PL_NAME, W4PL_NAME, 'activate_plugins', W4PL_SLUG, array(&$this, 'admin_page'));

		if($_GET['page'] == W4PL_SLUG){
			$update = false ;
			
			$action = $_GET['action'];
			if($action == 'include'){
				update_post_meta(  $_GET['id'], "_w4pl_post", '1' );
				$update = true ;
			}
			
			if($action == 'exclude'){
				update_post_meta(  $_GET['id'], "_w4pl_post", '0' );
				$update = true ;
			}
			
			if($update){
				wp_redirect(admin_url( 'edit.php' ));
				return ;
			}
		}
	}

	function admin_page(){ ?>
	<div id="tabset_wrapper" class="wrap">
    	<div class="icon32" id="icon-post"><br/></div>
        <h2><?php echo W4PL_NAME. " V:".W4PL_VERSION." Documentation."; ?><span style="font-size:12px; padding-left: 20px ;"><a href="http://w4dev.com" class="us" rel="developer" title="Web and wordpress development...">Developed by &raquo; W4 development</a> <a href="http://w4dev.com/" title="Visit Plugin Site">&raquo; Visit Plugin Site</a> <a href="mailto:sajib1223@gmail.com" rel="tabset_author_mail">&raquo; Mailto:Contact</a></span></h2>
		<ul style="width:60%; margin-left:30px; padding:25px; line-height:24px; font-size:16px; font-family:Georgia, 'Times New Roman', Times, serif;">
        <li>With <a href="http://w4dev.com/w4-plugin/w4-post-list/">W4 development's post list plugin</a>, you can show your category/post list in your <a href="<?php _e(admin_url('widgets.php')); ?>">themes widget area</a>.</li><hr />
        <li>After activating it from <a href="<?php _e(admin_url('plugins.php')); ?>">plugin page</a>, you can Visit the <a href="<?php _e(admin_url('widgets.php')); ?>">widget area</a> for showing or customizing the list in sidebar widget.</li><hr />
        <li>You can include or exclude posts from inside the post edit page or from the <a href="<?php _e(admin_url('edit.php')); ?>">wp-admin post page</a>.<span class="description"> ( From the hover flying menu.ex: edit | quick edit | trash | view | include in W4 post. See Screenshots)</span>.</li><hr />
        <li>Listings pages and more customizable options will be available through later version. Feel free to <a href="http://w4dev.com/w4-plugin/w4-post-list/">contact us</a>, if you found any bugs or you have a wonderful suggestion.</li><hr />
        </ul>
	</div>
<?php
	}
}
//Define widget======================
class W4PL_Widget extends WP_Widget {

	function W4PL_Widget() {
		$widget_ops = array(
					'classname' => 'w4_post_list',
					'description' => __( "List selected posts from selected category...")
				);
		$control_ops = array('width' => 200, 'height' => 400);
		$this->WP_Widget('w4_post_list', __('W4 post list'), $widget_ops,$control_ops );
		$this->alt_option_name = 'w4_post_list';
	}

	function widget($args, $instance){
		extract($args);
		$title = apply_filters('widget_title', empty($instance['title']) ? __('W4 post list') : $instance['title'], $instance, $this->id_base);
		echo $before_widget;
		if( $title ) echo $before_title . $title . $after_title;
		$this->_widget($instance);
        echo $after_widget;
	}
	
	function _widget($instance){
		$category_ids = $instance['categories'] ;
		
		if(empty($category_ids) || !is_array($category_ids)){
			echo "No category selected. Please select one to show its posts." ;
			return ;
		}

		if( $instance['post_content'] == '1' ){
			$new_excerpt_length = create_function('$length', "return " . $instance["excerpt_length"] . ";");
			add_filter('excerpt_length', $new_excerpt_length);
		}

		$category_ids = (array) $category_ids;
		echo "<ul class=\"w4pl_parent\">";
		
		foreach($category_ids as $category_id){
			$category = get_category($category_id) ;
			$category_name = $category->name ;
			$category_title = "<a href=\"".get_category_link($category_id)."\" title=\"View $category_name\">$category_name &raquo;</a>";
			
			$category_posts_ids = get_objects_in_term($category->cat_ID, 'category');
			$selected_posts_ids = array();
			
			foreach($category_posts_ids as $key => $category_posts_id){
				if(get_post_meta($category_posts_id,'_w4pl_post'))
					$selected_posts_ids[] = $category_posts_id;
			}
			
			if($instance['show_category_posts_count'] == '1'){
				$items = count($selected_posts_ids)>0 ? count($selected_posts_ids) : false ;
			
			}elseif($instance['show_category_posts_count'] == '2'){
				$items = count($category_posts_ids)>0 ? count($category_posts_ids) : false ;
			}
	
			$items = $items ? " <span class=\"item_count\">$items items</span>" : '' ;
			
			if($instance['show_category_posts_count'] != '0')
				$category_title .= $items ;

			echo "<li class=\"w4pl_list\">" ;
			echo "<h3 class=\"w4pl_cat_title\">$category_title</h3>" ;
			
			if( $instance['show_post_list'] == '1' ):
			$r = new WP_Query(array('cat' => $category->cat_ID, 'showposts' => '-1', 'posts_per_page' => '-1', 'meta_key' => '_w4pl_post', 'meta_value' => '1', 'post_status' => 'publish'));
			//Checking post
			if($r->have_posts()):
				echo "<ul class=\"w4pl_sub\">" ;
				while($r->have_posts()):
					$r->the_post() ;
					//$time = get_the_time( 'F jS, Y' ) ;
					$time = get_the_time( 'j-m-Y' ) ;
					$post_title = __("<a href=\"".get_permalink()."\" title=\"View ".get_the_title()."\">&raquo; ".get_the_title()."</a>") ;
					if($instance['show_post_date'])
						$post_title .= __(' <small> on ' . $time . '</small>') ;

					echo "<li class=\"w4pl_post_list\">" ;
					echo "<div class=\"w4pl_post_title\">$post_title</div>" ;

					//Excerpt
					if( $instance['post_content'] == '1' ){
						echo "<div class=\"w4pl_post_content\">".get_the_excerpt()."</div>" ;
					}
					
					//Post content
					if( $instance['post_content'] == '2' ){
						echo "<div class=\"w4pl_post_content\">".get_the_content()."</div>" ;
					}

					echo "</li>" ;
				endwhile; //End while
				echo "</ul>" ;
				wp_reset_postdata();
			endif; //Show post
			endif;
			echo "</li>" ;
		}
		echo "</ul>" ;
		//Remove the filter so that other content gets the real excerpt
		remove_filter( 'excerpt_length', $new_excerpt_length);
	}

	function update( $new_instance, $old_instance ) {
		$instance 								= $old_instance;
		$instance['title'] 						= strip_tags( $new_instance['title']);
		$instance['categories'] 				= (array)$new_instance['categories'];
		$instance['show_category_posts_count']	= (int)$new_instance['show_category_posts_count'];
		$instance['show_post_list'] 			= (bool) $new_instance['show_post_list'];
		$instance['show_post_date'] 			= (bool) $new_instance['show_post_date'];
		$instance['post_content']				= (int) $new_instance['post_content'];
		$instance['excerpt_length']				= (int) $new_instance['excerpt_length'];
		return $instance;
	}


	function form( $instance ){
		$title 						= isset($instance['title']) ? esc_attr($instance['title']) : 'Hit list:';
		$categories 				= isset($instance['categories']) ? (array)($instance['categories']) : array();
		$show_category_posts_count 	= isset($instance['show_category_posts_count']) ? (int)($instance['show_category_posts_count']) : 1;
		$show_post_list 			= isset($instance['show_post_list']) ? (bool)($instance['show_post_list']) : 1;
		$show_post_date 			= isset($instance['show_post_date']) ? (bool)($instance['show_post_date']) : 1;
		$post_content 				= isset($instance['post_content']) ? (int)($instance['post_content']) : 0;
		$excerpt_length 			= isset($instance['excerpt_length']) ? (int)($instance['excerpt_length']) : 10;
		
		?>
		<div id="w4pl_widget_admin">
			<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" 
            value="<?php echo $title; ?>" /></p>

            <p>Select category:<br /><small style="color:#AAA;">Manage the post inclsion/exclusion from the post edit page.</small><br />
			<?php echo $this->post_categories_checklist($this->get_field_name('categories'),$this->get_field_id('categories'),$categories); ?></p>
            
			<p><?php _e( 'Show posts count ?' ); ?><br /><small style="color:#AAA;">After the category name.</small>
			<br /><label><input type="radio" <?php checked( $show_category_posts_count, '0' ); ?> name="<?php echo $this->get_field_name('show_category_posts_count'); ?>" value="0"  /> <?php _e( 'Do not show' ); ?></label>
            <br /><label><input type="radio" <?php checked( $show_category_posts_count, '1' ); ?> name="<?php echo $this->get_field_name('show_category_posts_count'); ?>" value="1"  /> <?php _e( 'Show only included post count.' ); ?></label>
            <br /><label><input type="radio" <?php checked( $show_category_posts_count, '2' ); ?> name="<?php echo $this->get_field_name('show_category_posts_count'); ?>" value="2"  /> <?php _e( 'Show the actual category count.' ); ?></label>
            </p>
            
            <p><?php _e( 'Show post list ?' ); ?><br /><small style="color:#AAA;">List post under its category name.</small>
            <br /><label><input type="radio" <?php checked( $show_post_list, false ); ?> name="<?php echo $this->get_field_name('show_post_list'); ?>" value="0"  /> <?php _e( 'No.' ); ?></label>
            <br /><label><input type="radio" <?php checked( $show_post_list, true ); ?> name="<?php echo $this->get_field_name('show_post_list'); ?>" value="1"  /> <?php _e( 'Yes.' ); ?></label>
            </p>

            <p><?php _e( 'Show date appending post title ?' ); ?>
            <br /><label><input type="radio" <?php checked( $show_post_date, false ); ?> name="<?php echo $this->get_field_name('show_post_date'); ?>" value="0"  /> <?php _e( 'No.' ); ?></label>
            <br /><label><input type="radio" <?php checked( $show_post_date, true ); ?> name="<?php echo $this->get_field_name('show_post_date'); ?>" value="1"  /> <?php _e( 'Yes.' ); ?></label>
            </p>
            
            <p><?php _e( 'Show post content ?' ); ?><br /><small style="color:#AAA;">Under the post title.</small>
            <br /><label><input type="radio" <?php checked( $post_content, '0' ); ?> name="<?php echo $this->get_field_name('post_content'); ?>" value="0"  /> <?php _e( 'Do not show content' ); ?></label>
            <br /><label><input type="radio" <?php checked( $post_content, '1' ); ?> name="<?php echo $this->get_field_name('post_content'); ?>" value="1"  /> <?php _e( 'Show only excerpt.' ); ?></label>
            <br /><label><input type="radio" <?php checked( $post_content, '2' ); ?> name="<?php echo $this->get_field_name('post_content'); ?>" value="2"  /> <?php _e( 'Show full content.' ); ?></label>
            </p>

            <p><label for="<?php echo $this->get_field_id('excerpt_length'); ?>"><?php _e('Excerpt length:'); ?></label>
            <br /><small style="color:#AAA;">The content word limit.</small>
			<input type="text" value="<?php echo( $excerpt_length) ; ?>" name="<?php echo $this->get_field_name('excerpt_length'); ?>" 
			id="<?php echo $this->get_field_id('excerpt_length'); ?>" class="widefat"/></p>
            
            <div class="w4-post-list-support">Please support us by letting us know what problem you face or what additional functions you want from this plugin.
            <br /><a target="_blank" href="http://w4dev.com/w4-plugin/w4-post-list">Post a comment on plugin page</a></div>
		</div>
		<?php
	}
	
	function post_categories_checklist( $field_name = null, $field_id = null, $cat_array = array()){
		$categories = get_categories(array('hide_empty' => false));
		foreach( $categories as $category ){
			$checked = in_array($category->cat_ID, $cat_array) ? ' checked="checked" ' : '' ;
				//$checked = array_keys( $cat_array, $category->cat_ID) ? ' checked="checked" ' : '' ;
			$checklists[] = "<label><input name=\"".$field_name."[]\" type=\"checkbox\" $checked value=\"$category->cat_ID\" /> $category->cat_name</label><br />" ;
		}
		$checklists = implode( "\n", $checklists );
		return $checklists ;
	}
}
//load Widget==============================
add_action('widgets_init', 'W4PL_Widget_Init');
function W4PL_Widget_Init() {
  register_widget('W4PL_Widget');
}
//Begin=====================================
new W4PL_CORE();
?>