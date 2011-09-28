<?php
// The main form to collect post list data
function w4ld_list_form( $list_id = 0){
	global $wpdb, $w4pl_caps;

	if( !$w4pl_caps)
		$w4pl_caps = get_option( 'w4pl_options');;

	$list_id = (int) $list_id;
	if( !$list_id && isset( $_GET['list_id'] ))
		$list_id = (int) $_GET['list_id'];
		
	if( !w4pl_get_list( $list_id ))
		return false;
	
	$list = w4pl_get_list( $list_id );
	#echo '<pre>';print_r( $list );echo '</pre>';

	$list_option = $list['list_option'];
	$list_title = $list['list_title'];

	$form_action = add_query_arg( 'list_id', $list_id, w4pl_plugin_page_url());
	$form_hidden_elements = '<input type="hidden" value="'. $list_id . '" name="list_id"/>';

	extract( $list_option );
		
	$list_type_pc_hide = ( $list_type == 'pc' ) ? 'hide_box' : '';
	$list_type_op_hide = ( $list_type == 'op' ) ? 'hide_box' : '';
	$list_type_oc_hide = ( $list_type == 'oc' ) ? 'hide_box' : '';
	$list_type_op_by_cat_hide = ( $list_type == 'op_by_cat' ) ? 'hide_box' : '';

?>
	<form action="<?php echo $form_action ; ?>" method="post" id="w4_post_list_form" enctype="multipart/form-data">
		<div class="side_notice">
		<h2 style="padding-top:0px;"><?php _e( 'List id: ', 'w4-post-list');?> <span><?php echo $list_id; ?></span></h2>

		<?php
        if( !w4pl_is_list_user( $list ) && current_user_can( $w4pl_caps['manage_cap'] )):
			$list_user = get_userdata( $list['user_id'] );
			echo "<p><strong>List Username:</strong> <span>$list_user->display_name</span>,<br /><strong>Userid:</strong> {$list_user->ID}</p>";
		endif;
		?>

		<?php
		echo "<strong>" . __( "Copy this code", "w4-post-list"). " &rarr; </strong><input type='text' value='[postlist $list_id]' readonly='readonly' onfocus='this.select();'> " . __( "and paste it into your post, page or text widget content area to show this list.", 'w4-post-list');
		?>
   		<p><input type="submit" name="" class="save_w4_post_list_options" value="Save option" /><br />
        <?php
		echo '<a class="button" id="delete_list" rel="'. $list_title .'" title="Delete '. $list_title .' ?" 
			href="'. add_query_arg( array( "list_id" => $list_id, 'delete' => 'true'), w4pl_plugin_page_url()). '">' .__( 'deleted this list', 'w4-post-list' ) . '</a> 
			or <a class="button" title="Add new list" href="'. w4pl_add_url(). '">add new one</a></p>';
			
		?>
		
		<div class="w4ld_plugin_news"><h3>Updates From Plugin Server -</h3><?php w4pl_plugin_news(); ?></div>
        </div>

    	<?php echo $form_hidden_elements ; ?>

		<!--List Name-->
		<div class="option"><h3><label for="list_title"><?php _e('List name:', 'w4-post-list'); ?></label>
		<span class="w4pl_tip_handle"><span class="w4pl_tip"><?php _e( 'Give this post list a name, so that you can find it easily.', 'w4-post-list'); ?></span></span>
		</h3>
		<input type="text" id="list_title" value="<?php  echo( $list_title) ; ?>" name="list_title" id="list_title" class=""/></div>

		<!--List type-->
		<div class="option"><h3><?php _e( 'List type:', 'w4-post-list' ); ?>
		<span class="w4pl_tip_handle"><span class="w4pl_tip"><?php _e( 'Kind of list you need.', 'w4-post-list'); ?></span></span></h3>
		<ul>
			<li><label><input type="radio" <?php checked( $list_type, 'pc' ); ?> name="list_type" value="pc"  /> <?php 
			_e( 'Posts with categories', 'w4-post-list' ); ?></label></li>
			<li><label><input type="radio" <?php checked( $list_type, 'oc' ); ?> name="list_type" value="oc"  /> <?php 
			_e( 'Only categories', 'w4-post-list' ); ?></label>
			<li><label><input type="radio" <?php checked( $list_type, 'op' ); ?> name="list_type" value="op"  /> <?php 
			_e( 'Only posts', 'w4-post-list' ); ?></label></li>
			<li><label><input type="radio" <?php checked( $list_type, 'op_by_cat' ); ?> name="list_type" value="op_by_cat"  /> <?php 
			_e( 'Only posts - <small> select by category</small>', 'w4-post-list' ); ?></label></li>
		</ul></div>

		<!--Post order by-->
		<div class="option <?php echo "$list_type_pc_hide $list_type_oc_hide"; ?> hide_if_pc hide_if_oc show_if_op show_if_op_by_cat">
		<?php echo w4pl_form_order_by( "post_order_method", $post_order_method); ?>
		</div>

		<!--Maximum Posts-->
		<div class="option <?php echo "$list_type_pc_hide $list_type_oc_hide"; ?> hide_if_pc hide_if_oc show_if_op show_if_op_by_cat">
		<?php echo w4pl_form_max_posts( "post_max", $post_max); ?>
		</div>

		<!--Show Future Posts-->
		<div class="option <?php echo "$list_type_pc_hide $list_type_oc_hide $list_type_op_by_cat_hide"; ?> hide_if_pc hide_if_oc hide_if_op_by_cat show_if_op">
		<?php echo w4pl_form_show_future_posts( "show_future_posts", $show_future_posts); ?>
		</div>

		<!--Category and post Selections-->
		<div class="option <?php echo $list_type_op_hide; ?> show_if_pc show_if_oc show_if_op_by_cat hide_if_op">
		<h3><?php _e( 'Select categories/posts:', 'w4-post-list'); ?></h3>
		<?php echo w4pl_categories_checklist( $list_option); ?></div>

		<!--Select only Posts-->
		<div class="option <?php echo "$list_type_pc_hide $list_type_oc_hide $list_type_op_by_cat_hide"; ?> hide_if_pc hide_if_oc hide_if_op_by_cat show_if_op">
		<strong><?php _e( 'Select posts:', 'w4-post-list'); ?></strong>
		<?php echo w4ld_posts_checklist( $list_option); ?></div>

		<!--List effect-->
		<div class="option <?php echo "$list_type_oc_hide $list_type_op_hide $list_type_op_by_cat_hide"; ?> hide_if_oc hide_if_op hide_if_op_by_cat show_if_pc">
		<h3><?php _e( 'Show category posts with a jquery slide Up/Down effect?', 'w4-post-list' ); ?></h3>
		<div class="form_help"><?php _e( 'if you have customized your <strong>Category Template Loop</strong> design, the remember to put  both <code>%%category_title%%</code> and <code>%%category_posts%%</code> tags in same html element and without putting each one in separate html elements.', 'w4-post-list' ); ?><br /><br />
		<strong><?php _e( 'Example:', 'w4-post-list' ); ?></strong><pre>&lt;div&gt;<br /><code>%%category_title%%</code><br /><code>%%category_posts%%</code><br />&lt;/div&gt;</pre>
		<br />On-clicking on the category title, the selected posts under this category will appear. If a category doen have any selected posts, the category title link will take you to the category page.
		</div>

		<ul>
		<li><label><input type="radio" <?php checked( $list_effect, 'no' ); ?> name="list_effect" value="no"  /> <?php 
		_e( 'Not neccessary', 'w4-post-list' ); ?></label></li>
		<li><label><input type="radio" <?php checked( $list_effect, 'yes' ); ?> name="list_effect" value="yes"  /> <?php 
		_e( 'Yap, do it', 'w4-post-list' ); ?></label></li>
		<li><label><input type="radio" <?php checked( $list_effect, 'extended' ); ?> name="list_effect" value="extended"  /> <?php 
		_e( 'Do it. Also make the posts invisible at primary position', 'w4-post-list' ); ?></label></li>
		</ul></div>

		<!--Category item count-->
		<div class="option <?php echo "$list_type_op_hide $list_type_op_by_cat_hide"; ?> hide_if_op hide_if_op_by_cat show_if_pc show_if_oc">
		<h3><?php _e( 'Show posts count appending to category name ?', 'w4-post-list' ); ?>
      
        <span class="w4pl_tip_handle"><span><?php _e( 'Selection no will over ride the Template Tag <code>%%category_count%%</code> to return empty.', 'w4-post-list' ); ?></span></span></h3>
		This option has been implemented into Html template. Use '%%category_count%%' tags in 'Category Template Loop' to show the post item count of a category.
		</div>

		<!--Post read more text-->
		<div class="option <?php echo "$list_type_oc_hide"; ?> hide_if_oc show_if_pc show_if_op show_if_op_by_cat">
		<h3><label for="read_more_text"><?php _e('Readmore Text', 'w4-post-list'); ?></label>
		<span class="w4pl_tip_handle"><span><?php _e( 'Text for the template tag <code>%%more%%</code>. This text will be linked to the post title', 'w4-post-list' ); ?></span></span></h3>

		<input type="text" value="<?php echo( $read_more_text) ; ?>" name="read_more_text" id="read_more_text" /></div>

		<!--Post excerpt length-->
		<div class="option <?php echo "$list_type_oc_hide"; ?> hide_if_oc show_if_pc show_if_op show_if_op_by_cat">
		<h3><label for="excerpt_length"><?php _e('Excerpt length:', 'w4-post-list'); ?></label>
		<span class="w4pl_tip_handle"><span><?php _e( 'Word limit when showing post excerpt.', 'w4-post-list'); ?></span></span></h3>
		<input type="text" value="<?php echo( $excerpt_length) ; ?>" name="excerpt_length" id="excerpt_length" /></div>

		<h3 style="color:#FF0000">Html Design Template</h3>
		<div class="form_help">If you are not little expert understanding Basic HTMl and PhP Loop algorithm, just leave the design field as it is.<br />
		Template tag are placed in <code>'%%'</code> sign. Each tag has a repective value. Please make sure you understand them before you remove one.</div>
        
        <p>
        <a href="http://w4dev.com/wp/w4-post-list-design-template/#examples" target="_blank"><em>*** View some HTML Design Template Examples</em></a><br />
        <em style="color:red;">*** Click on the yellow question mark icon for help.</em></p>

		<div class="option">
		<h4><label for="w4pl_template_wrapper"><?php _e( 'Template Wrapper:', 'w4-post-list'); ?></strong></label>
		<span class="w4pl_tip_handle toogle_helper"><span><?php _e( 'Click to see available shortcode tags.', 'w4-post-list'); ?></span></span></h4>
		The complete list inside a wrapper.
        <div class="form_help toogle_help"><code>%%</code>postlist<code>%%</code> --  You complete post list html.</div>

		<textarea name="html_template[wrapper]" id="w4pl_template_wrapper"><?php echo $html_template['wrapper']; ?></textarea>
		</div>

		<div class="option <?php echo "$list_type_op_hide $list_type_op_by_cat_hide"; ?> hide_if_op hide_if_op_by_cat show_if_pc show_if_oc">
		<h4><label for="w4pl_category_template_wrapper"><?php _e( 'Category Template Wrapper:', 'w4-post-list'); ?></label>
		<span class="w4pl_tip_handle toogle_helper"><span><?php _e( 'Click to see available shortcode tags', 'w4-post-list'); ?></span></span></h4>

		Category list wrapper. This will be placed inside the Template wrapper if you are using "Posts with categories" or "Only categories" list type.
        <div class="form_help toogle_help"><code>%%</code>catloop<code>%%</code> --  Category loop container html.</div>
		<textarea name="html_template[wrapper_category]" id="w4pl_category_template_wrapper"><?php echo $html_template['wrapper_category']; ?></textarea>
		</div>

		<div class="option <?php echo "$list_type_op_hide $list_type_op_by_cat_hide"; ?> hide_if_op hide_if_op_by_cat show_if_pc show_if_oc">
		<h4><label for="w4pl_category_template_loop"><?php _e( 'Category Template Loop:', 'w4-post-list'); ?></label>
        <span class="w4pl_tip_handle toogle_helper"><span><?php _e( 'Click to see available shortcode tags', 'w4-post-list'); ?></span></span></h4>
        <div class="form_help toogle_help">
        <p>
        <code>%%</code>category_title<code>%%</code> --  Category title template.<br />
        <code>%%</code>category_count<code>%%</code> --  Category item count template. use <code>%%</code>cat_count<code>%%</code> to get the raw count.<br />
        <code>%%</code>category_posts<code>%%</code> --  Posts inside this category. If you leave this field empty, And using post category list type, selected posts wont be visible<br /><br />
		<code>%%</code>cat_link<code>%%</code> --  Category page link. ex: <code>http://example.com/category/uncategorized/</code><br />
		<code>%%</code>cat_count<code>%%</code> --  Category post amount.<br />
		<code>%%</code>cat_name<code>%%</code> --  Category name.<br />
		<code>%%</code>cat_desc<code>%%</code> --  Category description.<br />

        </p>
		</div>
		<textarea name="html_template[loop_category]" id="w4pl_category_template_loop"><?php echo $html_template['loop_category']; ?></textarea>
		</div>

		<div class="option <?php echo "$list_type_oc_hide"; ?> hide_if_oc show_if_pc show_if_op show_if_op_by_cat">
		<h4><label for="w4pl_post_template_wrapper"><?php _e( 'Post Template Wrapper:', 'w4-post-list'); ?></label>
		<span class="w4pl_tip_handle toogle_helper"><span><?php _e( 'Click to see available shortcode tags', 'w4-post-list'); ?></span></span></h4>
		
        Post list wrapper. This will be placed inside the Template wrapper.
		<div class="form_help toogle_help">
        <code>%%</code>postloop<code>%%</code> --  Post template loop.</div>
		<textarea name="html_template[wrapper_post]" id="w4pl_post_template_wrapper"><?php echo $html_template['wrapper_post']; ?></textarea>
		</div>

		<div class="option <?php echo "$list_type_oc_hide"; ?> hide_if_oc show_if_pc show_if_op show_if_op_by_cat">
		<h4><label for="w4pl_post_template_loop"><?php _e( 'Post Template Loop:', 'w4-post-list'); ?></label>
		<span class="w4pl_tip_handle toogle_helper"><span><?php _e( 'Click to see available shortcode tags', 'w4-post-list'); ?></span></span></h4>
        
        Writting a wrong tag field name will make the field name visible on your post list rather than parsing it with our code..<br /><br />
        If you wrap your Post Template Wrapper with <code>ol</code> or <code>ul</code>, you should wrap you loop template with <code>li</code> Html element.</p>
        <div class="form_help toogle_help">
		<p>
        <strong>Regular tags:</strong><br />
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
		</p>

		<p>
        <strong>Additional tags:</strong><br />
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
		</p>

        </div>
		<textarea name="html_template[loop_post]" id="w4pl_post_template_loop"><?php echo $html_template['loop_post']; ?></textarea>
		</div>
		<input type="hidden" name="save_w4_post_list_options" value="1" />
		<input type="submit" name="" class="save_w4_post_list_options" value="Save option" />
	</form>
<?php
	}

