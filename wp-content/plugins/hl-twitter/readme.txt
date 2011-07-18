=== HL Twitter ===
Contributors: Dachande663
Donate link: http://hybridlogic.co.uk/code/wordpress-plugins/hl-twitter/
Tags: twitter, tweet, post, auto tweet, social, social media, backup, hybridlogic, archive, shortcode, widget
Requires at least: 2.9.2
Tested up to: 3.1
Stable tag: trunk

HL Twitter stores tweets from multiple accounts and displays them via widget, archives and shortcodes as well as auto-tweeting new posts.

== Description ==

HL Twitter lets you display your tweets as a widget in your sidebar or even browse your entire Twitter history right on your blog. But it also does a whole lot more. You can track multiple Twitter accounts and store all of the tweets on your blog indefinitely (currently Twitter only keep your 3,200 most recent tweets) as well as pulling in any tweets that you reply to for future reference. You can then tweet from your Dashboard or have HL Twitter automatically tweet your new posts with a customisable message.

== Installation ==

1. Upload hl_twitter directory to your /wp-content/plugins directory
2. Activate plugin in WordPress admin
3. In WordPress admin, go to Twitter. You will be asked to link the plugin to your Twitter account.
4. Add the user(s) you wish to store tweets for. You can also import their most recent tweets.
5. To tweet from within WordPress visit the WordPress Admin Dashboard or look at the bottom of a Post page

To modify the widget theme:

1. Copy the hl_twitter_widget.php file from /wp-content/plugins/hl-twitter to /wp-content/themes/*your-current-theme*/
2. Edit the new hl_twitter_widget.php file in your theme directory
3. You can now update the plugin as normal and your changes will not be overwritten


To modify the archive theme:

1. Copy the hl_twitter_archive.php file from /wp-content/plugins/hl-twitter to /wp-content/themes/*your-current-theme*/
2. Edit the new hl_twitter_archive.php file in your theme directory
3. You can now update the plugin as normal and your changes will not be overwritten

To modify the shortcode theme:

1. In your current theme directory (e.g. /wp-content/themes/twentyten/) make a file called functions.php if it does not already exist.
2. In functions.php create a function called hl_twitter_shortcode()
3. This function will be passed two variables: $tweets and $num_tweets
4. Generate the necessary HTML and *return it*, DO NOT ECHO.





== Frequently Asked Questions ==

= How do I display a tweet in my post with Shortcodes? =

HL Twitter supports supports displaying tweets within a post or page using Shortcodes. To do so, use the following tag:

[hl-twitter]

By default, this will show the most recent tweet as found by HL Twitter. To change this you can use the following options:

* num: the number of tweets to display (optional, default 1)
* tweet: the ID of a specific tweet e.g. for http://twitter.com/Username/status/123456 use 123456 (optional)
* user: the Twitter ID of a specific user, this can be found in the URL on the edit user page in HL Twitter (optional, not needed if using tweet_id)
* search: will show all tweets that match the specified search term (optional)
* year: show only tweets made in this year e.g. 2011
* month: show only tweets made in this month e.g. 3
* day: show only tweets made on this day e.g. 27

Examples:

* [hl-twitter search="football" num=5] Show the first 5 tweets mentioning football
* [hl-twitter user=12345 year=2011 month=3] Show a single tweet from this user in March 2011

= How do I change the Widget/Archive Page/Shortcode? =

Please look look at the Installation tab which shows how to override the default styling in HL Twitter without losing your changes on each update.

= The link to Twitter button is stuck on loading / never loads? =

You must make sure that your server supports cURL, and more explicitly multi_curl, in PHP. Due to the unique nature of each server I can't give more specific advice, so Google is your best bet to get more information.

= Why can I only import 3,200 tweets? =

Twitter currently limit access to only the 3,200 most recent tweets for an account. If they increase this limit, HL Twitter will also increase.

= Why aren't all my tweets being pulled in? =

Twitter limits applications to a set number of requests per hour. If you are tracking a lot of people you may hit this limit before HL Twitter has finished importing all new tweets.

= How do I enable auto-tweeting? =

