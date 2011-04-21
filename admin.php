<?php
// Tabset database install
add_action( 'admin_init', 'w4_tabset_db_install');
function w4_tabset_db_install() {
	global $wpdb;
		
	if( w4_tabset_table_exists())
		return true;
		
	$charset_collate = '';
	if ( $wpdb->has_cap( 'collation' ) ) {
		if ( ! empty( $wpdb->charset ) )
			$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
		if ( ! empty( $wpdb->collate ) )
			$charset_collate .= " COLLATE $wpdb->collate";
	}
	
	$sql = "CREATE TABLE IF NOT EXISTS {$this->table} (
		tabset_id bigint(20) unsigned NOT NULL AUTO_INCREMENT PRIMARY KEY,
		tabset_title varchar(200) NOT NULL DEFAULT '',
		tabset_option text NOT NULL
		) {$charset_collate};";
		
	require_once( ABSPATH . 'wp-admin/upgrade-functions.php' ) ;
	dbDelta( $sql );
	
	if( !w4_tabset_table_exists())
		return false;
}
function w4_tabset_table_exists(){
	global $wpdb, $w4_tabset_table;
	return strtolower( $wpdb->get_var( "SHOW TABLES LIKE '$w4_tabset_table'" )) == strtolower( $w4_tabset_table );
}


add_action('w4_tabset_admin_action', 'w4_tabset_admin_save_option');
function w4_tabset_admin_save_option(){
	global $wpdb;
	if( $_GET['page'] != W4CT_SLUG )
		return;

		//Delete a tabset
		if( isset( $_GET['delete'])){
			if(w4_delete_tabset( $_GET['tabset_id']))
				header("Location: edit.php?page=" . W4CT_SLUG . "&message=tabset_deleted");
			
			else
				header("Location: edit.php?page=" . W4CT_SLUG . "&message=tabset_not_deleted");
			die();
		}
		
		//Create new tabset
		if( isset( $_GET['new_tabset'])){
			$tabset_id = w4_save_tabset();
			header("Location: edit.php?page=" . W4CT_SLUG . "&tabset_id=".$tabset_id."&message=tabset_created");
			die();
		}
		
		if( isset( $_GET['set_default'])){
			update_option('w4_content_tabset_default', $_GET['set_default']);
			header("Location: edit.php?page=" . W4CT_SLUG . "&tabset_id=".$_GET['set_default']."&message=default_set");
			die();
		}
		
		//Save an existing tabset
		if( !isset( $_POST['save_w4_content_tabset_options'] ) && !isset( $_POST['tabset_id']))
			return;
		
		$tabset_id = (int) $_POST['tabset_id'];
			
		if(isset($_POST['tabset_title']))
			$tabset_title = trim( stripslashes( $_POST['tabset_title']));

		$default_opt = w4_tabset_default_options();
		foreach( $default_opt as $key => $default ){
			if(!is_array($default))
				$tabset_option[$key] = $_REQUEST[$key];
		}
			
		$data = compact('tabset_id', 'tabset_title', 'tabset_option');
		$tabset_id = w4_save_tabset($data);
		
		header("Location: edit.php?page=" . W4CT_SLUG . "&tabset_id=".$tabset_id."&message=tabset_saved");
		die();
	}

	//Plugin page add
add_action( 'admin_menu', 'w4_tabset_admin_menu');
function w4_tabset_admin_menu(){
	global $w4_content_tabset;
#	$w4_content_tabset->_save_tabset_option();
	do_action('w4_tabset_admin_action');
	
	add_posts_page( W4CT_NAME, W4CT_NAME, 'edit_plugins', W4CT_SLUG, 'w4_tabset_admin_page');
}

//Plugin page
function w4_tabset_admin_page(){ ?>
	<div id="w4pl_admin" class="wrap">
    	<div class="icon32" id="icon-post"><br/></div>
        <h2>W4 content tabset <?php echo "V-".W4CT_VERSION ; ?>
        	<span class="desc"> - <?php _e( 'With w4 post list plugin you can show your selected post list, selected category list or making list with both of them in woedpress site.', 'w4-post-list' ); ?></span>
		</h2>

		<?php w4_tabset_admin_page_menu(); ?>
		<?php w4_tabset_menu(); ?>

		<?php
		$tabset_msgs = array(
						'tabset_saved'		=> __( 'Option saved.', 'w4-post-list'),
						'tabset_not_saved'	=> __( 'Unable to save. There may be a database connection error or this may not have been exists or you do not have capabilities to manage this.'),
						'tabset_created' 	=> __( 'New tabset style Created.', 'w4-post-list'),
						'tabset_deleted'	=> __( 'One tabset style has been deleted.', 'w4-post-list'),
						'tabset_not_deleted'=> __( 'Unable to delete this tabset style now. There may be a database connection error or this may not have been exists or you do not have capabilities to delete this.'),
						'default_set'		=> __( 'Saved as default.')
					);
		if ( isset( $_GET['message'])){
			$msg = $_GET['message'];
			if(in_array( $msg, array_keys( $tabset_msgs)))
				echo '<div id="message" class="updated fade">'.$tabset_msgs[$msg].'</div>';
		}
		if( !$_GET['tabset_id'])
			w4_tabset_admin_welcome_page();
		
        w4_tabset_form();
		?>
	</div>
<?php
}

