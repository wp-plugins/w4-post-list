<?php

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

	if( empty( $list_title )){
		$options['list_title'] = 'List-' . $list_id;
		$wpdb->update( $wpdb->post_list, $options, array( 'list_id' => $list_id ));
	}
	return $list_id;
}

function w4pl_default_options(){
	$default_options2 = array(
		'list_id'						=> (int) 0,
		'list_title'			 		=> '', 		
		'list_option'					=> array(
			'list_type'		 			=> 'pc',
			'list_effect' 				=> 'no',	
			#'show_category_posts_count'	=> 'no',	

			'post_max'					=> '',
			'post_order_method'			=> 'newest',
			'show_future_posts'			=> 'no',
			'read_more_text'			=> 'Continue reading...',
			'excerpt_length' 			=> (int) 10,

			'post_ids'					=> array(),
			'posts_not_in'				=> array(),
			'categories'				=> array(),
			'html_template'				=> array(
				'wrapper' 			=> w4pl_template_wrapper(),
				'wrapper_post'		=> w4pl_post_template_wrapper(),
				'loop_post' 		=> w4pl_post_template_loop(),
				'wrapper_category'	=> w4pl_category_template_wrapper(),
				'loop_category'		=> w4pl_category_template_loop()
			)
		),
		'user_id'					=> get_current_user_id(),
	);
	return $default_options2;
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

function w4pl_help_page(){ ?>
	<div class="has-right-sidebar">
	<div class="inner-sidebar" id="side-info-column">

	<div class="stuffbox"><h3><?php _e( 'Connect', 'w4-post-list' ); ?></h3>
	<div class="inside">
    	<ol>
		<?php $siteurl = site_url('/'); ?>
		<li><a href="<?php echo add_query_arg( array( 'utm_source' => $siteurl, 'utm_medium' => 'w4%2Bplugin', 'utm_campaign' => 'w4-post-list' ), 'http://w4dev.com/' ); ?>" target="_blank">Plugin Site</a></li>
		<li><a href="<?php echo add_query_arg( array( 'utm_source' => $siteurl, 'utm_medium' => 'w4%2Bplugin', 'utm_campaign' => 'w4-post-list' ), 'http://w4dev.com/w4-plugin/w4-post-list/' ); ?>" target="_blank">Plugin Page</a></li>
		<li><a href="<?php echo add_query_arg( array( 'utm_source' => $siteurl, 'utm_medium' => 'w4%2Bplugin', 'utm_campaign' => 'w4-post-list' ), 'http://w4dev.com/wp/w4-post-list-design-template/#examples' ); ?>" target="_blank">Plugin Html Template Examples</a></li>
		<li><a href="http://wordpress.org/extend/plugins/w4-post-list/" target="_blank">Rate us On WordPress</a></li>
		<li><a href="mailto:workonbd@gmail.com" target="_blank">Contact</a></li>
		</ol>
		
		<script type="text/javascript" src="http://apis.google.com/js/plusone.js"></script>

		<p>If u like this plugin, Share IT !!!</p>
		<table style="text-align:center; margin:10px;"><tr><td><iframe src="http://www.facebook.com/plugins/like.php?href=<?php echo urlencode( 'http://w4dev.com/w4-plugin/w4-post-list/' ); ?>&amp;send=false&amp;layout=box_count&amp;width=85&amp;show_faces=false&amp;action=recommend&amp;colorscheme=light&amp;font&amp;height=62" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:85px; height:62px;" allowTransparency="true"></iframe></td>

		<td><g:plusone size="tall" count="true" href="http://w4dev.com/w4-plugin/w4-post-list/"></g:plusone></td></tr></table>

	</div></div>

	<div class="stuffbox">
	<h3><?php _e( 'Using Shortcode', 'w4-post-list' ); ?></h3>
	<div class="inside"><?php _e( 'Use shortcode "postlist" with the list id to show a post list on a post or page content area.', 'w4-post-list' ); ?>	<?php _e( 'Example:', 'w4-post-list'); ?> <strong>[postlist 1]</strong>
	</div></div>

	<div class="stuffbox">
	<h3><?php _e( 'Call Post list PHP function:', 'w4-post-list'); ?></h3>
	<div class="inside"><?php _e( 'Show a specific post list directly to your theme, use tempate tag', 'w4-post-list' ); ?> <code>"w4_post_list"</code> 
	<?php _e( 'with the list id. Example:', 'w4-post-list'); ?> 
	<code>w4_post_list( 'the_list_id' )</code>.<br /><br /><?php _e( 'For returning value instead of echoing, use '); ?>
    <code>w4_post_list( 'the_list_id', false )</code>.
	</div></div>



	</div><!--#side-info-column-->

	<div id="post-body"><div id="post-body-content">

	<h4 style="color:#FF0000; padding-bottom:10px; border-bottom:1px solid #CCC;"><?php _e( 'Click on the list item name above to edit an existing list. Click on add new to add a new one', 'w4-post-list'); ?></h4>

	<h3 style="margin-bottom:0;">Updates from Plugin Server</h3>
    <p style="background-color:#FFFFE0; border:1px solid #E6DB55; padding:5px 10px; border-width:1px 0;"><?php w4pl_plugin_news(); ?></p>

	<div class="stuffbox"><h3><?php _e( 'Html Design Template', 'w4-post-list'); ?></h3>
	<div class="inside">
   	<h4 style="color:#FF0000;"><a target="_blank" href="<?php echo add_query_arg( array( 'utm_source' => $siteurl, 'utm_medium' => 'w4%2Bplugin', 'utm_campaign' => 'w4-post-list' ), 'http://w4dev.com/w4-plugin/w4-post-list/#understanding_options' ); ?>">Learn about plugins basic options..</a></h4>

    <p><?php _e( 'Design your post list template to match your theme style. We have made <strong>teplate tag</strong> for each element of a post list.<br />
<span style="color:#FF0000">Caution:</span> If you are not Expert Understanding Basic HTML, CSS and PHP Loop algorithm, just leave the post list "Html Design Template" field as it is. Just save the basic options.', 'w4-post-list' ); ?></p>
	<p><?php _e( 'Template <strong>Tags</strong> are placed in <code>"%%"</code> sign. Each tag has a repective value. Please make sure you understand them before you remove one.', 'w4-post-list' ); ?></p>
	</div></div>

	<div class="stuffbox"><h3><?php _e( 'Template Tags', 'w4-post-list'); ?></h3>
	<div class="inside w4pl_tags">
		<h4>General tags:</h4>
		<code>%%</code>postlist<code>%%</code> --  <?php _e( 'You complete post list html.', 'w4-post-list' ); ?><br />
		<code>%%</code>postloop<code>%%</code> -- <?php _e( 'Post Template Loop. While displaying posts, every post go through the <code>postloop</code> once.', 'w4-post-list' ); ?><br />
		<code>%%</code>catloop<code>%%</code> == <?php _e( 'Category Template Loop. While displaying categories, every category go through the <code>catloop</code> once', 'w4-post-list' ); ?><br /><br /><br />

		<h4>Category tags:</h4>
		<code>%%</code>category_title<code>%%</code> --  <?php _e( 'Category title template', 'w4-post-list' ); ?><br />
		<code>%%</code>category_count<code>%%</code> --  <?php _e( 'Category item count', 'w4-post-list' ); ?><br />
		<code>%%</code>category_posts<code>%%</code> --  <?php _e( 'Posts inside this category. If you leave this field empty, And using post category list type, selected posts wont be visible', 'w4-post-list' ); ?><br />
		<code>%%</code>cat_link<code>%%</code> --  <?php _e( 'Category page link. ex: <code>http://example.com/category/uncategorized/</code>', 'w4-post-list' ); ?><br />
		<code>%%</code>cat_count<code>%%</code> --  <?php _e( 'Category post amount.', 'w4-post-list' ); ?><br />
		<code>%%</code>cat_name<code>%%</code> --  <?php _e( 'Category name.', 'w4-post-list' ); ?><br />
		<code>%%</code>cat_desc<code>%%</code> --  <?php _e( 'Category description.', 'w4-post-list' ); ?><br /><br /><br />

		
		<h4>Post tags:</h4>
		<code>%%</code>title<code>%%</code> --  <?php _e( 'Post title template', 'w4-post-list' ); ?><br />
		<code>%%</code>meta<code>%%</code> --  <?php _e( 'Meta template. <code><em>Ex: Posted on date by author</em></code>', 'w4-post-list' ); ?><br />
		<code>%%</code>publish/date<code>%%</code> --  <?php _e( 'Post publishing date template', 'w4-post-list' ); ?><br />
		<code>%%</code>modified<code>%%</code> --  <?php _e( 'Post last update date template', 'w4-post-list' ); ?><br />
		<code>%%</code>author<code>%%</code> --  <?php _e( 'Post author template linked to author url', 'w4-post-list' ); ?><br />
		<code>%%</code>excerpt<code>%%</code> --  <?php _e( 'Post excerpt template', 'w4-post-list' ); ?><br />
		<code>%%</code>post_excerpt<code>%%</code> --  <?php _e( 'Raw Post excerpt without wrapper. By default we wrap it with a html div', 'w4-post-list' ); ?><br />
		<code>%%</code>content<code>%%</code> --  <?php _e( 'Post content template', 'w4-post-list' ); ?><br />
		<code>%%</code>post_content<code>%%</code> --  <?php _e( 'Raw Post content without wrapper', 'w4-post-list' ); ?><br />
		<code>%%</code>more<code>%%</code> --  <?php _e( 'Read more template', 'w4-post-list' ); ?><br /><br /><br />

		<h4>More Post tags:</h4>
		<code>%%</code>id<code>|</code>ID<code>%%</code> --  <?php _e( 'Post ID', 'w4-post-list' ); ?><br />
		<code>%%</code>link<code>|</code>post_permalink<code>%%</code> --  <?php _e( 'Post permalink url address', 'w4-post-list' ); ?><br />
		<code>%%</code>post_title<code>%%</code> --  <?php _e( 'Raw Post Title Without link', 'w4-post-list' ); ?><br />
		<code>%%</code>post_date<code>%%</code> --  <?php _e( 'Post date Raw', 'w4-post-list' ); ?><br />
		<code>%%</code>post_date_time<code>%%</code> --  <?php _e( 'Post time Raw', 'w4-post-list' ); ?><br />
		<code>%%</code>post_modified<code>%%</code> --  <?php _e( 'Post last Modified date Raw', 'w4-post-list' ); ?><br />
		<code>%%</code>post_modified_time<code>%%</code> --  <?php _e( 'Post last Modified time Raw', 'w4-post-list' ); ?><br />
		<code>%%</code>post_comment_count<code>%%</code> --  <?php _e( 'Number of Approved comment for this post', 'w4-post-list' ); ?><br />
		<code>%%</code>post_comment_url<code>%%</code> --  <?php _e( 'Comment url address for current post', 'w4-post-list' ); ?><br />
		<code>%%</code>post_author<code>%%</code> --  <?php _e( 'Post author name', 'w4-post-list' ); ?><br />
		<code>%%</code>post_author_url<code>%%</code> --  <?php _e( 'Post author url address', 'w4-post-list' ); ?><br /><br /><br />

		<h4>Example:</h4>
		<p><?php _e( 'So now, you can wrap a tag easily with your own html tags. Like:', 'w4-post-list' );
		?> <code>&lt;span class=&quot;my-time&quot;&gt;%%post_date%%&lt;/span&gt;</code></p>
	</div><!--inside-->
    </div><!--stuffbox-->


	</div></div><!---->
	</div>
<?php
}

// Retrive latest news about our plugin from our server
function w4pl_plugin_news( $echo = true, $refresh = false ){
	$transient = 'w4pl_plugin_news';
	$transient_old = $transient . '_old';
	$expiration = 7200;

	$output = get_transient( $transient );

	if( $refresh || !$output || empty( $output )){

		$objFetchSite = _wp_http_get_object();
		$response = $objFetchSite->request( 
		'http://w4dev.com/wp-admin/admin-ajax.php?action=w4_ajax&action_call=plugin_news', 
		array( 'method' => 'POST' ));

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

function w4pl_plugin_action_links( $links ){
	$readme_link['manage_plugin'] = '<a href="'. esc_attr( w4pl_plugin_page_url()) .'">' . __( 'Plugin', 'w4-post-list' ). '</a>';
	
	if( current_user_can( 'manage_options')){
		$readme_link['manage_plugin_options'] = '<a href="'. esc_attr( w4pl_option_page_url()) .'">' . __( 'Manage Options', 'w4-post-list' ). '</a>';
	}

	return array_merge( $links, $readme_link );
}
add_action( 'plugin_action_links_' . W4PL_BASENAME, 'w4pl_plugin_action_links' );

?>