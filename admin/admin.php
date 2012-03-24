<?php
add_action( 'admin_menu', 'w4pl_admin_menu');
function w4pl_admin_menu(){
	global $menu, $submenu, $submenu_file, $plugin_page, $w4pl_subpages, $w4pl_pagenow, $w4pl_action, $w4pl_admin_url, $w4pl_plugin_option;

	$w4pl_admin_url = admin_url( 'admin.php?page=' . W4PL_SLUG );

	$w4pl_plugin_option = w4pl_plugin_option();
	if( !current_user_can( $w4pl_plugin_option['access_cap'] ))
		return;

	$w4pl_subpages = array(
		'credentials' 	=> array( 'Credentials', 'manage_options' ),
		'docs' 			=> array( 'Documentation', $w4pl_plugin_option['access_cap'] )
	);
	
	//Prepare menu
	add_menu_page( W4PL_NAME , W4PL_NAME , $w4pl_plugin_option['access_cap'], W4PL_SLUG, 'w4pl_admin_page', W4PL_URL .'scripts/menu.png', '6' );

	if( $plugin_page == W4PL_SLUG ){
		$w4pl_action = isset( $_GET['action'] ) ? $_GET['action'] : "";
		$w4pl_pagenow = isset( $_REQUEST['subpage'] ) ? $_REQUEST['subpage'] : '';

		// Default Actions
		add_action( "w4pl_admin_action", 'w4pl_load_admin_scripts', 1 );
		add_action( 'w4pl_admin_action', 'w4pl_include_message' );
		add_action( 'w4pl_admin_body_top', 'w4pl_display_notice' );

		if( !empty( $w4pl_pagenow ))
			do_action( 'w4pl_admin_action_'. $w4pl_pagenow );

		else
			do_action( 'w4pl_admin_action_default' );

		do_action( 'w4pl_admin_action' );
	}

	// We are doing things a little bit fancy here. Not using the builtin wordpress submenu method.
	// As We have reated submenus within the main menu not a stand alone one, here is the solution.
	$i=1;
	$submenu[W4PL_SLUG][0] = array( W4PL_NAME, $w4pl_plugin_option['access_cap'], 'admin.php?page='. W4PL_SLUG );

	if( empty( $w4pl_pagenow ) && $plugin_page == W4PL_SLUG )
		$submenu_file = 'admin.php?page='.W4PL_SLUG;

	foreach( $w4pl_subpages as $key => $val ){
		$submenu_url = 'admin.php?page='. W4PL_SLUG .'&subpage='. $key;

		if( $w4pl_pagenow == $key )
			$submenu_file = $submenu_url;

		$submenu[W4PL_SLUG][$i] = array( $val[0], $val[1], $submenu_url );
		$i++;
	}
}

