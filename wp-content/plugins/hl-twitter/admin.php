<?php if(!HL_TWITTER_LOADED) die('Direct script access denied.');

/*
  Add admin menu pages
/*/
function hl_twitter_admin_menu() {
	add_menu_page('HL Twitter', 'HL Twitter', 'manage_options', 'hl_twitter', 'hl_twitter_admin_view_tweets', HL_TWITTER_URL.'menu_icon.png');
	add_submenu_page('hl_twitter', 'Tweets', 'Tweets', 'manage_options', 'hl_twitter', 'hl_twitter_admin_view_tweets');
	add_submenu_page('hl_twitter', 'Users', 'Users', 'manage_options', 'hl_twitter_users', 'hl_twitter_admin_view_users');
	add_submenu_page('hl_twitter', 'Settings', 'Settings', 'manage_options', 'hl_twitter_settings', 'hl_twitter_settings');
	
	add_meta_box('hl_twitter_box', 'Tweet Post', 'hl_twitter_post_box', 'post', 'advanced');
	add_meta_box('hl_twitter_box', 'Tweet Page', 'hl_twitter_post_box', 'page', 'advanced');
	
	# Feedback page
	add_submenu_page('hl_twitter', 'Feedback', 'Feedback', 'manage_options', 'hl_twitter_feedback', 'hl_twitter_feedback');
	
} // end func: hl_twitter_admin_menu



/*
	Feedback page via HL Feedback
*/
function hl_twitter_feedback() {
	$feedback = new hl_feedback('hl_twitter', HL_TWITTER_DIR.'hl_twitter.php');
	$feedback->render();
} // end func: hl_twitter_feedback




/*
	Called whenever a post is published (or saved when published)
*/
function hl_twitter_publish_post($post_id) {
	
	$auto_tweet_on = (bool) get_option(HL_TWITTER_AUTO_TWEET_SETTINGS, false);
	if(!$auto_tweet_on) return $post_id;
	
	$post_id = intval($post_id);
	$revision_id = wp_is_post_revision($post_id);
	if($revision_id) $post_id = $revision_id;
	
	$prior_auto_tweet = get_post_meta($post_id, HL_TWITTER_AUTO_TWEET_POSTMETA, true);
	if($prior_auto_tweet!='') return $post_id;
	
	$tweet = stripslashes($_POST['hl_twitter_box_tweet']);
	if($tweet=='') $tweet = hl_twitter_generate_post_tweet_text($post_id);
	if(!$tweet) return $post_id;
	if(strlen($tweet)>140) $tweet = substr($tweet,0,137).'...';
	
	$response = hl_twitter_tweet($tweet);
	if($response) {
		update_post_meta($post_id, HL_TWITTER_AUTO_TWEET_POSTMETA, $tweet);
	}
	
} // end func: hl_twitter_publish_post




/*
	Admin dashboard widget
*/
function hl_twitter_add_dashboard_widget() {
	wp_add_dashboard_widget('hl_twitter_dashboard_widget', 'Tweet Now!', 'hl_twitter_dashboard_widget');	
} // end func: hl_twitter_add_dashboard_widget

function hl_twitter_dashboard_widget() {
	?>
	<form method="post" action="">
		<p id="hl_twitter_dash_chars" style="float:right;margin:0;width:30px">140</p>
		<p id="hl_twitter_dash_response" style="margin-right:35px">Make a new tweet straight from your Dashboard!</p>
		<p><textarea name="hl_twitter_dash_new_tweet" id="hl_twitter_dash_new_tweet" style="width:100%"></textarea></p>
		<p style="text-align:right"><input type="submit" class="button" name="hl_twitter_dash_submit" id="hl_twitter_dash_submit" value="Tweet" /></p>
	</form>
	<script type="text/javascript">
	jQuery(document).ready(function($){
		
		$("#hl_twitter_dash_new_tweet").keyup(function(){
			var chars_remaining = 140 - $("#hl_twitter_dash_new_tweet").val().length;
			$("#hl_twitter_dash_chars").text(chars_remaining);
		});
		
		$("#hl_twitter_dash_submit").click(function(){
			$("#hl_twitter_dash_response").text("Sending...");
			jQuery.post(
				ajaxurl,
				{
					action: 'hl_twitter_ajax_new_tweet', 
					hl_tweet: $("#hl_twitter_dash_new_tweet").val()
				},
				function(response) {
					if(response.status=="success") {
						$("#hl_twitter_dash_response").text("Tweeted! Tweet again?");
						$("#hl_twitter_dash_new_tweet").val("");
						$("#hl_twitter_dash_chars").text("140");
					} else {
						$("#hl_twitter_dash_response").text("Error: "+response.error);
					}
				},
				"json"
			);
			return false;
		});
		
	});
	</script>
	<?php
} // end func: hl_twitter_dashboard_widget




/*
	AJAX handler for new tweets
*/
function hl_twitter_ajax_new_tweet() {
	
	$tweet = stripslashes(trim($_POST['hl_tweet']));
	if($tweet=='')
		die('{"status":"error","error":"Please make sure you have entered a tweet."}');
	
	if(strlen($tweet)>140)
		die('{"status":"error","error":"Your tweet is longer than 140 characters."}');
	
	if(md5($tweet)==$_SESSION['hl_twitter_ajax_last_tweet'])
		die('{"status":"error","error":"This tweet appears to be a duplicate."}');
	
	$response = hl_twitter_tweet($tweet);
	if(!$response)
		die('{"status":"error","error":"Could not connect to Twitter."}');
	
	$_SESSION['hl_twitter_ajax_last_tweet'] = md5($tweet);
	die('{"status":"success"}');
	
} // end func: hl_twitter_ajax_new_tweet
add_action('wp_ajax_hl_twitter_ajax_new_tweet', 'hl_twitter_ajax_new_tweet');




