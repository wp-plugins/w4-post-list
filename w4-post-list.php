<?php
/*
Plugin Name: W4 post list
Plugin URI: http://w4dev.com/w4-plugin/w4-post-list
Description: List your wordpress posts as you like in post or page or in sidebar widgets. !!
Version: 1.2.3
Author: Shazzad Hossain Khan
Author URI: http://w4dev.com/
*/
define( 'W4PL_DIR', plugin_dir_path(__FILE__)) ;
define( 'W4PL_URL', plugin_dir_url(__FILE__)) ;
define( 'W4PL_BASENAME', plugin_basename( __FILE__ )) ;
define( 'W4PL_VERSION', '1.2.3' ) ;
define( 'W4PL_NAME', 'W4 post list' ) ;
define( 'W4PL_SLUG', strtolower(str_replace(' ', '-', W4PL_NAME ))) ;

//Load our script and css file
class W4PL_CORE {
	function W4PL_CORE(){
		add_action( 'init', array(&$this, 'load_w4pl_scripts')) ;
		add_action( 'admin_menu', array(&$this, 'admin_menu')) ;
		add_action( 'plugin_action_links_'.W4PL_BASENAME, array(&$this, 'plugin_action_links' )) ;
	}

	//Load scripts
	function load_w4pl_scripts(){
		wp_enqueue_script( 'w4pl_js', W4PL_URL . 'w4-post-list.js', array( 'jquery' ), W4PL_VERSION ,true ) ;
		wp_enqueue_style( 'w4pl_css', W4PL_URL . 'w4-post-list.css', '', W4PL_VERSION ) ;
	}

	function plugin_action_links( $links ){
		$readme_link['readme'] = '<a href="'.esc_attr(admin_url('plugins.php?page='.W4PL_SLUG)).'">'.esc_html( __( 'How to use' )).'</a>';
		return array_merge( $links, $readme_link );
	}

	//Add amin page
	function admin_menu(){
		add_plugins_page( W4PL_NAME, W4PL_NAME, 'activate_plugins', W4PL_SLUG, array(&$this, 'admin_page'));
	}

	function admin_page(){ ?>
	<div id="tabset_wrapper" class="wrap">
    	<div class="icon32" id="icon-post"><br/></div>
        <h2><?php echo W4PL_NAME. " V:".W4PL_VERSION." Documentation."; ?><span style="font-size:12px; padding-left: 20px ;"><a href="http://w4dev.com" class="us" rel="developer" title="Web and wordpress development...">Developed by &raquo; W4 development</a> <a href="http://w4dev.com/" title="Visit Plugin Site">&raquo; Visit Plugin Site</a> <a href="mailto:sajib1223@gmail.com" rel="tabset_author_mail">&raquo; Mailto:Contact</a></span></h2>
		<ul style="width:60%; margin-left:30px; padding:25px; line-height:24px; font-size:16px; font-family:Georgia, 'Times New Roman', Times, serif;">
        <li>With <a href="http://w4dev.com/w4-plugin/w4-post-list/">W4 development's post list plugin</a>, you can show your category/post list in your <a href="<?php _e(admin_url('widgets.php')); ?>">themes widget area</a>.</li><hr />
        <li>After activating it from <a href="<?php _e(admin_url('plugins.php')); ?>">plugin page</a>, you can Visit the <a href="<?php _e(admin_url('widgets.php')); ?>">widget area</a> for showing or customizing the list in sidebar widget.</li><hr />
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
		echo "<div id=\"w4_post_list\">";
		echo $this->_widget($instance);
		echo "</div>";
        echo $after_widget;
	}
	
