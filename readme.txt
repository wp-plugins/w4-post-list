=== W4 Post List ===
Contributors: sajib1223
Tags: post, post list, custom post list, custom post type, widget, shortcode, media
Requires at least: 3.0
Tested up to: 3.9
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

W4 Post List lets you create a list of posts and display them. The extraordinary feature is, one can chose which information to show and where to show it regarding the post. The plugin gives you total freedom to create your list template using shortcode tags and style it.



== Description ==

Display Posts inside Post/Page content or widget areas by Shortcodes. Select what to show and design how to show it. Using the plugin is really easy. You will find Tinymce button on post/page editor to quickly inset a list. Also, there's a separate page for creating or editing list. 

Creating a list is just few steps.

= Build Query =
* post type
* post status
* post mime type
* post taxonomy terms
* post ids
* post parents
* post authors
* post meta ( meta_query )

= Group Results by =
* year
* month
* month year
* category, post tag or custom taxonomies
* authors
* parents

= Order Results by =
* post id
* post title
* post name
* post publish date
* post modified date
* menu order
* approved comment count
* meta value
* or random

= Multi-Page Pagination by =
* Next / Previous links
* Numeric navigation flat - Ex: 1, 2, 3.
* Numeric navigation showing in unordered list. 
* Enable/Disable pagination by ajax


= Creating Template =
Templates are designed using Shortcodes. You can create a simple list just showing post title and linked to the post page, or you can display complex list using any of the information relating to post. Some of the available shortcodes are - 

* post thumbnail
* post categories
* post tags
* post custom taxonomy terms
* post author name / links / avatar
* post publish time
* post modified time
* post excerpt
* post content
* post meta value (multiple times, with multiple meta keys)
* media thumbnail


Check all of the available shortcodes on <a href="http://w4dev.com/w4-plugin/w4-post-list">W4 Post List Plugin Page</a>.


= Check Example =
* <a href="http://w4dev.com/wp/w4-post-list-examples#w4pl-list-1381">Simple Posts List</a>
* <a href="http://w4dev.com/wp/w4-post-list-examples#w4pl-list-1391">Media List</a>
* <a href="http://w4dev.com/wp/w4-post-list-examples#w4pl-list-1401">Year/Month Archive</a>


= What's new latest version (1.6 +) =
* New: Tinymce button to create a list in seconds right from the post/page editor
* New: Group posts by parent, author, categories, tags, year, month, year month.
* New: Added loading state upon changing post type on options page
* New: Option panel
* Removed: Posts with categories, Only categories.


Happy using this Plugin ? Please Vote !! Your vote really makes the difference.


= Shortcode =
Use shortcode "postlist" to show your list inside post/page content area. Example: <code>[postlist 1]</code> will show the list having id "1".


== Installation ==

1. Upload zip to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Find W4 post list menu under Posts Menu. Add and manage post list from there.
4. User shortcode [postlist] with id, or copy shortcode from the config page.



== Frequently Asked Questions ==

Please ask any question regarding the plugin - <a href="http://wordpress.org/support/plugin/w4-post-list">here</a>.

If you find this plugin useful, please add your <a href="http://wordpress.org/support/view/plugin-reviews/w4-post-list">review</a>.


== Screenshots ==

1. Config Panel
2. Preview 1
3. Preview 2



== Changelog ==

= 1.7 =
* Introducing: Tinymce button. Now, create a post list right from the post/page edit screen. That means more Independence !!!
= 1.6.9 =
* Improved: Tax Query selection has been improved, that means more Independence !!!
= 1.6.8 =
* Improved: Meta Query selection has been improved.
= 1.6.7 =
* New: Filter by post format
= 1.6.6 =
* New: Meta query feature. Do query by multiple meta key/value with comparement
= 1.6.5 =
* New: Post author email shortcode
* New: Post item number shrtcode
* New: Copy list shortcode directly from the lists table page
= 1.6.4 =
* New: Group posts by parent, author, categories, tags, year, month, year month.
* New: Shortcodes to display group information.
= 1.6.3 =
* New: Added shortcode button to insert shortcode quickly, similar as tinymce button
= 1.6.1 =
* New: Added loading state upon changing post type on options page
= 1.6 =
* New / Clean Option Panel
* A Lot more shortcodes
* Add Css and JS for specific list

