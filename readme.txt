=== W4 Content tabset ===
Plugin Name: W4 Content tabset
Contributors: sajib1223, Shazzad Hossain Khan
Donate link: http://w4dev.com/w4-plugin/post-page-custom-tabset-shortcode/?ref=wp_tabset
Tags: tabset, shortcode, jquery
Requires at least: 3.0
Tested up to: 3.1.1
Stable tag: 1.4

Wordpress plugin for creating jquery tabs with shorcode inside posts and pages content. Also capable to show your custom field values in a post or page content area by shortcode.

== Description ==

 Visit <a href="http://w4dev.com/w4-plugin/post-page-custom-tabset-shortcode/">Plugin page</a> for full documentation, usage and walk-through.


= Upgrade Notice =
* Multiple tabset style (V 1.4).
* Removed preview box on the wp admin page (V 1.4).

* Added a preview box on the wp admin tabset page (V 1.3.8).
* Added multi tabset capabilities on a single post or page with same tabname (V 1.3.8).
* Important fix(V 1.3.7).
* Added tabset menu event (V 1.3.6).
* Fixed menu li Css issue (V 1.3.6).
* Fixed some Css issue (Recommend by Matt Mansfield).
* The Anchor tabset has been removed from Version 1.3.4, also the style. It has beem replaced by a new option, "effect".


With W4 development's Post/Page Tabset you can arrange your content inside a page with flexible navigation.


= The tabset comes with 3 Effects =

1. Normal Show/Hide.
2. Slide (Up/Down)
3. Fade effect.


= Extra =

Although you can show your post custom field value inside your post or page with this tabset plugin. Use short code "custom" for displaying a custom field content in post content area. This shortcode will be very usefull where you want your content to be shown as it is written, without any touch of wordpress migration.


= Usage =

* For inserting a tabset, use shortcode "tabset". example:[tabset][/tabset]
* For inserting multiple tabset on same page or post, give your tabset an unique id. example:[tabset id="1"][/tabset] or [tabset id="abc"][/tabset].
* For inserting a tab in a tabset, use shortcode "tabs" and its attribute "tabname". example:[tabs tabname="Your tab name"]Tab inside content[/tabs]
* Tabs should be in a Tabset area. So the structure should look like:

<pre>
[tabset]
[tabs tabname="Tab1"]Tab1 content[/tabs]
[tabs tabname="Tab2"]Tab2 content[/tabs]
[/tabset]

[tabset id="second"]
[tabs tabname="Tab1"]Tab1 content[/tabs]
[tabs tabname="Tab2"]Tab2 content[/tabs]
[/tabset]

[tabset style="my-style"]
[tabs tabname="Tab1"]Tab1 content[/tabs]
[tabs tabname="Tab2"]Tab2 content[/tabs]
[/tabset]
</pre>

* For displaying custom field value use shortcode custom.
Example:

<pre>
[custom key="your custom key name"]
</pre>

Please make sure you have written and checked the shortcodes appropriately.




== Installation ==
1. Upload `tabset.zip` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==
= Is this work on pages also ? =
Yap. It works same on both posts or pages.

= How many tabset i can use in a Post/Page ? =
As much as you want.

== Screenshots ==
1. Tabset screenshot .

== Changelog ==
= 1.3 =
* Added shortcode "custom" to show custom field value inside post although inside tabset.
= 1.3.1 =
* Added important notes and schreenshots.
= 1.3.2 =
* Added a new tabset style - the ui-style.
= 1.3.3 =
* Changed the default tabset style
= 1.3.4 =
* The Anchor tabser has been removed
* Added a new option, "effect".
= 1.3.5 =
* Fixed some Css issue.
= 1.3.6 =
* Added tabset menu event.
* Fixed menu li Css issue.
= 1.3.7 =
* Important fix(V 1.4.7).
= 1.3.8 =
* Added a preview box on the wp admin tabset page.
* Added multi tabset capabilities on a single post or page with same tabname.
= 1.4 =
* Multiple tabset style.
* Removed preview box on the wp admin page.


== Upgrade Notice ==
= 1.3 =
* Upgrade to Version .1.3 to get opprtunity to use shortcodes for previewing custom field value inside post.
= 1.3.1 =
* Added important notes and schreenshots.
= 1.3.2 =
* Handled the directory error.
* Added a new tabset style - the ui-style.
= 1.3.3 =
* Changed the default tabset style
= 1.3.4 =
* Fully compatible with wordpress 3.0.3
= 1.3.5 =
* Fixed some Css issue (Recommend by Matt Mansfield).
= 1.3.6 =
* Added tabset menu event.
* Fixed menu li Css issue.
= 1.3.7 =
* Important fix(V 1.4.7).
= 1.3.8 =
* Added a preview box on the wp admin tabset page.
* Added multi tabset capabilities on a single post or page with same tabname.
= 1.4 =
* Multiple tabset style.
* Removed preview box on the wp admin page.