// Plugin Page script loaders. Only Loads Them When Editing or adding new list..
function w4pl_load_admin_scripts(){
	wp_enqueue_script( 'w4-post-list-admin', W4PL_URL.'scripts/w4-post-list-admin.js', array( 'jquery','jquery-ui-core','jquery-ui-tabs' ), W4PL_VERSION , false );
	wp_enqueue_style( 'w4-post-list-admin', W4PL_URL . 'scripts/w4-post-list-admin.css', '', W4PL_VERSION );
}
function w4pl_admin_page(){
	global $w4pl_pagenow, $w4pl_action;
?>
<div class="wrap" id="w4pl_admin">
	<div id='w4pl_header'>
	<div id='icon-w4pl' class='icon32'><br></div>
	<h2 id="w4pl-title"><?php echo W4PL_NAME . ': V.' . W4PL_VERSION; ?></h2>
    <span class="desc"><?php _e( 'With the w4 post list plugin you can show a list of selected posts, selected categories or a list with both of them on your WordPress site. Plugin comes with heavy customizable options to make exactly what you want o do with this. You are free to write us sudggestions /bugs report /complains.', 'w4-post-list' ); ?></span>

	<?php if( !in_array( $w4pl_action, array( 'add', 'edit' ))): ?>
	<div style="overflow:hidden;">
    	<script type="text/javascript" src="http://apis.google.com/js/plusone.js"></script>
		<table style="margin-bottom:10px;"><tr>
        <td style="padding-right:5px;">Share this plugin:</td>
        <td style="padding-right:5px;"><g:plusone annotation="none" href="http://w4dev.com/w4-plugin/w4-post-list/"></g:plusone></td>
        <td><iframe src="http://www.facebook.com/plugins/like.php?href=<?php echo urlencode( 'http://w4dev.com/w4-plugin/w4-post-list/' ); ?>&amp;send=true&amp;layout=standard&amp;width=450&amp;show_faces=false&amp;action=recommend&amp;colorscheme=light&amp;font&amp;height=25" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:450px; height:25px;" allowTransparency="true"></iframe></td>
		</tr></table>
	</div>
	<?php endif; ?>
	</div><!--#w4pl_header-->
	<p style="background-color:#FFFFE0; border:1px solid #E6DB55; padding:5px 10px; border-width:1px 0; overflow:hidden;"><?php w4pl_plugin_news(); ?></p>

	<?php echo w4pl_pluginpage_menus(); ?>

	<div id="w4pl" class="metabox-holder">
		<div id="post-body"><div id="post-body-content">
			<?php do_action( 'w4pl_admin_body_top'); ?>
			<div id="w4pl_ajax_update"></div>

			<?php if( !empty( $w4pl_pagenow ))
				do_action( 'w4pl_admin_body_'. $w4pl_pagenow );

			else
				do_action( 'w4pl_admin_body_default' );

			do_action( 'w4pl_admin_body_bottom' ); ?>
		</div></div>
	</div><!--#w4pl-->
</div><!--#wrap-->
<?php
}

function w4pl_pluginpage_menus(){
	global $w4pl_admin_url, $w4pl_pagenow;
	
#	if( $w4pl_pagenow == 'credentials' ):
?>
	<div class="menu">
	<a href="http://w4dev.com/w4-plugin/w4-post-list/" target="_blank" title="<?php _e( 'Plugin Page', 'w4-post-list' ); ?>"> <?php 
	_e( 'Visit Plugin Page', 'w4-post-list' ); ?></a>
	<a href="mailto:sajib1223@gmail.com" rel="tabset_author_mail"> <?php 
	_e( 'Mailto:Author', 'w4-post-list' ); ?></a>
	<a href="http://wordpress.org/extend/plugins/w4-post-list/" target="_blank" rel="wordpress" class="vote"> <?php 
	_e( 'Vote on wordpress', 'w4-post-list' ); ?></a>

	<a style="border-color:#E6DB55;" href="<?php echo add_query_arg( array( 'subpage' => 'docs'), $w4pl_admin_url ); ?>"><?php _e( 'Documentation', 'w4-post-list' ); ?></a>

	<a style="border-color:#E6DB55;" href="<?php echo $w4pl_admin_url; ?>"><?php _e( 'Your Lists', 'w4-post-list' ); ?></a>
	<?php if( current_user_can( 'manage_options' )): ?>

        <a style="border-color:#E6DB55; border-width:1px;" href="<?php 
        echo add_query_arg( 'subpage', 'credentials', $w4pl_admin_url ); ?>"><?php 
        _e( 'Credentials', 'w4-post-list' ); ?></a>

		<?php if( $w4pl_pagenow == 'credentials' ): ?>
            <?php if( w4pl_table_exists()): ?>
                <a style="background-color:#e23a3a; color:#FFF; font-weight:bold; border:1px solid #740909;" id="remove_w4ldb" href="<?php 
                echo add_query_arg( array( 'subpage' => $w4pl_pagenow, 'action' => 'removedb' ), $w4pl_admin_url ); ?>"><?php 
                _e( 'Drop database table', 'w4-post-list' ); ?></a>
            <?php else: ?>
                <a style="background-color:#67A54B; color:#FFF; font-weight:bold; border:1px solid #3c7123;" href="<?php echo 
                add_query_arg( array( 'subpage' => $w4pl_pagenow, 'action' => 'installdb' ), $w4pl_admin_url ); ?>"><?php 
                _e( 'Install database table', 'w4-post-list' ); ?></a>
            <?php endif; ?>
        <?php endif; ?>
	<?php endif; ?>

	<?php if( $w4pl_pagenow != 'credentials' && w4pl_table_exists()): ?>
        <a style="background-color:#67A54B; color:#FFF; font-weight:bold; border-color:#3c7123; border-width:1px;" href="<?php echo 
        add_query_arg( 'action', 'add', $w4pl_admin_url ); ?>"><?php 
        _e( 'Add new', 'w4-post-list' ); ?></a>
	<?php endif; ?>
	</div>

	<?php
}

