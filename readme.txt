=== Plugin Name ===
Contributors: Shazzad Hossain Khan
Donate link: http://w4dev.com/
Tags: tabset, shortcode, post/page
Requires at least: 3.0
Tested up to: 3.0.2
Stable tag: 1.3

Here is a short description of the plugin.  This should be no more than 150 characters.  No markup here.

== Description ==
Post/Page Tabset is a nice plugin to arrange your content inside a page with flexible navigation. Although you can show your post custom field value inside your post or page with this tabset plugin.

With this you can easily embed tabs content in your post or page content area. The tabset will act like an anchor to each tab content area. Every tab content area will start with the tab navigation area. So, it doesn't matter which content area you are in, you can navigate easily.

This plugin also contains another short code "custom" for displaying a custom field content in post content area. This shortcode will be very usefull where you want your content to be shown as it is written, with any touch of wordpress migration

== Installation ==
1. Upload `tabset.zip` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==
= Is this work on pages also ? =
Yap. It works same on both posts or pages.

= How many tabset i can use in a Post/Page ?
As much as you want.

== Screenshots ==
1. tabset.gif.

== Changelog ==
= 1.3 =
* Added shortcode "custom" to show custom field value inside post although inside tabset.

== Upgrade Notice ==
= 1.3 =
* Upgrade to Version .1.3 to get opprtunity to use shortcodes for previewing custom field value inside post.

== How to use ==
    * For inserting a tabset, use shortcode "tabset". example:[tabset][/tabset]
    * For inserting a tab in a tabset, use shortcode "tabs" and its attribute "tabname". example:[tabs tabname="Your tab name"]Tab inside content[/tabs]
    * Tabs should be in a Tabset area. So the structure should look like:
      [tabset]
      [tabs tabname="Tab1"]Tab1 content[/tabs]
      [tabs tabname="Tab2"]Tab2 content[/tabs]
      [/tabset]
    * Please make sure you have written and checked the shortcodes appropriately.

    * Post/Page tabset support another shortcode "custom". For showing your post/page custom field value you can use shortcode "custom". example: [custom key="Your-custom-key-name"]
    * Shortcode "custom" receive one parameter "key". "key" is your custom field id/key name for current post.