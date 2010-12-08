<?php
/*
Plugin Name: W4 post list
Plugin URI: http://w4dev.com/w4-plugin/w4-post-list
Description: List your wordpress posts as you like in post or page or in sidebar widgets. !!
Version: 1.0
Author: Shazzad Hossain Khan
Author URI: http://w4dev.com/
*/
define( 'W4PL_DIR', plugin_dir_path(__FILE__)) ;
define( 'W4PL_URL', plugin_dir_url(__FILE__)) ;
define( 'W4PL_BASENAME', plugin_basename( __FILE__ )) ;
define( 'W4PL_VERSION', '1.0' ) ;
define( 'W4PL_NAME', 'W4 post list' ) ;
define( 'W4PL_SLUG', strtolower(str_replace(' ', '-', W4PL_NAME ))) ;

//Load our script and css file
class W4PL {
	function W4PL(){
		add_action( 'init', array(&$this, 'load_w4pl_scripts')) ;
		add_action( 'admin_menu', array(&$this, 'admin_menu')) ;
		add_action( 'widgets_init', array(&$this, 'load_w4pl_widget')) ;
		add_action( "admin_init", array(&$this, 'add_post_meta')) ;
		add_action( 'save_post', array(&$this, 'save_details'));
		add_action( 'post_row_actions', array(&$this, 'post_row_actions'));
		add_action( 'plugin_action_links_'.W4PL_BASENAME, array(&$this, 'plugin_action_links' )) ;
	}

	//Load scripts
	function load_w4pl_scripts(){
		wp_enqueue_script( 'w4pl_js', W4PL_URL . '/w4-post-list.js', array( 'jquery' , 'jquery-ui-core' ), '',false ) ;
		wp_enqueue_style( 'w4pl_css', W4PL_URL . '/w4-post-list.css' ) ;
	}

