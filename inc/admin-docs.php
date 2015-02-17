<?php
/**
 * @package W4 Post List
 * @author Shazzad Hossain Khan
 * @url http://w4dev.com/plugins/w4-post-list
**/


class W4PL_Admin_Page_Docs 
{
	function __construct()
	{
		add_action( 'admin_menu', array($this, 'admin_menu') );
		#add_action( 'activate_'. W4PL_BASENAME, array($this, 'plugin_activated'), 10, 3 );

		// add lists link to plugin links, so one can navigate quickly
		add_filter( 'plugin_action_links_' . W4PL_BASENAME, 		array($this, 'plugin_action_links') );
	}

	public function admin_menu()
	{
		$admin_page = add_submenu_page( 
			'edit.php?post_type='. W4PL_SLUG,
			__('Documentation', W4PL_TD),
			__('Documentation', W4PL_TD),
			'delete_users',
			W4PL_SLUG . '-docs',
			array( $this, 'admin_page')
		);
		add_action('load-'. $admin_page, array($this, 'load_page') );
	}

	public function admin_page()
	{ 
	?>
		<style>
		#wpbody code{ background:none; font-size:12px; } 
		pre{background-color:#F5F5F5; padding:10px; border:1px solid #DDD;} 
		pre code{ padding-left:0;} 
		.has-right-sidebar #post-body-content{ margin-right:320px;}
		.inner-sidebar{ width:301px;}
		#shortcode_hint th, #shortcode_hint td{ border-bottom:1px solid #D3D3D3; padding-top:10px; padding-bottom:10px;}
		#shortcode_hint tbody th{ background-color:#EEE;}
		#shortcode_hint thead .tag_name{padding-right:10px;}
		#shortcode_hint thead .tag_desc{padding-left:10px;}
		#shortcode_hint .tag_name{text-align: right; width: 100px;}
		#shortcode_hint .tag_desc{text-align: left; font-size:12px; line-height: 1.3em; padding-left:10px;}
		#wpbody h2{font-size:13px; line-height:14px!important; padding:8px 12px!important; margin:0!important; font-weight:bold; background-color:#666; border:1px solid #444;color:#FFF;}
		#wpbody h2 + pre{ margin-top:0;}
		hr{ margin:20px 0!important;}
		#poststuff h3{ border-bottom:1px solid #DDD;}
        </style>

		<div class="wrap about-wrap">
		<h1><strong>W4 Post List Docs</strong> - (<?php _e('Version', W4PL_TD); ?>: <strong><?php echo W4PL_VERSION; ?></strong>)</h1>
		<div class="about-text"><?php _e('This plugin lets you create a list of Posts (including pages & custom post type), Terms (category, tag & custom taxonomy) or Terms + Posts Combo template. Outputs are completely customizable using Shortcode & HTML', W4PL_TD); ?></div>
		<div class="has-right-sidebar"><div id="poststuff">

		<div class="inner-sidebar" id="side-info-column">

		<?php if( self::old_table_exists() ): ?>
		<div class="postbox"><h3><?php _e( 'Migration', W4PL_TD ); ?></h3>
		<div class="inside">
			<?php _e('Old database table exists. Please import old lists and delete the table.', W4PL_TD); ?>
			<p><a href="<?php echo add_query_arg( array( 'action' => 'import_from_old' ) ); ?>" class="button"><?php _e('Import'); ?></a>
			 <a href="<?php echo add_query_arg( array( 'action' => 'delete_old_table' ) ); ?>" class="button"><?php _e('Delete Table'); ?></a></p>
		</div><!--inside-->
		</div><!--postbox-->
		<?php endif; ?>

		<div class="postbox">
		<h3><?php _e( 'Usage', W4PL_TD ); ?></h3>
		<div class="inside">
			<strong>Shortcode</strong>
			<p><?php 
			printf( __('Use shortcode %s with the list id to show a list on post/page content area', W4PL_TD), '<code>postlist</code>');
			_e( 'Ex:', W4PL_TD); ?> <code>[postlist 1]</code></p>

			<strong>Function</strong>
			<p><?php 
			printf( __('Display list using %s function', W4PL_TD), '<code>do_shortcode</code>'); ?>
			<br /><code>&lt;?php</code><br /><code>echo do_shortcode('[postlist id=1]');</code><br /><code>?&gt;</code></p>
		</div></div><!--postbox-->

		<div class="postbox"><h3><?php _e( 'Plugin links', W4PL_TD ); ?></h3>
		<div class="inside">
		<ul class="w4outlinks">
			<?php $siteurl = site_url('/'); ?>
			<li><a class="button" href="<?php echo add_query_arg( array( 'utm_source' => $siteurl, 'utm_medium' => 'w4%2Bplugin', 'utm_campaign' => W4PL_TD ), 'http://w4dev.com/plugins/w4-post-list' ); ?>" target="_blank">Visit Plugin Page</a></li>
			<li><a class="button" href="<?php echo add_query_arg( array( 'utm_source' => $siteurl, 'utm_medium' => 'w4%2Bplugin', 'utm_campaign' => W4PL_TD ), 'http://w4dev.com/wp/w4-post-list-examples/#examples' ); ?>" target="_blank">Demos &amp; Examples</a></li>
			<li><a class="button" href="http://wordpress.org/support/view/plugin-reviews/w4-post-list" target="_blank">Post a review</a></li>
			<li><a class="button" href="<?php echo add_query_arg( array( 'utm_source' => $siteurl, 'utm_medium' => 'w4%2Bplugin', 'utm_campaign' => W4PL_TD ), 'http://codecanyon.net/item/soccer-engine-wordpress-plugin/9070583' ); ?>" target="_blank">Do u need a Soccer/Football Plugin ?</a></li>
			<li><?php _e('Contact Author', W4PL_TD); ?> - sajib1223@gmail.com</li>
			</ul>
		</div><!--inside-->
		</div><!--postbox-->

		<div class="postbox"><h3><?php _e( 'Plugin Updates', W4PL_TD ); ?></h3>
		<div class="inside">
		   <?php W4PL_Lists_Admin::plugin_news(); ?>
		</div><!--inside-->
		</div><!--postbox-->

		</div><!--#side-info-column-->

		<div id="post-body"><div id="post-body-content">
		<div class="postbox"><h3><?php _e( 'Template', W4PL_TD); ?></h3>
		<div class="inside">

		<p><?php _e( 'Template is the output of a list. It can be designed with shortcode and HTML. Find few examples below.', W4PL_TD ); ?></p>


		<h2><strong><?php _e('Example'); ?></strong>: <?php _e( 'Simple Unordered Post List', W4PL_TD ); ?></h2>
		<pre><code>[posts]
  &lt;ul&gt;
    &lt;li&gt;&lt;a href=&quot;[post_link]&quot;&gt;[post_title]&lt;/a&gt;&lt;li&gt;
  &lt;/ul&gt;
[/posts]</code></pre>


		<h2><strong><?php _e('Example'); ?></strong>: <?php _e( 'Post list having excerpt limited to 20 words, and using post class on post wrapper element', W4PL_TD ); ?></h2>
		<pre><code>[posts]
  &lt;div class=&quot;[post_class]&quot;&gt;
    &lt;h3&gt;&lt;a href=&quot;[post_link]&quot;&gt;[post_title]&lt;/a&gt;&lt;/h3&gt;
    &lt;p&gt;[post_excerpt wordlimit=&quot;20&quot;]&lt;/p&gt;
  &lt;/div&gt;
[/posts]</code></pre>

		<h2><strong><?php _e('Example'); ?>: <?php _e( 'Post list Group by Year (chose <em>Group By</em> option to Year while using this).', W4PL_TD ); ?></h2>
		<pre><code>[groups]
  &lt;ul&gt;
    &lt;li&gt;
      &lt;a href=&quot;[group_link]&quot;&gt;[group_name]&lt;/a&gt;
      [posts]
        &lt;ol&gt;
          &lt;li&gt;&lt;a href=&quot;[post_link]&quot;&gt;[post_title]&lt;/a&gt;&lt;li&gt;
        &lt;/ol&gt;
      [/posts]
    &lt;li&gt;
  &lt;/ul&gt;
[/groups]</code></pre>



		<h2><strong><?php _e('Example'); ?>: <?php _e( 'A Simple Unordered Category list', W4PL_TD ); ?></h2>
		<pre><code>[terms]
  &lt;ul&gt;
    &lt;li&gt;&lt;a href=&quot;[term_link]&quot;&gt;[term_name]&lt;/a&gt;&lt;li&gt;
  &lt;/ul&gt;
[/terms]</code></pre>


		<h2><strong><?php _e('Example'); ?>: <?php _e( 'Category Post list', W4PL_TD ); ?></h2>
		<pre><code>[terms]
  &lt;ul&gt;
    &lt;li&gt;
      &lt;a href=&quot;[term_link]&quot;&gt;[term_name]&lt;/a&gt;
      [posts]
        &lt;ol&gt;
          &lt;li&gt;&lt;a href=&quot;[post_link]&quot;&gt;[post_title]&lt;/a&gt;&lt;li&gt;
        &lt;/ol&gt;
      [/posts]
    &lt;li&gt;
  &lt;/ul&gt;
[/terms]</code></pre>


		<h2><strong><?php _e('Example'); ?>: <?php _e( 'A Simple Unordered Users list', W4PL_TD ); ?></h2>
		<pre><code>[users]
  &lt;ul&gt;
    &lt;li&gt;&lt;a href=&quot;[user_link]&quot;&gt;[user_name]&lt;/a&gt;&lt;li&gt;
  &lt;/ul&gt;
[/users]</code></pre>


		<h2><strong><?php _e('Example'); ?>: <?php _e( 'Users Post list', W4PL_TD ); ?></h2>
		<pre><code>[users]
  &lt;ul&gt;
    &lt;li&gt;
      &lt;a href=&quot;[user_link]&quot;&gt;[user_name]&lt;/a&gt;
      [posts]
        &lt;ol&gt;
          &lt;li&gt;&lt;a href=&quot;[post_link]&quot;&gt;[post_title]&lt;/a&gt;&lt;li&gt;
        &lt;/ol&gt;
      [/posts]
    &lt;li&gt;
  &lt;/ul&gt;
[/users]</code></pre>

		</div><!--inside-->
		</div><!--postbox-->
	
		<div class="postbox "><h3><?php _e( 'Available Shortcodes', W4PL_TD); ?></h3>
		<div class="inside"><?php $shortcodes = apply_filters( 'w4pl/get_shortcodes', array() ); ?>
		<table id="shortcode_hint" cellpadding="0" cellspacing="0">
		<thead><tr><th class="tag_name">Tag</th><th style="text-align:left; padding-left:10px;" class="tag_desc"><?php _e( 'Details', W4PL_TD); ?></th></tr></thead><tbody><?php
		foreach( $shortcodes as $shortcode => $attr ){ $rc = isset($rc) && $rc == '' ? $rc = 'alt' : ''; ?>
			<tr class="<?php echo $rc; ?>">
			<th valign="top" class="tag_name"><code>[<?php echo $shortcode; ?>]</code></th>
			<td class="tag_desc"><?php echo $attr['desc']; ?></td>
			</tr>
		<?php } ?>
		</tbody></table>

		</div><!--inside-->
		</div><!--postbox-->
		</div><!--#post-body-content--></div><!--#post-body-->


		</div><!--has-right-sidebar-->
		</div><!--#poststuff-->
		</div><!--wrap-->
	<?php
	}

	public function load_page()
	{
		global $wpdb;
		if( isset($_REQUEST['action']) && 'import_from_old' == $_REQUEST['action'] ){
			self::import_old_data();
			wp_redirect( add_query_arg( array('m' => 'imported', 'action' => false) ) );
			exit;
		}
		elseif( isset($_REQUEST['action']) && 'delete_old_table' == $_REQUEST['action']){
			$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}post_list" );
			wp_redirect( add_query_arg( array('m' => 'table_deleted', 'action' => false) ) );
			exit;
		}

		if( isset($_REQUEST['m']) ){
			add_action('admin_notices', array($this, 'admin_notices') );
		}
	}

