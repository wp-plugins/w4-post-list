<?php
add_action( 'admin_menu', 'w4pl_admin_menu');
function w4pl_admin_menu(){
	global $wp_error, $plugin_page, $w4pl_caps;

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
		do_action( 'w4pl_admin_action');
	}
}

//Plugin page option saving
add_action( 'w4pl_admin_action', 'w4pl_form_action');
function w4pl_form_action(){
	global $wpdb, $wp_error, $plugin_page, $w4pl_caps;
		
	if ( empty( $wp_error))
		$wp_error = new WP_Error();
		
	// If this is the option page and curren use have the manage_options capabilities
	if( W4PL_SLUG . '-options' == $plugin_page && current_user_can( 'manage_options')):
		if( isset( $_GET['databse'])){
			$db_call = $_GET['databse'];
			switch( $db_call):
				case 'remove':
					if( !w4pl_table_exists()){
						return $wp_error->add( 'table_not_installed', 'Theres no table exists.');
						
					}else{
						w4ld_db_remove();
						$url = add_query_arg( 'message', 'db_dropped', w4pl_option_page_url());
						wp_redirect( $url);
						exit;
					}
				break;
				case 'install':
					if( w4pl_table_exists()){
						return $wp_error->add( 'table_already_installed', 'Tables already installed.');
						
					}else{
						w4pl_db_install( true );
						$url = add_query_arg( 'message', 'db_installed', w4pl_option_page_url());
						wp_redirect( $url);
						exit;
					}
				break;
				case 'update_option':
					$data = get_option( '_w4pl_temp_option');
					foreach( (array) $data as $list){
						$list['list_option'] = maybe_unserialize( $list['list_option'] );
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
		
		
		return $wp_error->add( 'table_not_installed', $msg );
	}
	elseif( version_compare( get_option( '_w4pl_db_version' ), '2', '<')){

		if( current_user_can( 'manage_options'))
			$msg = sprintf( __( 'You have to update the database table structure for plugin to work properly. For this, remove the database table now and install again. Your old option will still remain. You can synchronise them after installation.<a class="button" href="%s">Remove database</a>', 'w4-post-list'), add_query_arg( 'databse', 'remove', w4pl_option_page_url()));
		
		else
			$msg = __( ' Please notify admin to update plugin databse before you can start using it..', 'w4-post-list');

		return $wp_error->add( 'table_not_installed', $msg );
	}
	if( W4PL_SLUG == $plugin_page && current_user_can( $w4pl_caps['access_cap'])):
		// Create new list
		if( isset( $_GET['new_list']) && 'true' == $_GET['new_list']){
			$list_id = w4pl_save_list();
	
			if( is_wp_error( $list_id)){
				return $wp_error = $list_id;
	
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
				return $wp_error = $list_id;
				
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
		$list_id = w4pl_save_list( $list_data );
	
		if( is_wp_error( $list_id)){
			return $wp_error = $list_id;
	
		}else{
			$url = add_query_arg( array( 'list_id' => $list_id, 'message' => 'list_saved'), w4pl_plugin_page_url());
			wp_redirect( $url);
			exit;
		}
	endif; //Pluginpage
}

function w4pl_admin_option_page(){
	global $wp_error, $wpdb;

	if ( empty( $wp_error ))
		$wp_error = new WP_Error();
?>
	<div id="w4pl_admin" class="wrap">
    	<div class="icon32" id="icon-post"><br/></div>
        <h2>Settings - <?php echo W4PL_NAME. " - V:" . W4PL_VERSION ; ?>
        	<span class="desc"><?php _e( 'With w4 post list plugin you can show your selected post list, selected category list or<br /> making list with both of them in woedpress site.', 'w4-post-list' ); ?></span>
		</h2>
        <div class="menu">
        	<a style="background-color:#FFF000; color:#000000;" href="<?php echo w4pl_plugin_page_url(); ?>"><?php _e( 'Documentation', 'w4-post-list' ); ?></a>
            <a href="http://w4dev.com/w4-plugin/w4-post-list/" target="_blank" title="<?php _e( 'Visit Plugin Page', 'w4-post-list' ); ?>"> <?php 
			_e( 'Visit Plugin Page', 'w4-post-list' ); ?></a>
            <a href="mailto:sajib1223@gmail.com" rel="tabset_author_mail"> <?php 
			_e( 'Mailto:Author', 'w4-post-list' ); ?></a>

            <?php if( w4pl_table_exists()): ?>
        	<a style="background-color:#FF0000;" id="remove_w4ldb" href="<?php echo add_query_arg( 'databse', 'remove', w4pl_option_page_url()); ?>"><?php _e( 'Drop database table', 'w4-post-list' ); ?></a>
            <?php else: ?>
            <a style="background-color:#00FF00; color:#000000;" href="<?php echo add_query_arg( 'databse', 'install', w4pl_option_page_url()); ?>"><?php _e( 'Install database table', 'w4-post-list' ); ?></a>
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
			$wp_error->add( 'update_option', sprintf( __( 'Your old database options are still available. 
			<a class="button" href="%1$s"><strong>Update them</strong></a> or <a class="button" href="%2$s"><strong>delete them</strong></a>', 'w4-post-list'),
			add_query_arg( 'databse', 'update_option', w4pl_option_page_url()), add_query_arg( 'databse', 'delete_option', w4pl_option_page_url())));
		}
		
		// Only get the last error
		if ( is_wp_error( $wp_error) && $wp_error->get_error_message())
			echo '<div id="" class="error">'. $wp_error->get_error_message() .'</div>';

		if ( isset( $_GET['message'])){
			$mkey = $_GET['message'];
			if( in_array( $mkey, array_keys( $list_messages)))
				echo '<div id="" class="updated fade">'. $list_messages[$mkey] .'</div>';
			
			elseif( in_array( $mkey, array_keys( $list_errors)))
				echo '<div id="" class="error">'. $list_errors[$mkey] .'</div>';
		}

		w4ld_option_form();
		?>
	</div>
<?php
}
function w4pl_admin_page(){
	global $wp_error, $wpdb, $w4pl_caps;

	if( !$w4pl_caps)
		$w4pl_caps = get_option( 'w4pl_options');
	
	if ( empty( $wp_error ))
		$wp_error = new WP_Error();
?>
	<div id="w4pl_admin" class="wrap">
    	<div class="icon32" id="icon-post"><br/></div>
        <h2><?php echo W4PL_NAME. " - V:" . W4PL_VERSION ; ?>
        	<span class="desc"><?php _e( 'With w4 post list plugin you can show your selected post list, selected category list or<br /> making list with both of them in woedpress site.', 'w4-post-list' ); ?></span>
        	
		</h2>
        <div class="menu">
        	<a style="background-color:#FFF000; color:#000000;" href="<?php echo w4pl_plugin_page_url(); ?>"><?php _e( 'Documentation', 'w4-post-list' ); ?></a>
            <a href="http://w4dev.com/w4-plugin/w4-post-list/" target="_blank" title="<?php _e( 'Visit Plugin Page', 'w4-post-list' ); ?>"> <?php 
			_e( 'Visit Plugin Page', 'w4-post-list' ); ?></a>
            <a href="http://wordpress.org/extend/plugins/w4-post-list/" target="_blank" rel="wordpress" class="vote"> <?php 
			_e( 'Please rate and vote for this on wordpress', 'w4-post-list' ); ?></a>
			<a style="background-color:#00FF00; color:#000000;" href="<?php w4pl_add_url( true); ?>"><?php _e( 'Add new', 'w4-post-list' ); ?></a>
		</div>
        
		<?php w4pl_item_menu(); ?>
		<?php
		$list_messages = array(
			'list_saved'		=> __( 'Option saved.', 'w4-post-list'),
			'list_created' 		=> __( 'New post list Created.', 'w4-post-list'),
			'list_deleted'		=> __( 'One post list has been deleted.', 'w4-post-list')
		);

		$list_errors = array(
			'list_not_saved'	=> __( 'Unable to save. There may be a database connection error or this list may not have been exists or you do 
			not have capabilities to manage this list.'),
			'list_not_deleted'	=> __( 'Unable to delete this list now. There may be a database 
			connection error or this list may not have been exists or you do not have capabilities to delete this.'),
			'no_list_found'		=> __( 'List not found.'),
			'no_permission'		=> __( 'You dont have no permission to manage others list.')
		);

		if ( is_wp_error( $wp_error) && $wp_error->get_error_message())
			echo '<div id="" class="error">'. $wp_error->get_error_message() .'</div>';

		if ( isset( $_GET['message'])){
			$mkey = $_GET['message'];
			if( in_array( $mkey, array_keys( $list_messages)))
				echo '<div id="" class="updated fade">'. $list_messages[$mkey] .'</div>';
			
			elseif( in_array( $mkey, array_keys( $list_errors)))
				echo '<div id="" class="error">'. $list_errors[$mkey] .'</div>';
		}

		if( !$_GET['list_id'])
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

	$list_option['post_ids'] 			= ( array ) $_POST['list_option']['post_ids'];
	$list_option['posts_not_in'] 		= w4pl_all_posts_id();
	foreach( $list_option['post_ids'] as $post_id){
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
		$categories[$cat_id]['posts_not_in'] 		= w4pl_category_posts_id( $cat_id );

		$categories[$cat_id]['post_ids'] 			= ( array ) $_POST['category_posts'][$cat_id];
		$categories[$cat_id]['show_future_posts'] 	= ( !$_POST['_w4_cat_show_future_posts_' . $cat_id]) ? 'no' : $_POST['_w4_cat_show_future_posts_'.$cat_id];
		
		#print_r( $_w4_cat_posts );
		
		foreach( $categories[$cat_id]['posts_not_in'] as $not_id){
			if( $keys = array_keys( $categories[$cat_id]['post_ids'], $not_id)){
				foreach( $keys as $k )
					unset( $categories[$cat_id]['posts_not_in'][$k]);
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
	<h4><?php _e( 'Click on the list item name above to edit an existing list. Click on add new to add a new one', 'w4-post-list'); ?></h4>
	<div class="stuffbox"><h3><?php _e( 'To Contribute for this plugin development -  ', 'w4-post-list' ); ?></h3>
	<div class="inside">
    <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
    <input type="hidden" name="business"
        value="w4development@gmail.com">
 
    <input type="hidden" name="cmd" value="_donations">
    <input type="hidden" name="item_name" value="W4 plugins">
    <input type="hidden" name="item_number" value="w4-post-list">
    <input type="hidden" name="currency_code" value="GBP">
 
    <input type="image" name="submit" border="0"
        src="https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif"
        alt="PayPal - The safer, easier way to pay online" style="border:none; background:none;">
    <img alt="" border="0" width="1" height="1"
        src="https://www.paypal.com/en_US/i/scr/pixel.gif" />
</form>
	</div></div>
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
       
	<div class="stuffbox"><h3><?php _e( 'New in version 1.4', 'w4-post-list'); ?></h3>
	<div class="inside"><ul class="whats_new">
		<li><?php _e( 'New option page. Admin can assign who can create/manage post list by existing capability. If a user has role to only create and manage his own list, he won\'t be able to see/edit/delete the rest of post list option management page.', 'w4-post-list'); ?></li>
		<li><?php _e( 'Post list database management process. Admin can drop or install the plugin database on click. People are recommended to do removal and install od database once if they have upgraded to V-1.4 from a old once. When dabase table is dropped, plugin keeps the old data and promp for synchronize it once after installation of plugin database table. Only admin can have this feature.', 'w4-post-list'); ?></li>
		<li><?php _e( 'HTML Design template. You can design you list HTMl templte. For instruction, follow <a href="http://w4dev.com/wp/w4-post-list-design-template/">http://w4dev.com/wp/w4-post-list-design-template/</a>', 'w4-post-list'); ?></li>
	</ul>
	</div></div>

	<div class="stuffbox"><h3><?php _e( 'Html Design Template', 'w4-post-list'); ?></h3>
	<div class="inside"><p><?php _e( 'Design your post list template to match your theme style. We have made <strong>teplate tag</strong> for each element of a post list.<br />
<span style="color:#FF0000">Caution: If you are not little expert understanding Basic HTMl and PhP Loop algorithm, just leave the design field as it is.</span>', 'w4-post-list' ); ?></p>
	<p><?php _e( 'Template <strong>Tags</strong> are placed in <code>"%%"</code> sign. Each tag has a repective value. Please make sure you understand them before you remove one.', 'w4-post-list' ); ?></p>
	</div></div>

	<div class="stuffbox"><h3><?php _e( 'Template Tags', 'w4-post-list'); ?></h3>
	<div class="inside"><ul class="help">
	<li><code>%%postlist%%</code> --  <?php _e( 'You complete post list html.', 'w4-post-list' ); ?></li>

	<li><code>%%postloop%%</code> == <?php _e( 'Post Template Loop. While displaying posts, every post go through the <code>postloop</code> once.', 'w4-post-list' ); ?></li>
	<li><code>%%title%%</code> --  <?php _e( 'Post title template. Title will be linked to the post page.', 'w4-post-list' ); ?></li>
    <li><code>%%publish%%</code> --  <?php _e( 'Post publish date.', 'w4-post-list' ); ?></li>
    <li><code>%%modified%%</code> --  <?php _e( 'Post last update date. (modified time)', 'w4-post-list' ); ?></li>
    <li><code>%%content%%</code> --  <?php _e( 'Post content.', 'w4-post-list' ); ?></li>
    <li><code>%%excerpt%%</code> --  <?php _e( 'Post excerpt. Excerpt lenght should be assigned.', 'w4-post-list' ); ?></li>
    <li><code>%%more%%</code> --  <?php _e( 'Read more link for post.', 'w4-post-list' ); ?></li>

	<li><code>%%catloop%%</code> == <?php _e( 'Category Template Loop. While displaying categories, every category go through the <code>catloop</code> once', 'w4-post-list' ); ?></li>
	<li><code>%%category_title%%</code> --  <?php _e( 'Title of this category, will be linked to the category url.', 'w4-post-list' ); ?></li>
	<li><code>%%category_count%%</code> --  <?php _e( 'Published post item inside this category.', 'w4-post-list' ); ?></li>
	<li><code>%%category_posts%%</code> ==  <?php _e( 'Posts of that category. In this position, the <code>postloop</code> tag will be parsed.', 'w4-post-list' ); ?></li>
	</ul>

	<p><?php _e( 'So now, you can wrap a tag easily with your own html tags. Like:', 'w4-post-list' ); ?> <code>&lt;span class=&quot;my-time&quot;&gt;%%publish%%&lt;/span&gt;</code></p>
	</div></div>

	<div class="stuffbox"><h3><?php _e( 'For PHP function usage:', 'w4-post-list'); ?></h3>
	<div class="inside"><p><?php _e( 'Show a specific post list directly to your theme, use tempate tag', 'w4-post-list' ); ?> <code>"w4_post_list"</code> 
	<?php _e( 'with the list id. Example:', 'w4-post-list'); ?> 
	<code>w4_post_list( 'the_list_id' )</code>.<br /><?php _e( 'For returning value instead of echoing, use '); ?>
    <code>w4_post_list( 'the_list_id', false )</code>.
	</p>
	</div></div>

	<div class="stuffbox"><h3><?php _e( 'Use shortcode "postlist" with the list id to show a post list on a post or page content area.', 'w4-post-list' ); ?>	<?php _e( 'Example:', 'w4-post-list'); ?> 
	<strong>[postlist 1]</strong></h3>

	<h3><?php _e( 'Web: ', 'w4-post-list' ); ?> <a href="http://w4dev.com" target="_blank">w4 development</a>,
	<?php _e( 'Email: ', 'w4-post-list' ); ?> <a href="mailto:workonbd@gmail.com" target="_blank">workonbd@gmail.com</a></h3>
	</div>
<?php
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
	foreach($lists as $list){
		if( $list->list_id){
			if( w4pl_is_list_user( $list) || current_user_can( $w4pl_caps['manage_cap'])){
			#	echo '1';
				$class = ($current == $list->list_id) ? 'current' : '';
				$title = empty($list->list_title) ? 'List#' . $list->list_id : $list->list_title;
				$url = add_query_arg( 'list_id', $list->list_id, w4pl_plugin_page_url());
	
				$all_post_list .= '<li><a href="'. $url . '" class="'.$class.'">'. $title .'</a></li>';
			}
		}
	}
	$all_post_list .= "</ul>";
	echo $all_post_list;
}
	

	
function w4pl_delete_list( $list_id){
	$list_id = (int) $list_id;
		
	if( !$list_id)
		return false;

	if( !w4pl_get_list( $list_id))
		return false;
	
	global $wpdb;
	
	if( !$wpdb->query( $wpdb->prepare( "DELETE FROM $wpdb->post_list WHERE list_id = %d", $list_id )))
		return new WP_Error( 'database_error', 'couldnot delete the list, databse error.');
		
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
		$user_id = get_userdata( $old_options['user_id'] ) ? $old_options['user_id'] : get_current_user_id();

		if( get_option( '_w4pl_db_version' ) == W4PL_DB_VERSION )
			$options = compact( 'list_title', 'list_option', 'user_id' );

		else
			$options = compact( 'list_title', 'list_option' );

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
	dbDelta( $sql );
}

function w4ld_db_remove(){
	global $wpdb;

	update_option( '_w4pl_db_version', W4PL_DB_VERSION );

	$query = $wpdb->prepare( "SELECT * FROM  $wpdb->post_list ORDER BY list_id ASC" );
	$lists = $wpdb->get_results( $query );
	$options_all = array();
	
	if ( $lists){
		delete_option( '_w4pl_temp_option');
		$i = 1;
		foreach( $lists as $list){
			
			if( is_object( $list))
				$list = get_object_vars( $list );
			$key = $list['list_id'];
			unset( $list['list_id'] );
			$options_all[$key] = $list;
			$i++;
		}		
		if( $i > 1 )
			update_option( '_w4pl_temp_option', $options_all);
	}
	return $wpdb->query( "DROP TABLE IF EXISTS $wpdb->post_list");
}

add_action( 'plugin_action_links_' . W4PL_BASENAME, 'w4pl_plugin_action_links' );
function w4pl_plugin_action_links( $links ){
	$readme_link['readme'] = '<a href="'.esc_attr( w4pl_plugin_page_url()).'">' . __( 'Manage', 'w4-post-list' ). '</a>';
	return array_merge( $links, $readme_link );
}

?>