<?php
/*
Plugin Name: HL Twitter
Plugin URI: http://hybridlogic.co.uk/code/wordpress-plugins/hl-twitter/
Description: Pulls in all tweets and optionally sends new tweets
Author: Luke Lanchester
Version: 2011.5.21
Author URI: http://www.lukelanchester.com/
Created: 2010-07-25
Modified: 2010-05-21
*/
@session_start(); // needed for oauth

define('HL_TWITTER_LOADED', true);
define('HL_TWITTER_DB_PREFIX', $table_prefix.'hl_twitter_');
define('HL_TWITTER_DIR', plugin_dir_path(__FILE__)); # inc /
define('HL_TWITTER_URL', plugin_dir_url(__FILE__)); # inc /
define('HL_TWITTER_AVATAR_CACHE_TTL', 3600); # How long should avatars be cached for, in seconds
define('HL_TWITTER_API_MAX_TWEET_LIMIT', 3200); # Twitter only keeps n tweets :(
define('HL_TWITTER_API_TWEETS_PER_PAGE', 200); # max num tweets per request
define('HL_TWITTER_CRON_KEY', ''); # ?hl_twitter_cron=KEY, deprecated, leave blank to disable
define('HL_TWITTER_TWEET_FORMAT', 'hl_twitter_tweet_format'); # Default format used in post box
define('HL_TWITTER_UPDATE_FREQUENCY', 'hl_twitter_update_frequency'); # Interval to check in
define('HL_TWITTER_SCHEDULED_EVENT_ACTION', 'hl_twitter_cron_handler'); # For WordPress Scheduled Event handler
define('HL_TWITTER_AUTO_TWEET_SETTINGS', 'hl_twitter_auto_tweet');
define('HL_TWITTER_AUTO_TWEET_POSTMETA', 'hl_twitter_has_auto_tweeted');
define('HL_TWITTER_DEFAULT_TWEET_FORMAT', 'I just posted %title%, read it here: %shortlink%');
define('HL_TWITTER_ARCHIVES_SLUG_KEY', 'hl_twitter_archives_slug');
define('HL_TWITTER_ARCHIVES_DEFAULT_SLUG', 'hl-twitter'); # URL fragment to identify Twitter page e.g. mysite.com/hl-twitter/Username/2011/03/27
define('HL_TWITTER_TRANSIENT_DISTINCT_MONTHS_KEY', 'hl_twitter_distinct_months_archive');
define('HL_TWITTER_OAUTH_CONSUMER_KEY', 'qeqJ3iEpoY9xVUSL2ZSIw'); # HL Twittter Application
define('HL_TWITTER_OAUTH_CONSUMER_SECRET', 'tL9zLT0y8zbMFUUKOXRiXKc3DYtLckNgFOpvYDm0rc'); # Not critical via OAuth
define('HL_TWITTER_OAUTH_CALLBACK', get_bloginfo('url').'/wp-admin/admin.php?page=hl_twitter&action=oauth_callback');
define('HL_TWITTER_OAUTH_TOKEN','hl_twitter_outh_token');
define('HL_TWITTER_OAUTH_TOKEN_SECRET','hl_twitter_outh_token_secret');

require_once HL_TWITTER_DIR.'admin.php'; # Admin views + functionality
require_once HL_TWITTER_DIR.'archive.php'; # Archive view
if(!class_exists('EpiCurl')) require_once HL_TWITTER_DIR.'api/EpiCurl.php'; # cURL wrapper
if(!class_exists('EpiOAuth')) require_once HL_TWITTER_DIR.'api/EpiOAuth.php'; # OAuth wrapper
if(!class_exists('EpiTwitter')) require_once HL_TWITTER_DIR.'api/EpiTwitter.php'; # Twitter API
require_once HL_TWITTER_DIR.'functions.php'; # Utility functions + helpers
require_once HL_TWITTER_DIR.'import.php'; # Cron + import functions
require_once HL_TWITTER_DIR.'widget.php'; # Widget class
require_once HL_TWITTER_DIR.'hl_feedback.php'; # HL Feedback Library

add_action('init', 'hl_twitter_init'); # On load, for (old) cron jobs
add_filter('query_vars', 'hl_twitter_add_query_vars');
add_action('parse_request', 'hl_twitter_rewrite_parse_request'); # Intercept Archive page requests
add_action(HL_TWITTER_SCHEDULED_EVENT_ACTION, 'hl_twitter_cron_handler'); # Used by the WordPress Event Scheduler
add_filter('cron_schedules','hl_twitter_cron_schedules'); # Add custom time intervals
add_action('admin_menu', 'hl_twitter_admin_menu'); # Add menu pages
add_action('publish_post', 'hl_twitter_publish_post'); # When a post is published, auto-tweet
add_action('wp_dashboard_setup', 'hl_twitter_add_dashboard_widget'); # Add dashboard widget
add_action('widgets_init', create_function('', 'return register_widget("hl_twitter_widget");')); # Add widget
add_shortcode('hl-twitter', 'hl_twitter_display_shortcode'); # Add shortcode
register_activation_hook(__FILE__,'hl_twitter_install');
register_deactivation_hook(__FILE__,'hl_twitter_uninstall');

