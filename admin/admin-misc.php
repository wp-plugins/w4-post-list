<?php
/* Delete a Post list */
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
	
/* Save or Update Post list */
function w4pl_save_list( $options = array()){
	global $wpdb;

	if( !is_array( $options ))
		$options = array();

	extract( $options );

	$list_id = (int) $list_id;

	if( empty( $list_title ))
		return new WP_Error( 'list_title_empty', 'Please enter title for the list' );

	if( $list_id ){
		// handling options
		$options = apply_filters( 'w4pl_sanitize_list_option', $options );

		$update = true;
		$old_options = w4pl_get_list( $list_id );

		$list_option = maybe_serialize( stripslashes_deep( $list_option ));
		$user_id = get_userdata( (int) $old_options['user_id'] ) ? $old_options['user_id'] : get_current_user_id();

		$options = compact( 'list_title', 'list_option', 'user_id' );

		$wpdb->update( $wpdb->post_list, $options, array( 'list_id' => $list_id));
	}
	else{
		
		$list_option = maybe_serialize( stripslashes_deep( $list_option ));
		$user_id = get_current_user_id();

		$options = compact( 'list_title', 'list_option', 'user_id' );

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

/* Is the current user created this list */
function w4pl_is_list_user( $list = array(), $list_id = 0 ){
	if( !$list )
		$list = w4pl_get_list( $list_id );
	
	if( is_object( $list ))
		$list = get_object_vars( $list );

	$cur_user_id = get_current_user_id();

	if( $cur_user_id == $list['user_id'] )
		return true;
	
	return false;
}

/* The default or front page of plugin */
function w4pl_admin_body_docs(){ ?>
	<div class="has-right-sidebar">
	<div class="inner-sidebar" id="side-info-column">

    	<ul class="w4outlinks">
		<?php $siteurl = site_url('/'); ?>
		<li><a style="text-align:center; background-color:#0488ba;" href="https://load.payoneer.com/LoadToPage.aspx?email=sajib1223@gmail.com" target="_blank">Plugin Needs Some Fund..<br /><span style="font-size:18px">Donate 20USD</span><br />through Payoneer</a></li>
		<li><a href="<?php echo add_query_arg( array( 'utm_source' => $siteurl, 'utm_medium' => 'w4%2Bplugin', 'utm_campaign' => 'w4-post-list' ), 'http://w4dev.com/' ); ?>" target="_blank">Visit Plugin Site</a></li>
		<li><a href="<?php echo add_query_arg( array( 'utm_source' => $siteurl, 'utm_medium' => 'w4%2Bplugin', 'utm_campaign' => 'w4-post-list' ), 'http://w4dev.com/w4-plugin/w4-post-list/' ); ?>" target="_blank">Visit Plugin Page</a></li>
		<li><a href="<?php echo add_query_arg( array( 'utm_source' => $siteurl, 'utm_medium' => 'w4%2Bplugin', 'utm_campaign' => 'w4-post-list' ), 'http://w4dev.com/wp/w4-post-list-design-template/#examples' ); ?>" target="_blank">Designing Examples</a></li>
		<li><a href="http://wordpress.org/extend/plugins/w4-post-list/" target="_blank">Rate On WordPress</a></li>
		<li><a href="mailto:workonbd@gmail.com" target="_blank">Contact Author</a></li>
		</ul>
		


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
		<code>%%</code>image<code>%%</code> --  <?php _e( 'Post Thumbnail/image template. You can stylize this image with "w4pl_post_thumb" css class.<br /><code>Ex: .w4pl_post_thumb:border:1px solid red;</code>', 'w4-post-list' ); ?><br />
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
add_action( 'w4pl_admin_body_docs', 'w4pl_admin_body_docs' );

/* Retrive latest updates about Post List plugin */
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

function w4pl_capabilities(){
	global $w4pl_caps;

	if( empty( $w4pl_caps ))
		$w4pl_caps = get_option( 'w4pl_options' );
	
	if( !is_array( $w4pl_caps ))
		$w4pl_caps = array();

	if( !in_array( $w4pl_caps['access_cap'], array( 'manage_options', 'edit_others_posts', 'publish_posts', 'edit_posts' )))
		$w4pl_caps['access_cap'] = 'manage_options';

	if( !in_array( $w4pl_caps['manage_cap'], array( 'manage_options', 'edit_others_posts', 'publish_posts', 'edit_posts' )))
		$w4pl_caps['manage_cap'] = 'manage_options';

	return $w4pl_caps;
}

/* Add an action link on plugins.php page */
function w4pl_plugin_action_links( $links ){
	$readme_link['manage_plugin'] = '<a href="'. esc_attr( w4pl_plugin_page_url()) .'">' . __( 'Plugin', 'w4-post-list' ). '</a>';
	
	if( current_user_can( 'manage_options')){
		$readme_link['manage_plugin_options'] = '<a href="'. esc_attr( add_query_arg( 'subpage', 'credentials', w4pl_plugin_page_url())) .'">' . __( 'Credentials', 'w4-post-list' ). '</a>';
	}

	return array_merge( $links, $readme_link );
}
add_action( 'plugin_action_links_' . W4PL_BASENAME, 'w4pl_plugin_action_links' );

?>