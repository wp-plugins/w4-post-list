<?php
add_action( 'admin_menu', 'w4pl_admin_menu');
function w4pl_admin_menu(){
	global $wpdb, $wp_error, $plugin_page, $w4pl_caps, $w4pl_request;

	$w4pl_caps = get_option( 'w4pl_options' );
	if( !in_array( $w4pl_caps['access_cap'], array( 'manage_options', 'edit_others_posts', 'publish_posts', 'edit_posts')))
		$w4pl_caps['access_cap'] = 'manage_options';

	if( !in_array( $w4pl_caps['manage_cap'], array( 'manage_options', 'edit_others_posts', 'publish_posts', 'edit_posts')))
		$w4pl_caps['manage_cap'] = 'manage_options';
	
	//Prepare menu
	add_menu_page( W4PL_NAME , W4PL_NAME , $w4pl_caps['access_cap'], W4PL_SLUG, 'w4pl_admin_page', '', '6' );
	add_submenu_page( W4PL_SLUG , 'Add list', 'Add New list' , $w4pl_caps['access_cap'], W4PL_SLUG.'&new_list=true', 'w4pl_admin_page' );
	add_submenu_page( W4PL_SLUG, 'Manage W4 post list Options', 'Manage', 'manage_options', W4PL_SLUG . '-options' , 'w4pl_admin_option_page');
	
	
	if( in_array( $plugin_page, array( W4PL_SLUG, W4PL_SLUG . '-options' ))){
		#load_plugin_textdomain( 'w4-post-list', false, dirname( W4PL_BASENAME ) . '/languages' );
		$w4pl_request = isset( $_GET['action'] ) && in_array( $_GET['action'], array( 'add', 'edit', 'delete' )) ? $_GET['action'] : "";
		do_action( 'w4pl_admin_action');
	}
}