= 1.5.7 =
* Template Tag Changed.
= 1.5.6 =
* Important Updates.
= 1.5.5 =
* Manage How to select the post image. Lots of options.
= 1.5.4 =
* Include Post Thumbnail/Image in the list.
= 1.5.3 =
* Category Post selection problem fixed.
= 1.5.1 =
* Post Comment Count and Comment url tag added.
* Fixed Html Template input issue.
= 1.5 =
* Stable Version
= 1.4.6 =
* A lot more template tag to arrange your post list with more flexibility.
= 1.4.5 =
* The show future posts bug has been solved. From now on, there won't be any selection problems.
* Sliding JavaScript has been updated to match the latest jQuery.
= 1.4 =
* Its been a total change in this version. New Management page added for Admins to assign capability for creating/managing post list. If a user has role to only create and manage his own list, he won't be able to see/edit/delete the rest of post list option page.
* Post list database management process. Admin can drop or install the plugin database on click. People are recommended to do removal and install old database once if they have upgraded to v.1.4 from a old once. When database table is dropped, plugin keeps the old data and prompt for synchronize it once after installation of plugin database table. Only admin can have this feature.
* HTML Design template. You can design you list HTML template. For instruction, follow <a href="http://w4dev.com/wp/w4-post-list-examples/">http://w4dev.com/wp/w4-post-list-examples/</a>
= 1.3.6 =
* List only posts by category.
* Show/Not show future posts.
* Post lists with maximum posts to show.
* One click select/deselect all posts.
= 1.3.4 =
* Option Saving Bug fixed
= 1.3.3 =
* Read more link after content.
* Jquery effects to manage the list option more easily.
* Changed post order by to an easier method.
* A new "post select by" option.
= 1.3.2 =
* Easier post sorting options.
= 1.3.1 =
* Changed parameter to easily understand options. Please deactivate and reactive plugin after update if you face any problem.
* Added template tag to show a specific post list at any place of your theme.
= 1.3 =
* Show list also on inside post content, page content.
= 1.2.7 =
* Enabled multi-lingual functionality.
= 1.2.5 =
* Show/hide post list with Sliding effect while showing posts with category
* Bug Fixed.
* Added new option to show last post-modified time.
= 1.2.4 =
* Fixed post list bugs.
= 1.2.3 =
* Changed the posts selection method.
* Changed the preview style.
= 1.2.2 =
* Changed past preview style. Update for using new listing style.
= 1.2.1 =
* Please update to Version 1.2.1 which fixed the category selection bugs from widget page
= 1.2 =
* Please update to Version 1.2 for showing the actual excerpt length and removing it from other contents.
= 1.1 =
* Please update to 1.1, to avoid the simple category count bug and enjoy the multi widget functionality.
= 1.0 =


== Upgrade Notice ==
= 1.7 =
* Introducing: Tinymce button. Now, create a post list right from the post/page edit screen. That means more Independence !!!
= 1.6.9 =
* Improved: Tax Query selection has been improved, that means more Independence !!!
= 1.6.8 =
* Improved: * Improved: Meta Query selection has been improved.
= 1.6.7 =
* New: Filter by post format
= 1.6.6 =
* New: Meta query feature. Do query by multiple meta key/value with comparement
= 1.6.5 =
* New: Post author email shortcode
* New: Post item number shrtcode
* New: Copy list shortcode directly from the lists table page
= 1.6.4 =
* New: Group posts by parent, author, categories, tags, year, month, year month.
* New: Shortcodes to display group information.
= 1.6.3 =
* New: Added shortcode button to insert shortcode quickly, similar as tinymce button
= 1.6.1 =
* New: Added loading state upon changing post type on options page
= 1.6 =
* New / Clean Option Panel
* A Lot more shortcodes
* Add Css and JS for specific list
* The Category list has been removed. If you think you need that, stick with the older version

= 1.5.7 =
* Template Tag Changed.
= 1.5.6 =
* Important Updates.
= 1.5.5 =
* Manage How to select the post image. Lots of options.
= 1.5.4 =
* Include Post Thumbnail/Image in the list.
= 1.5.3 =
* Category Post selection problem fixed.
= 1.5.1 =
* Post Comment Count and Comment url tag added.
* Fixed Html Template input issue.
= 1.5 =
* Stable Version

= 1.4.6 =
* A lot more template tag to arrange your post list with more flexibility.
= 1.4.5 =
* The show future posts bug has been solved. From now on, there won't be any selection problems.
* Sliding JavaScript has been updated to match the latest jQuery.
= 1.4 =
* Its been a total change in this version. New Management page added for Admins to assign capability for creating/managing post list. If a user has role to only create and manage his own list, he won't be able to see/edit/delete the rest of post list option page.
* Post list database management process. Admin can drop or install the plugin database on click. People are recommended to do removal and install old database once if they have upgraded to v.1.4 from a old once. When database table is dropped, plugin keeps the old data and prompt for synchronize it once after installation of plugin database table. Only admin can have this feature.
* HTML Design template. You can design you list HTML template. For instruction, follow <a href="http://w4dev.com/wp/w4-post-list-examples/">http://w4dev.com/wp/w4-post-list-examples/</a>
= 1.3.6 =
* List only posts by category.
* Show/Not show future posts.
* Post lists with maximum posts to show.
* One click select/deselect all posts.
= 1.3.4 =
* Option Saving Bug fixed
= 1.3.3 =
* Read more link after content.
* Jquery effects to manage the list option more easily.
* Changed post order by to an easier method.
* A new "post select by" option.
= 1.3.2 =
* Easier post sorting options.
= 1.3.1 =
* Changed parameter to easily understand options. Please deactivate and reactive plugin after update if you face any problem.
* Added template tag to show a specific post list at any place of your theme.
= 1.3 =
* Show list also on inside post content, page content.
= 1.2.7 =
* Enabled multi-lingual functionality.
= 1.2.5 =
* Show/hide post list with Sliding effect while showing posts with category
* Bug Fixed.
* Added new option to show last post-modified time.
= 1.2.4 =
* Fixed post list bugs.
= 1.2.3 =
* Changed the posts selection method.
* Changed the preview style.
= 1.2.2 =
* Changed past preview style. Update for using new listing style.
= 1.2.1 =
* Please update to Version 1.2.1 which fixed the category selection bugs from widget page
= 1.2 =
* Please update to Version 1.2 for showing the actual excerpt length and removing it from other contents.
= 1.1 =
* Please update to 1.1, to avoid the simple category count bug and enjoy the multi widget functionality.
= 1.0 =