	function _widget($instance){
		$category_ids = (array)$instance['categories'] ;
		$cat_max = (array)$instance['max'];
		$cat_posts = (array)$instance['posts'];
		
		
		if(empty($category_ids) || !is_array($category_ids)){
			echo "No category selected. Please select one to show its posts." ;
			return ;
		}
		
		//retrive selected categories id
		$category_ids = (array) $category_ids;
		$_content = "<ul class=\"w4pl_parent\">";

		foreach($category_ids as $category_id){
			$selected_posts_ids = (array)$cat_posts[$category_id];
			
			//All selected posts in all category
			if(!isset($all_posts_ids) && !is_array($all_posts_ids))
				$all_posts_ids = $selected_posts_ids;
			$all_posts_ids = array_merge($selected_posts_ids, $all_posts_ids);

			$max_show = ($cat_max[$category_id] != 0 ) ? intval($cat_max[$category_id]) : '-1' ;
			//print_r($max_show);
			$args = array('post__in' => $selected_posts_ids, 'showposts' => $max_show, 'posts_per_page' => $max_show, 'post_status' => 'publish');

			




			$category = get_category($category_id) ;
			$category_name = $category->name ;
			$category_title = "<a href=\"".get_category_link($category_id)."\" title=\"View all in $category_name\">$category_name &raquo;</a>";
			
			//$category_posts_ids = get_objects_in_term($category->cat_ID, 'category');
			//print_r($selected_posts_ids);
			if($instance['show_category_posts_count'] == '1'){
				$items = count($selected_posts_ids) > 0 ? count($selected_posts_ids) : false ;
			
			}elseif($instance['show_category_posts_count'] == '2'){
				$items = count($category_posts_ids) > 0 ? count($category_posts_ids) : false ;
			}
	
			$items = $items ? " <span class=\"item_count\">$items items</span>" : '' ;
			
			if($instance['show_category_posts_count'] != '0')
				$category_title .= $items ;

			//$category_title = "<a href=\"javascript:void(0);\" class=\"showhide_w4pl\"></a>".$category_title ;
			$_content .= "<li class=\"w4pl_list\">";
				$_content .= "$category_title";
				//print_r($selected_posts_ids);
				$_content .=  $this->_post_list($instance, $args);
			$_content .= "</li>" ;
		}
		$_content .= "</ul>" ;
		
		if( $instance['list_type'] == '1')
			return $this->_post_list($instance, array('post__in' => $all_posts_ids));

		return $_content;
	}

	function _post_list($instance, $args){
		if( $instance['list_type'] == '2' && count($selected_posts_ids) == 0)
			return;
		
		if( $instance['post_content'] == '1' ):
			$new_excerpt_length = create_function('$length', "return " . $instance["excerpt_length"] . ";");
			add_filter('excerpt_length', $new_excerpt_length);
		endif;
		
		$defaults = array('post_status' => 'publish');
		$args = wp_parse_args( $args, $defaults );

		
		query_posts($args);
		//Checking post
		if(have_posts()):
			$post_list = "<ul class=\"w4pl_sub\">" ;
			while(have_posts()):
				the_post() ;
				//$time = get_the_time( 'F jS, Y' ) ;
				$time = get_the_time( 'j-m-Y' ) ;
				$post_title = __("<a href=\"".get_permalink()."\" title=\"View ".get_the_title()."\">".get_the_title()."</a>") ;
				if($instance['show_post_date'])
				$post_title .= __(' <small> on ' . $time . '</small>') ;

				$post_list .= "<li class=\"w4pl_post_list\">" ;
				$post_list .= "<div class=\"w4pl_post_title\">$post_title</div>" ;

				//Excerpt
				if( $instance['post_content'] == '1' ){
					$post_list .= "<div class=\"w4pl_post_content\">".get_the_excerpt()."</div>" ;
				
				}elseif( $instance['post_content'] == '2' ){
					$post_list .= "<div class=\"w4pl_post_content\">".get_the_content()."</div>" ;
				}

				$post_list .= "</li>" ;
			endwhile;
			$post_list .= "</ul><!--End post list-->" ;
			wp_reset_query();
		endif; //End-if(have_posts()):
		
		//Remove the filter so that other content gets the real excerpt
		remove_filter( 'excerpt_length', $new_excerpt_length);
		return $post_list;
	}