	//Load widgets
	function load_w4pl_widget(){
		wp_register_sidebar_widget( 'widget_w4pl', 'W4 post list', array(&$this, 'widget_w4pl'), 'widget_w4pl');
		wp_register_widget_control( 'widget_w4pl', 'W4 post list', array(&$this, 'w4pl_widget_control'), 'widget_w4pl');
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

	//Widget
	function widget_w4pl( $args = array()){
		extract( $args ) ;
		$w4pl_options = get_option("widget_w4pl") ;
	 
		echo( $before_widget );
		echo( $before_title );
		echo( $w4pl_options['title'] );
		echo( $after_title );
		echo( "<div id=\"w4pl_sidebar\">" );
		$this->widget_w4pl_sidebar() ;
		echo( "</div>" );
		echo( $after_widget );
	}

	//Widget
	function widget_w4pl_sidebar(){
		$w4pl_options = get_option("widget_w4pl") ;
		$category_ids = $w4pl_options['w4pl_cat_ids'] ;
		
		if(empty($category_ids)){
			echo "No category selected. Please select one to show its posts." ;
			return ;
		}
		
		echo "<ul class=\"w4pl_parent\">";
		foreach($category_ids as $category_id){
			global $post ;
			$category = get_category($category_id) ;
			$category_name = $category->name ;
			$items = ($category->category_count > 0) ? "$category->category_count items" : "no items" ;
			$items = " <span class=\"item_count\">$items</span>" ;

			echo "<li class=\"w4pl_list\">" ;
			echo "<h3 class=\"w4pl_cat_title\"><a href=\"".get_category_link($category_id)."\" title=\"View $category_name\">$category_name &raquo;</a>$items</h3>" ;
			
			if( $w4pl_options['w4pl_show_post'] == '1' ){
			//query posts
			query_posts(array('cat' => $category->cat_ID, 'showposts' => '-1', 'posts_per_page' => '-1', 'meta_key' => '_w4pl_post', 'meta_value' => '1' )) ;
			//Checking post
			if(have_posts()){
				echo "<ul class=\"w4pl_sub\">" ;
				while(have_posts()){
					the_post() ;
					$time = get_the_time( 'F jS, Y' ) ;
					$post_title = "<a href=\"".get_permalink()."\" title=\"View ".get_the_title()."\">&raquo; ".get_the_title()."</a>" ;
					echo "<li class=\"w4pl_post_list\">" ;
					echo "<div class=\"w4pl_post_title\">$post_title - <small>$time</small></div>" ;

					//Excerpt
					if( $w4pl_options['w4pl_post_content'] == '1' ){
						add_filter( 'excerpt_length', array(&$this, 'w4pl_excerpt_length'));
						echo "<div class=\"w4pl_post_content\">".get_the_excerpt()."</div>" ;
					}
					
					//Post content
					if( $w4pl_options['w4pl_post_content'] == '2' ){
						echo "<div class=\"w4pl_post_content\">".get_the_content()."</div>" ;
					}

					echo "</li>" ;
				}				
				echo "</ul>" ;
			} //End have_posts()
			} //Show post
			echo "</li>" ;
		}
		wp_reset_query() ;
		remove_filter( 'excerpt_length', array(&$this, 'w4pl_excerpt_length'));
		echo "</ul>" ;
	}
	
	
	//Widget control
	function w4pl_widget_control(){
		$w4pl_default_options = array(
			'title' => 'Post list',
			'width' => '200',
			'height' => '300',
			'w4pl_excerpt_lenth' => '10',
			'w4pl_cat_ids' => array(),
			'w4pl_show_post' => '1',
			'w4pl_post_content' => false
		);
		//update_option( "widget_w4pl", $w4pl_options ) ;

		if( isset($_POST['w4pl_options'])){
			//print_r($_POST['post_category']) ;
			if( is_array($_POST['post_category'])){
				$w4pl_options['w4pl_cat_ids'] = $_POST['post_category'] ;
			}
			
			if(isset($_POST['w4pl_widget_title'])){
				$w4pl_options['title'] = $_POST['w4pl_widget_title'] ;
			}
			
			if(isset($_POST['w4pl_excerpt_lenth'])){
				$w4pl_options['w4pl_excerpt_lenth'] = $_POST['w4pl_excerpt_lenth'] ;
			}
			
			if(isset($_POST['w4pl_post_content'])){
				$w4pl_options['w4pl_post_content'] = $_POST['w4pl_post_content'] ;
			}
			
			if(isset($_POST['w4pl_show_post'])){
				$w4pl_options['w4pl_show_post'] = $_POST['w4pl_show_post'] ;
			}
			
			$w4pl_options = wp_parse_args( $w4pl_options, $w4pl_default_options ) ;
			update_option( "widget_w4pl", $w4pl_options ) ;
			//print_r($w4pl_options) ;
		}

		$get_w4pl_options = get_option("widget_w4pl") ;
		?>
		<div id="w4pl_widget_admin">
			<input type="hidden" name="w4pl_options" id="w4pl_options" value="1" />
			<p><label for="w4pl_widget_title">List Title:</label>
				<input type="text" value="<?php echo( $get_w4pl_options['title']) ; ?>" name="w4pl_widget_title" id="w4pl_widget_title" class="widefat"/>
            </p>
            
            <p>Show post list ?<br />
				<label><input type="radio" name="w4pl_show_post" id="w4pl_show_post1" <?php if($get_w4pl_options['w4pl_show_post'] == '1' ) echo 'checked="checked"' ;?> value="1" /> Yes</label><br />
                <label><input type="radio" name="w4pl_show_post" id="w4pl_show_post0" <?php if($get_w4pl_options['w4pl_show_post'] == '0' ) echo 'checked="checked"' ;?> value="0" /> No</label><br />
            </p>
            
            <p>Show post content ?<br />
				<label><input type="radio" name="w4pl_post_content" id="w4pl_post_content" <?php if($get_w4pl_options['w4pl_post_content'] == '0' ) echo 'checked="checked"' ;?> value="0" /> No</label><br />
                <label><input type="radio" name="w4pl_post_content" id="w4pl_post_content" <?php if($get_w4pl_options['w4pl_post_content'] == '1' ) echo 'checked="checked"' ;?> value="1" />Show only excerpt</label><br />
                <label><input type="radio" name="w4pl_post_content" id="w4pl_post_content" <?php if($get_w4pl_options['w4pl_post_content'] == '2' ) echo 'checked="checked"' ;?> value="2" />Show full content !!</label><br />
            </p>
            
            <p><label for="w4pl_excerpt_lenth">Excerpt lenth for posts/pages:</label>
				<input type="text" value="<?php echo( $get_w4pl_options['w4pl_excerpt_lenth']) ; ?>" name="w4pl_excerpt_lenth" id="w4pl_excerpt_lenth" class="widefat"/>
            </p>
			<p>
            	Select category:<br />
				<?php wp_terms_checklist( null, array( 'taxonomy' => 'category', 'selected_cats' => $get_w4pl_options['w4pl_cat_ids'] )) ; ?>
		</div>
	
<?php
	}
	function w4pl_excerpt_length( $length ){
		$get_w4pl_options = get_option("widget_w4pl") ;
		$lenth = $get_w4pl_options['w4pl_excerpt_lenth'] ;
		return $lenth ;
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

//Begin=====================================
	new W4PL();
?>