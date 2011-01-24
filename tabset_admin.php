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
	if(is_admin() && $_GET['page'] == 'w4-content-tabset' ){
		wp_enqueue_style( 'tabset_style', TABSET_URL . 'tabset.css', '', TABSET_VERSION );
		wp_enqueue_script( 'color_picker', TABSET_URL . 'colorpicker/jscolor.js', array( 'jquery','jquery-ui-core' ),TABSET_VERSION,true );
		add_filter( 'contextual_help','tabset_help');
		add_filter( 'admin_footer','tabset_admin_js');

	}

	wp_enqueue_script( 'tabset_js', TABSET_URL . 'tabset.js', array( 'jquery' , 'jquery-ui-core', 'jquery-ui-tabs' ), TABSET_VERSION, true );
	wp_enqueue_style( 'tabset_rewrite', TABSET_URL . 'rewrite.css', '', TABSET_VERSION );
}

function tabset_admin_js(){
?>
<script type="text/javascript">
(function($){
$(document).ready(function(){
	$("#tabset_help").click(function() {
		$("#contextual-help-wrap").slideToggle({ duration: 'fast' });
		$("#contextual-help-wrap").css({ 'background-color':'#FFFFE0', 'border-style': 'solid', 'border:color':'#E6DB55', 'border-width':'medium'});
		$('#contextual-help-link-wrap').toggle() ;
		return false;
	});
	
	$('#tabset-form input, #tabset-form textarea, #tabset-form select').keyup(function() {
		$('.tabset-save-button').val('Save now...');
	});
	
	$('#reset-tabset-options').click(function(){
		if( confirm( "Are you sure you want to reset \"W4 content tabset\" option to default ??" )){
			return true ;
		}
		return false ;
	});
});
})(jQuery);
</script>
<?php
}

function tabset_help(){
	$tabset_help = '<h2>' . __( 'W4 content tabset Documentation:') . '</h2>' .
	'<ul>' .
	'<li>' . __('For inserting a tabset, use shortcode "tabset". example:[tabset][/tabset]') . '</li>' .
	'<li>' . __('For inserting a tab in a tabset, use shortcode "tabs" and its attribute "tabname". example:[tabs tabname="Your tab name"]Tab inside content[/tabs]') . '</li>' .
	'<li>' . __('Tabs should be in a Tabset area. So the structure should look like:<br />[tabset]<br />[tabs tabname="Tab1"]Tab1 content[/tabs]<br />[tabs tabname="Tab2"]Tab2 content[/tabs]<br />[/tabset]') . '</li>' .

	'<li>' . __('If you need to use multiple tabset with same tabname on a single page or post add a "id" attribute to tabset shortcode for making them separate. Example:<br />[tabset id="1"]<br />[tabs tabname="Tab1"]Tab1 content[/tabs]<br />[tabs tabname="Tab2"]Tab2 content[/tabs]<br />[/tabset]<br />
	<br />[tabset id="2"]<br />[tabs tabname="Tab1"]Tab1 content[/tabs]<br />[tabs tabname="Tab2"]Tab2 content[/tabs]<br />[/tabset]') . '</li><br />' .

	'<li>' . __('W4 content tabset support another shortcode "custom". For showing your post/page custom field value you can use shortcode "custom". example: [custom key="Your-custom-key-name"] ') . '</li>' .
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
						'tabset_effect'						=> '1',
						'tabset_event'						=> 'on_click'
						);
	
	
	if( !get_option( 'w4_tabset_options' )){
		register_setting( 'w4_tabset_options', 'w4_tabset_options' );
		//Removing old databse format
		foreach( $tabset_default_options as $key => $default ){
			if( get_option($key)){
				$tabset_default_options[$key] = get_option( $key );
				delete_option( $key);
			}
		}
		//Remover old style
		if(get_option('tabset_style'))
			delete_option('tabset_style');
		//Adding default
		add_option( 'w4_tabset_options', $tabset_default_options );
	}

	if( isset( $_POST['save-tabset-options'] )){
		//update_option( 'w4_tabset_options', $tabset_default_options );
		foreach( $tabset_default_options as $key => $default ){
			$tabset_default_options[$key] = $_REQUEST[$key];
		}
		update_option( 'w4_tabset_options', $tabset_default_options );
		tabset_stylesheet_update() ;
		header("Location: edit.php?page=w4-content-tabset&saved=true") ;
		die();
	}

	if ( isset( $_POST['reset-tabset-options'] )) {
		update_option( 'w4_tabset_options', $tabset_default_options );
		tabset_stylesheet_update() ;
		header("Location: edit.php?page=w4-content-tabset&reset=true") ;
		die();
	}

	add_posts_page( "W4 content tabset", "W4 content tabset", 'publish_posts', 'w4-content-tabset', 'w4_content_tabset_admin_page' ) ;
}

