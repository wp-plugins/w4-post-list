<?php
function w4_tabset_get_option($key = null){
	$setting = 'w4_tabset_options';
	$options = get_option($setting);
	if(!$options)
		return false;

	if(!$key)
		return $options ;

	if($options[$key] == '' || !$options[$key] || empty($options[$key]))
		return false ;

	return $options[$key];
}
//Load our script and css file
add_action( 'init', 'load_tabset_scripts' ) ;
function load_tabset_scripts(){
	//script
		wp_enqueue_script( 'tabset_js', TABSET_URL . 'tabset.js', array( 'jquery' , 'jquery-ui-core', 'jquery-ui-tabs' ), TABSET_VERSION, true ) ;
		wp_enqueue_style( 'tabset_rewrite', TABSET_URL . 'rewrite.css', '', TABSET_VERSION ) ;
		wp_enqueue_style( 'tabset_style', TABSET_URL . 'tabset.css', '', TABSET_VERSION ) ;
		//for admin
		wp_enqueue_script( 'color_picker', TABSET_URL . 'colorpicker/jscolor.js', array( 'jquery' , 'jquery-ui-core' ),TABSET_VERSION,true ) ;
		
		if(is_admin() && $_GET['page'] == 'post-tabs' )
			add_filter( 'contextual_help', 'tabset_help' ) ;
}

function tabset_help(){
	$tabset_help = '<p>' . __( 'Post/Page tabset Documentation:') . '</p>' .
	'<ul>' .
	'<li>' . __('For inserting a tabset, use shortcode "tabset". example:[tabset][/tabset]') . '</li>' .
	'<li>' . __('For inserting a tab in a tabset, use shortcode "tabs" and its attribute "tabname". example:[tabs tabname="Your tab name"]Tab inside content[/tabs]') . '</li>' .
	'<li>' . __('Tabs should be in a Tabset area. So the structure should look like:<br />[tabset]<br />[tabs tabname="Tab1"]Tab1 content[/tabs]<br />[tabs tabname="Tab2"]Tab2 content[/tabs]<br />[/tabset]') . '</li>' .
	'<li>' . __('Please make sure you have written and checked the shortcodes appropriately.') . '</li><br />' .

	'<li>' . __('Post/Page tabset support another shortcode "custom". For showing your post/page custom field value you can use shortcode "custom". example: [custom key="Your-custom-key-name"] ') . '</li>' .
	'<li>' . __('Shortcode "custom" receive one parameter "key". "key" is your custom field id/key name for current post/page you are creating or editing.') . '</li>' .
	
	'<li>' . __('For farther documentation contact <a href="mailto:sajib1223@gmail.com" rel="tabset_author_mail">Shazzad</a> or visit plugin site <a href="http://w4dev.com/w4-plugin/post-page-custom-tabset-shortcode" class="us" rel="developer" title="Web and wordpress development...">W4 development</a>..') . '</li>' .
	'</ul>' ;
	return $tabset_help ;
}

//Add amin page
add_action( 'admin_menu', 'post_tab_admin_menu' ) ;
function post_tab_admin_menu(){
	$tabset_default_options = array(
						'tabset_menu_bg_color' 				=> '#EFEFEF',
						'tabset_menu_text_color'			=> '#5E5E5E',
						'tabset_menu_font_size'				=> '13px',
						'tabset_menu_bg_color_hover'		=> '#5E5E5E',
						'tabset_menu_text_color_hover' 		=> '#FFFFFF',
						'tabset_content_bg_color'			=> 'none',
						'tabset_content_border_color'		=> '#5E5E5E',
						'tabset_effect'						=> '1'
						) ;
	
	
	if( !get_option( 'w4_tabset_options' )){
		register_setting( 'w4_tabset_options', 'w4_tabset_options' );
		//removing old databse format
		foreach( $tabset_default_options as $key => $default ){
			if( get_option( $key )){
				$tabset_default_options[$key] = get_option( $key );
				delete_option( $key ) ;
			}
		}
		//Adding default
		add_option( 'w4_tabset_options', $tabset_default_options );
	}
	//Remover completely
	delete_option('tabset_style');

	if( isset( $_POST['save-tabset-options'] )){
		update_option( 'w4_tabset_options', $tabset_default_options );
		foreach( $tabset_default_options as $key => $default ){
			$tabset_default_options[$key] = $_REQUEST[$key];
		}
		update_option( 'w4_tabset_options', $tabset_default_options );
		tabset_stylesheet_update() ;
		header("Location: edit.php?page=post-tabs&saved=true") ;
		die();
	}

	if ( isset( $_POST['reset-tabset-options'] )) {
		update_option( 'w4_tabset_options', $tabset_default_options );
		tabset_stylesheet_update() ;
		header("Location: edit.php?page=post-tabs&reset=true") ;
		die();
	}

	add_posts_page( "Post/Page Tabs", "Post/Page tabset", 'publish_posts', 'post-tabs', 'post_tab_admin_page' ) ;
}