	function update( $new_instance, $old_instance ) {
		$instance 								= $old_instance;
		$instance['title'] 						= strip_tags( $new_instance['title']);
		$instance['list_type']				 	= (int) ($new_instance['list_type']);
		$instance['categories'] 				= (array) $new_instance['categories'];
		$instance['max'] 						= (array) $new_instance['max'];
		$instance['posts'] 						= (array) $new_instance['posts'];
		
		$instance['show_category_posts_count']	= (int) $new_instance['show_category_posts_count'];
		//$instance['show_post_list'] 			= (bool) $new_instance['show_post_list'];
		$instance['show_post_date'] 			= (bool) $new_instance['show_post_date'];
		$instance['post_content']				= (int) $new_instance['post_content'];
		$instance['excerpt_length']				= (int) $new_instance['excerpt_length'];
		return $instance;
	}


	function form( $instance ){
		$title 						= isset($instance['title']) ? esc_attr($instance['title']) : 'Hit list:';
		$list_type				 	= isset($instance['list_type']) ? (int)($instance['list_type']) : 0;
		$categories 				= isset($instance['categories']) ? (array)($instance['categories']) : array(array());
		$show_category_posts_count 	= isset($instance['show_category_posts_count']) ? (int)($instance['show_category_posts_count']) : 1;
		//$show_post_list 			= isset($instance['show_post_list']) ? (bool)($instance['show_post_list']) : 1;
		$show_post_date 			= isset($instance['show_post_date']) ? (bool)($instance['show_post_date']) : 1;
		$post_content 				= isset($instance['post_content']) ? (int)($instance['post_content']) : 0;
		$excerpt_length 			= isset($instance['excerpt_length']) ? (int)($instance['excerpt_length']) : 10;
		
		?>
		<div id="w4pl_widget_admin">
            <p><?php _e('<strong>Title:</strong>'); ?>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" 
            value="<?php echo $title; ?>" /></p>

			<p><?php _e( '<strong>List type ?</strong>' ); ?><br /><small style="color:#AAA;">Kind of list you need.</small>
			<br /><label><input type="radio" <?php checked( $list_type, '0' ); ?> name="<?php echo $this->get_field_name('list_type'); ?>" value="0"  /> <?php _e( 'Posts with categories' ); ?></label>
            <br /><label><input type="radio" <?php checked( $list_type, '1' ); ?> name="<?php echo $this->get_field_name('list_type'); ?>" value="1"  /> <?php _e( 'Only posts.' ); ?></label>
            <br /><label><input type="radio" <?php checked( $list_type, '2' ); ?> name="<?php echo $this->get_field_name('list_type'); ?>" value="2"  /> <?php _e( 'Only categories.' ); ?></label>
            </p>


            <p><strong>Select category:</strong><br /><small style="color:#AAA;">Manage the post inclsion/exclusion from the post edit page.</small></p>
			<?php echo $this->post_categories_checklist($instance); ?>
            
			<p><?php _e( '<strong>Show posts count ?</strong>' ); ?><br /><small style="color:#AAA;">After the category name.</small>
			<br /><label><input type="radio" <?php checked( $show_category_posts_count, '0' ); ?> name="<?php echo $this->get_field_name('show_category_posts_count'); ?>" value="0"  /> <?php _e( 'Do not show' ); ?></label>
            <br /><label><input type="radio" <?php checked( $show_category_posts_count, '1' ); ?> name="<?php echo $this->get_field_name('show_category_posts_count'); ?>" value="1"  /> <?php _e( 'Show only included post count.' ); ?></label>
            <br /><label><input type="radio" <?php checked( $show_category_posts_count, '2' ); ?> name="<?php echo $this->get_field_name('show_category_posts_count'); ?>" value="2"  /> <?php _e( 'Show the actual category count.' ); ?></label>
            </p>
            
            <!--<p><?php #_e( '<strong>Show post list ?</strong>' ); ?><br /><small style="color:#AAA;">List post under its category name.</small>
            <br /><label><input type="radio" <?php #checked( $show_post_list, false ); ?> name="<?php #echo $this->get_field_name('show_post_list'); ?>" value="0"  /> <?php #_e( 'No.' ); ?></label>
            <br /><label><input type="radio" <?php #checked( $show_post_list, true ); ?> name="<?php #echo $this->get_field_name('show_post_list'); ?>" value="1"  /> <?php #_e( 'Yes.' ); ?></label>
            </p>-->

            <p><?php _e( '<strong>Show date appending post title ?</strong>' ); ?>
            <br /><label><input type="radio" <?php checked( $show_post_date, false ); ?> name="<?php echo $this->get_field_name('show_post_date'); ?>" value="0"  /> <?php _e( 'No.' ); ?></label>
            <br /><label><input type="radio" <?php checked( $show_post_date, true ); ?> name="<?php echo $this->get_field_name('show_post_date'); ?>" value="1"  /> <?php _e( 'Yes.' ); ?></label>
            </p>
            
            <p><?php _e( '<strong>Show post content ?</strong>' ); ?><br /><small style="color:#AAA;">Under the post title.</small>
            <br /><label><input type="radio" <?php checked( $post_content, '0' ); ?> name="<?php echo $this->get_field_name('post_content'); ?>" value="0"  /> <?php _e( 'Do not show content' ); ?></label>
            <br /><label><input type="radio" <?php checked( $post_content, '1' ); ?> name="<?php echo $this->get_field_name('post_content'); ?>" value="1"  /> <?php _e( 'Show only excerpt.' ); ?></label>
            <br /><label><input type="radio" <?php checked( $post_content, '2' ); ?> name="<?php echo $this->get_field_name('post_content'); ?>" value="2"  /> <?php _e( 'Show full content.' ); ?></label>
            </p>

            <p><label for="<?php echo $this->get_field_id('excerpt_length'); ?>"><?php _e('<strong>Excerpt length:</strong>'); ?></label>
            <br /><small style="color:#AAA;">The content word limit.</small>
			<input type="text" value="<?php echo( $excerpt_length) ; ?>" name="<?php echo $this->get_field_name('excerpt_length'); ?>" 
			id="<?php echo $this->get_field_id('excerpt_length'); ?>" class="widefat"/></p>
            
            <div class="w4-post-list-support">
            <a target="_blank" href="http://w4dev.com/w4-plugin/w4-post-list">Reply on plugin page</a>
            Please support us by letting us know what problem you face or what additional functions you want from this plugin.
            <a target="_blank" href="http://www.facebook.com/w4dev">Find us on facebook</a>
            </div>
		</div>
		<?php
	}
	