function w4_content_tabset_admin_page(){
?>
	<div id="tabset_wrapper" class="wrap">
    	<div class="icon32" id="icon-post"><br/></div>
        <h2>W4 content tabset <?php echo "V-".TABSET_VERSION ; ?> <a id="tabset_help" class="tabset_links" href="javascript:void(0);" title="Tabset documentation"> Need help ?</a></h2>
        <h3>Example tabset Preview below:</h3>
        <?php tabset_preview(); ?>
<?php
	if ( $_REQUEST[ 'saved' ] ) echo '<div id="message" class="updated fade"><p><strong>Settings saved.</strong></p></div>';
	if ( $_REQUEST[ 'reset' ] ) echo '<div id="message" class="updated fade"><p><strong>Settings reset.</strong></p></div>';        
?>
	<form action="<?php echo admin_url("edit.php?page=w4-content-tabset") ; ?>" method="post" id="tabset-form" enctype="multipart/form-data">
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
                <td><input type="submit" name="save-tabset-options" class="button-primary tabset-save-button" value="Save Tabset option" /></td>
			</tr>
            
            <tr valign="top">
                <th scope="row">Tabset event (how the will open)</th>
                <td>
                <label><input type="radio" name="tabset_event" <?php checked( w4_tabset_get_option( 'tabset_event'),'on_click' ) ; ?> value="on_click" id="tabset_event1" />Onclick</label>
                <br />
                <label><input type="radio" name="tabset_event" <?php checked( w4_tabset_get_option( 'tabset_event'),'on_hover' ) ; ?> value="on_hover" id="tabset_event2" />On mousehover</label>
				</td>
                <td>&nbsp;</td>
			</tr>
            <tr valign="top">
                <th scope="row">Tabset menu item background color</th>
                <td><input type="text" class="color {pickerMode:'HVS', hash: true}" value="<?php echo  w4_tabset_get_option( 'tabset_menu_bg_color' ) ; ?>" id="tabset_menu_bg_color" name="tabset_menu_bg_color"/></td>
                <td><input type="submit" name="save-tabset-options" class="button-primary tabset-save-button" value="Save Tabset option" /></td>
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
                <th scope="row">Tabset menu hover/active item background color</th>
                <td><input type="text" class="color {pickerMode:'HVS', hash: true}" value="<?php echo w4_tabset_get_option( 'tabset_menu_bg_color_hover' ) ; ?>" id="tabset_menu_bg_color_hover" name="tabset_menu_bg_color_hover"/></td>
                <td><input type="submit" name="save-tabset-options" class="button-primary tabset-save-button" value="Save Tabset option" /></td>
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
                <th scope="row">Tabset border bottom color</th>
                <td><input type="text" class="color {pickerMode:'HVS', hash: true}" value="<?php echo w4_tabset_get_option( 'tabset_content_border_color' ) ; ?>" id="tabset_content_border_color" name="tabset_content_border_color"/></td>
                <td><input type="submit" name="save-tabset-options" class="button-primary tabset-save-button" value="Save Tabset option" /><br />
<input type="submit" id="reset-tabset-options" style="background: #FF0000;" name="reset-tabset-options" class="button-primary" value="Reset Tabset option" /></td>
			</tr>
            
		</tbody>
	</table>
	</form>
	</div>
<?php
}