function w4pl_admin_body_listpage(){
	global $wpdb, $w4pl_action, $w4pl_admin_url, $w4pl_plugin_option;

	if( !empty( $w4pl_action ) && in_array( $w4pl_action, array( 'add', 'edit', 'delete' )))
		return;

	$current_user_id = get_current_user_id();
	$query = $wpdb->prepare( "SELECT * FROM $wpdb->post_list ORDER BY list_id ASC" );

	if( !current_user_can( $w4pl_plugin_option['manage_cap'] )){
		$query = $wpdb->prepare( "SELECT * FROM $wpdb->post_list WHERE user_id = '$current_user_id' ORDER BY list_id ASC" );
	}

	if( !$w4pl_lists = $wpdb->get_results( $query ))
		$w4pl_lists = array();

	$list_types = array(
		"pc" => __( 'Posts with categories', 'w4-post-list' ),
		'oc' => __( 'Only categories', 'w4-post-list' ),
		'op' => __( 'Only posts', 'w4-post-list' ),
		'op_by_cat' => __( 'Only posts - <small> select by category</small>', 'w4-post-list' ));

	echo '<table cellspacing="0" cellpadding="0" border="0" width="100%" class="widefat">';
	echo '<thead><tr><th>ID</th><th>Title</th><th>List type</th></th><th>Author</th><th>Action</th></tr></thead><tbody>';

	$class = 'alternate';

	if( count( $w4pl_lists ) < 1 ):
		echo "<tr class='alternate'><td align='center' colspan='5'>No available list</td></tr>";
	
	else:
		foreach( $w4pl_lists as $w4pl_list ){
			$class = ( $class != 'alternate') ? 'alternate' : '';
			echo "<tr class='$class'>";
			echo "<td>{$w4pl_list->list_id}</td>";
			echo "<td>{$w4pl_list->list_title}</td>";

			echo "<td>";
			$lt = maybe_unserialize( $w4pl_list->list_option );
			#echo $w4pl_list->list_option;
			if( array_key_exists( $lt['list_type'], $list_types )){
				$t = $lt['list_type'];
				echo $list_types[$t];
			}
			else
				echo 'Unknown';

			echo "</td>";

			echo "<td>";
			if( $w4pl_list->user_id == get_current_user_id())
				echo 'you';
			
			else{
				$u_data = get_userdata( $w4pl_list->user_id );
				if( !is_wp_error( $u_data ) && isset( $u_data->display_name )){
					echo $u_data->display_name;
				}
				else{
					echo "Unknown user";
				}
			}
			echo "</td>";

			echo "<td>";
			printf( '<a href="%s">edit</a>', add_query_arg( array( 'action' => 'edit', 'list_id' => $w4pl_list->list_id ), $w4pl_admin_url ));
			printf( ' | <a href="%1$s" class="delete_list" rel="%2$s" >delete</a>', add_query_arg( array( 'action' => 'delete', 'list_id' => $w4pl_list->list_id ), $w4pl_admin_url ), $w4pl_list->list_title );
			echo "</td>";
			echo "</tr>";
		}
	endif;

	echo '</tbody></table>';
}
add_action( 'w4pl_admin_body_default', 'w4pl_admin_body_listpage' );