//Plugin page option saving
add_action( 'w4pl_admin_action', 'w4pl_form_action');
function w4pl_form_action(){
	global $wpdb, $plugin_page, $w4pl_caps, $w4_request;

	// If this is the option page and curren use have the manage_options capabilities
	if( W4PL_SLUG . '-options' == $plugin_page && current_user_can( 'manage_options')):
		if( isset( $_GET['databse'])){
			$db_call = $_GET['databse'];
			switch( $db_call):
				case 'remove':
					if( !w4pl_table_exists()){
						return w4pl_add_error( 'Theres no table exists.');
						
					}else{
						w4ld_db_remove();
						$url = add_query_arg( 'message', 'db_dropped', w4pl_option_page_url());
						wp_redirect( $url);
						exit;
					}
				break;
				case 'install':
					if( w4pl_table_exists()){
						return w4pl_add_error( 'Tables already installed.');
						
					}else{
						w4pl_db_install( true );
						$url = add_query_arg( 'message', 'db_installed', w4pl_option_page_url());
						wp_redirect( $url);
						exit;
					}
				break;
				case 'update_option':
					$data = get_option( '_w4pl_temp_option' );
					foreach( (array) $data as $list ){
						$list['list_option'] = maybe_unserialize( $list['list_option'] );

						// Unset list id
						unset( $list['list_id'] );

						// Insert new list
						w4pl_save_list( $list );
					}
					delete_option( '_w4pl_temp_option');
					$url = add_query_arg( 'message', 'db_updated', w4pl_option_page_url());
					wp_redirect( $url);
					exit;
				break;

				case 'delete_option':
					delete_option( '_w4pl_temp_option');
					$url = add_query_arg( 'message', 'old_option_cleared', w4pl_option_page_url());
					wp_redirect( $url);
					exit;
				break;

				default:
				break;
			endswitch;
		}
		// Save options
		if( isset( $_POST['save_w4pl_option_form'])){
			$options = array();
			$options['access_cap'] 			= $_POST['access_cap'];
			$options['manage_cap'] 			= $_POST['manage_cap'];
			
			update_option( 'w4pl_options', $options );
			$url = add_query_arg( 'message', 'option_updated', w4pl_option_page_url());
			wp_redirect( $url);
			exit;
		}
	endif;// Option page

	if( !w4pl_table_exists()){
		$msg = __( 'W4 post list plugin databse in not installed.', 'w4-post-list');
		
		if( current_user_can( 'manage_options'))
			$msg .= sprintf( __( ' <a class="button" href="%s">Install now</a>', 'w4-post-list'), add_query_arg( 'databse', 'install', w4pl_option_page_url()));
		
		else
			$msg .= __( ' Please notify admin to update plugin databse before you can start using it..', 'w4-post-list');
		
		
		return w4pl_add_error( $msg );
	}
	elseif( version_compare( get_option( '_w4pl_db_version' ), '2', '<')){

		if( current_user_can( 'manage_options'))
			$msg = sprintf( __( 'You have to update the database table structure for plugin to work properly. For this, remove the database table now and install again. Your old option will still remain. You can synchronise them after installation.<a class="button" href="%s">Remove database</a>', 'w4-post-list'), add_query_arg( 'databse', 'remove', w4pl_option_page_url()));
		
		else
			$msg = __( ' Please notify admin to update plugin databse before you can start using it..', 'w4-post-list');

		return w4pl_add_error( $msg );
	}

	if( W4PL_SLUG == $plugin_page && current_user_can( $w4pl_caps['access_cap'])):
		// Create new list
		if( isset( $_GET['new_list']) && 'true' == $_GET['new_list']){
			$list_id = w4pl_save_list();
	
			if( is_wp_error( $list_id )){
				return w4pl_add_error( $list_id->get_error_message());
	
			}else{
				$url = add_query_arg( array( 'list_id' => $list_id, 'message' => 'list_created'), w4pl_plugin_page_url());
				wp_redirect( $url);
				exit;
			}
		}
		
		// Check list by get list_id
		if( isset( $_GET['list_id'] )){
			if( ! w4pl_get_list( $_GET['list_id'] )){
				$url = add_query_arg( 'message', 'no_list_found', w4pl_plugin_page_url());
				wp_redirect( $url );
				exit;
			}
			$list = w4pl_get_list( $_GET['list_id']);

			if( !w4pl_is_list_user( $list ) && !current_user_can( $w4pl_caps['manage_cap'] )){
				$url = add_query_arg( 'message', 'no_permission', w4pl_plugin_page_url());
				wp_redirect( $url );
				exit;
			}
	
		}

		// Delete a list
		if( isset( $_GET['delete'] ) && 'true' == $_GET['delete'] ){
			$list_id = w4pl_delete_list( $_GET['list_id']);
			if( is_wp_error( $list_id)){
				return w4pl_add_error( $list_id->get_error_message());
				
			}else{
				$url = add_query_arg( 'message', 'list_deleted', w4pl_plugin_page_url());
				wp_redirect( $url);
				exit;
			}
		}

		// Stop here if we aren't saving options
		if( !isset( $_POST['save_w4_post_list_options'] ) && !isset( $_POST['list_id']))
			return;
			
		// Check list by post list_id
		if( isset( $_POST['list_id']) && !w4pl_get_list( $_POST['list_id'])){
			$url = add_query_arg( 'message', 'no_list_found', w4pl_plugin_page_url());
			wp_redirect( $url);
			exit;
		}
			
		$list_data = w4pl_get_list_form_data();
		
		// Save the list
		$list_id = w4pl_save_list( $list_data );
		#return;
	
		if( is_wp_error( $list_id )){
			return w4pl_add_error( $list_id->get_error_message());

		}else{
			$url = add_query_arg( array( 'list_id' => $list_id, 'message' => 'list_saved' ), w4pl_plugin_page_url());
			wp_redirect( $url );
			exit;
		}
	endif; //Pluginpage
}

