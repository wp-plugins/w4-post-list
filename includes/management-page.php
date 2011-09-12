<?php
// Plugin option page data handler
add_action( 'w4pl_admin_action', 'w4pl_admin_option_page_action');
function w4pl_admin_option_page_action(){
	global $wpdb, $plugin_page, $w4pl_caps, $w4_request, $w4pl_error;

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
?>