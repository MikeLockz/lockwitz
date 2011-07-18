<?php
/*
	Widget Theme for HL Twitter
	   To change this theme, copy hl_twitter_widget.php
	   to your current theme folder, do not edit this
	   file directly.
	
	Available Properties:
		$before_widget
		$after_widget
		$before_title
		$after_title
		$widget_title
		$show_avatars
		$show_powered_by
		$num_tweets: how many tweets to show
		$tweets: array of $tweet
		$tweet: object representing a tweet
			$tweet->twitter_tweet_id
			$tweet->tweet
			$tweet->lat
			$tweet->lon
			$tweet->created
			$tweet->reply_tweet_id
			$tweet->reply_screen_name
			$tweet->source
			$tweet->screen_name
			$tweet->name
			$tweet->avatar
		$user: represents the Twitter user (ONLY SET IF SHOWING A SINGLE USERS TWEETS!)
			$user->twitter_user_id
			$user->screen_name
			$user->name
			$user->num_friends
			$user->num_followers
			$user->num_tweets
			$user->registered
			$user->url
			$user->description
			$user->location
			$user->avatar			
*/
?>

<style type="text/css">
/*
	This is a basic set of rules designed to work well with
	the Twenty Ten theme provided as part of WordPress 3.0.
*/
#main .widget-area ul.hl_recent_tweets {
	clear: both;
	list-style: none;
	margin: 0;
	padding: 6px 0 0;
}

.hl_recent_tweets li {
	<?php if($show_avatars): ?>
	background-repeat: no-repeat;
	background-position: 0 3px;
	min-height: 48px;
	height: auto !important;
	height: 48px;
	padding-left: 54px;
	<?php endif; ?>
	margin-bottom: 6px;
}
.hl_recent_tweets p {
	margin-bottom: 0;
}
.hl_recent_tweets span {
	display: block;
	font-size: 10px;
}
.hl_recent_tweets_none {
	margin-bottom: 0;
}
.hl_recent_tweets_meta {
	font-size: 10px;
	color: #999;
	font-style: italic;
}
</style>

<?php echo $before_widget; ?>

<?php echo $before_title; ?>
	<?php echo $widget_title; ?>
<?php echo $after_title; ?>

<?php if($num_tweets>0): ?>
	<ul class="hl_recent_tweets">
		<?php foreach($tweets as $tweet): ?>
			<li <?php if($show_avatars): ?>style="background-image:url(<?php echo hl_twitter_get_avatar($tweet->avatar); ?>)"<?php endif; ?>>
				<p>
					<?php echo hl_twitter_show_tweet($tweet->tweet); ?>
					<span class="meta">
						<a href="http://twitter.com/<?php echo hl_e($tweet->screen_name); ?>/status/<?php echo hl_e($tweet->twitter_tweet_id); ?>">
							<?php echo hl_time_ago($tweet->created); ?> ago
						</a>
					</span>
				</p>
			</li>
		<?php endforeach; ?>
	</ul>
<?php else: ?>
	<p class="hl_recent_tweets_none">There are no recent tweets.</p>
<?php endif; ?>


<?php if($show_more_link or $show_powered_by): ?>
<p class="hl_recent_tweets_meta">
	<?php if($show_more_link): ?><a href="<?php echo hl_twitter_get_archives_root(); ?><?php if($single_user) echo '/'.$user->screen_name; ?>">View more tweets</a><?php endif; ?>
	<?php if($show_more_link and $show_powered_by): ?>|<?php endif; ?>
	<?php if($show_powered_by): ?>Powered by <a href="http://hybridlogic.co.uk/hl-twitter">HL Twitter</a><?php endif; ?>
</p>
<?php endif; ?>

<?php echo $after_widget; ?>