/*
	Display a tweet this link box on post/page pages
*/
function hl_twitter_post_box($post) {
	
	$auto_tweet_enabled = (bool) get_option(HL_TWITTER_AUTO_TWEET_SETTINGS, false);
	
	if($post->post_status!='publish') {
		if($auto_tweet_enabled) {
			echo '<p><input type="checkbox" name="hl_twitter_auto_tweet" checked="checked" /> If ticked, a tweet will be automatically created for this '.$post->post_type.' based on your Twitter settings.</p>';
		} else {
			echo '<p>When you have published this '.$post->post_type.', you can tweet a link to it from here.</p>';
		}
		return;
	}
	$tweet = hl_twitter_generate_post_tweet_text($post->ID);
	?>
	
	<p>
		<textarea name="hl_twitter_box_tweet" id="hl_twitter_box_tweet" style="width:99%"><?php echo $tweet; ?></textarea>
	</p>
	<p>
		<span id="hl_twitter_box_chars" style="float:right">140</span>
		<input class="button" type="button" name="hl_twitter_box_submit" id="hl_twitter_box_submit" value="Tweet Now!" />
		<span id="hl_twitter_box_response">The text above will be posted to Twitter straight away when you click the button.</span>
	</p>
	
	<script type="text/javascript">
	jQuery(document).ready(function($){
		
		$("#hl_twitter_box_tweet").keyup(function(){
			var chars_remaining = 140 - $("#hl_twitter_box_tweet").val().length;
			$("#hl_twitter_box_chars").text(chars_remaining);
		});
		
		$("#hl_twitter_box_tweet").keyup(); // Trigger character count on load
		
		$("#hl_twitter_box_submit").click(function(){
			$("#hl_twitter_box_response").text("Tweeting...");
			jQuery.post(
				ajaxurl,
				{
					action: 'hl_twitter_ajax_new_tweet', 
					hl_tweet: $("#hl_twitter_box_tweet").val()
				},
				function(response) {
					if(response.status=="success") {
						$("#hl_twitter_box_response").text("Your tweet has been sent to Twitter.");
						$("#hl_twitter_box_tweet").val("");
						$("#hl_twitter_box_chars").text("140");
					} else {
						$("#hl_twitter_box_response").text("Error: "+response.error);
					}
				},
				"json"
			);
			
			return false;
		});
		
	});
	</script>
	
	<?php
} // end func: hl_twitter_post_box



















