<?php
//Load our script and css file
add_action( 'init', 'load_tabset_scripts' ) ;
function load_tabset_scripts(){
	if( !is_admin()){
		wp_enqueue_script( 'tabset_js', TABSET_URL . '/tabset.js', array( 'jquery' , 'jquery-ui-core', 'jquery-ui-tabs' ),'',false ) ;
		wp_enqueue_style( 'tabset_css', TABSET_URL . '/tabset.css' ) ;

	}else{
		wp_enqueue_script( 'tabset_admin_js', TABSET_URL . '/tabset_admin.js', array( 'jquery' , 'jquery-ui-core' ),'',false ) ;
		wp_enqueue_script( 'color_picker', TABSET_URL . '/colorpicker/jscolor.js' ) ;
		
		if( $_GET['page'] == 'post-tabs' )
			add_filter( 'contextual_help', 'tabset_help' ) ;
	}
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
	$tabset_options = array(
						'tabset_menu_bg_color' 				=> '#efefef',
						'tabset_menu_text_color'			=> '#5e5e5e',
						'tabset_menu_hover_bg_color'		=> '#5e5e5e',
						'tabset_menu_text_hover_color' 		=> '#FFFFFF',
						'tabset_content_bg_color'			=> 'none',
						'tabset_active_content_bg_color'	=> '#E5EECC',
						'tabset_active_content_border_color'=> '#00FF00',
						'tabset_style'						=> '1'
						) ;

	foreach( $tabset_options as $key => $default ){
		if( false == get_option( $key )){
			update_option( $key, $default ) ;
		}
	}
	
	if( isset( $_POST['save-tabset-options'] )){
		foreach( $tabset_options as $key => $default ){
				update_option( $key, $_REQUEST[$key]) ;
		}
		tabset_stylesheet_update() ;
		header("Location: edit.php?page=post-tabs&saved=true") ;
		die;
	}

	if ( isset( $_POST['reset-tabset-options'] )) {
		foreach( $tabset_options as $key => $default ){
			delete_option( $key );
		}
		tabset_stylesheet_update() ;
		header("Location: edit.php?page=post-tabs&reset=true") ;
		die;
	}

	add_posts_page( "Post/Page Tabs", "Post/Page tabset", 'publish_posts', 'post-tabs', 'post_tab_admin_page' ) ;
}

function post_tab_admin_page(){
	if ( $_REQUEST[ 'saved' ] ) echo '<div id="message" class="updated fade"><p><strong>Settings saved.</strong></p></div>';
	if ( $_REQUEST[ 'reset' ] ) echo '<div id="message" class="updated fade"><p><strong>Settings reset.</strong></p></div>';
?>
	<div id="tabset_wrapper" class="wrap">
    	<div class="icon32" id="icon-post"><br/></div>
        <h2>Post/page Tabset <?php echo "V-".TABSET_VERSION ; ?> options<span style="font-size:12px; padding-left: 20px ;"><a href="http://w4dev.com" class="us" rel="developer" title="Web and wordpress development...">Developed by &raquo; W4 development</a> <a id="tabset_help" href="javascript:joiv(0);" title="Tabset documentation">&raquo; Need help ?</a> <a href="http://w4dev.com/w4-plugin/post-page-custom-tabset-shortcode" title="Visit Plugin Site">&raquo; Visit Plugin Site</a> <a href="mailto:sajib1223@gmail.com" rel="tabset_author_mail">&raquo; Mailto:Contact</a></span></h2>
        <form action="<?php echo admin_url("edit.php?page=post-tabs") ; ?>" method="post" id="tabset-form" enctype="multipart/form-data">
		<input type="hidden" value="save" name="action"/>
	<table class="form-table">
        <tbody>
			<tr valign="top">
                <th scope="row">Tabset style</th>
                <td>
                <label><input type="radio" name="tabset_style" <?php if( '1' == get_option( 'tabset_style')) echo 'checked="checked"' ; ?> value="1" id="tabset_style1" />Normal anchor Tabset</label>
                <br />
                <label><input type="radio" name="tabset_style" <?php if( '2' == get_option( 'tabset_style')) echo 'checked="checked"' ; ?> value="2" id="tabset_style2" />Hide inactive tabs (ui-style)</label>
				</td>
                <td>&nbsp;</td>
			</tr>
            
            <tr valign="top">
                <th scope="row">Tabset menu background color</th>
                <td><input type="text" class="color {pickerMode:'HVS', hash: true}" value="<?php echo  get_option( 'tabset_menu_bg_color', '#efefef' ) ; ?>" id="tabset_menu_bg_color" name="tabset_menu_bg_color"/></td>
                <td>&nbsp;</td>
			</tr>

        	<tr valign="top">
				<th scope="row">Tabset menu text color</th>
                <td><input type="text" class="color {pickerMode:'HVS', hash: true}" value="<?php echo get_option( 'tabset_menu_text_color', '#5e5e5e' ) ; ?>" id="tabset_menu_text_color" name="tabset_menu_text_color"/></td>
				<td>&nbsp;</td>
			</tr>
            
			<tr valign="top">
                <th scope="row">Tabset menu hover/active background color</th>
                <td><input type="text" class="color {pickerMode:'HVS', hash: true}" value="<?php echo get_option( 'tabset_menu_hover_bg_color', '#5e5e5e' ) ; ?>" id="tabset_menu_hover_bg_color" name="tabset_menu_hover_bg_color"/></td>
                <td>&nbsp;</td>
			</tr>
            
            <tr valign="top">
                <th scope="row">Tabset menu hover/active text color</th>
                <td><input type="text" class="color {pickerMode:'HVS', hash: true}" value="<?php echo get_option( 'tabset_menu_text_hover_color', '#FFFFFF' ) ; ?>" id="tabset_menu_text_hover_color" name="tabset_menu_text_hover_color"/></td>
                <td>&nbsp;</td>
			</tr>
            
            <tr valign="top">
                <th scope="row">Tabset content backgroung color</th>
                <td><input type="text" class="color {pickerMode:'HVS', hash: true}" value="<?php echo get_option( 'tabset_content_bg_color', 'none' ) ; ?>" id="tabset_content_bg_color" name="tabset_content_bg_color"/></td>
                <td>&nbsp;</td>
			</tr>
            
            <tr valign="top">
                <th scope="row">Tabset active content background color</th>
                <td><input type="text" class="color {pickerMode:'HVS', hash: true}" value="<?php echo get_option( 'tabset_active_content_bg_color', '#E5EECC' ) ; ?>" id="tabset_active_content_bg_color" name="tabset_active_content_bg_color"/></td>
                <td>&nbsp;</td>
			</tr>
            
            <tr valign="top">
                <th scope="row">Tabset active content border color</th>
                <td><input type="text" class="color {pickerMode:'HVS', hash: true}" value="<?php echo get_option( 'tabset_active_content_border_color', '#00FF00' ) ; ?>" id="tabset_active_content_border_color" name="tabset_active_content_border_color"/></td>
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
	$style = "#tab_area {
		margin: 10px 0px ;
		}
