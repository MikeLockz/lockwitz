<?php
/*
	Archive page for HL Twitter
	   This archive page is designed to work in the 
	   WordPress TwentyTen theme.
	   To change this theme, copy hl_twitter_archive.php
	   to your current theme folder, do not edit this
	   file directly!
	
	Available Properties:
		$data->is_search
		$data->is_year
		$data->is_month
		$data->is_day
		$data->is_single_user
		
		$data->timestamp (if year set)
		$data->distinct_months
		$data->order_by
		$data->order
		$data->num_users
		$data->users
		
		$data->total_pages
		$data->current_page
		$data->has_previous_page
		$data->has_next_page
		
		$data->total_tweets
		$data->num_tweets
		$data->tweets: array of $tweet
		$tweet: object representing a tweet
			$tweet->twitter_tweet_id
			$tweet->tweet
			$tweet->lat
			$tweet->lon
			$tweet->created
			$tweet->source
			$tweet->screen_name
			$tweet->name
			$tweet->avatar
			# The following properties are available for replied to tweets (use $tweet->reply_tweet!='' to test)
			$tweet->reply_tweet_id
			$tweet->reply_tweet
			$tweet->reply_created
			$tweet->reply_source
			$tweet->reply_screen_name
			$tweet->reply_name
			$tweet->reply_avatar
			
*/
$months = array('','January','February','March','April','May','June','July','August','September','October','November','December');
get_header();
?>

<style type="text/css">
	.hl-twitter-archive-reply {
		margin:-12px 0 12px;
		color: #888;
	}
</style>


<div id="container">
<div id="content" role="main">

<h1 class="page-title">
	<?php if( $data->is_search ): ?>
		Tweets containing "<?php echo esc_attr($data->is_search); ?>"
	<?php elseif( $data->is_day ): ?>
		Tweets made on <?php echo date('l jS F Y', $data->timestamp); ?>
	<?php elseif( $data->is_month ): ?>
		Tweets made in <?php echo date('F Y', $data->timestamp); ?>
	<?php elseif( $data->is_year ): ?>
		Tweets made in <?php echo date('Y', $data->timestamp); ?>
	<?php else : ?>
		Tweets
	<?php endif; ?>
	<?php if($data->is_single_user): ?>
		from <?php echo esc_attr($data->is_single_user); ?>
	<?php endif; ?>
	(<?php echo number_format($data->total_tweets); ?>)
</h1>


<form method="get" action="<?php echo hl_twitter_archive_link(); ?>">
	
	<?php if($data->num_users>1): ?>
		<select name="username">
			<option value="-">Everyone</option>
			<?php foreach($data->users as $user): ?>
				<option value="<?php echo $user->screen_name; ?>" <?php if($data->is_single_user==$user->screen_name) echo 'selected="selected"'; ?>>
					<?php echo $user->screen_name; ?>
				</option>
			<?php endforeach; ?>
		</select>
	<?php endif; ?>
	
	<select name="month">
		<option value="-">Any time</option>
		<?php foreach($data->distinct_months as $month): ?>
			<option value="<?php echo $month->year.'-'.$month->month; ?>" <?php if($data->is_month==$month->month and $data->is_year==$month->year) echo 'selected="selected"'; ?>>
				<?php echo $months[$month->month].' '.$month->year; ?> (<?php echo number_format($month->num); ?>)
			</option>
		<?php endforeach; ?>
	</select>
	
	<input type="text" name="s" value="<?php echo esc_attr($data->is_search); ?>" />
	<input type="hidden" name="page" value="1" />
	<input type="submit" value="Search" />
	
</form>


<?php if( $data->total_pages > 1 ): ?>
	<div id="nav-above" class="navigation">
		<?php if($data->has_previous_page): ?>
			<div class="nav-previous"><a href="<?php echo hl_twitter_archive_link(array('page'=>$data->has_previous_page)); ?>">&laquo; Previous</a></div>
		<?php endif; ?>
		<?php if($data->has_next_page): ?>
			<div class="nav-next"><a href="<?php echo hl_twitter_archive_link(array('page'=>$data->has_next_page)); ?>">Next &raquo;</a></div>
		<?php endif; ?>
	</div><!-- #nav-above -->
