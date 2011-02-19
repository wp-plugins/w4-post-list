<?php
/*
Plugin Name: W4 post list
Plugin URI: http://w4dev.com/w4-plugin/w4-post-list
Description: List your wordpress posts as you like in post or page or in sidebar widgets. !!
Version: 1.2.6
Author: Shazzad Hossain Khan
Author URI: http://w4dev.com/
*/
define( 'W4PL_DIR', plugin_dir_path(__FILE__)) ;
define( 'W4PL_URL', plugin_dir_url(__FILE__)) ;
define( 'W4PL_BASENAME', plugin_basename( __FILE__ )) ;
define( 'W4PL_VERSION', '1.2.6' ) ;
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
		wp_enqueue_script( 'w4pl_js', W4PL_URL . 'w4-post-list.js', array( 'jquery', 'jquery-ui-core' ), W4PL_VERSION ,true ) ;
		wp_enqueue_style( 'w4pl_css', W4PL_URL . 'w4-post-list.css', '', W4PL_VERSION ) ;
		#load_plugin_textdomain( 'w4-post-list', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	function plugin_action_links( $links ){
		$readme_link['readme'] = '<a href="'.esc_attr(admin_url('plugins.php?page='.W4PL_SLUG)).'">' . __( 'How to use', 'w4-post-list' ).'</a>';
		return array_merge( $links, $readme_link );
	}

	//Add amin page
	function admin_menu(){
		add_plugins_page( W4PL_NAME, W4PL_NAME, 'activate_plugins', W4PL_SLUG, array(&$this, 'admin_page'));
	}

	function admin_page(){ ?>
	<div id="tabset_wrapper" class="wrap">
    	<div class="icon32" id="icon-post"><br/></div>
        <h2><?php echo W4PL_NAME. " V:".W4PL_VERSION." Documentation."; ?><span style="font-size:12px; padding-left: 20px ;"><a href="http://w4dev.com" class="us" rel="developer" title="<?php _e( 'Web and wordpress development...', 'w4-post-list' ); ?>"> by &raquo; W4 development</a> <a href="http://w4dev.com/w4-plugin/w4-post-list/" title="<?php _e( 'Visit Plugin Site', 'w4-post-list' ); ?>">&raquo; <?php _e( 'Visit Plugin Site', 'w4-post-list' ); ?></a> <a href="mailto:sajib1223@gmail.com" rel="tabset_author_mail">&raquo; <?php _e( 'Mailto:Contact', 'w4-post-list' ); ?></a></span></h2>
		<ul style="width:60%; margin-left:30px; padding:25px; line-height:24px; font-size:16px; font-family:Georgia, 'Times New Roman', Times, serif;">
        <li><a href="http://w4dev.com/w4-plugin/w4-post-list/"><?php _e( 'With w4 post list plugin', 'w4-post-list' ); ?></a>, <?php _e( 'you can show your category/post list in your', 'w4-post-list'); ?> <a href="<?php echo admin_url('widgets.php'); ?>"><?php _e( 'themes widget area.', 'w4-post-list' ); ?></a></li><hr />
        <li><?php _e( 'After activating it from', 'w4-post-list' ); ?> <a href="<?php echo admin_url('plugins.php'); ?>"><?php _e( 'plugin page', 'w4-post-list' ); ?></a>, <?php _e( 'you can Visit the', 'w4-post-list'); ?> <a href="<?php echo admin_url('widgets.php'); ?>"><?php _e( 'widget area', 'w4-post-list' ); ?></a> <?php _e( 'for showing or customizing the list in sidebar widget.', 'w4-post-list' ); ?></li><hr />
        <li><?php _e( 'Listings pages and more customizable options will be available through later version. Feel free to', 'w4-post-list' ); ?> <a href="http://w4dev.com/w4-plugin/w4-post-list/" target="_blank"><?php _e( 'contact us', 'w4-post-list' ); ?></a>, <?php _e( 'if you found any bugs or you have a wonderful suggestion.', 'w4-post-list' ); ?></li><hr />
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
					'description' => __( 'List your selected posts or categories or both of them together...', 'w4-post-list' )
				);
		$control_ops = array('width' => 200, 'height' => 400);
		$this->WP_Widget('w4_post_list', 'W4 post list', $widget_ops,$control_ops );
		$this->alt_option_name = 'w4_post_list';
	}

	function widget($args, $instance){
		$w4pl = new W4PL_CORE();		
		extract($args);
		$title = apply_filters('widget_title', empty($instance['title']) ? 'W4 post list' : __( $instance['title'], 'w4-post-list' ), $instance, $this->id_base);
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
			_e( 'No category or post selected. Please select one to show here.', 'w4-post-list' );
			return ;
		}
		
		$category_ids = (array) $category_ids;
		$_content = "<ul class=\"w4pl_parent\">";

		foreach($category_ids as $category_id){
			$selected_posts_ids = (array) $cat_posts[$category_id];
			
			if(!isset($all_posts_ids) && !is_array($all_posts_ids))
				$all_posts_ids = $selected_posts_ids;
			$all_posts_ids = array_merge($selected_posts_ids, $all_posts_ids);

			
			if($cat_max[$category_id] != 0)
				$max_show = intval($cat_max[$category_id]);
			
			elseif(count($selected_posts_ids) == 0)
				$max_show = '0';
			
			else
				$max_show = '-1';

			$args = array('post__in' => $selected_posts_ids, 'showposts' => $max_show, 'posts_per_page' => $max_show, 'post_status' => 'publish');

			$category = get_category($category_id) ;
			$category_name = $category->name ;
			$category_title = '<a href="' . get_category_link($category_id) .'" title="'. sprintf( __( 'View all in', 'w4-post-list') .' %s', $category_name ) .'">'.$category_name.' &raquo;</a>';
			
			//Show selected post count
			if($instance['show_category_posts_count'] == '1'){
				$items = count($selected_posts_ids) > 0 ? count($selected_posts_ids) : false;
				if($max_show != '-1' && $items > $max_show)
					$items = $max_show;
			
			//Show actual post count
			}elseif($instance['show_category_posts_count'] == '2'){
				$items = $category->count > 0 ? $category->count : false ;
			}

			$items_text = $items ? sprintf( _n( __('one post', 'w4-post-list'), '%1$s '. __('posts', 'w4-post-list'), $items), $items): __('no post', 'w4-post-list');
			$items = ' <abbr class="item_count" title="'. sprintf( '%1$s '.__('listed under', 'w4-post-list').' %2$s', $items_text, $category_name) .'">'. $items_text .'</abbr>';
			
			if($instance['show_category_posts_count'] != '0')
				$category_title .= $items;

			$category_li_class = "w4pl_list";

			if($instance['list_effect'] == '1'):
				$category_li_class = "w4pl_list list_effect open";
				$category_title = "<span class=\"showhide_w4pl\" title=\"Hide list\"></span>" . $category_title;
			
			elseif($instance['list_effect'] == '2'):
				$category_li_class = "w4pl_list list_effect close";
				$category_title = "<span class=\"showhide_w4pl\" title=\"Show list\"></span>" . $category_title;

			endif;
			
			$_content .= "<li class=\"$category_li_class\">";
				$_content .= "$category_title";
				if(count($selected_posts_ids) != 0)
					$_content .=  $this->_post_list($instance, $args);

			$_content .= "</li>";
		}
		$_content .= "</ul>";
		
		if( $instance['list_type'] == '1')
			return $this->_post_list($instance, array('post__in' => $all_posts_ids));

		return $_content;
	}

	function _post_list($instance, $args){
		if( $instance['list_type'] == '2')
			return;
		
		if( $instance['post_content'] == '1' ):
			$new_excerpt_length = create_function('$length', "return " . $instance["excerpt_length"] . ";");
			add_filter('excerpt_length', $new_excerpt_length);
		endif;
		
		$defaults = array('post_status' => 'publish', 'showposts' => '-1', 'posts_per_page' => '-1');
		$args = wp_parse_args( $args, $defaults );

		
		query_posts($args);
		//Checking post
		if(have_posts()):
			$post_list = "<ul class=\"w4pl_sub\">" ;
			while(have_posts()):
				the_post() ;

				$post_title = __("<a href=\"".get_permalink()."\" title=\"View ".get_the_title()."\">".get_the_title()."</a>") ;
				if($instance['show_post_date'])
					$post_title .= sprintf(' <abbr class="small" title="%2$s"><strong>' . __("Published:", "w4-post-list") . '</strong> %1$s</abbr>', get_the_time('j-m-Y'), get_the_time('g:i a')) ;
				
				if($instance['show_post_modified_time'])
					$post_title .= sprintf(' <abbr class="small" title="%2$s"><strong>' . __("Updated:", "w4-post-list") . '</strong> %1$s</abbr>', get_post_modified_time('j-m-Y'), get_post_modified_time('g:i a')) ;
					

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
		$instance['list_effect']				= (int) $new_instance['list_effect'];

		$instance['categories'] 				= (array) $new_instance['categories'];
		$instance['max'] 						= (array) $new_instance['max'];
		$instance['posts'] 						= (array) $new_instance['posts'];
		
		$instance['show_category_posts_count']	= (int) $new_instance['show_category_posts_count'];
		$instance['show_post_date'] 			= (bool) $new_instance['show_post_date'];
		$instance['show_post_modified_time'] 	= (bool) $new_instance['show_post_modified_time'];
		
		$instance['post_content']				= (int) $new_instance['post_content'];
		$instance['excerpt_length']				= (int) $new_instance['excerpt_length'];
		return $instance;
	}


	function form( $instance ){
		$title 						= isset($instance['title']) ? esc_attr($instance['title']) : 'Hit list:';
		$list_type				 	= isset($instance['list_type']) ? (int)($instance['list_type']) : 0;
		$list_effect 				= isset($instance['list_effect']) ? (int)($instance['list_effect']) : 0;
		
		$categories 				= isset($instance['categories']) ? (array)($instance['categories']) : array(array());
		$show_category_posts_count 	= isset($instance['show_category_posts_count']) ? (int)($instance['show_category_posts_count']) : 1;

		$show_post_date 			= isset($instance['show_post_date']) ? (bool)($instance['show_post_date']) : 1;
		$show_post_modified_time	= isset($instance['show_post_modified_time']) ? (bool)($instance['show_post_modified_time']) : 1;
		$post_content 				= isset($instance['post_content']) ? (int)($instance['post_content']) : 0;
		$excerpt_length 			= isset($instance['excerpt_length']) ? (int)($instance['excerpt_length']) : 10;
		
		?>
		<div id="w4pl_widget_admin">
            <p><strong><?php _e( 'Title:', 'w4-post-list'); ?></strong>
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" 
            value="<?php echo $title; ?>" /></p>

			<p><strong><?php _e( 'List type:', 'w4-post-list' ); ?></strong><br />
            <small style="color:#AAA;"><?php _e( 'Kind of list you need.', 'w4-post-list'); ?></small>
			<br /><label><input type="radio" <?php checked( $list_type, '0' ); ?> name="<?php echo $this->get_field_name('list_type'); ?>" value="0"  /> <?php _e( 'Posts with categories', 'w4-post-list' ); ?></label>
            <br /><label><input type="radio" <?php checked( $list_type, '1' ); ?> name="<?php echo $this->get_field_name('list_type'); ?>" value="1"  /> <?php _e( 'Only posts', 'w4-post-list' ); ?></label>
            <br /><label><input type="radio" <?php checked( $list_type, '2' ); ?> name="<?php echo $this->get_field_name('list_type'); ?>" value="2"  /> <?php _e( 'Only categories', 'w4-post-list' ); ?></label>
            </p>

			<p><strong><?php _e( 'Show posts in category with a jquery slide effect ?', 'w4-post-list' ); ?></strong><br />
            <small style="color:#AAA;"><?php _e( 'Under the post title.', 'w4-post-list' ); ?></small>
            <br /><label><input type="radio" <?php checked( $list_effect, '0' ); ?> name="<?php echo $this->get_field_name('list_effect'); ?>" value="0"  /> <?php _e( 'Not neccessary', 'w4-post-list' ); ?></label>
            <br /><label><input type="radio" <?php checked( $list_effect, '1' ); ?> name="<?php echo $this->get_field_name('list_effect'); ?>" value="1"  /> <?php _e( 'Yap, do it', 'w4-post-list' ); ?></label>
            <br /><label><input type="radio" <?php checked( $list_effect, '2' ); ?> name="<?php echo $this->get_field_name('list_effect'); ?>" value="2"  /> <?php _e( 'Do it. Also make the posts invisible at primary position', 'w4-post-list' ); ?></label>
            </p>


            <p><strong><?php _e( 'Select category:', 'w4-post-list'); ?></strong><br />
            <small style="color:#AAA;"><?php _e( 'Hit save after selecting a category to make the category inside post show up below.', 'w4-post-list'); ?></small></p>
			<?php echo $this->post_categories_checklist($instance); ?>
            
			<br /><p><strong><?php _e( 'Show posts count appending to category name ?', 'w4-post-list' ); ?></strong><br />
            <small style="color:#AAA;"><?php _e( 'Will appear after the category name.', 'w4-post-list' ); ?></small>
			<br /><label><input type="radio" <?php checked( $show_category_posts_count, '0' ); ?> name="<?php echo $this->get_field_name('show_category_posts_count'); ?>" value="0"  /> <?php _e( 'Do not show', 'w4-post-list' ); ?></label>
            <br /><label><input type="radio" <?php checked( $show_category_posts_count, '1' ); ?> name="<?php echo $this->get_field_name('show_category_posts_count'); ?>" value="1"  /> <?php _e( 'Show only included post count', 'w4-post-list' ); ?></label>
            <br /><label><input type="radio" <?php checked( $show_category_posts_count, '2' ); ?> name="<?php echo $this->get_field_name('show_category_posts_count'); ?>" value="2"  /> <?php _e( 'Show the actual category count', 'w4-post-list' ); ?></label>
            </p>
            
            <p><strong><?php _e( 'Show published date appending to post title ?', 'w4-post-list' ); ?></strong><br />
            <label><input type="radio" <?php checked( $show_post_date, false ); ?> name="<?php echo $this->get_field_name('show_post_date'); ?>" value="0"  /> <?php _e( 'No', 'w4-post-list' ); ?></label>
            <br /><label><input type="radio" <?php checked( $show_post_date, true ); ?> name="<?php echo $this->get_field_name('show_post_date'); ?>" value="1"  /> <?php _e( 'Yes', 'w4-post-list' ); ?></label>
            </p>
            
            <p><strong><?php _e( 'Show last post-update time appending to post title ?', 'w4-post-list' ); ?></strong><br />
            <label><input type="radio" <?php checked( $show_post_modified_time, false ); ?> name="<?php echo $this->get_field_name('show_post_modified_time'); ?>" value="0"  /> <?php _e( 'No', 'w4-post-list' ); ?></label>
            <br /><label><input type="radio" <?php checked( $show_post_modified_time, true ); ?> name="<?php echo $this->get_field_name('show_post_modified_time'); ?>" value="1"  /> <?php _e( 'Yes', 'w4-post-list' ); ?></label>
            </p>
            
            <p><strong><?php _e( 'Show post content ?', 'w4-post-list' ); ?></strong><br />
            <small style="color:#AAA;">Under the post title.</small>
            <br /><label><input type="radio" <?php checked( $post_content, '0' ); ?> name="<?php echo $this->get_field_name('post_content'); ?>" value="0"  /> <?php _e( 'Do not show content', 'w4-post-list' ); ?></label>
            <br /><label><input type="radio" <?php checked( $post_content, '1' ); ?> name="<?php echo $this->get_field_name('post_content'); ?>" value="1"  /> <?php _e( 'Show only excerpt', 'w4-post-list' ); ?></label>
            <br /><label><input type="radio" <?php checked( $post_content, '2' ); ?> name="<?php echo $this->get_field_name('post_content'); ?>" value="2"  /> <?php _e( 'Show full content', 'w4-post-list' ); ?></label>
            </p>

            <p><label for="<?php echo $this->get_field_id('excerpt_length'); ?>"><strong><?php _e('Excerpt length:', 'w4-post-list'); ?></strong></label><br />
            <small style="color:#AAA;"><?php _e( 'The content word limit.', 'w4-post-list'); ?></small>
			<input type="text" value="<?php echo( $excerpt_length) ; ?>" name="<?php echo $this->get_field_name('excerpt_length'); ?>" 
			id="<?php echo $this->get_field_id('excerpt_length'); ?>" class="widefat"/></p>
            
            <div class="w4-post-list-support">
            <a target="_blank" href="http://w4dev.com/w4-plugin/w4-post-list"><?php _e( 'Reply on plugin page', 'w4-post-list' ); ?></a>
            <?php _e( 'Please support us by letting us know what problem you face or what additional functions you want from this plugin.', 'w4-post-list' ); ?>
            <a target="_blank" href="http://www.facebook.com/w4dev"><?php _e( 'Find us on facebook', 'w4-post-list'); ?></a>
            </div>
		</div>
		<?php
	}
	
	function post_categories_checklist($instance){
		$categories = get_categories(array('hide_empty' => false));
		$cat_ids = (array)$instance['categories'];
		$cat_max = (array)$instance['max'];
		$cat_posts = (array)$instance['posts'];
		
		foreach( $categories as $category ){
			$checked = in_array($category->cat_ID, $cat_ids) ? ' checked="checked" ' : '' ;
			
			//Category name
			$checklists[] = "<p class=\"cat_title\"><label><input name=\"" . $this->get_field_name('categories') . "[$category->cat_ID]\" type=\"checkbox\" $checked value=\"$category->cat_ID\" class=\"w4pl_cat_checkbox\" /> $category->cat_name</strong></label></p>" ;
			
			//Post listin of this category
			$class = ('' == $checked) ? 'hide_box' : '';
			$checklists[] .= "<div id=\"w4pl_postbox\" class=\"w4pl_postbox $class\">";

			query_posts(array('cat' => $category->cat_ID, 'showposts' => '-1', 'posts_per_page' => '-1', 'post_status' => 'publish'));
			if(have_posts()):

			//Maximum number of posts to show for the selected category
			$checklists[] = "<label><input size=\"3\" name=\"".$this->get_field_name('max')."[$category->cat_ID]\" type=\"text\" value=\"".$cat_max[$category->cat_ID]."\" /> "
			 . __( 'Maximum posts to show on front.', 'w4-post-list') . "</label><br />" ;
			
			$checklists[] .= "<span class=\"w4pl_select_posts\">". __( 'Select posts:', 'w4-post-list' ) ."</span><br />";
			while(have_posts()): the_post();
			$checked2 = in_array(get_the_ID(), (array)$cat_posts[$category->cat_ID]) ? ' checked="checked" ' : '' ;
			$checklists[] .= "<label><input name=\"".$this->get_field_name('posts')."[$category->cat_ID][]\" type=\"checkbox\" $checked2 value=\"".get_the_ID()."\" /> ". get_the_title().'</label><br />' ;
			endwhile;
			
			else:
			$checklists[] = __( 'No posts in this cat', 'w4-post-list' );
			endif;

			$checklists[] = "</div>";
		}
		if(!is_array($checklists))
			$checklists = (array) $checklists ;
		
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
$w4pl = new W4PL_CORE();
?>