#tab_area .tab_links {
	background-color: " .get_option('tabset_menu_bg_color') . " ;
	border: 1px solid #CCCCCC ;
	margin: 0px 0px 0px 0px ;
	padding: 0px 0px 0px 0px ;
}
.tab_links li{
	display: inline ;
	position: relative ;
	list-style-type: none ;
	}
.tab_links li a {
	color: ".get_option( 'tabset_menu_text_color' ). " ;
	text-decoration: none ;
	font-family: Geneva, Arial, Helvetica, sans-serif ;
	font-size: 13px ;
	font-weight: bold ;
	padding: 5px 15px ;
	display: inline-block ;
	position: relative ;
	}
.tab_links li a:hover, .tab_links li a.active, .tab_links li.ui-tabs-selected a {
	background-color: ".get_option( 'tabset_menu_hover_bg_color' )." ;
	color: ".get_option( 'tabset_menu_text_hover_color' ). " ;
	}
.tab_links li.ui-tabs-selected a span{
	width: 100% ;
	height: 2px;
	background-color: ".get_option( 'tabset_menu_hover_bg_color' )." ;
	position: absolute ;
	left: 0 ;
	bottom: -2px ;
	z-index: 10 ;
}
.tab_links a.active span, .tabset_style_1 .tab_links a:hover span {
	background: url(tabset.gif) no-repeat bottom ;
	width: 8px ;
	height: 5px ;
	position: absolute ;
	left: 40% ;
	bottom: -4px ;
	}
.tabset_style_1 div.tab_container {
	margin: 10px 0px ;
	background-color: ".get_option( 'tabset_content_bg_color')." ;
	}
.tabset_style_1 div.active {
	border-style: solid ;
	border-width: 1px;
	border-color: ". get_option( 'tabset_active_content_border_color') ." ;
	background-color: " . get_option( 'tabset_active_content_bg_color' ) . " ;
	}
.tabset_style_2 div.ui-tabs-hide {
	display: none ;
	}
.tabset_style_2 div.tab_container {
	margin: 0px;
	border-style: solid ;
	border-width: 1px;
	border-color: ". get_option( 'tabset_active_content_border_color') ." ;
	background-color: " . get_option( 'tabset_active_content_bg_color' ) . " ;
	}

.tabset_style_2 #tab_content_wrapper {
	position: relative ;
	overflow: auto ;
	}


#tab_area .tab_content {
	margin: 10px ;
	}" ;


	$fp = fopen( TABSET_DIR . '/tabset.css' ,"w" ) ;
	fwrite( $fp, $style ) ;
	fclose( $fp ) ;
	$_SESSION['counter'] = 1 ;
}


?>