function w4ld_posts_checklist($options = array()){

	$post_ids = (array) $options['post_ids'];
	$post_order = w4pl_sanitize_post_order_method( $options['post_order_method']);
	query_posts( array(
		'post_status' 		=> 'publish',
		'order' 			=> $post_order['order'],
		'orderby' 			=> $post_order['orderby'],
		'posts_per_page'	=> '-1',
		'showposts'			=> '-1'
	));

	if( have_posts()):
		$checklist = "<input type='checkbox' name=\"selector\" id=\"post_selector\" value=\"list_option[post_ids][]\"/> <label for=\"post_selector\">toggle select all</label>";
		$checklist .= "<ul class=\"post_list\">";
			
		while( have_posts()): the_post();
			$checked = in_array( get_the_ID(), $post_ids) ? ' checked="checked" ' : '';
			$checklist .= "<li><label title=\"". get_the_title() ."\"><input name=\"list_option[post_ids][]\" type=\"checkbox\" $checked value=\"". get_the_ID() ."\" /> " 
			. get_the_title() .'</label>'. sprintf( __( ' &laquo; Categories: %s', 'w4-post-list' ), get_the_category_list( ', ' )) .'</li>';
		endwhile;
		$checklist .= "</ul>";

	else:
		$checklist = __( 'No posts', 'w4-post-list' );

	endif;
	return $checklist ;
}