function post_tab_admin_page(){
	if ( $_REQUEST[ 'saved' ] ) echo '<div id="message" class="updated fade"><p><strong>Settings saved.</strong></p></div>';
	if ( $_REQUEST[ 'reset' ] ) echo '<div id="message" class="updated fade"><p><strong>Settings reset.</strong></p></div>';
?>
	<div id="tabset_wrapper" class="wrap">
    	<div class="icon32" id="icon-post"><br/></div>
        <h2>Post/page Tabset <?php echo "V-".TABSET_VERSION ; ?> options
        <p class="info">Developed by &raquo;<a href="http://w4dev.com" class="us" rel="developer" title="Web and wordpress development...">W4 development</a> &raquo;<a id="tabset_help" href="javascript:void(0);" title="Tabset documentation"> Need help ?</a> &raquo;<a href="http://w4dev.com/w4-plugin/post-page-custom-tabset-shortcode" title="Visit Plugin Site"> Visit Plugin Site</a> &raquo;<a href="mailto:sajib1223@gmail.com" rel="tabset_author_mail"> Mailto:Contact</a></p></h2>
        <p><span class="notice">Note: The Anchor tabser has been removed from this version also the style. Now it is taking a new option, "effect".</span></p>
        <form action="<?php echo admin_url("edit.php?page=post-tabs") ; ?>" method="post" id="tabset-form" enctype="multipart/form-data">
		<input type="hidden" value="save" name="action"/>
	<table class="form-table">
        <tbody>
			<tr valign="top">
                <th scope="row">Tabset Effects</th>
                <td>
                <label><input type="radio" name="tabset_effect" <?php checked( w4_tabset_get_option( 'tabset_effect'),1 ) ; ?> value="1" id="tabset_effect1" />Show/Hide</label>
                <br />
                <label><input type="radio" name="tabset_effect" <?php checked( w4_tabset_get_option( 'tabset_effect'),2 ) ; ?> value="2" id="tabset_effect2" />Slide (Up/Down)</label>
                <br />
                <label><input type="radio" name="tabset_effect" <?php checked( w4_tabset_get_option( 'tabset_effect'),3 ) ; ?> value="3" id="tabset_effect3" />Fade</label>
				</td>
                <td>&nbsp;</td>
			</tr>
            
            <tr valign="top">
                <th scope="row">Tabset menu background color</th>
                <td><input type="text" class="color {pickerMode:'HVS', hash: true}" value="<?php echo  w4_tabset_get_option( 'tabset_menu_bg_color' ) ; ?>" id="tabset_menu_bg_color" name="tabset_menu_bg_color"/></td>
                <td>&nbsp;</td>
			</tr>

        	<tr valign="top">
				<th scope="row">Tabset menu text color</th>
                <td><input type="text" class="color {pickerMode:'HVS', hash: true}" value="<?php echo w4_tabset_get_option( 'tabset_menu_text_color' ) ; ?>" id="tabset_menu_text_color" name="tabset_menu_text_color"/></td>
				<td>&nbsp;</td>
			</tr>
            
			<tr valign="top">
                <th scope="row">Tabset menu font-size(write in css style. Like: 13px or 13pt.)</th>
                <td><input type="text" value="<?php echo w4_tabset_get_option( 'tabset_menu_font_size' ) ; ?>" id="tabset_menu_font_size" name="tabset_menu_font_size"/></td>
                <td>&nbsp;</td>
			</tr>
            
            <tr valign="top">
                <th scope="row">Tabset menu hover/active background color</th>
                <td><input type="text" class="color {pickerMode:'HVS', hash: true}" value="<?php echo w4_tabset_get_option( 'tabset_menu_bg_color_hover' ) ; ?>" id="tabset_menu_bg_color_hover" name="tabset_menu_bg_color_hover"/></td>
                <td>&nbsp;</td>
			</tr>
            
            <tr valign="top">
                <th scope="row">Tabset menu hover/active text color</th>
                <td><input type="text" class="color {pickerMode:'HVS', hash: true}" value="<?php echo w4_tabset_get_option( 'tabset_menu_text_color_hover' ) ; ?>" id="tabset_menu_text_color_hover" name="tabset_menu_text_color_hover"/></td>
                <td>&nbsp;</td>
			</tr>
            
            <tr valign="top">
                <th scope="row">Tabset content background color</th>
                <td><input type="text" class="color {pickerMode:'HVS', hash: true}" value="<?php echo w4_tabset_get_option( 'tabset_content_bg_color' ) ; ?>" id="tabset_content_bg_color" name="tabset_content_bg_color"/></td>
                <td>&nbsp;</td>
			</tr>
            
            <tr valign="top">
                <th scope="row">Tabset active content border color</th>
                <td><input type="text" class="color {pickerMode:'HVS', hash: true}" value="<?php echo w4_tabset_get_option( 'tabset_content_border_color' ) ; ?>" id="tabset_content_border_color" name="tabset_content_border_color"/></td>
                <td>&nbsp;</td>
			</tr>
            
		</tbody>
	</table>
	<p class="submit">
    <input type="submit" id="save-tabset-options" name="save-tabset-options" class="button-primary" value="Save Tabset option" />
	<input type="submit" id="reset-tabset-options" style="background: #FF0000;" name="reset-tabset-options" class="button-primary" value="Reset Tabset option" />
    </p>
		</form>
	</div>
<?php
}