function w4pl_admin_body_formpage(){
	global $wpdb, $w4pl_subpages, $w4pl_pagenow, $w4pl_action, $w4pl_admin_url, $w4pl_plugin_option, $w4pl_list_option;

	if( empty( $w4pl_action ) || !in_array( $w4pl_action, array( 'add', 'edit' )))
		return;

	if( !$w4pl_list_option )
		$w4pl_list_option = array();

	$w4pl_list_option['list_id'] = isset( $w4pl_list_option['list_id'] ) ? $w4pl_list_option['list_id'] : 0;
	$w4pl_list_option['list_title'] = isset( $w4pl_list_option['list_title'] ) ? $w4pl_list_option['list_title'] : "";
	$w4pl_list_option['user_id'] = isset( $w4pl_list_option['user_id'] ) ? $w4pl_list_option['user_id'] : get_current_user_id();

	foreach( array( 
			'list_type'		 			=> 'pc',
			'list_effect' 				=> 'no',	
			'post_max'					=> 0,
			'post_order_method'			=> 'newest',
			'show_future_posts'			=> 'no',
			'read_more_text'			=> 'Continue reading...',
			'excerpt_length' 			=> 10,
			'image_size' 				=> 'thumbnail',

			'post_ids'					=> array(),
			'posts_not_in'				=> array(),
			'categories'				=> array(),
			'html_template'				=> array()
			) as $k => $d ){

		if( !isset( $w4pl_list_option['list_option'][$k] )){
			$w4pl_list_option['list_option'][$k] = $d;
		}
	}

	if( !isset( $w4pl_list_option['list_option']['html_template'] ))
		$w4pl_list_option['list_option']['html_template'] = array();

	$template_array = array( 
		'wrapper' 			=> 'w4pl_template_wrapper',
		'wrapper_post'		=> 'w4pl_post_template_wrapper',
		'loop_post' 		=> 'w4pl_post_template_loop',
		'wrapper_category'	=> 'w4pl_category_template_wrapper',
		'loop_category'		=> 'w4pl_category_template_loop'
	);

	foreach( $template_array as $template => $callback ){
		if( ( !isset( $w4pl_list_option['list_option']['html_template'][$template] ) || empty( $w4pl_list_option['list_option']['html_template'][$template] )) 
		&& is_callable( $callback ))
		$w4pl_list_option['list_option']['html_template'][$template] = call_user_func( $callback );
	}

	w4ld_list_form( $w4pl_list_option );
}
add_action( 'w4pl_admin_body_default', 'w4pl_admin_body_formpage' );

