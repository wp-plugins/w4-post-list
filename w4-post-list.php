<?php
/*
Plugin Name: W4 post list
Plugin URI: http://w4dev.com/w4-plugin/w4-post-list
Description: Lists wordpress posts, categories and posts with categories by W4 post list plugin. Show/Hide post list with jquery slide effect. Multi-lingual supported.
Version: 1.3.5
Author: Shazzad Hossain Khan
Author URI: http://w4dev.com/
*/

/*  Copyright 2011  Shazzad Hossain Khan  (email : sajib1223@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/*	Few  argument have been changed in version 1.3.1. Please if you feel 
	difficulties after upgrading to latest, go to your post list admin 
	setting page and save the options again.
*/

define( 'W4PL_DIR', plugin_dir_path(__FILE__)) ;
define( 'W4PL_URL', plugin_dir_url(__FILE__)) ;
define( 'W4PL_BASENAME', plugin_basename( __FILE__ )) ;
define( 'W4PL_VERSION', '1.3.5' ) ;
define( 'W4PL_NAME', 'W4 post list' ) ;
define( 'W4PL_SLUG', strtolower(str_replace(' ', '-', W4PL_NAME ))) ;

// Post list Class W4PL_CORE @
class W4PL_CORE {
	private $default_options = array();
	private $table = '';
	private $list_id = '';

	function W4PL_CORE(){
		global $wpdb;

		// Start from 1.3
		if(! get_option( '_w4pl_db_version'))
			add_option( '_w4pl_db_version', '1.3');

		$this->db_version = get_option('_w4pl_db_version');
		
		if( $this->db_version != W4PL_VERSION )
			$this->upgrade();

		$this->table = $wpdb->prefix . 'post_list';
		$this->default_options 	= array(
			'list_type'			 		=> 'pc',
			'list_effect' 				=> 'no',
			
			'categories'				=> array(),
			'show_category_posts_count'	=> 'no',
	
			'post_order_method'			=> 'newest',
			'max'						=> '',
			'post_by'					=> 'all',
			'post_ids'					=> array(),

			'show_post_date' 			=> 'no',
			'show_post_modified_time'	=> 'no',
			'post_content' 				=> 'no',
			'excerpt_more'				=> 'yes',
			'excerpt_length' 			=> (int) 10
		);

		add_action( 'init', array(&$this, 'load_plugin'));
		add_shortcode( 'postlist', array(&$this, 'do_shortcode'));

		add_action( 'admin_init', array(&$this, 'db_install'));
		add_action( 'admin_menu', array(&$this, 'admin_menu'));
		add_action( 'plugin_action_links_'.W4PL_BASENAME, array(&$this, 'plugin_action_links' ));
		add_action( 'activate_' . W4PL_BASENAME, array(&$this, 'upgrade' ));

		add_filter( 'sanitize_list_option', array(&$this, 'sanitize_list_option'));
	}
	
	function upgrade(){
		$this->db_install( true );
		$curr_version = W4PL_VERSION;

		if( $this->db_version != $curr_version ){
			global $wpdb;
			$query = $wpdb->prepare( "SELECT * FROM  $this->table ORDER BY list_id ASC" );
			if ( ! $lists = $wpdb->get_results( $query ))
				$lists = array();
	
			foreach($lists as $list){
				$opt = $this->get_list( $list->list_id);
				$this->save_list( $opt);
			}
			update_option( '_w4pl_db_version', $curr_version);
		}

		# update_option('_w4pl_db_version', $curr_version);
	}
	
	function db_install( $force = false ) {
		global $wpdb;
		
		if( $this->table_exists() && !$force)
			return;
		
		$sql = "CREATE TABLE {$wpdb->prefix}post_list (
			  list_id bigint(20) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
			  list_title varchar(200) NOT NULL DEFAULT '',
			  list_option text NOT NULL,
			  UNIQUE KEY list_id (list_id)
			);";

