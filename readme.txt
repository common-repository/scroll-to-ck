=== Scroll To CK ===
Contributors: ced1870
Tags: scroll, onepage, go to top
Requires at least: 4.5
Tested up to: 5.0.0
Stable tag: 1.1.3
License: GPLv2 or later

Scroll To CK allows you to scroll your page with you links and add a go to top button on scroll.

== Description ==

<p>Scroll To CK allows you to scroll your page with you links and add a go to top button on scroll.</p>

<h3>Features</h3>

Add the CSS class 'scrollTo' to your menu item, or any link in your page to scroll to the desired html ID.

* Scroll to any element in the page (using its ID)
* Works on any link, even in any menu
* Easy to use, just add a css class to the link
* Option to activate the Go To Top button
* Custom options for each link (duration and offset)
* Option to hide the Go To Top button under a custom resolution value


<h3>How to use it</h3>

The only thing you have to do is to add the CSS class on your links. You can do it in a menu item with the optional field "CSS class", or use a link anywhere in your page by adding it the class. Example of a link source code :

&lt;a href="#idoftheelement" class="scrollTo" /&gt;Go to Section 1&lt;/a&gt;

<p>Get more informations and see the demo on https://www.ceikay.com</p>


== Installation ==

1. Unzip the package `scroll-to-ck.zip`
2. Upload the folder `scroll-to-ck` to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

== Screenshots ==

== Changelog ==

= 1.1.2 =
* Add option to hide the Go To Top button on mobile with a custom resolution value
* Add data-speed and data-offset attribute options on each link to set custom settings per link

= 1.1.1 =
* Add z-index to avoid the button to be hidden

= 1.1.0 =
* Refactor the code to be used with CeiKay.com

= 1.0.2 =
* Use better method to load inline script
* Load CSS code in the footer for better SEO / performance

= 1.0.1 =
* Fix PHP warning

= 1.0.0 =
* First issue