function w4pl_categories_checklist( $list_option = array()){
	
	$categories = get_categories( array('hide_empty' => false));
	$category_options = (array) $list_option['categories'];
	$list_type_oc_hide = ( 'oc' == $list_option['list_type'] ) ? 'hide_box' : '';

	$checklist = '';
	foreach( $categories as $category ){
		$checked = ( in_array( $category->cat_ID, array_keys( $category_options ))) ? ' checked="checked" ' : '';
		$category_container_class = isset( $category_container_class ) && $category_container_class == 'first' ? 'second' : 'first';

		$default_options = array( 'cat_id' => $category->cat_ID, 'list_type' => $list_option['list_type'] );
		$category_option = wp_parse_args(
			isset( $category_options[$category->cat_ID] ) ? $category_options[$category->cat_ID] : array(),
			$default_options
		);

		//Category name
		$checklist .= "<div class=\"category $category_container_class\">";
			
		$checklist .= "<p class=\"cat_title\"><label><input name=\"list_option[categories][]\" type=\"checkbox\" 
		$checked value=\"$category->cat_ID\" class=\"w4pl_cat_checkbox\" /> $category->cat_name</strong></label> 
		<span class=\"category_post_handle $list_type_oc_hide hide_if_oc show_if_pc show_if_op_by_cat\" rel='w4cat_{$category->cat_ID}'>manage posts</span></p>";

		$checklist .= "<div id='w4cat_{$category->cat_ID}' class=\"w4c_inside hide_if_oc\">";
		$checklist .= w4pl_category_posts_checklist( $category_option, $list_option );
		$checklist .= "</div><!--.w4c_inside close-->";
		$checklist .= "</div><!--.category closed-->";
	}
	return $checklist;
}

