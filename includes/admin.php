<?php
add_action( 'admin_menu', 'w4pl_admin_menu');
function w4pl_admin_menu(){
	global $wpdb, $wp_error, $plugin_page, $w4pl_caps, $w4pl_request;

	$w4pl_caps = get_option( 'w4pl_options' );
	if( !in_array( $w4pl_caps['access_cap'], array( 'manage_options', 'edit_others_posts', 'publish_posts', 'edit_posts')))
		$w4pl_caps['access_cap'] = 'manage_options';

	if( !in_array( $w4pl_caps['manage_cap'], array( 'manage_options', 'edit_others_posts', 'publish_posts', 'edit_posts' )))
		$w4pl_caps['manage_cap'] = 'manage_options';
	
	//Prepare menu
	add_menu_page( W4PL_NAME , W4PL_NAME , $w4pl_caps['access_cap'], W4PL_SLUG, 'w4pl_admin_page', '', '6' );
	add_submenu_page( W4PL_SLUG , 'Add list', 'Add New list' , $w4pl_caps['access_cap'], W4PL_SLUG.'&new_list=true', 'w4pl_admin_page' );
	add_submenu_page( W4PL_SLUG, 'W4 post list Credentials', 'Credential', 'manage_options', W4PL_SLUG . '-options' , 'w4pl_admin_option_page');
	
	if( in_array( $plugin_page, array( W4PL_SLUG, W4PL_SLUG . '-options' ))){

		$w4pl_request = isset( $_GET['action'] ) && in_array( $_GET['action'], array( 'add', 'edit', 'delete' )) ? $_GET['action'] : "";

		do_action( 'w4pl_admin_action' );
	}
}

//Plugin page option saving
function w4pl_admin_action(){
	global $wpdb, $plugin_page, $w4pl_caps, $w4_request;

	// Check Plugin database
	if( !w4pl_table_exists()){

		$msg = __( 'W4 post list plugin databse in not installed.', 'w4-post-list' );

		if( current_user_can( 'manage_options' ))
			$msg .= sprintf( __( ' <a class="button" href="%s">Install now</a>', 'w4-post-list'), add_query_arg( 'databse', 'install', w4pl_option_page_url()));

		else
			$msg .= __( ' Please notify admin to update plugin databse before you can start using it..', 'w4-post-list');

		return w4pl_add_error( $msg );
	}
	elseif( version_compare( get_option( '_w4pl_db_version' ), W4PL_DB_VERSION, '<'  )){

		if( current_user_can( 'manage_options'))
			$msg = sprintf( __( 'You have to update the database table structure for plugin to work properly. For this, remove the database table now and install again. Your old option will still remain. You can synchronise them after installation.<a class="button" href="%s">Remove database</a>', 'w4-post-list'), add_query_arg( 'databse', 'remove', w4pl_option_page_url()));
			
		else
			$msg = __( ' Please notify admin to update plugin databse before you can start using it..', 'w4-post-list');

		return w4pl_add_error( $msg );
	}

	// Plugin Admin Page
	if( W4PL_SLUG != $plugin_page )
		return;

	// Handle post list information
	if( !current_user_can( $w4pl_caps['access_cap']))
		return;

	// Create new list
	if( isset( $_GET['new_list']) && 'true' == $_GET['new_list'] ){
		$list_id = w4pl_save_list();

		// Create new list return error.
		if( is_wp_error( $list_id )){
			return w4pl_add_error( $list_id->get_error_message());

		// New Post list creation sucessfull
		}else{
			$url = add_query_arg( array( 'list_id' => $list_id, 'message' => 'list_created'), w4pl_plugin_page_url());
			wp_redirect( $url);
			exit;
		}
	}

	// Editing List Options. Check if list exists or not.
	if( isset( $_GET['list_id'] )){

		// We didnt find a list with the id. Redirect user to list not found page
		if( ! w4pl_get_list( $_GET['list_id'] )){
			$url = add_query_arg( 'message', 'no_list_found', w4pl_plugin_page_url());
			wp_redirect( $url );
			exit;
		}

		$list = w4pl_get_list( $_GET['list_id'] );

		// Current user is not the author of the list. Or Current user doent have permission to edit others list. Give them a n permissiion message
		if( !w4pl_is_list_user( $list ) && !current_user_can( $w4pl_caps['manage_cap'] )){
			$url = add_query_arg( 'message', 'no_permission', w4pl_plugin_page_url());
			wp_redirect( $url );
			exit;
		}
	}

	// Delete a list
	if( isset( $_GET['delete'] ) && 'true' == $_GET['delete'] ){

		$list_id = w4pl_delete_list( $_GET['list_id'] );
		if( is_wp_error( $list_id)){
			return w4pl_add_error( $list_id->get_error_message());
				
		}else{
			$url = add_query_arg( 'message', 'list_deleted', w4pl_plugin_page_url());
			wp_redirect( $url);
			exit;
		}
	}

	// Stop here if not Updating a list options & list id not given
	if( !isset( $_POST['save_w4_post_list_options'] ) && !isset( $_POST['list_id'] ))
		return;

	// Check list by post list_id
	if( isset( $_POST['list_id']) && !w4pl_get_list( $_POST['list_id'] )){
		$url = add_query_arg( 'message', 'no_list_found', w4pl_plugin_page_url());
		wp_redirect( $url);
		exit;
	}

	// Get Posted List Data.
	$list_data = w4pl_post_data();

	// Update Data
	$list_id = w4pl_save_list( $list_data );

	// Updating list Options Return Error.
	if( is_wp_error( $list_id )){
		return w4pl_add_error( $list_id->get_error_message());

	// Sucessfully Updated.
	}else{
		$url = add_query_arg( array( 'list_id' => $list_id, 'message' => 'list_saved' ), w4pl_plugin_page_url());
		wp_redirect( $url );
		exit;
	}
}
add_action( 'w4pl_admin_action', 'w4pl_admin_action' );