/*
	View all tweets that have been recorded
*/
function hl_twitter_admin_view_tweets() {
	global $wpdb;
	if($_GET['action']=='oauth_callback' or !hl_twitter_is_oauth_verified()) return hl_twitter_admin_authorize_oauth();
	if($_GET['action']=='edit') return hl_twitter_admin_edit_tweet($_GET['id']);
	if($_GET['action']=='delete') return hl_twitter_admin_delete_tweet($_GET['id']);
	
	$tracked_users = $wpdb->get_results('SELECT id, twitter_user_id, screen_name FROM '.HL_TWITTER_DB_PREFIX.'users ORDER BY screen_name ASC');
	
	$sql_where = array();
	$filters = array();
	
	if(isset($_GET['user'])) {
		$get_user = intval($_GET['user']);
		if($get_user>0) {
			$filters['user'] = $get_user;
			$sql_where[] = $wpdb->prepare('t.twitter_user_id=%d',$get_user);
		}
	}
	
	if(isset($_GET['s']) and $_GET['s']!='') {
		$filters['s'] = $_GET['s'];
		$sql_where[] = $wpdb->prepare('MATCH(t.tweet) AGAINST(%s)',$_GET['s']);
	}
	
	if($_GET['dates']!='' and $_GET['datee']!='') {
		$date_start = strtotime($_GET['dates'].' 00:00:00');
		$date_end = strtotime($_GET['datee'].' 00:00:00');
		if($date_start>946684800) {
			if($date_end<$date_start) {
				$date_temp = $date_end;
				$date_end = $date_start;
				$date_start = $date_temp;
			}
			$filters['dates'] = date('Y-m-d', $date_start);
			$filters['datee'] = date('Y-m-d', $date_end);
			$sql_where[] = $wpdb->prepare('t.created >= %s AND t.created <= %s', date('Y-m-d 00:00:00', $date_start), date('Y-m-d 23:59:59', $date_end));
		}
	}
	
	if(count($sql_where)>0) {
		$sql_where = ' WHERE ('.implode(') AND (', $sql_where).') ';
	} else {
		$sql_where = '';
	}
	
	$total_objects = $wpdb->get_var('SELECT COUNT(*) FROM '.HL_TWITTER_DB_PREFIX.'tweets AS t '.$sql_where);
	$per_page = 20;
	$num_pages = ceil($total_objects/$per_page);
	$current_page = ($_GET['start']>0)?intval($_GET['start']):1;
	$pagination_url = 'admin.php?page=hl_twitter';
	foreach($filters as $uri_key=>$uri_val) if($uri_val!='') $pagination_url .= '&'.$uri_key.'='.$uri_val;
	$pagination = paginate_links(array(
		'base' => $pagination_url.'%_%',
		'format' => '&start=%#%',
		'total' => $num_pages,
		'current' => $current_page
	));
	$objects = $wpdb->get_results($wpdb->prepare('
		SELECT u.screen_name, u.avatar, u.name, t.* 
		FROM '.HL_TWITTER_DB_PREFIX.'tweets AS t
		LEFT JOIN '.HL_TWITTER_DB_PREFIX.'users AS u ON t.twitter_user_id = u.twitter_user_id
		'.$sql_where.'
		ORDER BY t.created DESC
		LIMIT %d, %d
	', ($current_page-1)*$per_page, $per_page));
	$num_objects = $wpdb->num_rows;
	
?>

<style type="text/css">@import "<?php echo HL_TWITTER_URL; ?>datepick/redmond.datepick.css";</style>
<script type="text/javascript" src="<?php echo HL_TWITTER_URL; ?>datepick/jquery.datepick.min.js"></script>
<script type="text/javascript">
jQuery(document).ready(function($){
	$(".hl_datepick").datepick({dateFormat: 'yyyy-mm-dd'});
});
</script>


<div class="wrap">
<form method="get" action="<?php echo $pagination_url; ?>">
	<input type="hidden" name="page" value="hl_twitter" />
	
	<h2>Tweets</h2>
	
	<p class="search-box">
		<label class="screen-reader-text" for="post-search-input">Search Tweets:</label>
		<input type="text" id="post-search-input" name="s" value="<?php echo hl_e($filters['s']); ?>">
		<input type="submit" value="Search Tweets" class="button">
	</p>
	
	<div class="tablenav">
		<div class="alignleft actions">
			<select name="user">
				<option value="0">All users</option>
				<?php foreach($tracked_users as $tracked_user): ?>
					<option value="<?php echo $tracked_user->twitter_user_id; ?>" <?php if($filters['user']==$tracked_user->twitter_user_id) echo 'selected="selected"'; ?>><?php echo hl_e($tracked_user->screen_name); ?></option>
				<?php endforeach; ?>
			</select>
			<label for="hl_dates">From:</label> <input type="text" name="dates" id="hl_dates" class="hl_datepick" value="<?php echo $filters['dates']; ?>" size="12" />
			<label for="hl_datee">To:</label> <input type="text" name="datee" id="hl_datee" class="hl_datepick" value="<?php echo $filters['datee']; ?>" size="12" />
			<input type="submit" value="Apply" class="button-secondary" />
			<?php if(count($filters)>0): ?>
				<a href="admin.php?page=hl_twitter">clear</a>
			<?php endif; ?>
		</div>
		<div class="tablenav-pages">
			<span class="displaying-num">Displaying <?php echo number_format(($current_page-1)*$per_page+1); ?>&ndash;<?php echo number_format(($current_page-1)*$per_page+$num_objects); ?> of <?php echo number_format($total_objects); ?></span>
			<?php echo $pagination; ?>
		</div>
	</div>
	<table class="widefat post fixed" cellspacing="0" style="clear: none;">
		<thead>
			<tr>
				<th scope="col" class="manage-column column-title" width="65">&nbsp;</th>
				<th scope="col" class="manage-column column-title" width="125">Name</th>
				<th scope="col" class="manage-column column-title">Tweet</th>
				<th scope="col" class="manage-column column-title" width="175">Created</th>
				<th scope="col" class="manage-column column-title" width="95">&nbsp;</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th scope="col" class="manage-column column-title">&nbsp;</th>
				<th scope="col" class="manage-column column-title">Name</th>
				<th scope="col" class="manage-column column-title">Tweet</th>
				<th scope="col" class="manage-column column-title">Created</th>
				<th scope="col" class="manage-column column-title">&nbsp;</th>
			</tr>
		</tfoot>
		<tbody>
			<?php if($num_objects>0): ?>
				<?php foreach($objects as $object): ?>
					<tr>
						<td><a href="admin.php?page=hl_twitter_users&amp;action=edit&amp;id=<?php echo hl_e($object->twitter_user_id); ?>"><img src="<?php echo hl_twitter_get_avatar($object->avatar); ?>" /></a></td>
						<td>
							<a href="admin.php?page=hl_twitter_users&amp;action=edit&amp;id=<?php echo hl_e($object->twitter_user_id); ?>"><?php echo hl_e($object->name); ?></a><br />
							<em><?php echo hl_e($object->screen_name); ?></em>
						</td>
						<td><?php echo hl_twitter_show_tweet($object->tweet); ?></td>
						<td>
							<?php echo hl_e($object->created); ?><br />
							<em><?php echo hl_time_ago($object->created); ?> ago</em>
						</td>
						<td><a href="admin.php?page=hl_twitter&amp;action=edit&amp;id=<?php echo hl_e($object->id); ?>">edit</a> | <a href="admin.php?page=hl_twitter&amp;action=delete&amp;id=<?php echo hl_e($object->id); ?>">delete</a></td>
					</tr>
				<?php endforeach; ?>
			<?php else: ?>
				<tr><td colspan="5">No tweets were found.</td></tr>
			<?php endif; ?>
		</tbody>
	</table>
	<div class="tablenav">
		<div class="tablenav-pages">
			<span class="displaying-num">Displaying <?php echo number_format(($current_page-1)*$per_page+1); ?>&ndash;<?php echo number_format(($current_page-1)*$per_page+$num_objects); ?> of <?php echo number_format($total_objects); ?></span>
			<?php echo $pagination; ?>
		</div>
	</div>
</form>
</div>
<?php
} // end func: hl_twitter_admin_view_tweets




/*
	Edit a tweet
*/
function hl_twitter_admin_edit_tweet($id) {
	global $wpdb;
	$id = intval($id);
	
	$object = $wpdb->get_row($wpdb->prepare('SELECT * FROM '.HL_TWITTER_DB_PREFIX.'tweets WHERE id=%d LIMIT 1', $id));
	if(!$object) return hl_twitter_error('The selected tweet could not be found.');
	
	
	if($_POST['submit']) {
		$object->tweet = stripslashes($_POST['tweet']);
		$object->lat = floatval($_POST['lat']);
		$object->lon = floatval($_POST['lon']);
		$object_created = strtotime($_POST['created']);
		$object->created = date_i18n('Y-m-d H:i:s', $object_created);
		$object->source = stripslashes($_POST['source']);
		
		$errors = array();
		if($object->tweet=='') $errors[] = 'make sure you have entered a tweet';
		if(strlen($object->tweet)>140) $errors[] = 'make sure your tweet is 140 characters or less';
		if($object_created<946684800) $errors[] = 'make sure you have entered a valid date';
		
		if(count($errors)==0) {
			$wpdb->query($wpdb->prepare(
				'UPDATE '.HL_TWITTER_DB_PREFIX.'tweets SET tweet=%s, lat=%f, lon=%f, created=%s, source=%s WHERE id=%d LIMIT 1',
				$object->tweet, $object->lat, $object->lon, $object->created, $object->source, $object->id
			));
			$msg_updated = 'Tweet updated successfully.';
		} else {
			$msg_error = 'Error(s) were encountered saving your tweet: '.implode(', ', $errors).'.';
		}
		
	} // end POST
	
?>
<div class="wrap">
	<h2>Edit Tweet</h2>
	
	<?php if($msg_updated): ?><div class="updated"><p><?php echo $msg_updated; ?></p></div><?php endif; ?>
	<?php if($msg_error): ?><div class="error"><p><?php echo $msg_error; ?></p></div><?php endif; ?>
	
	<form method="post" action="admin.php?page=hl_twitter&amp;action=edit&amp;id=<?php echo $object->id; ?>">
		<table class="form-table">
			<tr>
				<th scope="row">Tweet</th>
				<td><textarea name="tweet" rows="3" cols="50"><?php echo hl_e($object->tweet); ?></textarea></td>
			</tr>
			<tr>
				<th scope="row">Geo</th>
				<td>
					<input type="text" name="lat" value="<?php echo hl_e($object->lat); ?>" size="11" />,
					<input type="text" name="lon" value="<?php echo hl_e($object->lon); ?>" size="11" />
				</td>
			</tr>
			<tr>
				<th scope="row">Tweeted</th>
				<td><input type="text" name="created" value="<?php echo date_i18n('Y-m-d H:i:s', strtotime($object->created)); ?>" size="30" /></td>
			</tr>
			<tr>
				<th scope="row">Source</th>
				<td><input type="text" name="source" value="<?php echo hl_e($object->source); ?>" size="30" /></td>
			</tr>
		</table>
		<div class="submit">
			<a href="admin.php?page=hl_twitter" class="button-secondary">Cancel</a>
			<input type="submit" name="submit" value="Save" class="button-primary" />
		</div>
	</form>
	
</div>
<?php
} // end func: hl_twitter_admin_edit_tweet








/*
	Deletes an individual tweet (and any replied to tweets)
*/
function hl_twitter_admin_delete_tweet($id) {
	global $wpdb;
	$id = intval($id);
	
	$object = $wpdb->get_row($wpdb->prepare('SELECT * FROM '.HL_TWITTER_DB_PREFIX.'tweets WHERE id=%d',$id));
	if(!$object) return hl_twitter_error('The selected tweet could not be found.');
	
	if(isset($_POST['submit'])) {
		
		$wpdb->query($wpdb->prepare('DELETE FROM '.HL_TWITTER_DB_PREFIX.'tweets WHERE id=%d LIMIT 1',$object->id));
		if($object->reply_tweet_id>0) {
			$wpdb->query($wpdb->prepare('DELETE FROM '.HL_TWITTER_DB_PREFIX.'replies WHERE twitter_tweet_id=%d',$object->reply_tweet_id));
		}
		?>
		<div class="wrap">
			<h2>Delete Tweet</h2>
			<p>The selected tweet has been deleted.</p>
			<a href="admin.php?page=hl_twitter" class="button-primary">Back</a>
		</div>
		<?php
		return;
	}
	?>
	<div class="wrap">
		<h2>Delete Tweet</h2>
		<p>Are you sure you wish to remove this tweet from the database? Please note, if this is the most recent tweet from this user it may be re-imported later on.</p>
		<form method="post" action="admin.php?page=hl_twitter&amp;action=delete&amp;id=<?php echo $object->id; ?>">
			<p>
				<a href="admin.php?page=hl_twitter" class="button-secondary">Cancel</a>
				<input type="submit" name="submit" value="Delete" class="button-primary" />
			</p>
		</form>
	</div>
	<?php
} // end func: hl_twitter_admin_delete_tweet


































/*
	View all users that are tracked
*/
function hl_twitter_admin_view_users() {
	global $wpdb;
	
	if($_GET['action']=='oauth_callback' or !hl_twitter_is_oauth_verified()) return hl_twitter_admin_authorize_oauth();
	if($_GET['action']=='edit') return hl_twitter_admin_edit_user($_GET['id']);
	if($_GET['action']=='new') return hl_twitter_admin_new_user();
	if($_GET['action']=='delete') return hl_twitter_admin_delete_user($_GET['id']);
	if($_GET['action']=='import') return hl_twitter_admin_import_user_tweets($_GET['id']);
	
	$objects = $wpdb->get_results('
		SELECT id, twitter_user_id, screen_name, name, num_friends, num_followers, num_tweets, registered, avatar
		FROM '.HL_TWITTER_DB_PREFIX.'users
		ORDER BY screen_name ASC
	');
	$num_objects = $wpdb->num_rows;
	
?>
<div class="wrap">
<form method="get" action="<?php echo $pagination_url; ?>">
	<input type="hidden" name="page" value="hl_twitter" />
	
	<h2>Twitter Users</h2>
	
	<p>Below are all Twitter users that you are tracking. All tweets made by these users will be recorded and stored on this website.</p>
	
	<table class="widefat post fixed" cellspacing="0" style="clear: none;">
		<thead>
			<tr>
				<th scope="col" class="manage-column column-title" width="65">&nbsp;</th>
				<th scope="col" class="manage-column column-title">Name</th>
				<th scope="col" class="manage-column column-title">Tweets</th>
				<th scope="col" class="manage-column column-title">Following</th>
				<th scope="col" class="manage-column column-title">Followers</th>
				<th scope="col" class="manage-column column-title">Registered</th>
				<th scope="col" class="manage-column column-title" width="185">&nbsp;</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th scope="col" class="manage-column column-title">&nbsp;</th>
				<th scope="col" class="manage-column column-title">Name</th>
				<th scope="col" class="manage-column column-title">Tweets</th>
				<th scope="col" class="manage-column column-title">Following</th>
				<th scope="col" class="manage-column column-title">Followers</th>
				<th scope="col" class="manage-column column-title">Registered</th>
				<th scope="col" class="manage-column column-title">&nbsp;</th>
			</tr>
		</tfoot>
		<tbody>
			<?php if($num_objects>0): ?>
				<?php foreach($objects as $object): ?>
					<tr>
						<td><a href="admin.php?page=hl_twitter_users&amp;action=edit&amp;id=<?php echo hl_e($object->twitter_user_id); ?>"><img src="<?php echo hl_twitter_get_avatar($object->avatar); ?>" /></a></td>
						<td>
							<a href="admin.php?page=hl_twitter_users&amp;action=edit&amp;id=<?php echo hl_e($object->twitter_user_id); ?>"><?php echo hl_e($object->name); ?></a><br />
							<em><?php echo hl_e($object->screen_name); ?></em>
						</td>
						<td><a href="admin.php?page=hl_twitter&amp;user=<?php echo hl_e($object->twitter_user_id); ?>"><?php echo number_format($object->num_tweets); ?></a></td>
						<td><a href="http://twitter.com/<?php echo hl_e($object->screen_name); ?>/following"><?php echo number_format($object->num_friends); ?></a></td>
						<td><a href="http://twitter.com/<?php echo hl_e($object->screen_name); ?>/followers"><?php echo number_format($object->num_followers); ?></a></td>
						<td>
							<?php echo hl_e($object->registered); ?><br />
							<em><?php echo number_format((time()-strtotime($object->registered))/86400); ?> days ago</em>
						</td>
						<td>
							<a href="admin.php?page=hl_twitter_users&amp;action=edit&amp;id=<?php echo hl_e($object->twitter_user_id); ?>">edit</a> | 
							<a href="http://twitter.com/<?php echo hl_e($object->screen_name); ?>">twitter</a> | 
							<a href="admin.php?page=hl_twitter_users&amp;action=delete&amp;id=<?php echo hl_e($object->id); ?>">delete</a>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php else: ?>
				<tr><td colspan="7">No users were found.</td></tr>
			<?php endif; ?>
		</tbody>
	</table>
	<div class="tablenav">
		<div class="alignleft actions">
			<p><a href="admin.php?page=hl_twitter_users&amp;action=new" class="button-primary">Add new User</a></p>
		</div>
	</div>
</form>
</div>
<?php
} // end func: hl_twitter_admin_view_users








/*
	Edit a users settings
*/
function hl_twitter_admin_edit_user($twitter_user_id) {
	global $wpdb;
	$twitter_user_id = intval($twitter_user_id);
	$object = $wpdb->get_row($wpdb->prepare('SELECT * FROM '.HL_TWITTER_DB_PREFIX.'users WHERE twitter_user_id=%d LIMIT 1', $twitter_user_id));
	if(!$object) return hl_twitter_error('The selected Twitter user could not be found.');
	$actual_num_tweets = (int) $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM '.HL_TWITTER_DB_PREFIX.'tweets WHERE twitter_user_id=%d', $object->twitter_user_id));
	
	if($_POST['submit']) {
		$object->pull_in_replies = ($_POST['pull_in_replies']==1)?1:0;
		$wpdb->query($wpdb->prepare('UPDATE '.HL_TWITTER_DB_PREFIX.'users SET pull_in_replies=%d WHERE twitter_user_id=%d', $object->pull_in_replies, $object->twitter_user_id));
		$msg_updated = 'Your settings have been saved.';
	}
	
?>
<div class="wrap">
	<h2>Edit <?php echo hl_e($object->screen_name); ?></h2>
	
	<?php if($msg_updated): ?><div class="updated"><p><?php echo $msg_updated; ?></p></div><?php endif; ?>
	<?php if($msg_error): ?><div class="error"><p><?php echo $msg_error; ?></p></div><?php endif; ?>
	
	<p><?php echo hl_e($object->name); ?> has <?php echo number_format($object->num_tweets); ?> tweets of which <?php echo number_format($actual_num_tweets); ?> are stored on this site.</p>
	
	<form method="post" action="admin.php?page=hl_twitter_users&amp;action=edit&amp;id=<?php echo $object->twitter_user_id; ?>">
		<table class="form-table">
			<tr>
				<th scope="row">Store replied to tweets?</th>
				<td>
					<select name="pull_in_replies">
						<option value="0">Choose...</option>
						<option value="1" <?php if($object->pull_in_replies==1) echo 'selected="selected"'; ?>>Yes</option>
						<option value="0" <?php if($object->pull_in_replies==0) echo 'selected="selected"'; ?>>No</option>
					</select>
					<br /><span class="description">HL Twitter can also store all tweets that are replied to for later recollection. This feature is off by default as it uses a larger percentage of your Twitter API allowance.</span>
				</td>
			</tr>
		</table>
		<div class="submit">
			<a href="admin.php?page=hl_twitter_users" class="button-secondary">Cancel</a>
			<a href="admin.php?page=hl_twitter_users&amp;action=import&amp;id=<?php echo $object->twitter_user_id; ?>" class="button-secondary">Import</a>
			<input type="submit" name="submit" value="Save" class="button-primary" />
		</div>
	</form>
	
</div>
<?php
} // end func: hl_twitter_admin_edit_user








/*
	Deletes a Twitter user and all tweets from them
*/
function hl_twitter_admin_delete_user($id) {
	global $wpdb;
	$id = intval($id);
	
	$user = $wpdb->get_row($wpdb->prepare('SELECT * FROM '.HL_TWITTER_DB_PREFIX.'users WHERE id=%d',$id));
	if(!$user) return hl_twitter_error('The selected user could not be found.');
	
	if(isset($_POST['submit'])) {
		$wpdb->query($wpdb->prepare('DELETE FROM '.HL_TWITTER_DB_PREFIX.'users WHERE id=%d LIMIT 1',$user->id));
		if($_POST['delete_tweets']=='on') {
			$wpdb->query($wpdb->prepare('DELETE FROM '.HL_TWITTER_DB_PREFIX.'tweets WHERE user_id=%d',$user->twitter_user_id));
		}
		?>
		<div class="wrap">
			<h2>Delete User</h2>
			<p>The selected user has been deleted<?php if($_POST['delete_tweets']=='on'): ?> along with all of their tweets stored in the database<?php endif; ?>.</p>
			<a href="admin.php?page=hl_twitter_users" class="button-primary">Back</a>
		</div>
		<?php
		return;
	}
	?>
	<div class="wrap">
		<h2>Delete User</h2>
		<p>Are you sure you wish to stop tracking <strong><?php echo hl_e($user->screen_name); ?></strong>? You can also choose to remove all of their tweets from the database.</p>
		<form method="post" action="admin.php?page=hl_twitter_users&amp;action=delete&amp;id=<?php echo $user->id; ?>">
			<p><input type="checkbox" name="delete_tweets" id="delete_tweets" /> <label for="delete_tweets">Remove tweets from database as well?</label></p>
			<p>
				<a href="admin.php?page=hl_twitter_users" class="button-secondary">Cancel</a>
				<input type="submit" name="submit" value="Delete" class="button-primary" />
			</p>
		</form>
	</div>
	<?php
} // end func: hl_twitter_admin_delete_user

















/*
	Creates a new User
*/
function hl_twitter_admin_new_user() {
	global $wpdb;
	$api = hl_twitter_get_api();
	if(!$api) return hl_twitter_error('Could not connect to Twitter. Please make sure you have linked this plugin to your Twitter account.');
	
	$user = new stdClass;
	$user->screen_name = '';
	$user->import_replies = false;
	
	if($_POST['submit']!='') {
		
		$user->screen_name = stripslashes(trim($_POST['screen_name']));
		$user->import_replies = ($_POST['import_replies']=='on')?1:0;
		
		if($user->screen_name!='') {
			try {
				$data = $api->get('/users/show.json', array('screen_name'=>$_POST['screen_name']));
				if($data and $data->screen_name!='') {
					if($data->protected==0 or ( $data->protected==1 and isset($data->status)) ) {
						$user_id = $wpdb->get_var($wpdb->prepare('SELECT twitter_user_id FROM '.HL_TWITTER_DB_PREFIX.'users WHERE screen_name=%s LIMIT 1', $data->screen_name));
						if($wpdb->num_rows==0) {
							$wpdb->insert(
								HL_TWITTER_DB_PREFIX.'users',
								array(
									'screen_name' => $data->screen_name,
									'twitter_user_id' => $data->id,
									'name' => $data->name,
									'num_friends' => $data->friends_count,
									'num_followers' => $data->followers_count,
									'num_tweets' => $data->statuses_count,
									'registered' => date_i18n('Y-m-d H:i:s', strtotime($data->created_at)),
									'url' => $data->url,
									'description' => $data->description,
									'location' => $data->location,
									'avatar' => $data->profile_image_url,
									'created' => date_i18n('Y-m-d H:i:s'),
									'last_updated'=> '2000-01-01',
									'pull_in_replies' => $user->import_replies
								),
								array('%s','%s','%s','%d','%d','%d','%s','%s','%s','%s','%s','%s','%s', '%d')
							);
							$msg_updated = hl_e($user->screen_name).' has been added to the database.<br /><a href="admin.php?page=hl_twitter_users&amp;action=edit&amp;id='.$data->id.'">View details</a> | <a href="admin.php?page=hl_twitter_users&amp;action=import&amp;id='.$data->id.'">Import tweets</a>';
						} else { $msg_error = 'This user has already being added to the database of users. <a href="admin.php?page=hl_twitter_users&amp;action=edit&amp;id='.$user_id.'">View details</a>'; }
					} else { $msg_error = 'This persons tweets are protected and you do not have permission to view them.'; }
				} else { $msg_error = 'The selected user could not be found or there was an while contacting Twitter. Please try again later.'; }
			} catch(Exception $e) { $msg_error = 'The selected user could not be found or an error was encountered while contacting Twitter. Please try again later.'; }
		} else { $msg_error = 'Please make sure you have entered a valid screen name.'; }
		
	} // end SUBMIT
	
?>
<div class="wrap">
	
	<?php if($msg_error!=''): ?><div class="error"><p><?php echo $msg_error; ?></p></div><?php endif; ?>
	<?php if($msg_updated!=''): ?><div class="updated"><p><?php echo $msg_updated; ?></p></div><?php endif; ?>
	
	<h2>Add New User</h2>
	<p>Adding a new user will record all of their future tweets and profile information, along with their most recent 3,200 tweets and any tweets they reply to (if chosen). Please be aware that only a limited number of requests can be made to Twitter per hour, adding more users may cause you to reach this limit and risk not receiving new tweets.</p>
	
	<form method="post" action="admin.php?page=hl_twitter_users&amp;action=new">
		<table class="form-table">
			<tr>
				<th scope="row">Screen name</th>
				<td>@<input type="text" name="screen_name" value="<?php echo hl_e($user->screen_name); ?>" class="regular-text" />
				<br /><span class="description">For example: MyTwitterUsername, HybridLogic</span>
			</tr>
			<tr>
				<th scope="row">Options</th>
				<td>
					<ul>
						<li><input type="checkbox" name="import_replies" id="import_replies"<?php if($user->import_replies) echo 'checked="checked"'; ?> /> <label for="import_replies">Import any tweets that are replied to</label></li>
					</ul>
				</td>
			</tr>
		</table>
		<div class="submit">
			<input type="submit" name="submit" value="Add User" class="button-primary" />
		</div>
	</form>
	
</div>
<?php
} // end func: hl_twitter_admin_new_user







/*
	Import all tweets for the given User
*/
function hl_twitter_admin_import_user_tweets($id) {
	global $wpdb;
	$api = hl_twitter_get_api();
	if(!$api) return hl_twitter_error('Could not connect to Twitter. Please make sure you have linked this plugin to your Twitter account.');
	
	$user = $wpdb->get_row($wpdb->prepare('SELECT * FROM '.HL_TWITTER_DB_PREFIX.'users WHERE twitter_user_id=%d LIMIT 1',$id));
	if(!$user) return hl_twitter_error('The selected user could not be found.');
	$tweets_in_db = $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM '.HL_TWITTER_DB_PREFIX.'tweets WHERE twitter_user_id=%d', $user->twitter_user_id));
	$tweets_to_import = ($user->num_tweets>HL_TWITTER_API_MAX_TWEET_LIMIT)?HL_TWITTER_API_MAX_TWEET_LIMIT:$user->num_tweets;
	
	$remaining_hits = 0;
	try {
		$response = $api->get('/account/rate_limit_status.json');
	} catch (Exception $e) {
		echo $e->getMessage();
		die();
	}
	if($response and isset($response->remaining_hits) and $response->remaining_hits>0) $remaining_hits = (int) $response->remaining_hits;
	
	$estimated_num_hits = round( ceil($tweets_to_import/200) * 1.33 ); // rough error factor based on how often Twitter fails
	
?>
<div class="wrap">
	<h2>Tweet Importer</h2>
	
	<p>The HL Tweet Importer allows you to import tweets for a user and store them on your website. Due to a limitation set by Twitter, only the 3200 most recent tweets can be retrieved. This system makes very heavy use of the Twitter API, please only use it sparingly to prevent Twitter from blocking your website. Review the details below and click on the Start Import button to begin.</p>
	
	<ul>
		<li>Account name: <strong><?php echo hl_e($user->screen_name); ?></strong></li>
		<li>Tweets made: <strong><?php echo number_format($user->num_tweets); ?></strong></li>
		<li>Tweets in DB: <strong><?php echo number_format($tweets_in_db); ?></strong></li>
		<li>Tweets to import: <strong><?php echo number_format($tweets_to_import); ?></strong></li>
		<li>Estimated API usage: <strong><?php echo $estimated_num_hits; ?></strong> requests out of remaining <strong><?php echo $remaining_hits; ?></strong></li>
	</ul>
	
	<p id="hl_twitter_import_tweets_for_user_results"><a href="#import" id="hl_twitter_import_tweets_for_user" class="button-primary">Start Import</a></p>
	
	<script type="text/javascript" >
	jQuery(document).ready(function($) {
		var output = $("#hl_twitter_import_tweets_for_user_results");
		$("#hl_twitter_import_tweets_for_user").click(function(){
			output.html("Importing tweets... (this may take a while)");
			$.post(ajaxurl, { action: 'hl_twitter_import_tweets_for_user', id: <?php echo $user->twitter_user_id; ?> }, function(response) {
				output.html(response);
			});
			return false;
		});
	});
	</script>
	
</div>
<?php
} // end func: hl_twitter_admin_import_user_tweets









/*
	Imports all tweets for a user
*/
add_action('wp_ajax_hl_twitter_import_tweets_for_user', 'hl_twitter_import_tweets_for_user');
function hl_twitter_import_tweets_for_user() {
	global $wpdb;
	@ini_set('memory_limit', '512M');
	
	# Get our User and API objects
	$id = intval($_POST['id']);
	$user = $wpdb->get_row($wpdb->prepare('SELECT * FROM '.HL_TWITTER_DB_PREFIX.'users WHERE twitter_user_id=%d LIMIT 1',$id));
	if(!$user) die('The selected user could not be found.');
	$api = hl_twitter_get_api();
	if(!$api) die('Could not connect to Twitter. Please make sure you have linked this plugin to your Twitter account.');
	
	# Set up environment + vars
	$api->useAsynchronous(true);
	$api->setTimeout(60,60);
	set_time_limit(60);
	date_default_timezone_set(get_option('timezone_string','UTC'));
	$tweets_to_import = ($user->num_tweets>HL_TWITTER_API_MAX_TWEET_LIMIT)?HL_TWITTER_API_MAX_TWEET_LIMIT:$user->num_tweets;
	$num_pages = ceil($tweets_to_import/HL_TWITTER_API_TWEETS_PER_PAGE);
	if($num_pages==0) die('No tweets to be imported.');
	$sql_tweets = array();
	$all_responses_served = false;
	$max_retries = 5; # Try each request a maximum of 5 times
	$requests_outstanding = array();
	foreach(range(1,$num_pages) as $page) $requests_outstanding[$page] = $page;
	$args = array(
		'user_id'=>$user->twitter_user_id,
		'include_rts'=>1,
		'count'=>HL_TWITTER_API_TWEETS_PER_PAGE
	);
	
	while(!$all_responses_served) {
		if($max_retries<=0) break;
		
		$requests = array();
		
		foreach($requests_outstanding as $page) {
			$args['page'] = $page;
			$requests[$page] = $api->get('/statuses/user_timeline.json', $args);
		}
		
		foreach($requests as $page=>$response) {
			try {
				if($response->code==200) {
					if(is_array($response->response) and count($response->response)>0) {
						foreach($response->response as $raw_tweet) {
							$sql_tweets[] = $wpdb->prepare('(%s, %d, %s, %s, %s, %s, %d, %s, %f, %f)',
								$raw_tweet['id_str'], $raw_tweet['user']['id'], $raw_tweet['text'], date_i18n('Y-m-d H:i:s', strtotime($raw_tweet['created_at'])),
								strip_tags($raw_tweet['source']), $raw_tweet['in_reply_to_status_id_str'], $raw_tweet['in_reply_to_user_id'], $raw_tweet['in_reply_to_screen_name'],
								$raw_tweet['geo']['coordinates'][0], $raw_tweet['geo']['coordinates'][1]
							);
						}
					}
					unset($requests_outstanding[$page]);
				}
			} catch(Exception $e) {}
		}
		
		if(count($requests_outstanding)==0) {
			$all_responses_served = true;
			break;
		}
		
		$max_retries--;
	} // end while
	
	$count_sql_tweets = count($sql_tweets);
	if($count_sql_tweets>0) {
		$sql = '
			INSERT IGNORE INTO '.HL_TWITTER_DB_PREFIX.'tweets 
			(twitter_tweet_id, twitter_user_id, tweet, created, source, reply_tweet_id, reply_user_id, reply_screen_name, lat, lon)
			VALUES '.implode(', ',$sql_tweets);
		$wpdb->query($sql);
		$num_new_tweets = $wpdb->rows_affected;
		echo '<p>'.$count_sql_tweets.' tweets were returned by Twitter of which '.$num_new_tweets.' were saved as new tweets.</p>';
	} else {
		echo 'No tweets were returned by the Twitter API. Please try again later.';
	}
	
	die(); #### END ####
			
} // end func: hl_twitter_import_tweets_for_user





/*
	Settings page
*/
function hl_twitter_settings() {
	global $wpdb;
	if($_GET['action']=='unlink') return hl_twitter_admin_unlink();
	
	$api = hl_twitter_get_api();
	if(!$api) return hl_twitter_error('Could not connect to Twitter. Please make sure you have linked this plugin to your Twitter account.');
	$stats = new stdClass;
	$stats->hourly_limit = 'unknown';
	$stats->remaining_hits = 'unknown';
	$response = $api->get('/account/rate_limit_status.json');
	if($response and isset($response->hourly_limit) and $response->hourly_limit>0) {
		$stats = new stdClass;
		$stats->hourly_limit = number_format($response->hourly_limit);
		$stats->remaining_hits = number_format($response->remaining_hits);
	}
	
	$frequencies = array(
		'hl_10mins' => 'every 10 minutes',
		'hl_15mins' => 'every 15 minutes',
		'hl_30mins' => 'every 30 minutes',
		'hl_1hr' => 'every hour',
		'hl_3hrs' => 'every 3 hours',
		'hl_12hrs' => 'every 12 hours',
		'hl_24hrs' => 'every 24 hours'
	);
	
	$object = new stdClass;
	$object->tweet_format = get_option(HL_TWITTER_TWEET_FORMAT, HL_TWITTER_DEFAULT_TWEET_FORMAT);
	if(!function_exists('wp_get_shortlink')) $object->tweet_format = str_replace('%shortlink%', '%permalink%', $object->tweet_format);
	$object->auto_tweet = (bool) get_option(HL_TWITTER_AUTO_TWEET_SETTINGS, false);
	$object->update_frequency = get_option(HL_TWITTER_UPDATE_FREQUENCY, 'hl_1hr');
	$object->archive_slug = get_option(HL_TWITTER_ARCHIVES_SLUG_KEY, HL_TWITTER_ARCHIVES_DEFAULT_SLUG);
	
	if($_POST['submit']) {
		
		$object->tweet_format = stripslashes($_POST['object']['tweet_format']);
		update_option(HL_TWITTER_TWEET_FORMAT, $object->tweet_format);
		
		$object->auto_tweet = ($_POST['object']['auto_tweet']=='on')?true:false;
		update_option(HL_TWITTER_AUTO_TWEET_SETTINGS, $object->auto_tweet);
		
		if(array_key_exists($_POST['object']['update_frequency'], $frequencies)) {
			$object->update_frequency = $_POST['object']['update_frequency'];
			update_option(HL_TWITTER_UPDATE_FREQUENCY, $object->update_frequency);
			wp_clear_scheduled_hook(HL_TWITTER_SCHEDULED_EVENT_ACTION); # Remove cron
			wp_schedule_event(time(), $object->update_frequency, HL_TWITTER_SCHEDULED_EVENT_ACTION); # Add cron event handler
		}
		
		$new_archive_slug = sanitize_title(stripslashes(trim($_POST['object']['archive_slug'])));
		if($new_archive_slug != $object->archive_slug) {
			$object->archive_slug = sanitize_title(stripslashes(trim($_POST['object']['archive_slug'])));
			update_option(HL_TWITTER_ARCHIVES_SLUG_KEY, $object->archive_slug);
			hl_twitter_add_rewrite_rules();
		}
		
		$msg_updated = 'Your settings have been saved.';
	}
	
?>
<div class="wrap">
	<h2>Twitter Settings</h2>
	
	<?php if($msg_updated): ?><div class="updated"><p><?php echo $msg_updated; ?></p></div><?php endif; ?>
	<?php if($msg_error): ?><div class="error"><p><?php echo $msg_error; ?></p></div><?php endif; ?>
	
	<div style="float:right;width:250px;background:#FFF;border:1px solid #DFDFDF;padding:0 12px 6px;margin-left:24px;">
		
		<p>If you've found HL Twitter useful, please consider donating in order to help support it's developer. Thank you.</p>
		
		<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
			<input type="hidden" name="cmd" value="_s-xclick">
			<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----">
			<input type="image" src="https://www.paypalobjects.com/WEBSCR-640-20110429-1/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
			<img alt="" border="0" src="https://www.paypalobjects.com/WEBSCR-640-20110429-1/en_GB/i/scr/pixel.gif" width="1" height="1">
		</form>
		
	</div>
	
	<p>You currently have <strong><?php echo $stats->remaining_hits; ?></strong> out of <strong><?php echo $stats->hourly_limit; ?></strong> requests remaining to the Twitter API. This limit is reset hourly. If you wish to unlink this plugin with your Twitter account, <a href="admin.php?page=hl_twitter_settings&amp;action=unlink">click here</a>.</p>
	
	<form method="post" action="admin.php?page=hl_twitter_settings">
		<table class="form-table">
			<tr>
				<th scope="row">Tweet format</th>
				<td><input type="text" name="object[tweet_format]" value="<?php echo hl_e($object->tweet_format); ?>" class="regular-text" />
				<br />
				<strong>Supported tags:</strong>
				<ul>
					<li><code>%title%</code> The post title e.g. My Blog Post</li>
					<li><code>%permalink%</code> The link for this post e.g. mysite.com/2010/08/my-blog-post</li>
					<?php if(function_exists('wp_get_shortlink')): ?>
						<li><code>%shortlink%</code> The shortlink for this post e.g. mysite.com/?p=123 or bit.ly/abc123</li>
					<?php endif; ?>
					<li><code>%date%</code> The date of publication as set in your Settings e.g. <?php echo date_i18n(get_option('date_format')); ?></li>
					<li><code>%time%</code> The time of publication as set in your Settings e.g. <?php echo date_i18n(get_option('time_format')); ?></li>
					<li><code>%categories%</code> A comma separated list of categories e.g. Movies, Games, Pictures</li>
					<li><code>%tags%</code> A comma separated list of tags e.g. red, white, blue</li>
				</ul>
			</tr>
			<tr>
				<th scope="row">Automatically tweet?</th>
				<td>
					<input type="checkbox" name="object[auto_tweet]" <?php if($object->auto_tweet): ?>checked="checked"<?php endif; ?> />
					<br /><span class="description">If enabled, posts will be automatically tweeted when they are first published.</span>
				</td>
			</tr>
			
			<tr>
				<th scope="row">Archive Pages</th>
				<td>
					<?php bloginfo('wpurl'); ?>/<input type="text" name="object[archive_slug]" value="<?php echo hl_e($object->archive_slug); ?>" class="regular-text" />
					<br /><span class="description">The slug for your HL Twitter archives</span>
				</td>
			</tr>
			
			<tr>
				<th scope="row">Frequency</th>
				<td>
					Check for new tweets: 
					<select name="object[update_frequency]">
						<?php foreach($frequencies as $k=>$v): ?>
							<option value="<?php echo $k; ?>" <?php if($object->update_frequency==$k) echo 'selected="selected"'; ?>><?php echo $v; ?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			
		</table>
		<div class="submit">
			<input type="submit" name="submit" value="Save" class="button-primary" />
		</div>
	</form>
</div>
<?php
} // end func: hl_twitter_settings





















/*
	Unlink a Twitter account
*/
function hl_twitter_admin_unlink() {
	
	if($_POST['submit']) {
		update_option(HL_TWITTER_OAUTH_TOKEN, '');
		update_option(HL_TWITTER_OAUTH_TOKEN_SECRET, '');
		?>
		<div class="wrap">
			<h2>Unlink Account</h2>
			<p>This plugin has now been unlinked.</p>
			<p><a href="admin.php?page=hl_twitter" class="button-primary">Continue</a></p>
		</div>
		<?php
		return;
	} // end SUBMIT
	
?>
	<div class="wrap">
		<h2>Unlink Account</h2>
		<p>Are you sure you wish to unlink this plugin with its current Twitter account? You will not be able to use this plugin until it has been linked with a Twitter account.</p>
		<form method="post" action="admin.php?page=hl_twitter_settings&amp;action=unlink">
			<p>
				<a href="admin.php?page=hl_twitter_settings" class="button-secondary">Cancel</a>
				<input type="submit" name="submit" value="Unlink" class="button-primary" />
			</p>
		</form>
	</div>
<?php
} // end func: hl_twitter_admin_unlink









/*
	Gets OAuth permission from Twitter
	  * Sends user to Twitter if not verified
	  * Accepts user back from Twitter and verifies
*/
function hl_twitter_admin_authorize_oauth() {
	
	// They already have a token
	if(hl_twitter_is_oauth_verified()) {
		echo '<div class="wrap"><h2>Connect to Twitter</h2><p>This plugin has already being linked to a Twitter account. To unlink, <a href="admin.php?page=hl_twitter_settings&amp;action=unlink">click here</a></p></div>';
		return;
	}
	
	// Callback from Twitter, validate, verify + save
	if($_REQUEST['oauth_verifier']!='') {
		$api = new EpiTwitter(HL_TWITTER_OAUTH_CONSUMER_KEY, HL_TWITTER_OAUTH_CONSUMER_SECRET);
		$api->setToken($_GET['oauth_token']);
		$token = $api->getAccessToken(array('oauth_verifier'=>$_GET['oauth_verifier']));
		$api->setToken($token->oauth_token, $token->oauth_token_secret);
		if($token and $token->oauth_token!='' and $token->oauth_token_secret) {
			update_option(HL_TWITTER_OAUTH_TOKEN, $token->oauth_token);
			update_option(HL_TWITTER_OAUTH_TOKEN_SECRET, $token->oauth_token_secret);
			echo '<div class="wrap"><h2>Connect to Twitter</h2><p>You have successfully linked to Twitter.</p><p><a href="admin.php?page=hl_twitter">View tweets</a> | <a href="admin.php?page=hl_twitter_users&amp;action=new">Add a new user</a></p></div>';
			return;
		}
		echo '<div class="wrap"><h2>Connect to Twitter</h2><p>An error occurred while trying to connect to Twitter. Please try again later.</p></div>';
		return;
	}
	
	// Check cURL support
	if(!function_exists('curl_exec')) {
		return hl_twitter_error('cURL support was not found on this server. Please make sure cURL is enabled for PHP and try again.');
	}
	
	// They are not connected to Twitter yet + haven't requested a token
	?>
	<div class="wrap">
		<h2>Connect to Twitter</h2>
		<p>To use HL Twitter you must first authorise it via Twitter. To connect, click on the link below.</p>
		<p id="hl_twitter_oauth_get_authorize_url">Loading...</p>
	</div>
	<script type="text/javascript" >
	jQuery(document).ready(function($) {
		$.post(ajaxurl, { action: 'hl_twitter_oauth_get_authorize_url' }, function(response) {
			$("#hl_twitter_oauth_get_authorize_url").html(response);
		});
	});
	</script>
	<?php
	return;
	
} // end func: hl_twitter_admin_authorize_oauth




/*
	Generates a new request URL for this user
	  * Called by hl_twitter_admin_authorize_oauth
*/
add_action('wp_ajax_hl_twitter_oauth_get_authorize_url', 'hl_twitter_oauth_get_authorize_url');
function hl_twitter_oauth_get_authorize_url() {
	$api = new EpiTwitter(HL_TWITTER_OAUTH_CONSUMER_KEY, HL_TWITTER_OAUTH_CONSUMER_SECRET);
	$url = $api->getAuthenticateUrl(null, array('oauth_callback'=>HL_TWITTER_OAUTH_CALLBACK));
	echo '<a href="'.$url.'" class="button-primary">Connect to Twitter</a>';
	die();
} // end func: hl_twitter_oauth_get_authorize_url













/*
  Display an error page with optional message
/*/
function hl_twitter_error($msg=false) {
?>
  <div class="wrap">
    <h2>An Error Occurred</h2>
    <?php if($msg!=''): ?>
      <div class="error"><p><?php echo $msg; ?></p></div>
    <?php endif; ?>
    <p>Unfortunately an error occurred while trying to handle your request that the system could not resolve. Please try again.</p>
  </div>
<?php
} // end func: hl_twitter_error