//Plugin page option saving
function w4pl_admin_action(){
	global $wpdb, $plugin_page, $w4pl_plugin_option, $w4pl_pagenow, $w4pl_action, $w4pl_admin_url, $w4pl_list_option;

	// Check Plugin database
	if( !w4pl_table_exists()){

		$msg = __( 'W4 post list plugin databse in not installed.', 'w4-post-list' );

		if( current_user_can( 'manage_options' ))
			$msg .= sprintf( __( ' <a class="button" href="%s">Install now</a>', 'w4-post-list'), add_query_arg( array( 'subpage' => 'credentials', 'action' => 'installdb' ), $w4pl_admin_url ));

		else
			$msg .= __( ' Please notify admin to update plugin databse before you can start using it..', 'w4-post-list');

		return w4pl_add_error( $msg );
	}
	elseif( version_compare( get_option( '_w4pl_db_version' ), W4PL_DB_VERSION, '<'  )){

		if( current_user_can( 'manage_options'))
			$msg = sprintf( __( 'You have to update the database table structure for plugin to work properly. For this, Drop the Database Table and Install again. Your old option will still remain. You can synchronise them after Installation, <a id="remove_w4ldb" href="%s">remove database now ?</a>', 'w4-post-list'), add_query_arg( array( 'subpage' => 'credentials', 'action' => 'removedb' ), $w4pl_admin_url ));
			
		else
			$msg = __( ' Please notify admin to update W4 Post list Plugin Database before you can start using it..', 'w4-post-list' );

		return w4pl_add_error( $msg );
	}

	if( !empty( $w4pl_pagenow ))
		return;

	// Handle post list information
	if( !current_user_can( $w4pl_plugin_option['access_cap'] ))
		return;

	// Delete a list
	if( $w4pl_action && $w4pl_action == 'delete' ){
		if( !isset( $_GET['list_id'] )){
			$url = add_query_arg( array( 'error' => 'list_not_deleted' ), $w4pl_admin_url );
			wp_redirect( $url);
			exit;
		}
		elseif( !$w4pl_list_option = w4pl_get_list( $_GET['list_id'] )){
			$url = add_query_arg( array( 'error' => 'no_list_found' ), $w4pl_admin_url );
			wp_redirect( $url);
			exit;
		}
		
		if( !current_user_can( $w4pl_plugin_option['manage_cap'] ) && !w4pl_is_list_user( $w4pl_list_option )){
			$url = add_query_arg( array( 'error' => 'no_permission' ), $w4pl_admin_url );
			wp_redirect( $url);
			exit;
		}

		$list_id = w4pl_delete_list( $_GET['list_id'] );
		if( is_wp_error( $list_id )){
			return w4pl_add_error( $list_id->get_error_message());

		}else{
			$url = add_query_arg( 'message', 'list_deleted', $w4pl_admin_url );
			wp_redirect( $url);
			exit;
		}
	}

	if( $w4pl_action && $w4pl_action == 'edit' ){
		if( !isset( $_GET['list_id'] )){
			$url = add_query_arg( array( 'action' => 'add' ), $w4pl_admin_url );
			wp_redirect( $url);
			exit;
		}

		elseif( !$w4pl_list_option = w4pl_get_list( $_GET['list_id'] )){
			$url = add_query_arg( array( 'error' => 'no_list_found' ), $w4pl_admin_url );
			wp_redirect( $url);
			exit;
		}

		if( !current_user_can( $w4pl_plugin_option['manage_cap'] ) && !w4pl_is_list_user( $w4pl_list_option )){
			$url = add_query_arg( array( 'error' => 'no_permission' ), $w4pl_admin_url );
			wp_redirect( $url);
			exit;
		}
	}

	// Stop here if no option posted yet..
	if( !isset( $_POST['w4pl_update_list'] ))
		return;

	$update = false;
	if( !isset( $_GET['list_id'] ))
		$list_id = 0;

	elseif( !w4pl_get_list( $_GET['list_id'] )){
		$url = add_query_arg( array( 'action' => 'add' ), $w4pl_admin_url );
		wp_redirect( $url );
		exit;
	}
	elseif( isset( $_GET['list_id'] ) && w4pl_get_list( $_GET['list_id'] )){
		$list_id = $_GET['list_id'];
		$update = true;
	}

	$list_title = isset( $_POST['list_title'] ) ? $_POST['list_title'] : "";
	$list_option = array();

	foreach( array( 
			'list_type'		 			=> 'pc',
			'list_effect' 				=> 'no',	
			'post_max'					=> 0,
			'post_order_method'			=> 'newest',
			'show_future_posts'			=> 'no',
			'read_more_text'			=> 'Continue reading...',
			'excerpt_length' 			=> 10,
			'image_size' 				=> 'thumbnail'
			) as $k => $d ){
		$list_option[$k] =  isset( $_POST[$k] ) ? $_POST[$k] : $d;
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

		$term_ids = isset( $_POST['w4pl_terms'] ) ? $_POST['w4pl_terms'] : array();
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

	$list_id = w4pl_save_list( $list_data );

	// Updating list Options Return Error.
	if( is_wp_error( $list_id )){
		return w4pl_add_error( $list_id->get_error_message());

	// Sucessfully Updated.
	}else{
		if( $update ){
			$url = add_query_arg( array( 'action' => 'edit', 'list_id' => $list_id, 'message' => 'list_updated' ), $w4pl_admin_url );
			wp_redirect( $url );
			exit;
		}
		
		$url = add_query_arg( array( 'action' => 'edit', 'list_id' => $list_id, 'message' => 'list_inserted' ), $w4pl_admin_url );
		wp_redirect( $url );
		exit;
	}
}
add_action( 'w4pl_admin_action', 'w4pl_admin_action' );

// Plugin Credential Page
function w4pl_admin_body_credentials(){
	if( current_user_can( 'manage_options' )){
		w4pl_option_form();
	}
	else{
		echo 'You dont have permission to manage this page.';
	}
}
add_action( 'w4pl_admin_body_credentials', 'w4pl_admin_body_credentials' );

// Plugin option page data handler
function w4pl_admin_action_credentials(){
	global $wpdb, $w4pl_plugin_option, $w4pl_action, $w4pl_pagenow, $w4pl_admin_url;

	// If this is the option page and curren use have the manage_options capabilities
	if( !current_user_can( 'manage_options' )){
		$url = add_query_arg( array( 'error' => 'no_permission' ), $w4pl_admin_url );
		wp_redirect( $url);
		exit;
	}

	if( get_option( '_w4pl_temp_option' ) && w4pl_table_exists()){
		w4pl_add_message( sprintf( __( 'Your old database options are still available. 
		<a class="button" href="%1$s"><strong>Update them</strong></a> or <a class="button" href="%2$s"><strong>delete them</strong></a>', 'w4-post-list' ),
		add_query_arg( array( 'subpage' => $w4pl_pagenow, 'action' => 'update_option' ), $w4pl_admin_url ),
		add_query_arg( array( 'subpage' => $w4pl_pagenow, 'action' => 'delete_option' ), $w4pl_admin_url )
		));
	}

	if( !empty( $w4pl_action )):
		switch( $w4pl_action ):
			case 'removedb':
				if( !w4pl_table_exists()){
					return w4pl_add_error( 'Theres no table exists to drop.');

				}else{
					w4ld_db_remove();
					$url = add_query_arg( array( 'subpage' => $w4pl_pagenow, 'message' => 'db_dropped' ), $w4pl_admin_url );
					wp_redirect( $url);
					exit;
				}
			break;

			case 'installdb':
				if( w4pl_table_exists()){
					return w4pl_add_error( 'Tables already Installed.');

				}else{
					w4pl_db_install( true );
					$url = add_query_arg( array( 'subpage' => $w4pl_pagenow, 'message' => 'db_installed' ), $w4pl_admin_url );
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
				$url = add_query_arg( array( 'subpage' => $w4pl_pagenow, 'message' => 'db_updated' ), $w4pl_admin_url );
				wp_redirect( $url);
				exit;
			break;

			case 'delete_option':
				delete_option( '_w4pl_temp_option');
				$url = add_query_arg( array( 'subpage' => $w4pl_pagenow, 'message' => 'old_option_cleared' ), $w4pl_admin_url );
				wp_redirect( $url);
				exit;
			break;

			default:
			break;
		endswitch;
	endif;// Get Database

	// Save options
	if( isset( $_POST['w4pl_update_credentials'] )){
		$options = array();
		foreach( array( 'access_cap', 'manage_cap', 'image_source', 'image_meta_key' ) as $v ){
			$options[$v] = isset( $_POST[$v] ) ? $_POST[$v] : '';
		}

		update_option( 'w4pl_options', $options );

		$url = add_query_arg( array( 'subpage' => $w4pl_pagenow, 'message' => 'option_updated' ), $w4pl_admin_url );
		wp_redirect( $url);
		exit;
	}
}
add_action( 'w4pl_admin_action_credentials', 'w4pl_admin_action_credentials');
?>