function w4pl_category_posts_checklist( $category_option, $list_option ){

	$default = array(
		'max' 				=> '',
		'post_ids' 			=> array(),
		'post_order_method'	=> 'newest',
		'show_future_posts'	=> 'no'
	);

	$category_option = wp_parse_args( $category_option, $default );
	extract( $category_option);
	
	$list_type_op_by_cat_hide = ( $list_type == 'op_by_cat' ) ? 'hide_box' : '';
	$post_order = w4pl_sanitize_post_order_method( $post_order_method);
	query_posts( array(
		'post_status' 		=> 'publish',
		'order' 			=> $post_order['order'],
		'orderby' 			=> $post_order['orderby'],
		'cat' 				=> $cat_id,
		'posts_per_page'	=> '-1',
		'showposts'			=> '-1'
	));

	if( have_posts()):
		$checklist = "<div class=\"hide_if_op_by_cat show_if_pc $list_type_op_by_cat_hide\">";
		$checklist .= w4pl_form_order_by( "w4pl_categories_post_order_method[$cat_id]", $post_order_method ). '<br /><br />';
		$checklist .= '</div>';

		$checklist .= w4pl_form_show_future_posts( "_w4_cat_show_future_posts_". $cat_id, $show_future_posts );
		#$max_field_name = "w4pl_categories_max[$cat_id]";
		
		$checklist .= '<br /><br />' . w4pl_form_max_posts( "w4pl_categories_max[$cat_id]" , $max) ;
		$checklist .= "<br /><br /><strong>". __( 'Select posts:', 'w4-post-list' ) ."</strong> ";
			
		$checklist .= "<input type='checkbox' name=\"selector\" id=\"post_selector_for_{$cat_id}\" value=\"category_posts[$cat_id][]\" /> <label for=\"post_selector_for_{$cat_id}\">toggle select all</label>";

		$checklist .= "<ul class=\"post_list\">";
		while( have_posts()): the_post();
			$checked2 = in_array( get_the_ID(), $post_ids) ? ' checked="checked" ' : '';
			$checklist .= "<li><label title=\"". get_the_title() ."\"><input name=\"category_posts[$cat_id][]\" type=\"checkbox\" $checked2 
			value=\"".get_the_ID()."\" /> ". get_the_title() .'</label></li>' ;
		endwhile;
		$checklist .= "</ul>";

	else:
		$checklist = '<span class="red">' . __( 'No posts in this cat', 'w4-post-list' ) .'</span>';
	endif;
		
	return $checklist;
}