	function post_categories_checklist($instance){
		$categories = get_categories(array('hide_empty' => false));
		$cat_ids = (array)$instance['categories'];
		$cat_max = (array)$instance['max'];
		$cat_posts = (array)$instance['posts'];
		
		//print_r($cat_posts);
		
		foreach( $categories as $category ){
			$checked = in_array($category->cat_ID, $cat_ids) ? ' checked="checked" ' : '' ;
			
			//Category name
			$checklists[] = "<p class=\"cat_title\"><label><input name=\"" . $this->get_field_name('categories') . "[$category->cat_ID]\" type=\"checkbox\" $checked value=\"$category->cat_ID\" class=\"w4pl_cat_checkbox\" /> $category->cat_name</strong></label></p>" ;
			
			//Post listin of this category
			query_posts(array('cat' => $category->cat_ID, 'showposts' => '-1', 'posts_per_page' => '-1', 'post_status' => 'publish'));
			if(have_posts()):
			$class = ('' == $checked) ? 'hide_box' : '';
			$checklists[] .= "<div id=\"w4pl_postbox\" class=\"w4pl_postbox $class\">";
			//Maximum number of posts to show for the selected category
			$checklists[] .= "<label><input size=\"3\" name=\"".$this->get_field_name('max')."[$category->cat_ID]\" type=\"text\" value=\"".$cat_max[$category->cat_ID]."\" /> Maximum posts to show.</label><br />" ;
			
			$checklists[] .= "<span class=\"w4pl_select_posts\">Select posts:</span><br />";
			while(have_posts()): the_post();
			$checked2 = in_array(get_the_ID(), (array)$cat_posts[$category->cat_ID]) ? ' checked="checked" ' : '' ;
			$checklists[] .= "<label><input name=\"".$this->get_field_name('posts')."[$category->cat_ID][]\" type=\"checkbox\" $checked2 value=\"".get_the_ID()."\" /> ". get_the_title().'</label><br />' ;
			endwhile;

			$checklists[] .= "</div>";
			
			
			else:
			$checklists[] .= "No posts in his cat";
			endif;
			
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