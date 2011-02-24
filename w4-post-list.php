<?php
/*
Plugin Name: W4 post list
Plugin URI: http://w4dev.com/w4-plugin/w4-post-list
Description: Lists wordpress posts, categories and posts with categories by W4 post list plugin. Show/Hide post list with jquery slide effect. Multi-lingual supported.
Version: 1.3
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


define( 'W4PL_DIR', plugin_dir_path(__FILE__)) ;
define( 'W4PL_URL', plugin_dir_url(__FILE__)) ;
define( 'W4PL_BASENAME', plugin_basename( __FILE__ )) ;
define( 'W4PL_VERSION', '1.3' ) ;
define( 'W4PL_NAME', 'W4 post list' ) ;
define( 'W4PL_SLUG', strtolower(str_replace(' ', '-', W4PL_NAME ))) ;

//Load our script and css file
class W4PL_CORE {
	private $default_options = array();
	private $table = '';
	private $list_id = '';

	function W4PL_CORE(){
		global $wpdb;
		
		$this->table = $wpdb->prefix . 'post_list';
		$this->default_options 	= array(
			'list_type'			 		=> (int) 0,
			'list_effect' 				=> (int) 0,
			
			'categories'				=> array(),
			'show_category_posts_count'	=> (int) 0,
	
			'posts'						=> array(),
			'post_order'				=> 'ASC',
			'post_orderby'				=> 'date',
			'show_post_date' 			=> (bool) 1,
			'show_post_modified_time'	=> (bool) 1,
			'post_content' 				=> (int) 0,
			'excerpt_length' 			=> (int) 10
		);

		add_action( 'init', array(&$this, 'load_scripts')) ;
		add_shortcode( 'postlist', array(&$this, 'do_shortcode'));
		
		add_action( 'admin_init', array(&$this, 'db_install'));
		add_action( 'admin_menu', array(&$this, 'admin_menu'));
		add_action( 'plugin_action_links_'.W4PL_BASENAME, array(&$this, 'plugin_action_links' ));
		#add_action( 'activate_' . W4PL_BASENAME, array(&$this, 'activated' ));
	}

	//Load scripts
	function load_scripts(){
		wp_enqueue_script( 'w4pl_js', W4PL_URL . 'w4-post-list.js', array( 'jquery', 'jquery-ui-core','jquery-ui-tabs','jquery-ui-sortable' ), W4PL_VERSION ,true );
		wp_enqueue_style( 'w4pl_css', W4PL_URL . 'w4-post-list.css', '', W4PL_VERSION ) ;
		load_plugin_textdomain( 'w4-post-list', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
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
		if(!$this->get_list( $list_id)){
			if(is_user_logged_in() && current_user_can('edit_plugins'))
				return __( 'No post list found with given id in shortcode. Please make sure a post list exists with the given id.', 'w4-post-list' );
			
			else
				return false;
		}
	
		$options = $this->get_list($list_id, 'list_option');
		return $this->_generate_list($options);
	}
	
	private function _generate_list($options){
		$category_ids = $options['categories'] ;
		
		if($options['list_type'] != '1' && (empty($category_ids) || !is_array($category_ids))){
			if(is_user_logged_in() && current_user_can('edit_plugins'))
				return __( 'No category selected. Please select one to show here.', 'w4-post-list' );
			
			else
				return false;
		}
		
		if( $options['list_type'] == '1'){
			$post_ids = $options['posts'];
			
			if(!is_array($post_ids) || '1' > count($post_ids))
				return __( 'No post selected. Please select one to show here.', 'w4-post-list' );
		
			return $this->_generate_post_list($options, array('post__in' => $options['posts'], 'order' => $options['post_order'], 'orderby' => $options['post_orderby']));
		}
		
		$_content = "<div id=\"w4_post_list\">";
		$_content .= "<ul class=\"w4pl_parent\">";

		foreach($category_ids as $category_id => $category_options){
			$selected_posts_ids = $category_options['post_ids'];
			
			if($category_options["max"] != 0)
				$max_show = intval($category_options["max"]);
			
			elseif(count($selected_posts_ids) == 0)
				$max_show = '0';
			
			else
				$max_show = '-1';

			$args = array('post__in' => $selected_posts_ids, 'showposts' => $max_show, 'posts_per_page' => $max_show, 'post_status' => 'publish', 'order' => $category_options['post_order'], 'orderby' => $category_options['post_orderby']);

			$category = get_category($category_id) ;
			$category_name = $category->name ;
			$category_title = '<a href="' . get_category_link($category_id) .'" title="'. sprintf( __( 'View all in', 'w4-post-list') .' %s', $category_name ) .'">'.$category_name.' &raquo;</a>';
			
			//Show selected post count
			if( $options['show_category_posts_count'] == '1' ){
				$items = count($selected_posts_ids) > 0 ? count($selected_posts_ids) : false;
				if($max_show != '-1' && $items > $max_show)
					$items = $max_show;
			}
			//Show actual post count
			if($options['show_category_posts_count'] == '2'){
				$items = $category->count > 0 ? $category->count : false ;
			}

			$items_text = $items ? sprintf( _n( __('one post', 'w4-post-list'), '%1$s '. __('posts', 'w4-post-list'), $items), $items): __('no post', 'w4-post-list');
			$items = ' <abbr class="item_count" title="'. sprintf( '%1$s '.__('listed under', 'w4-post-list').' %2$s', $items_text, $category_name) .'">'. $items_text .'</abbr>';
			
			if($options['show_category_posts_count'] == '1' && $options['list_type'] != '0' )
				$items = '';
			
			if($options['show_category_posts_count'] == '1' || $options['show_category_posts_count'] == '2')
				$category_title .= $items;

			$category_li_class = "w4pl_list";

			if($options['list_effect'] == '1'):
				$category_li_class = "w4pl_list list_effect open";
				$category_title = "<span class=\"showhide_w4pl\" title=\"Hide list\"></span>" . $category_title;
			
			elseif($options['list_effect'] == '2'):
				$category_li_class = "w4pl_list list_effect close";
				$category_title = "<span class=\"showhide_w4pl\" title=\"Show list\"></span>" . $category_title;

			endif;
			
			$_content .= "<li class=\"$category_li_class\">";
				$_content .= "$category_title";
				if(count($selected_posts_ids) != 0)
					$_content .=  $this->_generate_post_list($options, $args);

			$_content .= "</li>";
		}
		$_content .= "</ul>";
		$_content .= "</div>";
		
		return $_content;
	}

	private function _generate_post_list($options, $args){
		#print_r($args);
		if( $options['list_type'] == '2')
			return;
		
		if( $options['post_content'] == '1' ):
			$new_excerpt_length = create_function('$length', "return " . $options["excerpt_length"] . ";");
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

				$post_title = __("<a href=\"".get_permalink()."\" title=\"View ".get_the_title()."\">".get_the_title()."</a>", 'w4-post-list') ;
				if($options['show_post_date'])
					$post_title .= sprintf(' <small><abbr class="" title="%2$s"><strong>' . __("Published:", "w4-post-list") . '</strong> %1$s</abbr></small>', get_the_time('j-m-Y'), get_the_time('g:i a')) ;
				
				if($options['show_post_modified_time'])
					$post_title .= sprintf(' <small><abbr class="" title="%2$s"><strong>' . __("Updated:", "w4-post-list") . '</strong> %1$s</abbr></small>', get_post_modified_time('j-m-Y'), get_post_modified_time('g:i a')) ;
					

				$post_list .= "<li class=\"w4pl_post_list\">" ;
				$post_list .= "<div class=\"w4pl_post_title\">$post_title</div>" ;

				//Excerpt
				if( $options['post_content'] == '1' ){
					$post_list .= "<div class=\"w4pl_post_content\">".get_the_excerpt()."</div>" ;
				
				}elseif( $options['post_content'] == '2' ){
					$post_list .= "<div class=\"w4pl_post_content\">".get_the_content()."</div>" ;
				}

				$post_list .= "</li>" ;
			endwhile;
			$post_list .= "</ul><!--End post list-->" ;
			wp_reset_query();
		endif; //End-if(have_posts()):
		
		//Remove the filter so that other content gets the real excerpt
		remove_filter( 'excerpt_length', $new_excerpt_length);
		
		if( $options['list_type'] == '1')
			$post_list = "<div id=\"w4_post_list\">" . $post_list ."</div>";

		return $post_list;
	}

	function db_install() {
		global $wpdb;
		
		if($this->table_exists())
			return true;
		
		$charset_collate = '';
		if ( $wpdb->has_cap( 'collation' ) ) {
			if ( ! empty( $wpdb->charset ) )
				$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
			if ( ! empty( $wpdb->collate ) )
				$charset_collate .= " COLLATE $wpdb->collate";
		}
	
		$sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}post_list (
			list_id bigint(20) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
			list_title varchar(200) NOT NULL DEFAULT '',
			list_option text NOT NULL
			) {$charset_collate};";
		
		require_once( ABSPATH . 'wp-admin/upgrade-functions.php' ) ;
		dbDelta( $sql );
		
		if(!$this->table_exists())
			return false;

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
        	<span style="font-size:12px; padding-left: 20px ;"><a href="http://w4dev.com" target="_blank" class="us" rel="developer" title="<?php 
			_e( 'Web and wordpress development...', 'w4-post-list' ); ?>"> by &raquo; W4 development</a> 
            <a href="http://w4dev.com/w4-plugin/w4-post-list/" target="_blank" title="<?php _e( 'Visit Plugin Site', 'w4-post-list' ); ?>">&raquo; <?php 
			_e( 'Visit Plugin Site', 'w4-post-list' ); ?></a> <a href="mailto:sajib1223@gmail.com" rel="tabset_author_mail">&raquo; <?php 
			_e( 'Mailto:Author', 'w4-post-list' ); ?></a> <a href="http://wordpress.org/extend/plugins/w4-post-list/" target="_blank" rel="wordpress">&raquo; <?php 
			_e( 'Vote on wordpress', 'w4-post-list' ); ?></a></span>
		</h2>
        <div class="desc"><?php _e( 'With w4 post list plugin you can show your selected post list, selected category list or making list with both of them in woedpress site.', 'w4-post-list' ); ?></div>
        
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
		if(!$_GET['list_id'])
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
			header("Location: edit.php?page=" . W4PL_SLUG . "&list_id=".$list_id."&message=list_created");
			die();
		}
		
		//Save an existing list
		if( !isset( $_POST['save_w4_post_list_options'] ) && !isset( $_POST['list_id']))
			return;
		
		$list_id = (int) $_POST['list_id'];
			
		if(isset($_POST['list_title']))
			$list_title = trim( stripslashes( $_POST['list_title']));

		
		foreach( $this->default_options as $key => $default ){
			if(!is_array($default))
				$list_option[$key] = $_REQUEST[$key];
		}
			
		$_w4_cat_ids = (array) $_REQUEST['_w4_cat_ids'];
		$categories = array();
		foreach( $_w4_cat_ids as $_w4_cat_id){
			$_w4_cat_max = (!$_REQUEST['_w4_cat_max_'.$_w4_cat_id]) ? '' : $_REQUEST['_w4_cat_max_'.$_w4_cat_id];
			$_w4_cat_posts = !is_array($_REQUEST['_w4_cat_posts_'.$_w4_cat_id]) ? array() : $_REQUEST['_w4_cat_posts_'.$_w4_cat_id];
			$_w4_cat_post_order = (!$_REQUEST['_w4_cat_post_order_'.$_w4_cat_id]) ? 'ASC' : $_REQUEST['_w4_cat_post_order_'.$_w4_cat_id];
			$_w4_cat_post_orderby = (!$_REQUEST['_w4_cat_post_orderby_'.$_w4_cat_id]) ? 'date' : $_REQUEST['_w4_cat_post_orderby_'.$_w4_cat_id];
			
			$categories[$_w4_cat_id] = array( 
				'position' 		=> 0,
				'max' 			=> $_w4_cat_max,
				'post_ids' 		=> $_w4_cat_posts,
				'post_order'	=> $_w4_cat_post_order,
				'post_orderby'	=> $_w4_cat_post_orderby
			);
		}
		$list_option['categories'] = $categories;
		$list_option['posts'] = $_REQUEST['_w4_post_ids'];
		
		$data = compact('list_id', 'list_title', 'list_option');

		$list_id = $this->save_list($data);
		
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
		
		$list_option = (array) $all_option['list_option'];
		$list_title = $all_option['list_title'];
		
		$f_action = admin_url( "edit.php?page=" . W4PL_SLUG . "&list_id=" . $list_id);
		$hidden = '<input type="hidden" value="'. $list_id . '" name="list_id"/>';

		$form_options = $list_option;
		extract( $form_options);
		
		$list_effect_pc_show = ( $list_type == '0' ) ? '' : 'hide_box';
		$list_effect_pc_hide = ( $list_type == '0' ) ? 'hide_box' : '';
		
		$list_effect_po_show = ( $list_type == '1' ) ? '' : 'hide_box';
		$list_effect_po_hide = ( $list_type == '1' ) ? 'hide_box' : '';
		
		$list_effect_co_show = ( $list_type == '2' ) ? '' : 'hide_box';
		$list_effect_co_hide = ( $list_type == '2' ) ? 'hide_box' : '';
		
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
            
            <div class="option"><strong><?php _e( 'List type:', 'w4-post-list' ); ?></strong>
            <small><?php _e( 'Kind of list you need.', 'w4-post-list'); ?></small>
			<br /><label><input type="radio" <?php checked( $list_type, '0' ); ?> name="list_type" value="0"  /> <?php _e( 'Posts with categories', 'w4-post-list' ); ?></label>
            <br /><label><input type="radio" <?php checked( $list_type, '1' ); ?> name="list_type" value="1"  /> <?php _e( 'Only posts', 'w4-post-list' ); ?></label>
            <br /><label><input type="radio" <?php checked( $list_type, '2' ); ?> name="list_type" value="2"  /> <?php _e( 'Only categories', 'w4-post-list' ); ?></label>
            </div>

			<div class="option <?php echo $list_effect_co_hide; ?> <?php echo $list_effect_pc_hide; ?>"><strong><?php _e( 'Post order:', 'w4-post-list' ); ?></strong>
            <br /><label><input type="radio" <?php checked( $post_order, 'ASC' ); ?> name="post_order" value="ASC"  /> <?php _e( 'ASC', 'w4-post-list'  ); ?></label>
            <br /><label><input type="radio" <?php checked( $post_order, 'DESC' ); ?> name="post_order" value="DESC"  /> <?php _e( 'DESC', 'w4-post-list' ); ?></label>
			</div>

			<div class="option <?php echo $list_effect_co_hide; ?> <?php echo $list_effect_pc_hide; ?>"><strong><?php _e( 'Post order by:', 'w4-post-list' ); ?></strong>
            <br /><label><input type="radio" <?php checked( $post_orderby, 'date' ); ?> name="post_orderby" value="date"  /> <?php _e( 'Date', 'w4-post-list'  ); ?></label>
            <br /><label><input type="radio" <?php checked( $post_orderby, 'author' ); ?> name="post_orderby" value="author"  /> <?php _e( 'Author', 'w4-post-list' ); ?></label>
            <br /><label><input type="radio" <?php checked( $post_orderby, 'title' ); ?> name="post_orderby" value="title"  /> <?php _e( 'Title', 'w4-post-list'  ); ?></label>
            <br /><label><input type="radio" <?php checked( $post_orderby, 'modified' ); ?> name="post_orderby" value="modified"  /> <?php _e( 'Modified date', 'w4-post-list' ); ?></label>
            <br /><label><input type="radio" <?php checked( $post_orderby, 'ID' ); ?> name="post_orderby" value="ID"  /> <?php _e( 'Post ID', 'w4-post-list'  ); ?></label>
            <br /><label><input type="radio" <?php checked( $post_orderby, 'rand' ); ?> name="post_orderby" value="rand"  /> <?php _e( 'Randomize', 'w4-post-list' ); ?></label>
            <br /><label><input type="radio" <?php checked( $post_orderby, 'comment_count' ); ?> name="post_orderby" value="comment_count"  /> <?php _e( 'Comments count', 'w4-post-list' ); ?></label>
            </div>
            
            <div class="option <?php echo $list_effect_po_hide; ?>"><strong><?php _e( 'Select category:', 'w4-post-list'); ?></strong>
            <small><?php _e( 'Hit save after selecting a category to make the category inside post show up below.', 'w4-post-list'); ?></small></p>
			<?php echo $this->categories_checklist($form_options); ?></div>
            
            <div class="option <?php echo $list_effect_pc_show; ?>"><strong><?php _e( 'Show posts in category with a jquery slide effect ?', 'w4-post-list' ); ?></strong>
            <small><?php _e( 'Under the post title.', 'w4-post-list' ); ?></small>
            <br /><label><input type="radio" <?php checked( $list_effect, '0' ); ?> name="list_effect" value="0"  /> <?php _e( 'Not neccessary', 'w4-post-list' ); ?></label>
            <br /><label><input type="radio" <?php checked( $list_effect, '1' ); ?> name="list_effect" value="1"  /> <?php _e( 'Yap, do it', 'w4-post-list' ); ?></label>
            <br /><label><input type="radio" <?php checked( $list_effect, '2' ); ?> name="list_effect" value="2"  /> <?php _e( 'Do it. Also make the posts invisible at primary position', 'w4-post-list' ); ?></label>
            </div>

            <div class="option <?php echo $list_effect_co_hide; ?> <?php echo $list_effect_pc_hide; ?>"><strong><?php _e( 'Select posts:', 'w4-post-list'); ?></strong>
            <small><?php _e( 'Posts.', 'w4-post-list'); ?></small></p>
            <?php echo $this->posts_checklist($form_options); ?></div>
            
			<div class="option <?php echo $list_effect_po_hide; ?>"><strong><?php _e( 'Show item count appending to category name ?', 'w4-post-list' ); ?></strong>
            <small><?php _e( 'Will appear after the category name.', 'w4-post-list' ); ?></small>
			<br /><label><input type="radio" <?php checked( $show_category_posts_count, '0' ); ?> name="show_category_posts_count" value="0"  /> <?php _e( 'Do not show', 'w4-post-list' ); ?></label>
            <?php if($list_type != '2'){ ?>
            <br />
            <label><input type="radio" <?php checked( $show_category_posts_count, '1' ); ?> name="show_category_posts_count" value="1"  /> <?php _e( 'Show only included post count', 'w4-post-list' ); ?></label>
            <?php }else{ ?>
				<input style="display:none;" type="radio" <?php echo checked( $show_category_posts_count, '1' ); ?> name="show_category_posts_count" value="1"  />
			
			<?php } ?>
            
            <br /><label><input type="radio" <?php checked( $show_category_posts_count, '2' ); ?> name="show_category_posts_count" value="2"  /> <?php _e( 'Show the actual category count', 'w4-post-list' ); ?></label>
            </div>
            
            <div class="option <?php echo $list_effect_co_hide; ?>"><strong><?php _e( 'Show published date appending to post title ?', 'w4-post-list' ); ?></strong><br />
            <label><input type="radio" <?php checked( $show_post_date, false ); ?> name="show_post_date" value="0"  /> <?php _e( 'No', 'w4-post-list' ); ?></label>
            <br /><label><input type="radio" <?php checked( $show_post_date, true ); ?> name="show_post_date" value="1"  /> <?php _e( 'Yes', 'w4-post-list' ); ?></label>
            </div>
            
            <div class="option <?php echo $list_effect_co_hide; ?>"><strong><?php _e( 'Show last post-update time appending to post title ?', 'w4-post-list' ); ?></strong><br />
            <label><input type="radio" <?php checked( $show_post_modified_time, false ); ?> name="show_post_modified_time" value="0"  /> <?php _e( 'No', 'w4-post-list' ); ?>
            </label>
            <br />
            <label>
            <input type="radio" <?php checked( $show_post_modified_time, true ); ?> name="show_post_modified_time" value="1"  /> <?php _e( 'Yes', 'w4-post-list' ); ?></label>
            </div>
            
            <div class="option <?php echo $list_effect_co_hide; ?>"><strong><?php _e( 'Show post content ?', 'w4-post-list' ); ?></strong>
            <small>Under the post title.</small>
            <br /><label><input type="radio" <?php checked( $post_content, '0' ); ?> name="post_content" value="0"  /> <?php _e( 'Do not show content', 'w4-post-list' ); ?></label>
            <br /><label><input type="radio" <?php checked( $post_content, '1' ); ?> name="post_content" value="1"  /> <?php _e( 'Show only excerpt', 'w4-post-list' ); ?></label>
            <br /><label><input type="radio" <?php checked( $post_content, '2' ); ?> name="post_content" value="2"  /> <?php _e( 'Show full content', 'w4-post-list' ); ?></label>
            </div>

            <div class="option <?php echo $list_effect_co_hide; ?>"><label for="excerpt_length"><strong><?php _e('Excerpt length:', 'w4-post-list'); ?></strong></label>
            <small><?php _e( 'The content word limit.', 'w4-post-list'); ?></small><br />
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
			$options['list_option'] = maybe_serialize( stripslashes_deep( $list_option ));

			$result = $wpdb->update( $this->table, $options, array( 'list_id' => $list_id));
		}
		else{
			$options['list_option'] = maybe_serialize( stripslashes_deep( $this->default_options));

			$result = $wpdb->insert( $this->table, $options );
			$list_id = $wpdb->insert_id;
		}
		
		$list_title = $this->get_list($list_id, 'list_title');
		if( empty( $list_title)){
			$options['list_title'] = 'List-' .$list_id;
			$wpdb->update( $this->table, $options, array( 'list_id' => $list_id));
		}

		return $list_id;
	}

	function get_list($list_id = '', $col = null){
		global $wpdb;
		
		$list_id = (int) $list_id;
		
		if(!$list_id)
			return false;
		
		$query = $wpdb->prepare( "SELECT * FROM  $this->table WHERE list_id = %d", $list_id );
		
		if ( !$row = $wpdb->get_row( $query ))
			return false;
		
		$row->list_option = maybe_unserialize( $row->list_option);
		$row = (array) $row;
		
		if(isset($col) && in_array($col, array_keys($row)))
			return $row[$col];
		
		return $row;
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
	
	private function categories_checklist($options = array()){
		$categories = get_categories(array('hide_empty' => false));
		$w4pl_cats = (array) $options['categories'];
		//$w4pl_cat_max = $w4pl_cats['max'];
		//$w4pl_cat_posts = $w4pl_cats['posts'];
		
		foreach( $categories as $category ){
			
			if(in_array($category->cat_ID, array_keys($w4pl_cats))){
				$cat_selected =  true;
				$checked = ' checked="checked" ';
				$w4pl_cat_max = $w4pl_cats[$category->cat_ID]["max"] ? $w4pl_cats[$category->cat_ID]["max"] : '';
				$w4pl_cat_posts = !is_array($w4pl_cats[$category->cat_ID]["post_ids"]) ? array() : $w4pl_cats[$category->cat_ID]["post_ids"];
				$w4pl_cat_post_order = $w4pl_cats[$category->cat_ID]['post_order'] ? $w4pl_cats[$category->cat_ID]['post_order'] : 'ASC';
				$w4pl_cat_post_orderby = $w4pl_cats[$category->cat_ID]['post_orderby'] ? $w4pl_cats[$category->cat_ID]['post_orderby'] : 'date';
			
			}
			else{
				$cat_selected =  false;
				$checked = '';
				$w4pl_cat_max = '';
				$w4pl_cat_posts = array();
			}
			
			//Category name
			$checklist .= '<div class="category">';
			$checklist .= "<p class=\"cat_title\"><label><input name=\"_w4_cat_ids[]\" type=\"checkbox\" $checked value=\"$category->cat_ID\" class=\"w4pl_cat_checkbox\" /> $category->cat_name</strong></label></p>" ;
			
			//Post listin of this category
			if($options['list_type'] == '0'){
			
			$class = ('' == $checked) ? 'hide_box' : '';
			
			
			$checklist .= "<div id=\"\" class=\"w4c_inside $class\">";
			$checklist .= '<strong>'. __( 'Post order:', 'w4-post-list' ). '</strong>';
            
			$checklist .= '
			
            <br /><label><input type="radio" '. checked( $w4pl_cat_post_order, 'ASC', false ).' name="_w4_cat_post_order_'. $category->cat_ID .'" value="ASC" /> '. __( 'ASC', 'w4-post-list'  ). '</label>
            <br /><label><input type="radio" '. checked( $w4pl_cat_post_order, 'DESC', false ).' name="_w4_cat_post_order_'. $category->cat_ID .'" value="DESC" /> '. __( 'DESC', 'w4-post-list' ). '</label>

            <br /><br /><strong>'. __( 'Post order by:', 'w4-post-list' ). '</strong>
            <br /><label><input type="radio" '. checked( $w4pl_cat_post_orderby, 'date', false ).' name="_w4_cat_post_orderby_'. $category->cat_ID .'" value="date"  /> '. __( 'Date', 'w4-post-list'  ). '</label>
            <br /><label><input type="radio" '. checked( $w4pl_cat_post_orderby, 'author', false ).' name="_w4_cat_post_orderby_'. $category->cat_ID .'" value="author"  /> '. __( 'Author', 'w4-post-list' ).'</label>
            <br /><label><input type="radio" '. checked( $w4pl_cat_post_orderby, 'title', false ).' name="_w4_cat_post_orderby_'. $category->cat_ID .'" value="title"  /> '. __( 'Title', 'w4-post-list'  ).'</label>
            <br /><label><input type="radio" '. checked( $w4pl_cat_post_orderby, 'modified', false ).' name="_w4_cat_post_orderby_'. $category->cat_ID .'" value="modified"  /> '. __( 'Modified date', 'w4-post-list' ).'</label>
            <br /><label><input type="radio" '. checked( $w4pl_cat_post_orderby, 'ID', false ).' name="_w4_cat_post_orderby_'. $category->cat_ID .'" value="ID"  /> '. __( 'Post ID', 'w4-post-list'  ).'</label>
            <br /><label><input type="radio" '. checked( $w4pl_cat_post_orderby, 'rand', false ).' name="_w4_cat_post_orderby_'. $category->cat_ID .'" value="rand"  /> '. __( 'Randomize', 'w4-post-list' ).'</label>
            <br /><label><input type="radio" '. checked( $w4pl_cat_post_orderby, 'comment_count', false ).' name="_w4_cat_post_orderby_'. $category->cat_ID .'" value="comment_count"  /> '. __( 'Comments count', 'w4-post-list' ).'</label>';
			
			//Maximum number of posts to show for the selected category
			$checklist .= '<br /><br /><label>'. __( 'Maximum posts to show on front.', 'w4-post-list') . '<br /><input size="3" name="_w4_cat_max_'. $category->cat_ID .'" type="text" value="'. $w4pl_cat_max . '" /></label>';


			query_posts(array('cat' => $category->cat_ID, 'showposts' => '-1', 'posts_per_page' => '-1', 'post_status' => 'publish'));
			if(have_posts()):


				
				$checklist .= "<br /><br /><strong>". __( 'Select posts:', 'w4-post-list' ) ."</strong><br />";
				$checklist .= "<ul class=\"post_list\">";

				while(have_posts()): the_post();
				$checked2 = in_array(get_the_ID(), $w4pl_cat_posts) ? ' checked="checked" ' : '' ;
				$checklist .= "<li><label><input name=\"_w4_cat_posts_{$category->cat_ID}[]\" type=\"checkbox\" $checked2 value=\"".get_the_ID()."\" /> " 
				. get_the_title().'</label></li>' ;
				endwhile;
				$checklist .= "</ul>";
			else:
				$checklist .= __( 'No posts in this cat', 'w4-post-list' );
			endif;

			$checklist .= "</div>";
			
			}
			$checklist .= "</div>";
			
		}
		return $checklist;
	}
	
	private function posts_checklist($options = array()){
		$posts = (array) $options['posts'];

		query_posts(array('showposts' => '-1', 'posts_per_page' => '-1', 'post_status' => 'publish', 'order' => $options['post_order'], 'orderby' => $options['post_orderby']));
		if(have_posts()):
			$checklist .= "<ul class=\"post_list\">";

			while(have_posts()): the_post();
				$checked = in_array(get_the_ID(), $posts) ? ' checked="checked" ' : '' ;
				$checklist .= "<li><label><input name=\"_w4_post_ids[]\" type=\"checkbox\" $checked value=\"".get_the_ID()."\" /> " 
				. get_the_title().'</label></li>' ;
			endwhile;

			$checklist .= "</ul>";
		else:
				$checklist .= __( 'No posts', 'w4-post-list' );
		endif;

		return $checklist ;
	}
	
	function dropdown_list_selector($select_name, $select_id, $selected = 0){
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
		<h3>Understanding options:</h3>
        <ul class="help">
        <li><strong><?php _e( 'List ID:', 'w4-post-list'); ?></strong><br /><?php _e( 'Current list id. This id is necessary for showing list with shortcode. You can show a post list on your post or page by list id.', 'w4-post-list'); ?><br /><span class="red"><?php _e( 'Example: [postlist 1]', 'w4-post-list'); ?></span> <?php _e( 'will show the list having id 1.', 'w4-post-list'); ?></li>
        <li><strong><?php _e( 'List name:', 'w4-post-list'); ?></strong><br /><?php _e( 'This is not very essential now. Just for finding a list with this name on post list page menu.', 'w4-post-list'); ?></li>
        <li><strong><?php _e( 'List type:', 'w4-post-list'); ?></strong><br /><?php _e( 'List type chooser. Only post list, only category list and both them together are available.', 'w4-post-list'); ?><br /><span class="red"><?php _e( 'Note:', 'w4-post-list'); ?></span> <?php _e( 'Selecting and saving this option will hide or reveal related options. So we recommend you do make a save after choosing your list type.', 'w4-post-list'); ?></li>
        <li><strong><?php _e( 'Show posts in category with a jquery slide effect:', 'w4-post-list'); ?></strong><br /><?php _e( 'This is only for "Posts with categories" list type. Possitive selection will create a show/hide effect with jQuery to your list.', 'w4-post-list'); ?></li>
        <li><strong><?php _e( 'Post order:', 'w4-post-list'); ?></strong><br /><?php _e( 'Basic order type for posts.', 'w4-post-list'); ?></li>
        <li><strong><?php _e( 'Post order by:', 'w4-post-list'); ?></strong><br /><?php _e( 'In Which base the post will be orderby. Available oprions are "author", "date", "title", "modified", "menu_order", "parent", "ID", "rand", "comment_count".', 'w4-post-list'); ?></li>
        <li><strong><?php _e( 'Show item count appending to category name:', 'w4-post-list'); ?></strong><br /><?php _e( '"Show only included post count" will show post selected for the category for this list. "Show the actual category count" will show the actual published posts count for the category.', 'w4-post-list'); ?></li>
        <li><strong><?php _e( 'Show published date appending to post title:', 'w4-post-list'); ?></strong><br /><?php _e( 'Show post publishing date.', 'w4-post-list'); ?></li>
        <li><strong><?php _e( 'Show last post-update time appending to post title:', 'w4-post-list'); ?></strong><br /><?php _e( 'Post mpdified time.', 'w4-post-list'); ?></li>
        <li><strong><?php _e( 'Show post content:</strong><br />Display post content or post excerpt under the post title. You have to set the excerpt length to show post excerpt. This won\'t effect your sites other excerpt length.', 'w4-post-list'); ?></li>
        </ul>
        
        <p><?php _e( 'Feel free to', 'w4-post-list' ); ?> <a href="http://w4dev.com/w4-plugin/w4-post-list/" target="_blank"><?php _e( 'contact us', 'w4-post-list' ); ?></a>, <?php _e( 'if you found any bugs or you have a wonderful suggestion.', 'w4-post-list' ); ?></p>
<?php
	}
	
	function table_exists(){
		global $wpdb;
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
		
		if(!$w4pl->w4_post_list($instance['PL_ID']))
			return;

		echo $before_widget;
		if( $title ) echo $before_title . $title . $after_title;
		echo $w4pl->w4_post_list($instance['PL_ID']);
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
			<?php $w4pl->dropdown_list_selector($this->get_field_name('PL_ID'), $this->get_field_id('PL_ID'), $PL_ID); ?></p>

            <div class="w4-post-list-support">
            <?php _e( 'Please support us by letting us know what problem you face or what additional functions you want from this plugin.', 'w4-post-list' ); ?>
            <a target="_blank" href="http://w4dev.com/w4-plugin/w4-post-list"><?php _e( 'Reply on plugin page', 'w4-post-list' ); ?></a>
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
//Begin=====================================
$w4pl = new W4PL_CORE();
?>