Auto-tweeting, having HL Twitter tweet a new message whenever you publish a post or page, is disabled by default. To enable it go to HL Twitter -> Settings in your WordPress admin. You can also change the default text that is shown in the tweet. When publishing a new post or page, you will not be able to choose whether or not to tweet for this post.

= The Tweet Archive page doesn't load =

WordPress sometimes fails to load the link to the Twitter archive page, most commonly after installing or updating. To fix it, go to HL Twitter -> Settings and change the Archive Pages slug to something different e.g. my-tweets, then press Save. Finally go to Settings -> Permalinks and click Save there. This will force WordPress to check for new links, including HL Twitter.

= New tweets are not automatically imported after the initial import =

HL Twitter uses the internal Events system to periodically check for new tweets, unfortunately this does not work on some blogs. A manual solution is to set up a cron job (or equivalent) on your server. Please note that modifications made to hl_twitter.php may be overwritten by future plugin updates.

* Open /wp-content/plugins/hl-twitter/hl_twitter.php
* Find the line define('HL_TWITTER_CRON_KEY', '');
* Change to define('HL_TWITTER_CRON_KEY', 'my-secret-key'); changing my-secret-key to be a phrase only you know
* Open http://yourwebsite.com/?hl_twitter_cron=my-secret-key in your browser. This will trigger an automatic update. If the page says that tweets were imported, continue, otherwise get in touch with the plugin author via the contact form. (Do not worry if a line says errors were encountered, as long as tweets were saved to the database).
* Add a cron job to your server to load this URL however often you require.

= Some of my tweets have the wrong links! =

For example: http://twitter.com/Username/status/123456789 displays as http://twitter.com/Username/status/123456000.

This was an issue caused by certain versions of PHP truncating the IDs when it loaded them from Twitter. This was fixed in Version 2011.3.11, apologies to anyone who was affected by this issue.

== Screenshots ==

1. Example user list showing tweet, follower and friend counts.
2. Default widget styling with the WordPress TwentyTen theme.

== Changelog ==

= 2011.5.21 =
* Updated the Twitter library so HL Twitter should work on more blogs now

= 2011.5.5 =
* Changed the menu and widget names to HL Twitter to reduce confusion
* Tweets archive page now shows the tweets that were replied to (where saved)
* Added a donations button, thank you to everyone who has donated so far!

= 2011.3.13 =
* Added support for using Widgets without dynamic sidebars
* Added support for shortcodes

= 2011.3.12 =
* Fixed more Twitter ID issues, thanks for all the reports.

= 2011.3.11 =
* Added a brand new archive page system which lets you browse all the tweets stored by HL Twitter on your blog.
* General code maintenance
* FIXED: A critical bug caused by some versions of PHP json_encode where IDs were truncated e.g. 123456789 became 123456000. Very sorry to anyone who was affected by this.

= 2011.3.7 =
* Fixed deleting tweets

= 2011.3.1 =
* Auto-tweet now checks to make sure tweet isn't empty
* Added multi_curl support info

= 2010.7.3 =
* Initial development

= 2010.7.4 =
* Importer now loads all tweets for user

= 2010.7.18 =
* Importer now pulls multiple twitter accounts

= 2010.7.28 =
* Switch to OAuth
* Import now works asynchronously across users

= 2010.9.1 =
* Major bug fixes, admin design tweaks

= 2010.9.3 =

* First public release
* Added widget + controls
* Added WordPress event scheduling handlers

= 2010.9.12 =

* Added auto-tweet ability
* Added Feedback panel

= 2010.9.13 =

* Avatars are now resized and cached locally (thanks to Scotts for the heads up)

= 2010.9.15 =

* Emergency fix; a regression bug was present in 2010.9.13 that affected all plugin users.

= 2010.9.15b =

* Updated the auto-tweet feature to support more fields and improve performance
* Widget now has more options including setting a title and hiding avatars
* Added support for WordPress 2.9.2

== Upgrade Notice ==

= 2010.9.3 =
First public release

= 2010.9.15 =
Fixes a bug caused by 2010.9.13 update. Very sorry to anyone who downloaded the plugin and was affected in this interim period.