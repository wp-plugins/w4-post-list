=== Plugin Name ===
Contributors: sajib1223, Shazzad Hossain Khan
Donate link: http://w4dev.com/
Tags: tabset, shortcode, post/page
Requires at least: 3.0
Tested up to: 3.0.3
Stable tag: 1.3.3

== Description ==
With W4 development's Post/Page Tabset you can arrange your content inside a page with flexible navigation.

= The tabset comes with 2 style =

1. First tabset will act like an anchor to each tab content area.(every tab content area will start with a new the tab navigation area).
2. Second will work as like "ui tabs" hiding the inactive tabs.

= Extra =

Although you can show your post custom field value inside your post or page with this tabset plugin. Use short code "custom" for displaying a custom field content in post content area. This shortcode will be very usefull where you want your content to be shown as it is written, without any touch of wordpress migration.

= Usage =

* For inserting a tabset, use shortcode "tabset". example:[tabset][/tabset]
* For inserting a tab in a tabset, use shortcode "tabs" and its attribute "tabname". example:[tabs tabname="Your tab name"]Tab inside content[/tabs]
* Tabs should be in a Tabset area. So the structure should look like:

<pre>
[tabset]
[tabs tabname="Tab1"]Tab1 content[/tabs]
[tabs tabname="Tab2"]Tab2 content[/tabs]
[/tabset]
</pre>

= Please make sure you have written and checked the shortcodes appropriately. =


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
= 1.3.3
* Changed the default tabset style

== Upgrade Notice ==
= 1.3 =
* Upgrade to Version .1.3 to get opprtunity to use shortcodes for previewing custom field value inside post.
= 1.3.1 =
* Added important notes and schreenshots.
= 1.3.2 =
* Handled the directory error.
* Added a new tabset style - the ui-style.
= 1.3.3
* Changed the default tabset style