=== Author Categories ===
Contributors: kdiweb
Donate link: http://www.makesites.cc/donate.php
Tags: categories, author, post count, menu
Requires at least: 2.5
Tested up to: 2.7.1
Stable tag: 1.0

A wrapper of the default 'wp_list_categories' to present an author's menu, when in the author pages. 

== Description ==

Numerous people are using Wordpress for blogging and in many cases there are more than one authors on a website. It's uncomfortably surprising that Wordpress doesn't support out of the box a category menu for each author separately.

I was looking for this feature online for my personal need but couldn't find it on any plugin. To be exact I was using a modified version of <a href="http://www.makesites.cc/programming/by-makis/level10-blog-matrix-plugin-for-wordpress-2x/" class="external-link" target="_blank">another similar plugin</a> but that was until version 2.3 where the database structure changed for Wordpress and it simply stopped working.

As I saw it, it wasn't worth fixing old, deprecated (and highly cluttered) code, and there weren't any other solutions out there, so I decided to create a new plugin. Thankfully the new database and API made it as easy as I had hoped for.

I ended up with this plugin, that was created as a wrapper of the default category menu.

It is lightweight and can easily plug-in, plug-out. Furthermore, being an extension of the default category menu, means that none of the functionality (sorting, post count etc.) is lost. In fact it can be easily extended and could support future versions of the blogging platform for years to come.


== Installation ==

1. Upload the file "wp_author_categories.php" in your "wp-content/plugins" directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Inlude the function "wp_author_categories()" in your template files

More detailed information is provided in the "Usage" section of this document. 

== Frequently Asked Questions ==

= Known limitations? =

To mention some of the things this plugin (in it's current version) won't do for you:
* It only works with posts (not pages)
* The markup created is directed for the list-type menu (not the drop down version) with simply parenthesis around the post count. 
* The author is added as a standard GET variable and does not support the SE friendly URLs (although it still works)

= Wishlist? =

This is naturally an extension of the current limitations...
* Add some sort of caching mechanism so all the post count calculations are not done on every page re-load
* Externalise the menu structure to a template file
* Support other versions of the category menu (dropdown, tag cloud...)
* Ability to work with any sort of customized URLs


= Need more help? Found a bug? Have an idea? =

Thank you for using this piece of software. Don't hesitate to respond with any comments or suggestions. 

Contact me at [Make Sites](http://www.makesites.cc/contact/ "This is yet another Make Sites production")

You can always find the latest version of this plugin here: 
http://www.makesites.cc/projects/wp_author_categories

== Screenshots ==

== Usage ==

After you upload an activate the plugin through your admin panel, all you need to do is call the custom function wp_author_categories() that will create an author's menu for you. Notice that this only works in author pages and will revert to the default category menu in any other case. 

If you are using the default template, you can easily find this line in "sidebar.php" (that displays the category menu):
			<?php wp_list_categories('show_count=1&title_li=<h2>Categories</h2>'); ?>

You can replace it with this condition that will use the wp_author_categories() function instead, when you are visiting the author pages

			<?php 
			/*
			* First write down the arguments you want to use for you menu and store them in a variable. 
			* These are the same for the default menu ("wp_list_categories") and the author menu ("wp_author_categories")
			* you can find more information on the options you can use here: http://codex.wordpress.org/Template_Tags/wp_list_categories
			*/
			$args = 'show_count=1&title_li=<h2>Categories</h2>';
			if($author){
				wp_author_categories($args); 
			} else{
				wp_list_categories($args); 
			}
			?>

Alternatively, and if you already have an "author.php" file in your template folder, you can simply rename "wp_list_categories()" to "wp_author_categories()", passing the same arguments. 

To uninstall, it's as easy as doing the reverse actions. Delete all references of the wp_author_categories() function from your template files and uninstall the plugin through that Wordpress's admin panel.

== License ==

This work is released under the terms of the GNU General Public License:
http://www.gnu.org/licenses/gpl-2.0.txt