function w4_tabset_admin_welcome_page(){
	$plugin_feed = fetch_feed( 'http://w4dev.com/w4-plugin/feed/');
	$wp_feed = fetch_feed( 'http://w4dev.com/wp/feed/');
	echo '<div id="w4_tabset_welcome_page">';

	if( !is_wp_error($plugin_feed)){
		echo '<h4>W4 plugins</h4>';
		wp_widget_rss_output( $plugin_feed, array( 'items' => '5'));
		$plugin_feed->__destruct();
		unset($plugin_feed);
	}
	
	if( !is_wp_error($wp_feed)){
		echo '<h4>Wordpress tips</h4>';
		wp_widget_rss_output( $wp_feed, array( 'items' => '10', 'show_date' => true));
		
		$wp_feed->__destruct();
		unset($wp_feed);
	}
	echo '</div>';
}

function w4_tabset_form( $tabset_id = 0){
		$tabset_id = (int) $tabset_id;
		
		if( !$tabset_id & isset( $_GET['tabset_id']))
			$tabset_id = (int) $_GET['tabset_id'];
		
		if( !w4_get_tabset( $tabset_id ))
			return false;
	
		$all_option = w4_get_tabset( $tabset_id );
		#echo '<pre>'; print_r( $all_option); echo '</pre>';
		
		$tabset_option = (array) $all_option['tabset_option'];
		$tabset_title = $all_option['tabset_title'];
		
		$f_action = admin_url( "edit.php?page=" . W4CT_SLUG . "&tabset_id=" . $tabset_id);
		$hidden = '<input type="hidden" value="'. $tabset_id . '" name="tabset_id"/>';

		$form_options = $tabset_option;
		
		extract( $form_options);
	?>
   
	<form action="<?php echo $f_action ; ?>" method="post" id="tabset_form" enctype="multipart/form-data">
		<?php #echo '<pre>'; print_r($all_option); echo '</pre>'; ?>
    	<?php echo $hidden ; ?>
		<input type="hidden" value="save" name="action"/>
<?php if( get_option('w4_content_tabset_default') && $tabset_id == get_option('w4_content_tabset_default')): ?>
	<a class="tabset_button" title="This will be use, if we didnt find any style arrtibute in tabset shortcode.." href="javascript:void(0)">This is default</a>
<?php else: ?>
	<a class="tabset_button" title="If we didnt find any style arrtibute in tabset shortcode, default style will be use.." href="<?php echo admin_url('edit.php?page=w4-content-tabset&tabset_id='.$tabset_id.'&set_default='.$tabset_id); ?>">Set it as default</a>
<?php endif; ?>

<a id="delete_list" rel="<?php echo $tabset_title; ?>" class="tabset_button" href="<?php echo admin_url('edit.php?page=w4-content-tabset&tabset_id='.$tabset_id.'&delete'); ?>">Delete</a>
            <div class="option"><label for="tabset_title"><strong><?php _e('Tabset style/class name:', 'w4-content-tabset'); ?></strong></label>
            <br /><small><?php _e( 'The class name for this tabset style. This class name will be used for calling this style with shortcode.', 'w4-content-tabset'); ?></small>
			<input type="text" value="<?php echo( $tabset_title) ; ?>" name="tabset_title" 
			id="tabset_title" class=""/></div>
            
            <div class="option"><strong><?php _e( 'Tabset effect:', 'w4-content-tabset' ); ?></strong>
            <small><?php _e( 'Kind of tabset you need.', 'w4-content-tabset'); ?></small>
			<br /><label><input type="radio" <?php checked( $tabset_effect, '1' ); ?> name="tabset_effect" value="1"  /> <?php _e( 'Show/Hide', 'w4-content-tabset' ); ?></label>
            <br /><label><input type="radio" <?php checked( $tabset_effect, '2' ); ?> name="tabset_effect" value="2"  /> <?php _e( 'Slide (Up/Down)', 'w4-content-tabset' ); ?>
            </label>
            <br /><label><input type="radio" <?php checked( $tabset_effect, '3' ); ?> name="tabset_effect" value="3"  /> <?php _e( 'Fade', 'w4-content-tabset' ); ?></label>
            </div>
			
			<div class="option"><strong><?php _e( 'Tabset event (how the will open', 'w4-content-tabset' ); ?></strong>
            <br /><label><input type="radio" <?php checked( $tabset_event, 'on_click' ); ?> name="tabset_event" value="on_click" /> <?php _e( 'On click', 'w4-content-tabset'  ); ?></label>
            <br /><label><input type="radio" <?php checked( $tabset_event, 'on_hover' ); ?> name="tabset_event" value="on_hover" /> <?php _e( 'On mousehover', 'w4-content-tabset' ); ?></label>
			</div>
            
            <div class="option"><strong><?php _e( 'Tabset menu item background color', 'w4-content-tabset' ); ?></strong>
            <br /><label><input type="text" class="color {pickerMode:'HVS', hash: true}" value="<?php echo $tabset_menu_bg_color; ?>" name="tabset_menu_bg_color" /></label>
			</div>
            
            <div class="option"><strong><?php _e( 'Tabset menu text color', 'w4-content-tabset' ); ?></strong>
            <br /><label><input type="text" class="color {pickerMode:'HVS', hash: true}" value="<?php echo $tabset_menu_text_color; ?>" name="tabset_menu_text_color" /></label>
			</div>
            
            <div class="option"><strong><?php _e( 'Tabset menu font-size(write in css style. Like: 13px or 13pt.)', 'w4-content-tabset' ); ?></strong>
            <br /><label><input type="text" value="<?php echo $tabset_menu_font_size; ?>" class="" name="tabset_menu_font_size"/></label>
			</div>
            
            <div class="option"><strong><?php _e( 'Tabset menu hover/active item background color', 'w4-content-tabset' ); ?></strong>
            <br /><label><input type="text" class="color {pickerMode:'HVS', hash: true}" value="<?php echo $tabset_menu_bg_color_hover; ?>" name="tabset_menu_bg_color_hover" /></label>
			</div>
            
            <div class="option"><strong><?php _e( 'Tabset menu hover/active text color', 'w4-content-tabset' ); ?></strong>
            <br /><label><input type="text" class="color {pickerMode:'HVS', hash: true}" value="<?php echo $tabset_menu_text_color_hover; ?>" name="tabset_menu_text_color_hover" /></label>
			</div>
            
            <div class="option"><strong><?php _e( 'Tabset content background color', 'w4-content-tabset' ); ?></strong>
            <br /><label><input type="text" value="<?php echo $tabset_content_bg_color; ?>" class="color {pickerMode:'HVS', hash: true}" name="tabset_content_bg_color"/></label>
			</div>

            <div class="option"><strong><?php _e( 'Tabset border bottom color', 'w4-content-tabset' ); ?></strong>
            <br /><label><input type="text" value="<?php echo $tabset_content_border_color; ?>" class="color {pickerMode:'HVS', hash: true}" name="tabset_content_border_color"/></label>
			</div>
			
            <input type="submit" name="save_w4_content_tabset_options" class="save_w4_content_tabset_options" value="Save option" /><br />
	</form>
<?php
}

