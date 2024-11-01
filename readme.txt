=== Statbadge ===
Contributors: Teledir
Donate link: http://www.teledir.de/donate
Tags: stats, wordpress, alexa, pagerank, posts, tags, categories, statistics, badge, widget, widgets, statistics, sidebar, visits, visitors, post, posts, comments, tags, activity, admin, plugin, links, google, page, twitter, tweet, counter, twittercounter
Requires at least: 2.5
Tested up to: 2.7
Stable tag: 1.3

Statbadge displays selected informations like post count, number of comments, pagerank, alexa ranking and many more in the sidebar of your blog. 

== Description ==

IMPORTANT: If you update from a version prior to 0.5, you have to reenable the sidebar widget!

Statbadge displays up to 16 different statistic values about your blog. You can select each one of them to be shown or not. You can integrate Statbadge everywhere in your template or via the sidebar widget.

Check out more [Wordpress Plugins](http://www.teledir.de/wordpress-plugins "Wordpress Plugins") and [Widgets](http://www.teledir.de/widgets "Widgets") brought to you by [Teledir](http://www.teledir.de "Teledir").

== Installation ==

1. Unpack the zipfile statbadge-X.y.zip
1. Upload folder `statbadge` to the `/wp-content/plugins/` directory
1. Make cache directory writeable chmod 777
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Place `<?php if(function_exists('statbadge_display'))statbadge_display(); ?>` in your template or use the sidebar widgets.

== Frequently Asked Questions ==

= Does Statbadge displays backlinks =

Yes, but since Google disabled backlink checking, the value comes from technorati and is not very exact.

== Screenshots ==

1. Statbadge example english, you can customize the colors, border etc.
2. Statbadge example german, you can customize the colors, border etc.

== Change Log ==

* v1.3 27.04.2010 minor xhtml issue fix
* v1.2 21.07.2009 added user count support, small url param fix
* v1.1 08.07.2009 fixed some spelling errors
* v1.0 07.07.2009 small caching improvement
* v0.9 18.06.2009 very small security improvement
* v0.8 16.06.2009 removed use of str_split to support PHP < 5
* v0.7 16.06.2009 added twitter counter support
* v0.6 10.06.2009 small translation fix
* v0.5 09.06.2009 fixed activity rank calculation, fixed double #id in widget mode
* v0.4 08.06.2009 sorry, svn trunk mixup - don't use 0.3!
* v0.3 07.06.2009 small translation fix
* v0.2 06.06.2009 fixed widget title, deeplink
* v0.1 03.06.2009 initial release