// Option page form.
function w4ld_option_form(){
	global $wpdb;

	$default_options = array(
		'access_cap' 			=> 'manage_options',
		'manage_cap' 			=> 'manage_options'
	);

	if( !get_option( 'w4pl_options' ))
		update_option( 'w4pl_options', $default_options );

	$options = get_option( 'w4pl_options' );

	$capability_options = array(
		'manage_options' 	=> __(' Admins Only', 'w4-post-list' ),
		'edit_others_posts' => __( 'Admins, Editors', 'w4-post-list' ),
		'publish_posts' 	=> __( 'Admins, Editors, Authors', 'w4-post-list' ),
		'edit_posts' 		=> __( 'Admins, Editors, Authors, Contributors', 'w4-post-list' )
		);

	extract( $options);
?>
	<form action="<?php echo w4pl_option_page_url(); ?>" method="post" id="w4pl_option_form" enctype="multipart/form-data">
		<div class="side_notice"><input type="submit" name="save_w4pl_option_form" class="button-primary save_w4pl_option_form" value="Save option" /></div>

		<div class="option"><label for="access_cap"><strong><?php _e( 'Create and manage post list:', 'w4-post-list'); ?></strong></label>
		<span class="w4pl_tip_handle"><span><?php _e( 'You can restrict acess to post creation. A user must have this capability, if he has the next capability. Otherwise, he wont use the next capability.', 'w4-post-list'); ?></span></span>
		<br />
		<select name="access_cap">
		<?php
		foreach ( $capability_options as $k => $v):
			$selected = $k == $access_cap ? ' selected=selected ': '';
			echo "<option value=\"$k\" $selected >$v</option>";
		endforeach;
		?>
		</select>
		</div>

		<div class="option"><label for="manage_cap"><strong><?php _e( 'Edit/delete/manage All post list:', 'w4-post-list'); ?></strong></label>
		<span class="w4pl_tip_handle"><span><?php _e( 'Probably admin should have it. If you want user create this, set the previous one..', 'w4-post-list'); ?></span></span>
		<br />
		<select name="manage_cap">
		<?php
		foreach ( $capability_options as $k => $v):
			$selected = $k == $manage_cap ? ' selected=selected ': '';
			echo "<option value=\"$k\" $selected >$v</option>";
		endforeach;
		?>
		</select>
		</div>
	</form>
<?php
}