		require_once( ABSPATH . 'wp-admin/upgrade-functions.php' ) ;
		dbDelta( $sql );
	}

	//Load scripts
	function load_plugin(){
		wp_enqueue_script( 'w4pl_js', W4PL_URL . 'w4-post-list.js', array( 'jquery', 'jquery-ui-core','jquery-ui-tabs','jquery-ui-sortable' ), W4PL_VERSION ,true );
		wp_enqueue_style( 'w4pl_css', W4PL_URL . 'w4-post-list.css', '', W4PL_VERSION ) ;
	}

	function plugin_action_links( $links ){
		$readme_link['readme'] = '<a href="'.esc_attr(admin_url('edit.php?page='.W4PL_SLUG)).'">' . __( 'Settings', 'w4-post-list' ).'</a>';
		return array_merge( $links, $readme_link );
	}
	
	function do_shortcode( $attr){
		if(!is_array($attr))
			$attr = array($attr);
		
		$list_id = array_shift($attr);
		$list_id = (int) $list_id;
		
		return $this->w4_post_list( $list_id);
	}
	
	function w4_post_list($list_id){
		load_plugin_textdomain( 'w4-post-list', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

		if(!$this->get_list( $list_id)){
			if(is_user_logged_in() && current_user_can('edit_plugins'))
				return __( 'No post list found with given id in shortcode. Please make sure a post list exists with the given id.', 'w4-post-list' );
			
			else
				return false;
		}
	
		#$list_option = $this->get_list($list_id, 'list_option');
		$list_options = $this->get_list($list_id);
		
		return $this->_generate_list( $list_options);
	}
	
	private function _generate_list( $list_options ){
		$options = $list_options['list_option'];
		$category_ids = $options['categories'];
		
		if( $options['list_type'] != 'op' && ( empty($category_ids) || !is_array( $category_ids))){
			if(is_user_logged_in() && current_user_can('edit_plugins'))
				return __( 'No category selected. Please select one to show here.', 'w4-post-list' );
			
			else
				return false;
		}
		
		if( $options['list_type'] == 'op'){
			$post_ids = $options['post_ids'];
			
			if(!is_array($post_ids) || '1' > count($post_ids) && (is_user_logged_in() && current_user_can('edit_plugins')))
				return __( 'No post selected. Please select one to show here.', 'w4-post-list' );
			
			$post_order = $this->sanitize_post_order_method( $options['post_order_method']);
			return $this->_generate_post_list( $list_options, array( 'post__in' => $options['post_ids'],
			 'order' => $post_order['order'], 'orderby' => $post_order['orderby']));
		}
		
		$_content = "<div id=\"w4_post_list\">";
		$_content .= "<ul class=\"w4pl_cat_list\">";

		foreach($category_ids as $category_id => $category_options){
			$selected_posts_ids = $category_options['post_ids'];
			$post_by = $category_options['post_by'];
			
			if($category_options["max"] != 0)
				$showposts = intval($category_options["max"]);
			
			else
				$showposts = '-1';
			
			$post_order = $this->sanitize_post_order_method( $category_options['post_order_method']);

			$args = array(
					'post_status' 	=> 'publish',
					'order' 		=> $post_order['order'],
					'orderby' 		=> $post_order['orderby'],
					'cat' 			=> $category_id,
					'showposts' 	=> $showposts,
					);
			
			#if( 'all' == $post_by){
			#	$args['cat'] = $category_id;
				//$args['showposts'] = '-1';
			#}
			#if( 'all_present' == $post_by ){
				//$args['post__in'] = get_objects_in_term( $category_id, 'category');
			#	$args['cat'] = $category_id;
			#}
			$no_posts = false;
			if(in_array( $post_by, array('show_selected', 'all_present'))){
				$args['post__in'] = $category_options['post_ids'];
				if( !count($category_options['post_ids']))
					$no_posts = true;
			}
			elseif( 'hide_selected' == $post_by){
				$args['post__not_in'] = $category_options['post_ids'];
			}
			
			$category = get_category($category_id) ;
			$category_name = $category->name ;

			$category_li_class = "";
			//Only build the jquery show/hide if post and category list type selected
			if( 'pc' == $options['list_type']){
				if($options['list_effect'] == 'extended'):
					$category_li_class = "list_effect close";
					$category_name_before = "<span class=\"marker\"></span>";
#					$category_name_before = "<span class=\"marker\" title=\"Show list\"></span>";
	
				elseif( $options['list_effect'] == 'yes'):
					$category_li_class = "list_effect open";
					$category_name_before = "<span class=\"marker\"></span>";
#					$category_name_before = "<span class=\"marker\" title=\"Hide list\"></span>";
				endif;
			}


			$category_title = '<a class="w4pl_handler" href="' . get_category_link($category_id) .'" title="'
			. sprintf( __( 'View all in', 'w4-post-list') .' %s', $category_name ) .'">'. $category_name_before . $category_name.' &raquo;</a>';
			
			
			if( in_array( $options['show_category_posts_count'], array('included', 'all')) && 'op' != $options['list_type']){
				//Show selected post count
				if( 'included' == $options['show_category_posts_count'] ){
					$items = count($category_options['post_ids']) > 0 ? count($category_options['post_ids']) : '0';
					if($showposts != '-1' && $items > $showposts)
						$items = $showposts;
				}
				//Show actual post count
				if('all' == $options['show_category_posts_count']){
					$items = $category->count;
				}
				
				$count_text = ' <abbr class="item_count" title="'. sprintf( '%1$s '.__('listed under', 'w4-post-list').' %2$s', $items, $category_name) 
				.'">('. $items .')</abbr>';
				
				if( isset($post_by) && 'hide_selected' != $post_by)
					$category_title .= $count_text;
			}
						
			$_content .= "<li class=\"$category_li_class\">";
				$_content .= "$category_title";
				if(!$no_posts)
					$_content .=  $this->_generate_post_list($list_options, $args);

			$_content .= "</li>";
		}
		$_content .= "</ul>";
		$_content .= "</div>";
		
		return $_content;
	}

	private function _generate_post_list( $list_options, $args){
		$options = $list_options['list_option'];
		
		if( $options['list_type'] == 'oc')
			return;
		
		if( 'excerpt' == $options['post_content']):
			$new_excerpt_length = create_function('$length', "return " . $options["excerpt_length"] . ";");
			add_filter('excerpt_length', $new_excerpt_length);
			
			$new_excerpt_more = create_function('$more', "return false;");
			add_filter( 'excerpt_more', $new_excerpt_more);

		endif;
		
		$defaults = array('post_status' => 'publish', 'showposts' => '-1');
		$args = wp_parse_args( $args, $defaults );

		
		query_posts($args);
		//Checking post
		if( have_posts()):
			$post_list = "<ul class=\"w4pl_posts\">" ;
			while(have_posts()):
				the_post() ;

				$post_title = __("<a class=\"w4pl_post_title\" href=\"".get_permalink()."\" title=\"View ".get_the_title()."\">".get_the_title()."</a>", 'w4-post-list') ;
				if( 'yes' == $options['show_post_date'])
					$post_title .= sprintf(' <small><abbr class="" title="%2$s"><strong>' . __("Published:", "w4-post-list") . 
					'</strong> %1$s</abbr></small>', get_the_time('j-m-Y'), get_the_time('g:i a')) ;
				
				if( 'yes' == $options['show_post_modified_time'])
					$post_title .= sprintf(' <small><abbr class="" title="%2$s"><strong>' . __("Updated:", "w4-post-list") . 
					'</strong> %1$s</abbr></small>', get_post_modified_time('j-m-Y'), get_post_modified_time('g:i a')) ;
					

				$post_list .= "<li class=\"\">" ;
				#$post_list .= "<div class=\"w4pl_post_title\">$post_title</div>" ;
				// Removed the dividion from the post title
				$post_list .= "$post_title" ;

				// Pos content or Excerpt with/without readmore
				if( 'no' != $options['post_content']):
					$post_list .= "<div class=\"w4pl_post_content\">";

					if( 'excerpt' == $options['post_content'] )
						$post_list .= get_the_excerpt();
					
					elseif( 'content' == $options['post_content'] )
						$post_list .= get_the_content();
					
					if( 'yes' == $options['excerpt_more'])
						$post_list .= '&hellip;<a href="'. get_permalink().'">'. __( 'continue reading &raquo;','w4-post-list').'</a>';
					
					$post_list .= "</div>";

				endif;

				$post_list .= "</li>" ;
			endwhile;
			$post_list .= "</ul><!--End post list-->" ;
			wp_reset_query();
		endif; //End-if(have_posts()):
		
		//Remove the filter so that other content gets the real excerpt
		remove_filter( 'excerpt_length', $new_excerpt_length);
		remove_filter( 'excerpt_more', $new_excerpt_more);
		
		if( $options['list_type'] == 'op')
			$post_list = "<div id=\"w4_post_list\">" . $post_list ."</div>";

		return $post_list;
	}

	//Plugin page add
	function admin_menu(){
		$this->_save_list_option();
		add_posts_page( W4PL_NAME, W4PL_NAME, 'edit_plugins', W4PL_SLUG, array(&$this, 'admin_page'));
	}
	
	//Plugin page
	function admin_page(){ ?>
	<div id="w4pl_admin" class="wrap">
    	<div class="icon32" id="icon-post"><br/></div>
        <h2><?php echo W4PL_NAME. " V:" . W4PL_VERSION ; ?>
        	<span class="desc"><?php _e( 'With w4 post list plugin you can show your selected post list, selected category list or<br /> making list with both of them in woedpress site.', 'w4-post-list' ); ?></span>
        	
		</h2>
        <div class="menu">
        	<a href="<?php echo admin_url('edit.php?page='.W4PL_SLUG); ?>"><?php _e( 'Home', 'w4-post-list' ); ?></a>
        	<a href="http://w4dev.com" target="_blank" class="us" rel="developer" title="<?php 
			_e( 'Web and wordpress development...', 'w4-post-list' ); ?>">By W4 Development</a>
            <a href="http://w4dev.com/w4-plugin/w4-post-list/" target="_blank" title="<?php _e( 'Visit Plugin Page', 'w4-post-list' ); ?>"> <?php 
			_e( 'Visit Plugin Page', 'w4-post-list' ); ?></a>
            <a href="mailto:sajib1223@gmail.com" rel="tabset_author_mail"> <?php 
			_e( 'Mailto:Author', 'w4-post-list' ); ?></a>
            <a href="http://wordpress.org/extend/plugins/w4-post-list/" target="_blank" rel="wordpress" class="vote"> <?php 
			_e( 'Please rate and vote for this on wordpress', 'w4-post-list' ); ?></a></div>
        
		<?php $this->post_list_menu(); ?>
		<?php
		$list_msgs = array(
						'list_saved'		=> __( 'Option saved.', 'w4-post-list'),
						'list_not_saved'	=> __( 'Unable to save. There may be a database connection error or this list may not have been exists or you do not have capabilities to manage this list.'),
						'list_created' 	=> __( 'New post list Created.', 'w4-post-list'),
						'list_deleted'		=> __( 'One post list has been deleted.', 'w4-post-list'),
						'list_not_deleted'	=> __( 'Unable to delete this list now. There may be a database connection error or this list may not have been exists or you do not have capabilities to delete this.')
					);
		if (isset( $_GET['message'])){
			$msg = $_GET['message'];
			if(in_array($msg, array_keys($list_msgs)))
				echo '<div id="message" class="updated fade">'.$list_msgs[$msg].'</div>';
		}
		if( !$_GET['list_id'])
			echo $this->help_page();

        $this->list_form();
		?>
	</div>
	<?php
	}

	//Plugin page option saving
	function _save_list_option(){
		global $wpdb;
		if($_GET['page'] != W4PL_SLUG)
			return;

		//Delete a list
		if(isset( $_GET['delete'])){
			if($this->delete_list( $_GET['list_id']))
				header("Location: edit.php?page=" . W4PL_SLUG . "&message=list_deleted");
			
			else
				header("Location: edit.php?page=" . W4PL_SLUG . "&message=list_not_deleted");
			die();
		}
		
		//Create new list
		if(isset( $_GET['new_list'])){
			$list_id = $this->save_list();
			header("Location: edit.php?page=" . W4PL_SLUG . "&list_id=". $list_id . "&message=list_created");
			die();
		}

		//Save an existing list
		if( !isset( $_POST['save_w4_post_list_options'] ) && !isset( $_POST['list_id']))
			return;
		
		$list_id = (int) $_POST['list_id'];
			
		if( isset($_POST['list_title']))
			$list_title = trim( stripslashes( $_POST['list_title']));

		
		foreach( $this->default_options as $key => $default ){
			if( !is_array($default))
				$list_option[$key] = $_REQUEST[$key];
		}
			
		$_w4_cat_ids = (array) $_REQUEST['_w4_cat_ids'];
		$categories = array();
		foreach( $_w4_cat_ids as $cat_id){
			$_w4_cat_max = (!$_REQUEST['_w4_cat_max_'. $cat_id]) ? '' : $_REQUEST['_w4_cat_max_'.$cat_id];
			$_w4_cat_posts = !is_array($_REQUEST['_w4_cat_posts_'.$cat_id]) ? array() : $_REQUEST['_w4_cat_posts_'.$cat_id];
			
			$_w4_cat_post_order_method = (!$_REQUEST['_w4_cat_post_order_method_'.$cat_id]) ? 'newest' : $_REQUEST['_w4_cat_post_order_method_'.$cat_id];
			$_w4_cat_post_by = (!$_REQUEST['_w4_cat_post_by_'.$cat_id]) ? 'all' : $_REQUEST['_w4_cat_post_by_'.$cat_id];

			if( 'all_present' == $_w4_cat_post_by || 'all' == $_w4_cat_post_by )
				$_w4_cat_posts = get_objects_in_term( $cat_id, 'category');

			$categories[$cat_id] = array( 
				//'position' 		=> 0,
				'max' 				=> $_w4_cat_max,
				'post_ids' 			=> $_w4_cat_posts,
				'post_order_method'	=> $_w4_cat_post_order_method,
				'post_by'			=> $_w4_cat_post_by
			);
		}
		$list_option['categories'] = $categories;
		$list_option['post_ids'] = $_REQUEST['_w4_post_ids'];
		
		$data = compact('list_id', 'list_title', 'list_option');
		$list_id = $this->save_list( $data);

		header("Location: edit.php?page=" . W4PL_SLUG . "&list_id=".$list_id."&message=list_saved");
		die();
	}
	
	
	function list_form( $list_id = 0){
		$list_id = (int) $list_id;
		
		if(!$list_id & isset($_GET['list_id']))
			$list_id = (int) $_GET['list_id'];
		
		if(!$this->get_list( $list_id ))
			return false;
	
		$all_option = $this->get_list( $list_id );
		# echo '<pre>'; print_r($all_option); echo '</pre>';
		
		$list_option = (array) $all_option['list_option'];
		$list_title = $all_option['list_title'];
		
		$f_action = admin_url( "edit.php?page=" . W4PL_SLUG . "&list_id=" . $list_id);
		$hidden = '<input type="hidden" value="'. $list_id . '" name="list_id"/>';

		# $form_options = $list_option;
		// unneccerary usage
		extract( $list_option);
		
		$list_effect_pc_show = ( $list_type == 'pc' ) ? '' : 'hide_box';
		$list_effect_pc_hide = ( $list_type == 'pc' ) ? 'hide_box' : '';
		
		$list_effect_po_show = ( $list_type == 'op' ) ? '' : 'hide_box';
		$list_effect_po_hide = ( $list_type == 'op' ) ? 'hide_box' : '';
		
		$list_effect_co_show = ( $list_type == 'oc' ) ? '' : 'hide_box';
		$list_effect_co_hide = ( $list_type == 'oc' ) ? 'hide_box' : '';
		
?>
	<form action="<?php echo $f_action ; ?>" method="post" id="post_list_form" enctype="multipart/form-data">
		<h3><?php
        	_e( 'List id: ', 'w4-post-list');
			echo '<span class="red">'. $list_id . '</span>';
			echo ' <a id="delete_list" rel="'.$list_title.'" href="'. admin_url( "edit.php?page=" . W4PL_SLUG . "&list_id=" . $list_id . '&delete=true'). '">deleted this ?</a>';
			?>
		</h3>
		<?php #echo '<pre>'; print_r($all_option); echo '</pre>'; ?>
    	<?php echo $hidden ; ?>
		<input type="hidden" value="save" name="action"/>
    		<div class="option"><label for="list_title"><h3><?php _e('List name:', 'w4-post-list'); ?>
            <small><?php _e( 'Give this post list a name, so that you can find it easily.', 'w4-post-list'); ?></small></h3>
			<input type="text" value="<?php echo( $list_title) ; ?>" name="list_title" 
			id="list_title" class=""/></div>
            
            <!-- List type -->
            <div class="option"><strong><?php _e( 'List type:', 'w4-post-list' ); ?></strong>
            <small><?php _e( 'Kind of list you need.', 'w4-post-list'); ?></small>
			<br /><label><input type="radio" <?php checked( $list_type, 'pc' ); ?> name="list_type" value="pc"  /> <?php _e( 'Posts with categories', 'w4-post-list' ); ?></label>
            <br /><label><input type="radio" <?php checked( $list_type, 'op' ); ?> name="list_type" value="op"  /> <?php _e( 'Only posts', 'w4-post-list' ); ?></label>
            <br /><label><input type="radio" <?php checked( $list_type, 'oc' ); ?> name="list_type" value="oc"  /> <?php _e( 'Only categories', 'w4-post-list' ); ?></label>
            </div>
			
            <!-- Post order by -->
			<div class="option <?php echo $list_effect_co_hide; ?> <?php echo $list_effect_pc_hide; ?> hide_if_pc hide_if_oc show_if_op"><strong><?php _e( 'Post order by:', 'w4-post-list' ); ?></strong>
				<br /><label><input type="radio" <?php checked( $post_order_method, 'newest', true ); ?> name="post_order_method" value="newest"  /> 
				<?php _e( 'Newest -<small>recent</small>', 'w4-post-list'  );?></label>

				<br /><label><input type="radio" <?php checked( $post_order_method, 'oldest', true ); ?> name="post_order_method" value="oldest"  /> 
				<?php _e( 'Oldest -<small>less recent</small>', 'w4-post-list'  );?></label>

				<br /><label><input type="radio" <?php checked( $post_order_method, 'most_popular', true ); ?> name="post_order_method" value="most_popular"  /> 
				<?php _e( 'Most popular -<small>maximum commented post will be shown first</small>', 'w4-post-list' ); ?></label>

				<br /><label><input type="radio" <?php checked( $post_order_method, 'less_popular', true ); ?> name="post_order_method" value="less_popular"  /> 
				<?php _e( 'Less popular -<small>minimum commented post will be shown first</small>', 'w4-post-list' ); ?></label>

				<br /><label><input type="radio" <?php checked( $post_order_method, 'a_title', true ); ?> name="post_order_method" value="a_title"  /> 
				<?php _e( 'Sort by post title -<small>A-Z</small>', 'w4-post-list'  ); ?></label>

				<br /><label><input type="radio" <?php checked( $post_order_method, 'z_title', true ); ?> name="post_order_method" value="z_title"  /> 
				<?php _e( 'Sort by post title -<small>Z-A</small>', 'w4-post-list'  ); ?></label>

				<br /><label><input type="radio" <?php checked( $post_order_method, 'random', true ); ?> name="post_order_method" value="random"  /> 
				<?php _e( 'Random -<small>anything can happen</small>', 'w4-post-list' ); ?></label>
			</div>
            
            <!-- Categpry post -->
            <div class="option <?php echo $list_effect_po_hide; ?> show_if_pc show_if_oc hide_if_op"><strong><?php _e( 'Select categories and posts:', 'w4-post-list'); ?></strong></p>
			<?php echo $this->categories_checklist($list_option); ?></div>
            
            <!-- List effect -->
            <div class="option <?php echo $list_effect_pc_show; ?> show_if_pc hide_if_oc hide_if_op"><strong><?php _e( 'Show category posts with a jquery slide Up/Down effect?', 'w4-post-list' ); ?></strong>
            <small><?php _e( 'Under the post title.', 'w4-post-list' ); ?></small>
            <br /><label><input type="radio" <?php checked( $list_effect, 'no' ); ?> name="list_effect" value="no"  /> <?php _e( 'Not neccessary', 'w4-post-list' ); ?></label>
            <br /><label><input type="radio" <?php checked( $list_effect, 'yes' ); ?> name="list_effect" value="yes"  /> <?php _e( 'Yap, do it', 'w4-post-list' ); ?></label>
            <br /><label><input type="radio" <?php checked( $list_effect, 'extended' ); ?> name="list_effect" value="extended"  /> <?php _e( 'Do it. Also make the posts invisible at primary position', 'w4-post-list' ); ?></label>
            </div>
			
            <!-- Post select -->
            <div class="option <?php echo $list_effect_co_hide; ?> <?php echo $list_effect_pc_hide; ?> hide_if_pc hide_if_oc show_if_op"><strong><?php _e( 'Select posts:', 'w4-post-list'); ?></strong>
            </p>
            <?php echo $this->posts_checklist($list_option); ?></div>
            
            <!-- Categpry item count -->
			<div class="option <?php echo $list_effect_po_hide; ?> show_if_pc show_if_oc hide_if_op"><strong><?php _e( 'Show item count appending to category name ?', 'w4-post-list' ); ?></strong>
            <small><?php _e( 'Will appear after the category name.', 'w4-post-list' ); ?></small>
			<br /><label><input type="radio" <?php checked( $show_category_posts_count, 'no' ); ?> name="show_category_posts_count" value="no"  /> <?php _e( 'No', 'w4-post-list' ); ?></label>
            <br /><label><input type="radio" <?php checked( $show_category_posts_count, 'all' ); ?> name="show_category_posts_count" value="all"  /> <?php _e( 'Yes', 'w4-post-list' ); ?></label>
            </div>
            
            <!-- Post publish date -->
            <div class="option <?php echo $list_effect_co_hide; ?> show_if_pc hide_if_oc show_if_op"><strong><?php _e( 'Show published date appending to post title ?', 'w4-post-list' ); ?></strong>
            <br /><label><input type="radio" <?php checked( $show_post_date, 'no' ); ?> name="show_post_date" value="no"  /> <?php _e( 'No', 'w4-post-list' ); ?></label>
            <br /><label><input type="radio" <?php checked( $show_post_date, 'yes' ); ?> name="show_post_date" value="yes"  /> <?php _e( 'Yes', 'w4-post-list' ); ?></label>
            </div>
            
            <!-- Post update time -->
            <div class="option <?php echo $list_effect_co_hide; ?> show_if_pc hide_if_oc show_if_op"><strong><?php _e( 'Show last post-update time appending to post title ?', 'w4-post-list' ); ?></strong>
            <br /><label><input type="radio" <?php checked( $show_post_modified_time, 'no' ); ?> name="show_post_modified_time" value="no"  /> <?php _e( 'No', 'w4-post-list' ); ?>
            	</label>
            <br /><label><input type="radio" <?php checked( $show_post_modified_time, 'yes' ); ?> name="show_post_modified_time" value="yes"  /> <?php _e( 'Yes', 'w4-post-list' ); ?>
                </label>
            </div>
            
            <!-- Post content option -->
            <div class="option <?php echo $list_effect_co_hide; ?> show_if_pc hide_if_oc show_if_op"><strong><?php _e( 'Show post content ?', 'w4-post-list' ); ?></strong>
            	<small>Under the post title.</small>
            	<br /><label><input type="radio" <?php checked( $post_content, 'no' ); ?> name="post_content" value="no"  /> <?php _e( 'Do not show content', 'w4-post-list' ); ?>
                </label>
            	<br /><label><input type="radio" <?php checked( $post_content, 'excerpt' ); ?> name="post_content" value="excerpt"  /> <?php _e( 'Show only excerpt', 'w4-post-list' ); ?>
                </label>
            	<br /><label><input type="radio" <?php checked( $post_content, 'content' ); ?> name="post_content" value="content"  /> <?php _e( 'Show full content', 'w4-post-list' ); ?>
                </label>
            </div>
			
            <!-- Post excerpt more -->
            <div class="option <?php echo $list_effect_co_hide; if( 'no' == $post_content) echo 'hide_box'; ?> show_if_post_content_content show_if_post_content_excerpt hide_if_post_content_no"><label for="excerpt_length"><strong><?php _e('Show readmore link ?', 'w4-post-list'); ?></strong></label>
            	<br /><label><input type="radio" <?php checked( $excerpt_more, 'no' ); ?> name="excerpt_more" value="no"  /> <?php _e( 'No', 'w4-post-list' ); ?>
                </label>
            	<br /><label><input type="radio" <?php checked( $excerpt_more, 'yes' ); ?> name="excerpt_more" value="yes"  /> <?php _e( 'Yes', 'w4-post-list' ); ?>
           </div>
			
            <!-- Post excerpt length -->
            <div class="option <?php echo $list_effect_co_hide; if( 'excerpt' != $post_content) echo ' hide_box'; ?> show_if_post_content_excerpt hide_if_post_content_no hide_if_post_content_content hide_if_oc"><label for="excerpt_length"><strong><?php _e('Excerpt length:', 'w4-post-list'); ?></strong></label>
            <small><?php _e( 'The content word limit. This wll only be applied if there is no manual post excerpt entry.', 'w4-post-list'); ?></small><br />
			<input type="text" value="<?php echo( $excerpt_length) ; ?>" name="excerpt_length" 
			id="excerpt_length" class=""/></div>
            
		<input type="submit" name="save_w4_post_list_options" class="save_list_option" value="Save option" /><br />
	</form>
<?php
	}
	
	function delete_list($list_id){
		$list_id = (int) $list_id;
		
		if(!$list_id)
			return false;

		
		if(!$this->get_list($list_id))
			return false;
		
		global $wpdb;
		$del = $wpdb->query( $wpdb->prepare( "DELETE FROM $this->table WHERE list_id = %d", $list_id ));
		
		if(!$del)
			return false;
		
		return $list_id;
	}
	
	//Save options
	function save_list($options = array()){
		global $wpdb;
		
		if(!is_array($options))
			$options = array();
		
		extract($options);
		$list_id = (int) $list_id;
		
		if($list_id){
			$update = true;
			$old_options = $this->get_list($list_id, 'list_option');
			// handling options
#			$list_option = apply_filters( 'w4_post_list_option_save_pre', $list_option );
			$list_option = apply_filters( 'sanitize_list_option', $list_option );
			$list_option = maybe_serialize( stripslashes_deep( $list_option ));

			$options = compact( 'list_option', 'list_title');
			$result = $wpdb->update( $this->table, $options, array( 'list_id' => $list_id));
		}
		else{
			$options['list_option'] = maybe_serialize( stripslashes_deep( $this->default_options));

			$result = $wpdb->insert( $this->table, $options );
			$list_id = $wpdb->insert_id;
		}
		
		$list_title = $this->get_list( $list_id, 'list_title');
		if( empty( $list_title)){
			$options['list_title'] = 'List-' .$list_id;
			$wpdb->update( $this->table, $options, array( 'list_id' => $list_id));
		}

		return $list_id;
	}

	function get_list( $list_id = '', $col = null){
		global $wpdb;
		
		$list_id = (int) $list_id;
		
		if(!$list_id)
			return false;
		
		$query = $wpdb->prepare( "SELECT * FROM  $this->table WHERE list_id = %d", $list_id );
		
		if ( !$row = $wpdb->get_row( $query ))
			return false;
		
		$row->list_option = maybe_unserialize( $row->list_option );
		$row->list_option = apply_filters( 'sanitize_list_option', $row->list_option );
		$row = (array) $row;
		
		if(isset($col) && in_array($col, array_keys($row)))
			return $row[$col];
		
		return $row;
	}
	
	// Sanitize list insert
	function sanitize_list_option( $list_option){ 
		extract($list_option);
		$yn_array = array( 'yes', 'no');
		
		// Version 1.3.1 *****************************************************
		if(!in_array( $list_type, array('pc', 'op', 'oc'))){
			if( '1' == $list_type )
				$list_type = 'op';
			
			elseif( '2' == $list_type)
				$list_type = 'oc';
			
			else
				$list_type = 'pc';
		}
			
		if(!in_array($list_effect, array( 'yes', 'no', 'extended'))){
			if( '1' == $list_effect )
				$list_effect = 'yes';
			
			elseif( '2' == $list_effect)
				$list_effect = 'extended';
			
			else
				$list_effect = 'no';
		}
		
		if(!in_array($show_post_date, $yn_array)){
			if( '1' == $show_post_date)
				$show_post_date = 'yes';
			
			else
				$show_post_date = 'no';
		}
		
		
		if(!in_array($show_post_modified_time, $yn_array)){
			if( '1' == $show_post_modified_time)
				$show_post_modified_time = 'yes';
			
			else
				$show_post_modified_time = 'no';
		}			
		
		if(!in_array($show_category_posts_count, array('no', 'included', 'all'))){
			if( '1' == $show_category_posts_count)
				$show_category_posts_count = 'included';
			
			elseif( '2' == $show_category_posts_count)
				$show_category_posts_count = 'all';
			
			else
				$show_category_posts_count = 'no';
		}
		
		if(!in_array( $post_content, array('no', 'excerpt', 'content'))){
			if( '1' == $post_content)
				$post_content = 'excerpt';
			
			elseif( '2' == $post_content)
				$post_content = 'content';
			
			else
				$post_content = 'no';
			

			if( 'excerpt' == $post_content && empty($excerpt_length))
				$excerpt_length = '10';
			
			if(!is_array($post_ids))
				$post_ids = array();

		}

		// Version 1.3.3 *****************************************************
		if( !$post_order_method)
			$post_order_method = 'newest';
		
		if( !$post_content)
			$post_content = 'no';
		
		if( 'included' == $show_category_posts_count )
			$show_category_posts_count = 'all';
		
		if(!in_array($excerpt_more, $yn_array)){
			$excerpt_more = 'yes';
		}

		$list_option = compact(
				'list_type',
				'list_effect',
				'categories',
				'show_category_posts_count',
				'post_ids',
				'post_order',
				'post_order_method',
				'show_post_date',
				'show_post_modified_time',
				'post_content',
				'excerpt_more',
				'excerpt_length'
			);
		return $list_option;
	}

	private function categories_checklist( $options = array()){
		$categories = get_categories( array('hide_empty' => false));
		$w4pl_cats = (array) $options['categories'];
		//$w4pl_cat_max = $w4pl_cats['max'];
		//$w4pl_cat_posts = $w4pl_cats['post_ids'];
		
		foreach( $categories as $category ){
			$cat_selected =  false;
			$checked = '';
			$w4pl_cat_max = '';
			$w4pl_cat_posts = array();
			$w4pl_cat_post_by = '';

			if( in_array($category->cat_ID, array_keys($w4pl_cats))){
				$cat_selected =  true;
				$checked = ' checked="checked" ';
				$w4pl_cat_max = $w4pl_cats[$category->cat_ID]["max"] ? $w4pl_cats[$category->cat_ID]["max"] : '';
				$w4pl_cat_posts = !is_array( $w4pl_cats[$category->cat_ID]["post_ids"]) ? array() : $w4pl_cats[$category->cat_ID]["post_ids"];
				$w4pl_cat_post_order_method = $w4pl_cats[$category->cat_ID]['post_order_method'] ? $w4pl_cats[$category->cat_ID]['post_order_method'] : 'date';
				$w4pl_cat_post_by = $w4pl_cats[$category->cat_ID]['post_by'] ? $w4pl_cats[$category->cat_ID]['post_by'] : 'all';
			
			}
			if( $category_container_class == 'first' ) $category_container_class = 'second';
			else $category_container_class = 'first';

			//Category name
			$checklist .= "<div class=\"category $category_container_class\">";
			
			#if( $options['list_type'] != 'op'){
				if( $options['list_type'] == 'oc')
					$checklist .= "<p class=\"cat_title\"><label><input name=\"_w4_cat_ids[]\" type=\"checkbox\" $checked value=\"$category->cat_ID\" class=\"w4pl_cat_checkbox\" /> $category->cat_name</strong></label> <span class='hide_box category_post_handle hide_if_oc show_if_pc' rel='w4cat_{$category->cat_ID}'>manage posts</span></p>" ;
			
			//Post listin of this category
				else
					$checklist .= "<p class=\"cat_title\"><label><input name=\"_w4_cat_ids[]\" type=\"checkbox\" $checked value=\"$category->cat_ID\" class=\"w4pl_cat_checkbox\" /> $category->cat_name</strong></label> <span class='category_post_handle hide_if_oc show_if_pc' rel='w4cat_{$category->cat_ID}'>manage posts</span></p>" ;

			//$class = ('' == $checked || $options['list_type'] != 'pc') ? 'hide_box' : '';
			
			$checklist .= "<div id='w4cat_{$category->cat_ID}' class=\"w4c_inside hide_if_oc\">";
			$checklist .= $this->category_posts_checklist( $category->cat_ID, $w4pl_cat_posts, $w4pl_cat_post_order_method, $w4pl_cat_max, $w4pl_cat_post_by );
			$checklist .= "</div><!--.w4c_inside close-->";
			$checklist .= "</div><!--.category closed-->";
			
		}
		return $checklist;
	}
	
	private function category_posts_checklist( $cat_id, $selected_posts, $order_method, $max, $post_by ){
		global $wp_query;

		$post_order = $this->sanitize_post_order_method( $order_method);
		query_posts( array(
				'post_status' 	=> 'publish',
				'order' 		=> $post_order['order'],
				'orderby' 		=> $post_order['orderby'],
				'cat' 			=> $cat_id,
				'showposts' 	=> '-1',
			));
		// print_r($this->sanitize_post_order_method('most_popular'));
		// query_posts( array('cat' => $category->cat_ID, 'showposts' => '-1', 'posts_per_page' => '-1', 'post_status' => 'publish'));
		if( have_posts()):
			$checklist .= $this->form_order_by( "_w4_cat_post_order_method_".$cat_id, $order_method );
			$checklist .= '<br /><br />' . $this->form_max_posts( "_w4_cat_max_". $cat_id, $max) ;
			$checklist .= '<br /><br />' . $this->form_posts_by( "_w4_cat_post_by_". $cat_id, $post_by );
			$checklist .= "<br /><br /><strong>". __( 'Select posts:', 'w4-post-list' ) ."</strong><br />";

			$checklist .= "<ul class=\"post_list\">";
			while( have_posts()): the_post();
				$checked2 = in_array( get_the_ID(), $selected_posts) ? ' checked="checked" ' : '';
				$checklist .= "<li><label title=\"". get_the_title() ."\"><input name=\"_w4_cat_posts_{$cat_id}[]\" type=\"checkbox\" $checked2 
				value=\"".get_the_ID()."\" /> ". get_the_title() .'</label></li>' ;
			endwhile;
			$checklist .= "</ul>";

		else:
			$checklist .= '<span class="red">' . __( 'No posts in this cat', 'w4-post-list' ) .'</span>';
		endif;
		
		return $checklist;
	}
	
	private function posts_checklist($options = array()){
		$post_ids = (array) $options['post_ids'];
		$post_order = $this->sanitize_post_order_method( $options['post_order_method']);
		query_posts( array(
					'post_status' 	=> 'publish',
					'order' 		=> $post_order['order'],
					'orderby' 		=> $post_order['orderby'],
					'showposts' 	=> '-1',
					));
		
		if( have_posts()):

			$checklist .= "<ul class=\"post_list\">";
			while( have_posts()): the_post();
				$checked = in_array( get_the_ID(), $post_ids) ? ' checked="checked" ' : '';
				$checklist .= "<li><label title=\"". get_the_title() ."\"><input name=\"_w4_post_ids[]\" type=\"checkbox\" $checked value=\"". get_the_ID() ."\" /> " 
				. get_the_title() .'</label>'. sprintf( __( ' &laquo; Categories: %s', 'w4-post-list' ), get_the_category_list( ', ' )) .'</li>';
			endwhile;
			$checklist .= "</ul>";

		else:

			$checklist .= __( 'No posts', 'w4-post-list' );

		endif;
		return $checklist ;
	}

	function form_order_by( $input_name, $selected){
		return '<strong>'. __( 'Post order by:', 'w4-post-list' ). '</strong>
				<br /><label><input type="radio" '. checked( $selected, 'newest', false ).' name="'.$input_name.'" 
				value="newest"  /> '. __( 'Newest -<small>recent</small>', 'w4-post-list'  ). '</label>

				<br /><label><input type="radio" '. checked( $selected, 'oldest', false ).' name="'.$input_name.'" 
				value="oldest"  /> '. __( 'Oldest -<small>less recent</small>', 'w4-post-list'  ). '</label>

				<br /><label><input type="radio" '. checked( $selected, 'most_popular', false ).' name="'.$input_name.'" 
				value="most_popular"  /> '. __( 'Most popular -<small>maximum commented post will be shown first</small>', 'w4-post-list' ).'</label>

				<br /><label><input type="radio" '. checked( $selected, 'less_popular', false ).' name="'.$input_name.'" 
				value="less_popular"  /> '. __( 'Less popular -<small>minimum commented post will be shown first</small>', 'w4-post-list' ).'</label>

				<br /><label><input type="radio" '. checked( $selected, 'a_title', false ).' name="'.$input_name.'" 
				value="a_title"  /> '. __( 'Sort by post title -<small>A-Z</small>', 'w4-post-list'  ).'</label>

				<br /><label><input type="radio" '. checked( $selected, 'z_title', false ).' name="'.$input_name.'" 
				value="z_title"  /> '. __( 'Sort by post title -<small>Z-A</small>', 'w4-post-list'  ).'</label>

				<br /><label><input type="radio" '. checked( $selected, 'random', false ).' name="'.$input_name.'" 
				value="random"  /> '. __( 'Random -<small>anything can happen</small>', 'w4-post-list' ).'</label>
			';
	}
	
	function form_posts_by( $input_name, $selected){
		return '<strong>'. __( 'Post by:', 'w4-post-list' ). '</strong>
				<br /><label><input type="radio" '. checked( $selected, 'show_selected', false ).' name="'. $input_name .
				'" value="show_selected"  /> '. __( 'Show only selected posts.', 'w4-post-list'). '</label>

				<br /><label><input type="radio" '. checked( $selected, 'hide_selected', false ).' name="'. $input_name .
				'" value="hide_selected"  /> '. __( 'Hide selected posts and show rest.', 'w4-post-list'). '</label>

				<br /><label><input type="radio" '. checked( $selected, 'all', false ).' name="'. $input_name .
				'" value="all"  /> '. __( 'Show All including future posts.', 'w4-post-list' ).'</label>

				<br /><label><input type="radio" '. checked( $selected, 'all_present', false ).' name="'. $input_name .
				'" value="all_present"  /> '. __( 'Show All excluding future posts.', 'w4-post-list' ).'</label>
			';
	}
	
	function form_max_posts( $input_name, $value){
		return '<label><strong>'. __( 'Number of posts to show', 'w4-post-list') . '</strong>
		<br /><input size="3" name="'. $input_name. '" type="text" value="'. $value . '" /></label>';
	}
	
	function sanitize_post_order_method( $order = 'newest'){
		$array = array(
					'newest'		=> array( 'orderby' => 'date', 'order' => 'DESC'),
					'oldest'		=> array( 'orderby' => 'date', 'order' => 'ASC'),
					'most_popular'	=> array( 'orderby' => 'comment_count', 'order' => 'DESC'),
					'less_popular'	=> array( 'orderby' => 'comment_count', 'order' => 'ASC'),
					'a_title'		=> array( 'orderby' => 'title', 'order' => 'ASC'),
					'z_title'		=> array( 'orderby' => 'title', 'order' => 'DESC'),
					'random'		=> array( 'orderby' => 'rand', 'order' => 'ASC'),
				);
		// $default = array( 'orderby' => 'date', 'order' => 'DESC');
		// if(!$order || !in_array($order, array_keys($array)))
		// return $default;
		
		return $array[$order];
	}

	private function post_list_menu($current = 0){
		global $wpdb;
		
		$query = $wpdb->prepare( "SELECT * FROM  $this->table ORDER BY list_id ASC" );
		
		if ( ! $lists = $wpdb->get_results( $query )){
			$lists = array();
		}
		$current = (int) $current;
		
		if(!$current & isset($_GET['list_id']))
			$current = (int) $_GET['list_id'];

		
		$all_post_list = '<ul class="post_list_menu">';
		foreach($lists as $list){
			if( $list->list_id){
				$class = ($current == $list->list_id) ? 'current' : '';
				$title = empty($list->list_title) ? 'List#' . $list->list_id : $list->list_title;
				$url = admin_url( 'edit.php?page=' . W4PL_SLUG . '&list_id=' . $list->list_id );
				
				$all_post_list .= '<li><a href="'. $url . '" class="'.$class.'">'. $title .'</a></li>';
			}
		}
		
		$all_post_list .= '<li><a href="'. admin_url( 'edit.php?page=' . W4PL_SLUG . '&new_list=true' ) . '" class="create">+ Create new</a></li>';
		$all_post_list .= "</ul>";
		
		echo $all_post_list;
	}

	// For widget
	function dropdown_post_list_selector( $select_name, $select_id, $selected = 0){
		global $wpdb;
		
		$query = $wpdb->prepare( "SELECT * FROM  $this->table" );
		
		if ( ! $lists = $wpdb->get_results( $query ) )
			return false; // No data
		
		$selected = (int) $selected;
		
		$all_post_list = "<select name=\"$select_name\" id=\"$select_id\">\n";
		foreach($lists as $list){
			$sel = ($selected == $list->list_id) ? 'selected="selected"' : '';
			$title = empty($list->list_title) ? 'List#' . $list->list_id : $list->list_title;
			$all_post_list .= "<option value=\"$list->list_id\" $sel >$title</option>\n";
		}
		$all_post_list .= "</select>";
		
		echo $all_post_list;
	}

	function help_page(){ ?>
		<p><span class="red">Version 1.3.4 - Bug fixed by Wolfgang Fischer.</span> <a href="mailto:sajib1223@gmail.com">report another bug &raquo;</a></p>
		<h3><?php _e( 'New in version 1.3.3', 'w4-post-list'); ?></h3>
        <ul class="help">
		<li><?php _e( 'Jquery effects to manage the list option more easily.', 'w4-post-list'); ?></li>
		<li><?php _e( 'Changed post order by to an easier method.', 'w4-post-list'); ?></li>
		<li><?php _e( 'A new "post select by" option.', 'w4-post-list'); ?></li>
		</ul>

		<p><?php _e( 'Show a specific post list directly to your theme, use tempate tag', 'w4-post-list' ); ?> "w4_post_list" 
		<?php _e( 'with the list id. Example:', 'w4-post-list'); ?> 
		<strong>w4_post_list( 'the_list_id' )</strong>.<br /><?php _e( 'For returning value instead of echoing, use '); ?>
        <strong>w4_post_list( 'the_list_id', false )</strong>.</p>

		<h3>Understanding options:</h3>
        <ul class="help">
        <li><strong><?php _e( 'List ID:', 'w4-post-list'); ?></strong><br /><?php _e( 'Current list id. This id is necessary for showing list with shortcode. You can show a post list on your post or page by list id.', 'w4-post-list'); ?><br /><span class="red"><?php _e( 'Example: [postlist 1]', 'w4-post-list'); ?></span> <?php _e( 'will show the list having id 1.', 'w4-post-list'); ?></li>

        <li><strong><?php _e( 'List name:', 'w4-post-list'); ?></strong><br /><?php _e( 'This is not very essential now. Just for finding a list with this name on post list page menu.', 'w4-post-list'); ?></li>

        <li><strong><?php _e( 'List type:', 'w4-post-list'); ?></strong><br /><?php _e( 'List type chooser. Only post list, only category list and both them together are available.', 'w4-post-list'); ?><br /><span class="red"><?php _e( 'Note:', 'w4-post-list'); ?></span> <?php _e( 'Selecting and saving this option will hide or reveal related options. So we recommend you do make a save after choosing your list type.', 'w4-post-list'); ?></li>

        <li><strong><?php _e( 'Show posts in category with a jquery slide effect:', 'w4-post-list'); ?></strong><br /><?php _e( 'This is only for "Posts with categories" list type. Possitive selection will create a show/hide effect with jQuery to your list.', 'w4-post-list'); ?></li>

        <li><strong><?php _e( 'Post order by:', 'w4-post-list'); ?></strong><br /><?php _e( 'In Which base the post will be orderby. Available options are newest, oldest, most popular, less popular, by alphabetic order (A-Z/Z-A) and random.', 'w4-post-list'); ?></li>

		<li><strong><?php _e( 'Posts by:', 'w4-post-list'); ?></strong><br /><?php _e( 'How the post will be observed. You may want to show selected posts or may be hide them. Also show with all future posts or without them.', 'w4-post-list'); ?></li>

        <li><strong><?php _e( 'Show item count appending to category name:', 'w4-post-list'); ?></strong><br /><?php _e( 'Show the published posts number for the category.', 'w4-post-list'); ?></li>

        <li><strong><?php _e( 'Show published date appending to post title:', 'w4-post-list'); ?></strong><br /><?php _e( 'Show post publishing date.', 'w4-post-list'); ?></li>

        <li><strong><?php _e( 'Show last post-update time appending to post title:', 'w4-post-list'); ?></strong><br /><?php _e( 'Post mpdified time.', 'w4-post-list'); ?></li>

        <li><strong><?php _e( 'Show post content:</strong><br />Display post content or post excerpt under the post title. You have to set the excerpt length to show post excerpt. This won\'t effect your sites other excerpt length.', 'w4-post-list'); ?></li>
        
        <li><strong><?php _e( 'Show readmore link ? :</strong><br />Display a read more link after the post content.', 'w4-post-list'); ?></li>
        </ul>
        
        <p><?php _e( 'Feel free to', 'w4-post-list' ); ?> <a href="http://w4dev.com/w4-plugin/w4-post-list/" target="_blank"><?php _e( 'contact us', 'w4-post-list' ); ?></a>, <?php _e( 'if you found any bugs or you have a wonderful suggestion.', 'w4-post-list' ); ?></p>
<?php
	}
	
	function table_exists(){
		global $wpdb;
		$wpdb->query( "alter table '$this->table' ADD 'list_type' CHAR( 2 ) NOT NULL DEFAULT 'pc' AFTER 'list_title'");
		
		return strtolower( $wpdb->get_var( "SHOW TABLES LIKE '$this->table'" )) == strtolower( $this->table );
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
		
		if(!w4_post_list($instance['PL_ID'], false))
			return;

		echo $before_widget;
		if( $title ) echo $before_title . $title . $after_title;
		w4_post_list( $instance['PL_ID']);
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
		$w4pl = new W4PL_CORE;
		
		?>
		<div id="w4pl_widget_admin">
            <p><strong><?php _e( 'Title:', 'w4-post-list'); ?></strong><br />
			<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" 
            value="<?php echo $title; ?>" /></p>
            
            <p><strong><?php _e( 'Select a post list:', 'w4-post-list'); ?></strong><br />
			<?php $w4pl->dropdown_post_list_selector($this->get_field_name('PL_ID'), $this->get_field_id('PL_ID'), $PL_ID); ?></p>

            <div class="w4-post-list-support">
            <?php _e( 'Please support us by letting us know what problem you face or what additional functions you want from this plugin.', 'w4-post-list' ); ?>
            <a target="_blank" href="http://wordpress.org/extend/plugins/w4-post-list/"><?php _e( 'Vote for w4 post list', 'w4-post-list' ); ?></a>
            <a target="_blank" href="http://w4dev.com/w4-plugin/w4-post-list/"><?php _e( 'Reply on plugin page', 'w4-post-list' ); ?></a>
            <a target="_blank" href="http://www.facebook.com/w4dev"><?php _e( 'Find us on facebook', 'w4-post-list'); ?></a>
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
//use function w4_post_list() as template tag to show a post list anywhere in your theme
$w4pl = new W4PL_CORE();

if(!function_exists('w4_post_list')):
function w4_post_list($list_id = '0', $echo = true ){
	global $w4pl;
	if(!is_object( $w4pl ) || !is_a( $w4pl, 'W4PL_CORE' ))
		$w4pl = new W4PL_CORE();
	
	if(!$echo)
		return $w4pl->w4_post_list( $list_id );
		
	else
		echo $w4pl->w4_post_list( $list_id );
}
endif;
?>