// Admin page template
function w4pl_admin_page(){
	global $wpdb, $w4pl_caps;

	if( !$w4pl_caps )
		$w4pl_caps = get_option( 'w4pl_options' );
?>
	<!-- W4 post list plugin Admin page -->
	<div id="w4pl_admin" class="wrap">
    	<div class="icon32" id="icon-post"><br/></div>
        <h2><?php echo W4PL_NAME. " &#8212; " . W4PL_VERSION ; ?>
        	<span class="desc"><?php _e( 'With the w4 post list plugin you can show a list of selected posts, selected categories or
a list with both of them on your WordPress site..', 'w4-post-list' ); ?></span>
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

		// If No List is beign edited or shown, show the documentation page
		if( !isset( $_GET['list_id'] ))
			echo w4pl_help_page();

		// Show Post list Form
        w4ld_list_form();
		?>
	</div>
<?php
}

function w4pl_post_data(){

	// Get Default list options
	$default_options = w4pl_default_options();

	$list_id = isset( $_POST['list_id'] ) ? $_POST['list_id'] : 0;
	$list_title = isset( $_POST['list_title'] ) ? $_POST['list_title'] : "";
	$list_option = array();

	foreach( $default_options['list_option'] as $key => $val ){
		// If a value is not available, use default value for it.
		if( !is_array( $val ))
			$list_option[$key] = isset( $_POST[$key] ) ? $_POST[$key] : $val;
	}

	if( !isset( $list_option['list_type'] ) || !in_array( $list_option['list_type'], array( 'pc', 'op', 'oc', 'op_by_cat' )))
		$list_option['list_type'] = 'pc';

	if( $list_option['list_type'] == 'op' ){
		$list_option['posts_not_in'] 		= w4pl_all_posts_id();
		$list_option['post_ids'] 			= (array) $_POST['w4pl_PID'];
		$list_option['categories']			= array();

		foreach( $list_option['post_ids'] as $post_id ){
			if( $keys = array_keys( $list_option['posts_not_in'], $post_id)){
				foreach( $keys as $k){
					unset( $list_option['posts_not_in'][$k]);
				}
			}
		}
	}
	else{
		$list_option['post_ids'] = array();
		$list_option['posts_not_in'] = array();
		$terms = array();

		$term_ids =  $_POST['w4pl_terms'];
		if( count( $term_ids ) > 0 ){
			foreach( (array) $term_ids as $term_id ){
				
				$terms[$term_id]['post_order_method'] 	= isset( $_POST['w4pl_terms_POM'][$term_id] ) ? $_POST['w4pl_terms_POM'][$term_id] : '';
				$terms[$term_id]['max'] 				= isset( $_POST['w4pl_terms_MAX'][$term_id] ) ? $_POST['w4pl_terms_MAX'][$term_id] : '';
				$terms[$term_id]['post_ids'] 			= isset( $_POST['w4pl_term_PID'][$term_id] ) ? $_POST['w4pl_term_PID'][$term_id] : array();
				$terms[$term_id]['show_future_posts'] 	= isset( $_POST['w4pl_term_SFP'][$term_id] ) ? $_POST['w4pl_term_SFP'][$term_id] : '';
				
				$_posts = w4pl_term_posts( $term_id );

				$terms[$term_id]['posts_not_in'] = !empty( $_posts ) ? $_posts : array();

				foreach( $terms[$term_id]['post_ids'] as $not_id ){
					if( $keys = array_keys( $terms[$term_id]['posts_not_in'], $not_id )){
						foreach( $keys as $k ){
							unset( $terms[$term_id]['posts_not_in'][$k]);
						}
					}
				}
			}
		}
		$list_option['categories'] = $terms;
	}

	$list_option['html_template']						= isset( $_POST['html_template'] ) ? $_POST['html_template'] : array();

	$list_data = compact( 'list_id', 'list_title', 'list_option' );
	return $list_data;
}
?>