function w4pl_form_order_by( $input_name, $selected){
	return '<h4>'. __( 'Post order by:', 'w4-post-list' ). '</h4>
				<label><input type="radio" '. checked( $selected, 'newest', false ).' name="'.$input_name.'" 
				value="newest"  /> '. __( 'Newest -<small>recent</small>', 'w4-post-list'  ). '</label>

				<br /><label><input type="radio" '. checked( $selected, 'oldest', false ).' name="'.$input_name.'" 
				value="oldest"  /> '. __( 'Oldest -<small>less recent</small>', 'w4-post-list'  ). '</label>

				<br /><label><input type="radio" '. checked( $selected, 'most_popular', false ).' name="'.$input_name.'" 
				value="most_popular"  /> '. __( 'Most popular -<small>maximum commented post will be shown first</small>', 'w4-post-list' ).'</label>

				<br /><label><input type="radio" '. checked( $selected, 'less_popular', false ).' name="'.$input_name.'" 
				value="less_popular"  /> '. __( 'Less popular -<small>minimum commented post will be shown first</small>', 'w4-post-list' ).'</label>

				<br /><label><input type="radio" '. checked( $selected, 'a_title', false ).' name="'.$input_name.'" 
				value="a_title"  /> '. __( 'Sort by post title -<small>A-Z</small>', 'w4-post-list'  ).'</label>

				<br /><label><input type="radio" '. checked( $selected, 'z_title', false ).' name="'.$input_name.'" 
				value="z_title"  /> '. __( 'Sort by post title -<small>Z-A</small>', 'w4-post-list'  ).'</label>

				<br /><label><input type="radio" '. checked( $selected, 'random', false ).' name="'.$input_name.'" 
				value="random"  /> '. __( 'Random -<small>anything can happen</small>', 'w4-post-list' ).'</label>
	';
}
	
function w4pl_form_show_future_posts( $input_name, $selected ){
	return '<h4>'. __( 'Show future posts:', 'w4-post-list' ). '</h4>
		<label><input type="radio" '. checked( $selected, 'no', false ).' name="'. $input_name .
		'" value="no"  /> '. __( 'No.', 'w4-post-list'). '</label>

		<br /><label><input type="radio" '. checked( $selected, 'yes', false ).' name="'. $input_name .
		'" value="yes"  /> '. __( 'Yes.', 'w4-post-list'). '</label>';
}

function w4pl_form_max_posts( $input_name, $value){
	return '<h4><label for="'.$input_name.'">'. __( 'Maximum posts to show', 'w4-post-list') . '</label> <span class="w4pl_tip_handle"><span>leave empty or 0 to show all</span></span></h4><input size="3" id="'.$input_name.'" name="'. $input_name. '" type="text" value="'. $value . '" />';
}
?>