function tabset_stylesheet_update(){
	extract(w4_tabset_get_option());

$style = "#tab_area{margin:10px 0px;}
#tab_area .tab_content{margin:10px;}
#tab_content_wrapper{position:relative;overflow:auto;}
#tab_area div.tab_container{margin:10px 0px;border-bottom:1px solid {$tabset_content_border_color};background-color:{$tabset_content_bg_color};}
#tab_area div.ui-tabs-hide{display:none;}
#tab_area ul.tab_links{border-bottom:3px solid {$tabset_menu_bg_color_hover};padding:0;margin:0;}
#tab_area ul.tab_links li{display:inline-block;position:relative;list-style-type:none;padding:0;margin:0;}
#tab_area ul.tab_links li a{color:{$tabset_menu_text_color};text-decoration:none;font-family:Geneva, Arial, Helvetica, sans-serif;font-size:{$tabset_menu_font_size};line-height:normal;font-weight:bold;padding:7px 15px 5px 15px;display:inline-block;position:relative;background-color:{$tabset_menu_bg_color};-moz-border-radius-topleft:5px;-moz-border-radius-topright:5px;}
#tab_area ul.tab_links li a:hover, #tab_area ul.tab_links li a.active, #tab_area ul.tab_links li.ui-tabs-selected a{background-color:{$tabset_menu_bg_color_hover};color:{$tabset_menu_text_color_hover};}" ;


	$fp = fopen( TABSET_DIR . '/rewrite.css' ,"w" ) ;
	fwrite( $fp, $style ) ;
	fclose( $fp ) ;
	$_SESSION['counter'] = 1 ;
}

//Setup themes primary widget=========================
add_action( 'widgets_init', 'tabset_widgets_init' );
function tabset_widgets_init() {
	register_sidebar( array(
		'name' => 'Tabset widget one',
		'id' => 'tabset-widget-one',
		'description' => 'Tabset widget one',
		'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>'
	)) ;
	register_sidebar( array(
		'name' => 'Tabset widget two',
		'id' => 'tabset-widget-two',
		'description' => 'Tabset widget two',
		'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
		'after_widget' => '</li>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>'
	)) ;
}

//Define widget======================
class TABSET_Widget extends WP_Widget {

	function TABSET_Widget() {
		$widget_ops = array(
					'classname' => 'w4_tabset',
					'description' => __( "W4 tabset")
				);
		$control_ops = array('width' => 200, 'height' => 400);
		$this->WP_Widget('w4_tabset', __('W4 tabset'), $widget_ops,$control_ops );
	}

	function widget($args, $instance){
		extract($args);
		echo $before_widget;
		$this->_widget($instance);
        echo $after_widget;
	}
	
	function _widget($instance){
		echo "<div id=\"w4_tabset_widget\">";
			echo "<ul>";
			echo "<li><a href=\"#w4_tabset_widget_container_1\">One</a></li><li><a href=\"#w4_tabset_widget_container_2\">Two</a></li>";
			echo "</ul>";
			echo '<div id="w4_tabset_widget_container_1" class="w4_tabset_widget_container">';
				dynamic_sidebar( 'tabset-widget-one' ) ;
			echo "</div>";
			echo '<div id="w4_tabset_widget_container_2" class="w4_tabset_widget_container">';
				dynamic_sidebar( 'tabset-widget-two' ) ;
			echo "</div>";

		echo "</div>";
	}

	function update( $new_instance, $old_instance ) {
	}


	function form( $instance ){
	}
	
}
//load Widget==============================
add_action('widgets_init', 'TABSET_Widget_Init');
function TABSET_Widget_Init() {
  register_widget('TABSET_Widget');
}
?>