<?php endif; ?>


<?php if ( $data->num_tweets==0 ) : ?>
	
	<div id="post-0" class="post error404 not-found">
		<h1 class="entry-title">No Tweets Found</h1>
		<div class="entry-content">
			<p>No tweets were found on this page.</p>
		</div><!-- .entry-content -->
	</div><!-- #post-0 -->
	
<?php else: ?>

	<?php foreach($data->tweets as $tweet): $tweet->timestamp = strtotime($tweet->created); ?>
	
			<div id="tweet-<?php echo $tweet->twitter_tweet_id; ?>" class="post-<?php echo $tweet->twitter_tweet_id; ?> post type-tweet">
			
				<div class="entry-summary">
					<p><?php echo hl_twitter_show_tweet($tweet->tweet); $tweet->reply_timestamp = strtotime($tweet->reply_created); ?></p>
						
					<?php if($tweet->reply_tweet!=''): ?>
						<blockquote class="hl-twitter-archive-reply">
							<small>
								<?php echo hl_twitter_show_tweet($tweet->reply_tweet); ?>
								<div class="entry-utility">
									Tweeted on <a href="http://twitter.com/<?php echo $tweet->reply_screen_name; ?>/status/<?php echo $tweet->reply_tweet_id; ?>" title="<?php echo date_i18n('g:ia', $tweet->reply_timestamp); ?>"><?php echo date_i18n('F j, Y', $tweet->reply_timestamp); ?></a>
									by <a class="url fn n" href="http://twitter.com/<?php echo $tweet->reply_screen_name; ?>" title="View Twitter profile"><?php echo $tweet->reply_screen_name; ?></a>
								</div>
							</small>
						</blockquote>
					<?php endif; ?>
					
				</div><!-- .entry-summary -->
			
				<div class="entry-utility">
					<span class="meta-prep meta-prep-author">Tweeted on</span> 
					<a href="http://twitter.com/<?php echo $tweet->screen_name; ?>/status/<?php echo $tweet->twitter_tweet_id; ?>" title="<?php echo date_i18n('g:ia', $tweet->timestamp); ?>" rel="bookmark"><span class="entry-date"><?php echo date_i18n('F j, Y', $tweet->timestamp); ?></span></a> 
					<span class="meta-sep">by</span> 
					<span class="author vcard"><a class="url fn n" href="http://twitter.com/<?php echo $tweet->screen_name; ?>" title="View Twitter profile"><?php echo $tweet->screen_name; ?></a></span>
					<?php if($tweet->reply_screen_name!=''): ?>
						<span class="meta-sep">in reply to</span> <a href="http://twitter.com/<?php echo $tweet->reply_screen_name; ?>/status/<?php echo $tweet->reply_tweet_id; ?>"><?php echo $tweet->reply_screen_name; ?></a>
					<?php endif; ?>
				</div><!-- .entry-utility -->
			
			</div><!-- #post-## -->
	
	<?php endforeach; ?>

<?php endif; ?>


<?php if( $data->total_pages > 1 ): ?>
	<div id="nav-below" class="navigation" style="margin-top:18px">
		<?php if($data->has_previous_page): ?>
			<div class="nav-previous"><a href="<?php echo hl_twitter_archive_link(array('page'=>$data->has_previous_page)); ?>">&laquo; Previous</a></div>
		<?php endif; ?>
		<?php if($data->has_next_page): ?>
			<div class="nav-next"><a href="<?php echo hl_twitter_archive_link(array('page'=>$data->has_next_page)); ?>">Next &raquo;</a></div>
		<?php endif; ?>
	</div><!-- #nav-below -->
<?php endif; ?>


</div><!-- #content -->
</div><!-- #container -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>