	public function admin_notices()
	{
		if( isset($_REQUEST['m']) && 'imported' == $_REQUEST['m'] ){
			echo '<div id="message" class="updated"><p>'. __('Old entries imported', W4PL_TD). '</p></div>';
		}
		elseif( isset($_REQUEST['m']) && 'table_deleted' == $_REQUEST['m'] ){
			echo '<div id="message" class="updated"><p>'. __('Old table deleted', W4PL_TD). '</p></div>';
		}
	}

	public function import_old_data()
	{
		if( !self::old_table_exists() )
			return;

		global $wpdb;
		$records = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}post_list");

		if( empty($records) )
			return;

		foreach( $records as $record )
		{
			$list_options = maybe_unserialize( $record->list_option );

			extract($list_options);
			extract($html_template);

			if( isset($list_options['list_type']) && 'oc' == $list_options['list_type'] ){
				continue;
			}

			$list_data = array();
			if( isset($post_max) && !empty($post_max) ){
				$list_data['posts_per_page'] = (int) $post_max;
				$list_data['limit'] = (int) $post_max;
			}

			// grab orderby method
			$order_methods = array(
				'newest'		=> array( 'orderby' => 'date', 'order' => 'DESC'),
				'oldest'		=> array( 'orderby' => 'date', 'order' => 'ASC'),
				'most_popular'	=> array( 'orderby' => 'comment_count', 'order' => 'DESC'),
				'less_popular'	=> array( 'orderby' => 'comment_count', 'order' => 'ASC'),
				'a_title'		=> array( 'orderby' => 'title', 'order' => 'ASC'),
				'z_title'		=> array( 'orderby' => 'title', 'order' => 'DESC'),
				'random'		=> array( 'orderby' => 'rand', 'order' => 'ASC')
			);
			if( isset($post_order_method) && isset($order_methods[$post_order_method]) ){
				$list_data['orderby'] =$order_methods[$post_order_method]['orderby'];
				$list_data['order'] =  $order_methods[$post_order_method]['order'];
			}

			// grab post ids and post ids
			if( isset($post_ids) && !empty($post_ids) ){
				$list_data['post__in'] = implode( ',', $post_ids );
			}
			elseif( isset($posts_not_in) && !empty($posts_not_in) ){
				$list_data['post__not_in'] = implode( ',', $posts_not_in );
			}

			// grab post categories
			if( isset($categories) && !empty($categories) ){
				foreach ( $categories as $ci => $cd ){
					if( is_array($cd) ){
						$list_data['tax_query_category'][] = $ci;

						if( isset($cd['post_ids']) && !empty($cd['post_ids']) ){
							$list_data['post__in'] = array_merge( $cd['post_ids'], $list_data['post__in'] );
						}
						if( isset($cd['posts_not_in']) && !empty($cd['posts_not_in']) ){
							$list_data['post__not_in'] = array_merge( $cd['posts_not_in'], $list_data['post__not_in'] );
						}
					}
					else{
						$list_data['tax_query_category'][] = $cd;
					}
				}
			}
			if( !empty($list_data['post__in']) ){
				$list_data['post__in'] = array_map('intval', $list_data['post__in']);
				$list_data['post__not_in'] = array();
			}
			elseif( !empty($list_data['post__not_in']) ){
				$list_data['post__not_in'] = array_map('intval', $list_data['post__not_in']);
				$list_data['post__in'] = array();
			}

			if( isset($wrapper) && !empty($wrapper) ){
				$list_data['template'] = $wrapper;
			}
			if( isset($wrapper_post) && !empty($wrapper_post) ){
				if( ! isset($list_data['template']) || empty($list_data['template']) )
					$list_data['template'] = '[postlist]';

				$list_data['template'] = str_replace( '[postlist]', $wrapper_post, $list_data['template']);
				$list_data['template'] = str_replace( '[postloop]', '[loop]', $list_data['template']);
			}
			if( isset($loop_post) && !empty($loop_post) ){
				$list_data['template_loop'] = str_replace('[image]', '[post_thumbnail]', $loop_post);
			}

			if( empty($list_data) )
				continue;

			$post_data = array('post_title' => $record->list_title, 'post_type' => W4PL_SLUG, 'post_status' => 'publish');
			$post_ID = wp_insert_post( $post_data );
			if( !is_wp_error($post_ID) ){
				update_post_meta( $post_ID, '_w4pl', $list_data );
			}
		}
	}

	public function plugin_activated()
	{
		global $wpdb;
		self::import_old_data();
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}post_list" );
	}

	public static function plugin_action_links( $links )
	{
		$readme_link['doc'] = '<a href="'. 'edit.php?post_type=w4pl&page=w4pl-docs">' . __('Docs', W4PL_TD ). '</a>';
		return array_merge( $links, $readme_link );
	}

	// Check our old plugin table exists or not
	public function old_table_exists(){
		global $wpdb;
		return strtolower( $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}post_list'" )) == "{$wpdb->prefix}post_list";
	}
}

	new W4PL_Admin_Page_Docs;
?>