add_filter( 'admin_footer', 'w4_tabset_admin_js');
function w4_tabset_admin_js(){	?>
	<script type="text/javascript">
        (function($){$(document).ready(function(){
            $("#tabset_help").click(function() {
                $("#contextual-help-wrap").slideToggle({ duration: 'fast' });
                $("#contextual-help-wrap").css({ 'background-color':'#DDDDDD', 'border-style': 'solid', 'border:color':'#E6DB55', 'border-width':'medium'});
                $('#contextual-help-link-wrap').toggle() ;
                return false;
            });
            
			$("#tabset_form .option").bind({
				mouseover: function(){
					$(".option input.save_w4_content_tabset_options").remove();
					$(this).append('<input type="submit" name="save_w4_content_tabset_options" class="save_w4_content_tabset_options" value="Save option" />');
				}
			});
			
			$('a#delete_list').click(function(){
				var name = $(this).attr('rel');
				if( confirm( "Are you sure you want to delete '" + name + "' ?" )){
					return true ;
				}
				return false ;
			});
        })})(jQuery);
        </script>
	<?php
	}

add_filter( 'contextual_help', 'w4_tabset_help');
function w4_tabset_help(){
		$tabset_help = '<h2>' . __( 'W4 content tabset Documentation:') . '</h2>' .
		'<ul>' .
		'<li>' . __('For inserting a tabset, use shortcode "tabset". example:[tabset][/tabset]') . '</li>' .
		'<li>' . __('For inserting a tab in a tabset, use shortcode "tabs" and its attribute "tabname". example:[tabs tabname="Your tab name"]Tab inside content[/tabs]') . '</li>' .
		'<li>' . __('Tabs should be in a Tabset area. So the structure should look like:<br />[tabset]<br />[tabs tabname="Tab1"]Tab1 content[/tabs]<br />[tabs tabname="Tab2"]Tab2 content[/tabs]<br />[/tabset]') . '</li>' .
	
		'<li>' . __('To define style for your tabset, use style parameter on tabset shortcode:<br />[tabset class="my-tabset"]<br />[tabs tabname="Tab1"]Tab1 content[/tabs]<br />[tabs tabname="Tab2"]Tab2 content[/tabs]<br />[/tabset]') . '</li><br />' .

		'<li>' . __('If you need to use multiple tabset with same tabname on a single page or post add a "id" attribute to tabset shortcode for making them separate. Example:<br />[tabset id="1"]<br />[tabs tabname="Tab1"]Tab1 content[/tabs]<br />[tabs tabname="Tab2"]Tab2 content[/tabs]<br />[/tabset]<br />
		<br />[tabset id="2"]<br />[tabs tabname="Tab1"]Tab1 content[/tabs]<br />[tabs tabname="Tab2"]Tab2 content[/tabs]<br />[/tabset]') . '</li><br />' .
	
		'<li>' . __('W4 content tabset support another shortcode "custom". For showing your post/page custom field value you can use shortcode "custom". example: [custom key="Your-custom-key-name"] ') . '</li>' .
		'<li>' . __('Shortcode "custom" receive one parameter "key". "key" is your custom field id/key name for current post/page you are creating or editing.') . '</li>' .
		
		'<li>' . __('For farther documentation contact <a href="mailto:sajib1223@gmail.com" rel="tabset_author_mail">Shazzad</a> or visit plugin site <a href="http://w4dev.com/w4-plugin/post-page-custom-tabset-shortcode" class="us" rel="developer" title="Web and wordpress development...">W4 development</a>..') . '</li>' .
		'</ul>' ;
		return $tabset_help ;
	}

function w4_tabset_menu( $current = 0){
	global $wpdb, $w4_tabset_table;
		
	$query = $wpdb->prepare( "SELECT * FROM  $w4_tabset_table ORDER BY tabset_id ASC" );
		
	if ( ! $tabsets = $wpdb->get_results( $query ))
		$tabsets = array();

		$current = (int) $current;
		
	if( !$current & isset( $_GET['tabset_id']))
		$current = (int) $_GET['tabset_id'];

		
	$tabset_admin_menu = '<ul class="post_list_menu tabset_admin_menu">';
	foreach($tabsets as $tabset){
		if( $tabset->tabset_id){
			$class = ($current == $tabset->tabset_id) ? 'current' : '';
			$title = empty($tabset->tabset_title) ? 'tabset-' . $tabset->tabset_id : $tabset->tabset_title;
			$url = admin_url( 'edit.php?page=' . W4CT_SLUG . '&tabset_id=' . $tabset->tabset_id );
				
			$tabset_admin_menu .= '<li><a href="'. $url . '" class="'.$class.'">'. $title .'</a></li>';
		}
	}
	
	$tabset_admin_menu .= '<li><a href="'. admin_url( 'edit.php?page=' . W4CT_SLUG . '&new_tabset=true' ) . '" class="create">+ Create new</a></li>';
	$tabset_admin_menu .= "</ul>";
		
	echo $tabset_admin_menu;
}

//=================================Admin
function w4_tabset_admin_page_menu(){
?>
<div class="menu">
    <a href="<?php echo admin_url('edit.php?page='.W4CT_SLUG); ?>"><?php _e( 'Home', 'w4-post-list' ); ?></a>
    <a href="http://w4dev.com" target="_blank" class="us" rel="developer" title="<?php 
    _e( 'Web and wordpress development...', 'w4-post-list' ); ?>">By W4 Development</a>
    <a href="http://w4dev.com/w4-plugin/post-page-custom-tabset-shortcode/?ref=admin_page_menu" target="_blank" title="<?php _e( 'Visit Plugin Page', 'w4-post-list' ); ?>"> <?php 
    _e( 'Visit Plugin Page', 'w4-post-list' ); ?></a>
    <a href="mailto:sajib1223@gmail.com" rel="tabset_author_mail"> <?php _e( 'Mailto:Author', 'w4-post-list' ); ?></a>
    <a href="http://wordpress.org/extend/plugins/postpage-content-anchor-tabset/" target="_blank" rel="wordpress"> <?php _e( 'Vote on WordPress', 'w4-post-list' ); ?></a>
    <a id="tabset_help" style="background-color: #FFF000;color: #000000;" class="" href="javascript:void(0);" title="<?php _e( 'Tabset documentation'); ?>"> Need help ?</a>
</div>
<?php
}
?>