function tabset_preview(){
	echo tabset_replace_callback('
	[tabset]
		[tabs tabname="Plugin Info"]
			<p class="info">Developed by 
			<a href="http://w4dev.com" class="tabset_links" rel="developer" title="Wordpress themes, plugins and framework development...">W4 development</a>  
			<a href="http://w4dev.com/w4-plugin/post-page-custom-tabset-shortcode" class="tabset_links" title="Visit Plugin Page">Visit plugin page</a>
			<a href="http://www.facebook.com/w4dev" class="tabset_links" title="Find us on facebook">Find us on facebook</a>
			<a href="mailto:sajib1223@gmail.com" class="tabset_links" title="Send Email to plugin author" rel="tabset_author_mail">Mailto @uthor</a></p>
		[/tabs]
		[tabs tabname="Whats new in version 1.3.8"]
			<p class="info">We have added this preview box on the top for a short preview of how your tabset will be shown on the front page.<br /><br />
			Now you can use multi tabset on a single post or page. For this you have to add an attriute "id" to tabset shortcode.
			To view how, click on the "need help" button above.<br /><br />
			From now on you don\'t have t update your tabset option whenever you make a plugin update. We have added a plugin activation hook which will rewrite your stylesheet automatically after sucessfull update with your saved tabset options.
			</p>
		[/tabs]
		[tabs tabname="Special notice"]
			<p class="info"><span class="notice">The Anchor tabser has been removed completely after the version 1.3.3.<br />If you need to use anchor tabset, you could use our old version 1.3.3</span></p>
		[/tabs]
		[tabs tabname="We need your feedback"]
			<p class="info">Please visit <a href="http://w4dev.com/w4-plugin/post-page-custom-tabset-shortcode" class="tabset_links" title="Visit Plugin Page">Plugin page</a> or <a href="http://www.facebook.com/w4dev" class="tabset_links" title="Find us on facebook">Our facebook page</a> and through us a suggestion or any problem you face with this plugin or any compliment if you have.</p>
		[/tabs]
	[/tabset]');
}

function tabset_stylesheet_update(){
	extract(w4_tabset_get_option());

$style = "#tab_area{margin:10px 0px;}
#tab_area .tab_content{padding:10px 5px;}
#tab_content_wrapper{position:relative;overflow:hidden;border-bottom:1px solid {$tabset_content_border_color};padding:0 0 1px 0;}
#tab_area div.tab_container{padding:0;margin:0;border-bottom:1px solid {$tabset_content_border_color};background-color:{$tabset_content_bg_color};}
#tab_area div.ui-tabs-hide{display:none;}
#tab_area ul.tab_links{border-bottom:3px solid {$tabset_menu_bg_color_hover};overflow:hidden;padding:0;margin:0;list-style-type:none;list-style-position:outside;text-align:left;}
#tab_area ul.tab_links li{display:inline;float:left;position:relative;list-style-type:none;list-style-position:outside;padding:0;margin:0 5px 0 0;}
#tab_area ul.tab_links li a{color:{$tabset_menu_text_color};text-decoration:none;font-family:Geneva, Arial, Helvetica, sans-serif;font-size:{$tabset_menu_font_size};line-height:normal;font-weight:bold;padding:7px 15px 5px 15px;display:block;position:relative;background-color:{$tabset_menu_bg_color};-moz-border-radius-topleft:5px;-moz-border-radius-topright:5px;}
#tab_area ul.tab_links li a:hover, #tab_area ul.tab_links li a.active, #tab_area ul.tab_links li.ui-tabs-selected a{background-color:{$tabset_menu_bg_color_hover};color:{$tabset_menu_text_color_hover};}
.ui-tabs-hide, ui-tabs-panel{display:none;}
.ui-widget-content{display:block;}" ;

	$fp = fopen( TABSET_DIR . '/rewrite.css' ,"w");
	fwrite( $fp, $style );
	fclose( $fp );
	$_SESSION['counter'] = 1;
}
?>