function w4pl_admin_option_page(){
	global $wpdb, $w4pl_caps;

	if( !$w4pl_caps )
		$w4pl_caps = get_option( 'w4pl_options' );
?>
	<div id="w4pl_admin" class="wrap">
    	<div class="icon32" id="icon-post"><br/></div>
        <h2>Settings - <?php echo W4PL_NAME. " - V:" . W4PL_VERSION ; ?>
        	<span class="desc"><?php _e( 'With w4 post list plugin you can show your selected post list, selected category list or<br /> making list with both of them in woedpress site.', 'w4-post-list' ); ?></span>
		</h2>
        <div class="menu">
            <a href="http://w4dev.com/w4-plugin/w4-post-list/" target="_blank" title="<?php _e( 'Plugin Page', 'w4-post-list' ); ?>"> <?php 
			_e( 'Visit Plugin Page', 'w4-post-list' ); ?></a>
            <a href="mailto:sajib1223@gmail.com" rel="tabset_author_mail"> <?php 
			_e( 'Mailto:Author', 'w4-post-list' ); ?></a>
            <a href="http://wordpress.org/extend/plugins/w4-post-list/" target="_blank" rel="wordpress" class="vote"> <?php 
			_e( 'Vote on wordpress', 'w4-post-list' ); ?></a>

			<a style="background-color:#FFFFE0; border:1px solid #E6DB55; border-left:none;" href="<?php w4pl_option_page_url( true); ?>"><?php _e( 'Manage Plugin Option/Database', 'w4-post-list' ); ?></a>
        	<a style="background-color:#FFFFE0; border:1px solid #E6DB55;" href="<?php echo w4pl_plugin_page_url(); ?>"><?php _e( 'Documentation/Home', 'w4-post-list' ); ?></a>


            <?php if( w4pl_table_exists()): ?>
        	<a style="background-color:#e23a3a; color:#FFF; font-weight:bold; border:1px solid #740909;" id="remove_w4ldb" href="<?php echo add_query_arg( 'databse', 'remove', w4pl_option_page_url()); ?>"><?php _e( 'Drop database table', 'w4-post-list' ); ?></a>
            <?php else: ?>
            <a style="background-color:#67A54B; color:#FFF; font-weight:bold; border:1px solid #3c7123;" href="<?php echo add_query_arg( 'databse', 'install', w4pl_option_page_url()); ?>"><?php _e( 'Install database table', 'w4-post-list' ); ?></a>
            <?php endif; ?>
            
		</div>
        
		<?php
		$list_messages = array(
			'option_updated'		=> __( 'Option Updted.', 'w4-post-list'),
			'db_updated' 			=> __( 'Informations updated. You can get the on main page. However, the list id and list shortcode may have been changed.', 'w4-post-list'),
			'db_dropped' 			=> __( 'Database table dropped.', 'w4-post-list'),
			'db_installed' 			=> __( 'Database installed sucessfully..', 'w4-post-list'),
			'old_option_cleared'	=> __( 'Old Information cleared.', 'w4-post-list')
		);

		$list_errors = array(
			'no_list_found'			=> __( 'List not found.', 'w4-post-list')
		);

		if( get_option( '_w4pl_temp_option') && w4pl_table_exists()){
			w4pl_add_message( sprintf( __( 'Your old database options are still available. 
			<a class="button" href="%1$s"><strong>Update them</strong></a> or <a class="button" href="%2$s"><strong>delete them</strong></a>', 'w4-post-list'),
			add_query_arg( 'databse', 'update_option', w4pl_option_page_url()), add_query_arg( 'databse', 'delete_option', w4pl_option_page_url())));
		}

		if ( isset( $_GET['message'])){
			$mkey = $_GET['message'];
			if( in_array( $mkey, array_keys( $list_messages )))
				w4pl_add_message( $list_messages[$mkey], $mkey );
			
			elseif( in_array( $mkey, array_keys( $list_errors)))
				w4pl_add_error( $list_errors[$mkey], $mkey );
		}

		w4pl_display_error();

		w4ld_option_form();
		?>
	</div>
<?php
}
function w4pl_admin_page(){
	global $wpdb, $w4pl_caps;

	if( !$w4pl_caps)
		$w4pl_caps = get_option( 'w4pl_options');
?>
	<div id="w4pl_admin" class="wrap">
    	<div class="icon32" id="icon-post"><br/></div>
        <h2><?php echo W4PL_NAME. " - V:" . W4PL_VERSION ; ?>
        	<span class="desc"><?php _e( 'With w4 post list plugin you can show your selected post list, selected category list or<br /> making list with both of them in woedpress site.', 'w4-post-list' ); ?></span>
        	
		</h2>
        <div class="menu">
            <a href="http://w4dev.com/w4-plugin/w4-post-list/" target="_blank" title="<?php _e( 'Visit Plugin Page', 'w4-post-list' ); ?>"> <?php 
			_e( 'Visit Plugin Page', 'w4-post-list' ); ?></a>
            <a href="http://wordpress.org/extend/plugins/w4-post-list/" target="_blank" rel="wordpress" class="vote"> <?php 
			_e( 'Vote on wordpress', 'w4-post-list' ); ?></a>
			<?php if( current_user_can( 'manage_options' )): ?>
			<a style="background-color:#FFFFE0; border:1px solid #E6DB55; border-left:none;" href="<?php w4pl_option_page_url( true); ?>"><?php _e( 'Manage Plugin Option/Database', 'w4-post-list' ); ?></a>
			<?php endif; ?>
        	<a style="background-color:#FFFFE0; border:1px solid #E6DB55;" href="<?php echo w4pl_plugin_page_url(); ?>"><?php _e( 'Documentation/Home', 'w4-post-list' ); ?></a>
			<a style="background-color:#67A54B; color:#FFF; font-weight:bold; border-color:#3c7123; border-width:1px;" href="<?php w4pl_add_url( true); ?>"><?php _e( 'Add new', 'w4-post-list' ); ?></a>
		</div>
        
		<?php w4pl_item_menu(); ?>
		<?php
		$list_messages = array(
			'list_saved'		=> __( 'Option saved.', 'w4-post-list'),
			'list_created' 		=> __( 'New post list Created. Now change options below and save to prepare your list.', 'w4-post-list'),
			'list_deleted'		=> __( 'One post list has been deleted.', 'w4-post-list')
		);

		$list_errors = array(
			'list_not_saved'	=> __( 'Unable to save. There may be a database connection error or this list may not have been exists or you do 
			not have capabilities to manage this list.'),
			'list_not_deleted'	=> __( 'Unable to delete this list now. There may be a database 
			connection error or this list may not have been exists or you do not have capabilities to delete this.'),
			'no_list_found'		=> __( 'List not found.'),
			'no_permission'		=> __( 'You dont have no permission to manage other users list.')
		);

		if ( isset( $_GET['message'])){
			$mkey = $_GET['message'];
			if( in_array( $mkey, array_keys( $list_messages )))
				w4pl_add_message( $list_messages[$mkey], $mkey );
			
			elseif( in_array( $mkey, array_keys( $list_errors)))
				w4pl_add_error( $list_errors[$mkey], $mkey );
		}

		w4pl_display_error();

		if( !isset( $_GET['list_id'] ))
			echo w4pl_help_page();

        w4ld_list_form();
		?>
	</div>
<?php
}

function w4pl_get_list_form_data(){
	$default_options = w4pl_default_options();

	$list_id = $_POST['list_id'];
	$list_title = $_POST['list_title'];
	$list_option = $_POST['list_option'];

	foreach( $default_options['list_option'] as $key => $val ){
		if( !is_array( $val ))
			$list_option[$key] = $_POST[$key];
	}
	
	$list_option['posts_not_in'] 		= w4pl_all_posts_id();
	$list_option['post_ids'] 			= (array) $_POST['list_option']['post_ids'];

	foreach( $list_option['post_ids'] as $post_id ){
		if( $keys = array_keys( $list_option['posts_not_in'], $post_id)){
			foreach( $keys as $k){
				unset( $list_option['posts_not_in'][$k]);
			}
		}
	}

	$categories = array();
	$category_ids =  $_POST['list_option']['categories'];
	foreach( (array) $category_ids as $cat_id ){
		
		$categories[$cat_id]['post_order_method'] 	= $_POST["w4pl_categories_post_order_method"][$cat_id];
		$categories[$cat_id]['max'] 				= $_POST["w4pl_categories_max"][$cat_id];

		$categories[$cat_id]['post_ids'] 			= isset( $_POST['category_posts'][$cat_id] ) ? $_POST['category_posts'][$cat_id] : array();
		$categories[$cat_id]['show_future_posts'] 	= isset( $_POST['_w4_cat_show_future_posts_' . $cat_id] ) ? $_POST['_w4_cat_show_future_posts_'.$cat_id] : '';
		
		$all_cat_posts = w4pl_category_posts_id( $cat_id );
		$categories[$cat_id]['posts_not_in'] = !empty( $all_cat_posts ) ? $all_cat_posts : array();

#		print_r($categories[$cat_id]['posts_not_in']); echo '<br />';
#		print_r($categories[$cat_id]['post_ids']);echo '<br />';
		
		foreach( $categories[$cat_id]['post_ids'] as $not_id ){
			if( $keys = array_keys( $categories[$cat_id]['posts_not_in'], $not_id )){
#				echo 'Keys:'; print_r($keys); echo '<br />';
				foreach( $keys as $k ){
					
					unset( $categories[$cat_id]['posts_not_in'][$k]);
				
				}
			}
		}
		
	}

	$list_option['categories'] = $categories;

	$list_option['html_template']						= array();
	$list_option['html_template']['wrapper'] 			= w4pl_template_wrapper( $_POST['html_template']['wrapper'] );
	$list_option['html_template']['wrapper_post']		= w4pl_post_template_wrapper( $_POST['html_template']['wrapper_post'] );
	$list_option['html_template']['loop_post'] 			= w4pl_post_template_loop( $_POST['html_template']['loop_post'] );
	$list_option['html_template']['wrapper_category']	= w4pl_category_template_wrapper( $_POST['html_template']['wrapper_category'] );
	$list_option['html_template']['loop_category']		= w4pl_category_template_loop( $_POST['html_template']['loop_category'] );

	$list_data = compact( 'list_id', 'list_title', 'list_option' );
	return $list_data;
}

function w4pl_help_page(){ ?>
	<div class="has-right-sidebar">
	<div class="inner-sidebar" id="side-info-column">

	<div class="stuffbox w4pl_updates"><h3><?php _e( 'Plugin News Updates', 'w4-post-list'); ?></h3>
	<div class="inside">
	<?php w4pl_plugin_news(); ?>
	</div></div>

	<div class="stuffbox"><h3><?php _e( 'New in version 1.4', 'w4-post-list'); ?></h3>
	<div class="inside"><ul class="whats_new">
		<li><?php _e( 'New option page. Admin can assign who can create/manage post list by existing capability. If a user has role to only create and manage his own list, he won\'t be able to see/edit/delete the rest of post list option management page.', 'w4-post-list'); ?></li>
		<li><?php _e( 'Post list database management process. Admin can drop or install the plugin database on click. People are recommended to do removal and install od database once if they have upgraded to V-1.4 from a old once. When dabase table is dropped, plugin keeps the old data and promp for synchronize it once after installation of plugin database table. Only admin can have this feature.', 'w4-post-list'); ?></li>
		<li><?php _e( 'HTML Design template. You can design you list HTMl templte. For instruction, follow <a href="http://w4dev.com/wp/w4-post-list-design-template/">http://w4dev.com/wp/w4-post-list-design-template/</a>', 'w4-post-list'); ?></li>
	</ul>
	</div></div>

	<div class="stuffbox">
	<h3><?php _e( 'Shortcode', 'w4-post-list' ); ?></h3>
	<div class="inside"><?php _e( 'Use shortcode "postlist" with the list id to show a post list on a post or page content area.', 'w4-post-list' ); ?>	<?php _e( 'Example:', 'w4-post-list'); ?> <strong>[postlist 1]</strong>
	</div></div>

	<div class="stuffbox">
	<h3><?php _e( 'Call Post list PHP function:', 'w4-post-list'); ?></h3>
	<div class="inside"><?php _e( 'Show a specific post list directly to your theme, use tempate tag', 'w4-post-list' ); ?> <code>"w4_post_list"</code> 
	<?php _e( 'with the list id. Example:', 'w4-post-list'); ?> 
	<code>w4_post_list( 'the_list_id' )</code>.<br /><br /><?php _e( 'For returning value instead of echoing, use '); ?>
    <code>w4_post_list( 'the_list_id', false )</code>.
	</div></div>

	<div class="stuffbox">
	<h3><?php _e( 'Contact', 'w4-post-list' ); ?></h3>
	<div class="inside">
	<?php _e( 'Web: ', 'w4-post-list' ); ?> <a href="http://w4dev.com" target="_blank">w4 development</a><br />
	<?php _e( 'Email: ', 'w4-post-list' ); ?> <a href="mailto:workonbd@gmail.com" target="_blank">workonbd@gmail.com</a>
	</div></div>

	<div class="stuffbox"><h3><?php _e( 'Contribution', 'w4-post-list' ); ?></h3>
	<div class="inside">
    	<ul>
        <li><a href="http://wordpress.org/extend/plugins/w4-post-list/" target="_blank">Review this plugin on WordPress</a></li>
        <li><a href="http://w4dev.com/?utm_source=w4-post-list" target="_blank">Visit Author's site</a></li>
        </ul>
	</div></div>

	</div><!--#side-info-column-->

	<div id="post-body"><div id="post-body-content">

	<h4 style="color:#FF0000;"><?php _e( 'Click on the list item name above to edit an existing list. Click on add new to add a new one', 'w4-post-list'); ?></h4>
	<div class="stuffbox"><h3><?php _e( 'Understanding Plugin Options', 'w4-post-list'); ?>:</h3>
    <div class="inside">
    <ul class="help">
        <li><strong><?php _e( 'List ID:', 'w4-post-list'); ?></strong><br /><?php _e( 'Current list id. This id is necessary for showing list with shortcode. You can show a post list on your post or page by list id.', 'w4-post-list'); ?><br /><?php _e( 'Example:', 'w4-post-list'); ?> <code>[postlist 1]</code> <?php _e( 'will show the list having id 1.', 'w4-post-list'); ?></li>

        <li><strong><?php _e( 'List name:', 'w4-post-list'); ?></strong><br /><?php _e( 'This is not very essential now. Just for finding a list with this name on post list page menu.', 'w4-post-list'); ?></li>

        <li><strong><?php _e( 'List type:', 'w4-post-list'); ?></strong><br /><?php _e( 'List type chooser. Only post list, only category list and both them together are available.', 'w4-post-list'); ?><br /><span class="red"><?php _e( 'Note:', 'w4-post-list'); ?></span> <?php _e( 'Selecting and saving this option will hide or reveal related options. So we recommend you do make a save after choosing your list type.', 'w4-post-list'); ?></li>

        <li><strong><?php _e( 'Show posts in category with a jquery slide effect:', 'w4-post-list'); ?></strong><br /><?php _e( 'This is only for "Posts with categories" list type. Possitive selection will create a show/hide effect with jQuery to your list.', 'w4-post-list'); ?></li>

        <li><strong><?php _e( 'Post order by:', 'w4-post-list'); ?></strong><br /><?php _e( 'In Which base the post will be orderby. Available options are newest, oldest, most popular, less popular, by alphabetic order (A-Z/Z-A) and random.', 'w4-post-list'); ?></li>

		<li><strong class="red"><?php _e( 'Show future Posts:', 'w4-post-list'); ?></strong><br /><?php _e( 'Automatically add future posts to the category post/only posts/only posts by category list or remove.', 'w4-post-list'); ?></li>

        <li><strong><?php _e( 'Show item count appending to category name:', 'w4-post-list'); ?></strong><br /><?php _e( 'Show the published posts number for the category.', 'w4-post-list'); ?></li>

        <li><strong><?php _e( 'Readmore text', 'w4-post-list'); ?>:</strong><br /><?php _e( 'Display a read more link after the post content.', 'w4-post-list'); ?></li>
    </ul>
	</div></div>
       
	<div class="stuffbox"><h3><?php _e( 'Html Design Template', 'w4-post-list'); ?></h3>
	<div class="inside"><p><?php _e( 'Design your post list template to match your theme style. We have made <strong>teplate tag</strong> for each element of a post list.<br />
<span style="color:#FF0000">Caution: If you are not little expert understanding Basic HTMl and PhP Loop algorithm, just leave the design field as it is.</span>', 'w4-post-list' ); ?></p>
	<p><?php _e( 'Template <strong>Tags</strong> are placed in <code>"%%"</code> sign. Each tag has a repective value. Please make sure you understand them before you remove one.', 'w4-post-list' ); ?></p>
	</div></div>

	<div class="stuffbox"><h3><?php _e( 'Template Tags', 'w4-post-list'); ?></h3>
	<div class="inside w4pl_tags">
		<h4>General tags:</h4>
		<code>%%</code>postlist<code>%%</code> --  <?php _e( 'You complete post list html.', 'w4-post-list' ); ?><br />
		<code>%%</code>postloop<code>%%</code> -- <?php _e( 'Post Template Loop. While displaying posts, every post go through the <code>postloop</code> once.', 'w4-post-list' ); ?><br />
		<code>%%</code>catloop<code>%%</code> == <?php _e( 'Category Template Loop. While displaying categories, every category go through the <code>catloop</code> once', 'w4-post-list' ); ?>

		<h4>Category tags:</h4>
        <code>%%</code>category_title<code>%%</code> --  <?php _e( 'Category title template', 'w4-post-list' ); ?><br />
        <code>%%</code>category_count<code>%%</code> --  <?php _e( 'Category item count', 'w4-post-list' ); ?><br />
        <code>%%</code>category_posts<code>%%</code> --  <?php _e( 'Posts inside this category. If you leave this field empty, And using post category list type, selected posts wont be visible', 'w4-post-list' ); ?><br />

        <h4>Post tags:</h4>
        <code>%%</code>title<code>%%</code> --  <?php _e( 'Post title template', 'w4-post-list' ); ?><br />
        <code>%%</code>meta<code>%%</code> --  <?php _e( 'Meta template. <code><em>Ex: Posted on date by author</em></code>', 'w4-post-list' ); ?><br />
        <code>%%</code>publish/date<code>%%</code> --  <?php _e( 'Post publishing date template', 'w4-post-list' ); ?><br />
        <code>%%</code>modified<code>%%</code> --  <?php _e( 'Post last update date template', 'w4-post-list' ); ?><br />
        <code>%%</code>author<code>%%</code> --  <?php _e( 'Post author template linked to author url', 'w4-post-list' ); ?><br />
        <code>%%</code>excerpt<code>%%</code> --  <?php _e( 'Post excerpt template', 'w4-post-list' ); ?><br />
        <code>%%</code>post_excerpt<code>%%</code> --  <?php _e( 'Raw Post excerpt without wrapper. By default we wrap it with a html div', 'w4-post-list' ); ?><br />
        <code>%%</code>content<code>%%</code> --  <?php _e( 'Post content template', 'w4-post-list' ); ?><br />
        <code>%%</code>content<code>%%</code> --  <?php _e( 'Raw Post content without wrapper', 'w4-post-list' ); ?><br />
        <code>%%</code>more<code>%%</code> --  <?php _e( 'Read more template', 'w4-post-list' ); ?><br />

        <h4>More Post tags:</h4>
        <code>%%</code>id<code>|</code>ID<code>%%</code> --  <?php _e( 'Post ID', 'w4-post-list' ); ?><br />
        <code>%%</code>link<code>|</code>post_permalink<code>%%</code> --  <?php _e( 'Post permalink url address', 'w4-post-list' ); ?><br />
        <code>%%</code>publish/date<code>%%</code> --  <?php _e( 'Post publishing date', 'w4-post-list' ); ?><br />
        <code>%%</code>post_title<code>%%</code> --  <?php _e( 'Raw Post Title Without link', 'w4-post-list' ); ?><br />
        <code>%%</code>post_author<code>%%</code> --  <?php _e( 'Post author name', 'w4-post-list' ); ?><br />
        <code>%%</code>post_author<code>%%</code> --  <?php _e( 'Post author url address', 'w4-post-list' ); ?><br />

		<h4>Example:</h4>
        <p><?php _e( 'So now, you can wrap a tag easily with your own html tags. Like:', 'w4-post-list' );
		?> <code>&lt;span class=&quot;my-time&quot;&gt;%%publish%%&lt;/span&gt;</code></p>
	</div><!--inside-->
    </div><!--stuffbox-->


	</div></div><!---->
	</div>
<?php
}

// Retrive latest news about our plugin from our server
function w4pl_plugin_news( $echo = true, $refresh = false ){
	$transient = 'w4pl_plugin_newss';
	$transient_old = $transient . '_old';
	$expiration = 86400;

	$output = get_transient( $transient );

	if( $refresh || !$output || empty( $output )){

		$objFetchSite = _wp_http_get_object();
		$response = $objFetchSite->request( 
		'http://w4dev.com/wp-content/themes/w4-framework/lib/framework/ajax.php?action=plugin_news&item=1', 
		array( 'method' => 'GET' ));

		if ( is_wp_error( $response ) || empty( $response['body'] )){
			$output = get_option( $transient_old );
		}
		else{
			$output = $response['body'];
		}

		set_transient( $transient, $output, $expiration );
		// Save last new forever if a newer is not available..
		update_option( $transient_old, $output );
	}
	
	if( !$echo )
		return $output;
	else
		echo $output;
}

function w4pl_item_menu( $current = 0){
	global $wpdb, $w4pl_caps;
		
	if( !w4pl_table_exists())
		return '';
	
	$query = $wpdb->prepare( "SELECT * FROM $wpdb->post_list ORDER BY list_id ASC" );
		
	if ( ! $lists = $wpdb->get_results( $query ))
			$lists = array();

	$current = (int) $current;
	if( !$current & isset( $_GET['list_id']))
		$current = (int) $_GET['list_id'];
	
	if( !$w4pl_caps)
		$w4pl_caps = get_option( 'w4pl_options');
	
	$all_post_list = '<ul class="post_list_menu">';
	foreach( $lists as $list ){
		if( $list->list_id){
			if( w4pl_is_list_user( $list) || current_user_can( $w4pl_caps['manage_cap'])){

				$class = ($current == $list->list_id) ? 'current' : '';
				$title = empty( $list->list_title ) ? 'List#' . $list->list_id : $list->list_title;
				$url = add_query_arg( 'list_id', $list->list_id, w4pl_plugin_page_url());
	
				$all_post_list .= '<li><a title="Edit post list - '.$title.'" href="'. $url . '" class="'.$class.'">'. $title .'</a></li>';
			}
		}
	}
	$all_post_list .= "</ul>";
	echo $all_post_list;
}
	

	
function w4pl_delete_list( $list_id ){
	$list_id = (int) $list_id;
		
	if( !$list_id)
		return false;

	if( !w4pl_get_list( $list_id))
		return false;
	
	global $wpdb;
	
	if( !$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->post_list WHERE list_id = %d", $list_id )))
		return w4pl_add_error( 'couldnot delete the list, databse error.', 'database_error' );
		
	return $list_id;
}
	
//Save options
function w4pl_save_list( $options = array()){
	global $wpdb;

	if( !is_array( $options ))
		$options = array();

	if( !$list_id && !$options['list_option'])
		$options = w4pl_default_options();

	$list_id = (int) $options['list_id'];
	$list_title = $options['list_title'];
	$list_option = $options['list_option'];

	if( $list_id){
		// handling options
		$options = apply_filters( 'w4pl_sanitize_list_option', $options );

		$update = true;
		$old_options = w4pl_get_list( $list_id);

		$list_option = maybe_serialize( stripslashes_deep( $list_option ));
		$user_id = get_userdata( (int) $old_options['user_id'] ) ? $old_options['user_id'] : get_current_user_id();


		if( version_compare( get_option( '_w4pl_db_version' ), '2', '<' )){
			$options = compact( 'list_title', 'list_option' );

		}elseif( get_option( '_w4pl_db_version' ) == '2' ){
			$options = compact( 'list_title', 'list_option', 'user_id' );

		}

		$wpdb->update( $wpdb->post_list, $options, array( 'list_id' => $list_id));
	}
	else{
		
		$list_option = maybe_serialize( stripslashes_deep( $list_option ));
		$user_id = get_current_user_id();

		if( get_option( '_w4pl_db_version' ) == W4PL_DB_VERSION )
			$options = compact( 'list_title', 'list_option', 'user_id' );

		else
			$options = compact( 'list_title', 'list_option' );

		if( !$wpdb->insert( $wpdb->post_list, $options ))
			return new WP_Error( 'database_error', 'Could not add new list. Database problem.' );

		$list_id = $wpdb->insert_id;
	}

	if( empty( $list_title)){
		$options['list_title'] = 'List-' . $list_id;
		$wpdb->update( $wpdb->post_list, $options, array( 'list_id' => $list_id ));
	}
	return $list_id;
}

function w4pl_is_list_user( $list = array(), $list_id = 0){
	if( !$list )
		$list = w4pl_get_list( $list_id);
	
	if( is_object( $list ))
		$list = get_object_vars( $list);

	$cur_user_id = get_current_user_id();

	if( $cur_user_id == $list['user_id'])
		return true;
	
	return false;
}
/* We resave all the data upon activation/reactivation. As we change our data 
** structure it is important to resave the options and update the database once if available.
*/
function w4pl_database_update(){
	global $wpdb;
	w4pl_db_install( true );
#	update_option( '_w4pl_db_version', W4PL_DB_VERSION );
}
	
function w4pl_db_install( $force = false ){
	global $wpdb;

	if( w4pl_table_exists() && !$force )
		return;

	if( !empty ( $wpdb->charset ))
		$charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";

	if( !empty ( $wpdb->collate))
		$charset_collate .= " COLLATE {$wpdb->collate}";

	$sql[] = "CREATE TABLE $wpdb->post_list(
		  list_id bigint(20) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
		  list_title varchar(200) NOT NULL DEFAULT '',
		  list_option longtext NOT NULL,
		  user_id bigint(20) NOT NULL DEFAULT '0',
		  UNIQUE  KEY  list_id (list_id)
	){$charset_collate};";

	require_once( ABSPATH . 'wp-admin/upgrade-functions.php' );

	if( dbDelta( $sql ))
		update_option( '_w4pl_db_version', W4PL_DB_VERSION );
}

function w4ld_db_remove(){
	global $wpdb;

	// Remove the database version first
	delete_option( '_w4pl_db_version' );

	// Get Existing data @ array format
	$lists = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM  $wpdb->post_list ORDER BY list_id ASC" ), ARRAY_A );

	// Create an array to export list data
	$export_lists = array();
	
	if ( !empty( $lists )){
		delete_option( '_w4pl_temp_option' );
		update_option( '_w4pl_temp_option', $lists );
	}
	return $wpdb->query( "DROP TABLE IF EXISTS $wpdb->post_list" );
}

add_action( 'plugin_action_links_' . W4PL_BASENAME, 'w4pl_plugin_action_links' );
function w4pl_plugin_action_links( $links ){
	$readme_link['manage_plugin'] = '<a href="'. esc_attr( w4pl_plugin_page_url()) .'">' . __( 'Plugin', 'w4-post-list' ). '</a>';
	
	if( current_user_can( 'manage_options')){
		$readme_link['manage_plugin_options'] = '<a href="'. esc_attr( w4pl_option_page_url()) .'">' . __( 'Manage Options', 'w4-post-list' ). '</a>';
	}

	return array_merge( $links, $readme_link );
}
?>