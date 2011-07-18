<?php if(!HL_TWITTER_LOADED) die('Direct script access denied.');


/*
	Updates all users and their tweets
*/
function hl_twitter_import($user_id=false) {
	global $wpdb;
	$timer_start = microtime(true);
	$lines = array();
	$api = hl_twitter_get_api();
	if(!$api) return array('status'=>'error','lines'=>array('Could not connect to Twitter. Please make sure you have linked this plugin to your Twitter account.'));
	$api->setTimeout(60,60);
	set_time_limit(60);
	date_default_timezone_set(get_option('timezone_string','UTC'));
	
	$sql = '
		SELECT
			u.twitter_user_id,
			u.screen_name,
			u.pull_in_replies,
			(SELECT t.twitter_tweet_id FROM '.HL_TWITTER_DB_PREFIX.'tweets AS t WHERE t.twitter_user_id=u.twitter_user_id ORDER BY t.created DESC LIMIT 1) AS last_tweet_id 
		FROM '.HL_TWITTER_DB_PREFIX.'users AS u
	';
	$user_id = intval($user_id);
	if($user_id>0) $sql .= ' WHERE u.twitter_user_id= '.$user_id;
	$users = $wpdb->get_results($sql);
	if(!$wpdb->num_rows) return array('status'=>'error','lines'=>array('No users were found in the database. Please add at least one user to track and try again.'));
	
	$lines[] = 'Importing Twitter data for <strong>'.$wpdb->num_rows.'</strong> user(s)';
	
	$lines = array_merge($lines, hl_twitter_import_user_data($api, $users));
	
	$lines = array_merge($lines, hl_twitter_import_tweets($api, $users));
	
	$lines[] = 'Import completed in '.round(microtime(true)-$timer_start,3).' seconds';
	
	return array('result'=>'success', 'lines'=>$lines);
	
} // end func: hl_twitter_import


/*
	Inserts any new tweets
*/
function hl_twitter_import_tweets($api, $users) {
	global $wpdb;
	$lines = array();
	$api->useAsynchronous();
	$final_data = array();
	$users_outstanding = array();
	foreach($users as $user) $users_outstanding[$user->twitter_user_id] = $user;
	$users = $users_outstanding;
	$all_responses_served = false;
	$max_requests = 5; // try each request a max 5 times
	
	while(!$all_responses_served) {
		if($max_requests<=0) break;
		$requests = array();
		
		foreach($users_outstanding as $user_id=>$user) {
			$args = array(
				'user_id'=>$user->twitter_user_id,
				'include_rts'=>1,
				'count'=>HL_TWITTER_API_TWEETS_PER_PAGE,
				'page'=>1
			);
			if($user->last_tweet_id!='') {
				$args['since_id'] = $user->last_tweet_id;
			}
			$requests[$user_id] = $api->get('/statuses/user_timeline.json', $args);
		}
		
		foreach($requests as $user_id=>$response) {
			try {
				if($response->code==200) {
					$final_data[$user_id] = $response->response;
					unset($users_outstanding[$user_id]);
				}
			} catch(Exception $e) {}
		}
		
		if(count($users_outstanding)==0) {
			$all_responses_served = true;
		}
		
		$max_requests--;
	} // end while: 
	
	if(!$all_responses_served) {
		$lines[] = 'Warning: 1 or more requests failed to complete with Twitter. Please try again later.';
	}
	
	$tweet_replies = array();
	$sql_inserts = array();
	foreach($final_data as $user_id=>$tweets) {
		if(count($tweets)>0) {
			foreach($tweets as $raw_tweet) {
				
				if($users[$user_id]->pull_in_replies==1 and $raw_tweet['in_reply_to_status_id']!='') {
					$tweet_replies[$raw_tweet['in_reply_to_status_id_str']] = $raw_tweet['in_reply_to_status_id_str'];
				}
				
				$sql_inserts[] = $wpdb->prepare('(%s, %d, %s, %s, %s, %s, %d, %s, %f, %f)',
					$raw_tweet['id_str'], $raw_tweet['user']['id'], $raw_tweet['text'], date_i18n('Y-m-d H:i:s', strtotime($raw_tweet['created_at'])),
					strip_tags($raw_tweet['source']), $raw_tweet['in_reply_to_status_id_str'], $raw_tweet['in_reply_to_user_id'], $raw_tweet['in_reply_to_screen_name'],
					$raw_tweet['geo']['coordinates'][0], $raw_tweet['geo']['coordinates'][1]
				);
			}
			$lines[] = count($tweets).' new tweet(s) found for: <strong>'.$users[$user_id]->screen_name.'</strong>';
		} else {
			$lines[] = 'No new tweets found for: <strong>'.$users[$user_id]->screen_name.'</strong>';
		}
	} // end foreach: final_data
	
	$sql = '
		INSERT IGNORE INTO '.HL_TWITTER_DB_PREFIX.'tweets 
		(twitter_tweet_id, twitter_user_id, tweet, created, source, reply_tweet_id, reply_user_id, reply_screen_name, lat, lon)
		VALUES '.implode(', ',$sql_inserts);
	$wpdb->query($sql);
	
	$new_tweets = (count($sql_inserts)==0)?0:$wpdb->rows_affected;
	$lines[] = $new_tweets.' tweet(s) were added to your database.';
	
	if(count($tweet_replies)>0) {
		$result = hl_twitter_import_tweet_replies($api, $tweet_replies);
		if($result) $lines = array_merge($lines, $result);
	}
	
	return $lines;
	
} // end func: hl_twitter_import_tweets







/*
	Pulls in tweets that have been replied to by designated users
*/
function hl_twitter_import_tweet_replies($api, $tweet_reply_ids) {
	global $wpdb;
	if(count($tweet_reply_ids)==0) return false;
	
	$lines = array();
	$tweets_outstanding = $tweet_reply_ids;
	$all_responses_served = false;
	$max_requests = 3; // try each request a max 3 times
	$final_data = array();
	
	while(!$all_responses_served) {
		if($max_requests<=0) break;
		$requests = array();
		
		foreach($tweets_outstanding as $tweet_key=>$tweet_id) {
			$requests[$tweet_key] = $api->get('/statuses/show/'.$tweet_id.'.json');
		}
		
		foreach($requests as $tweet_key=>$response) {
			try {
				if($response->code==200) {
					$final_data[$tweet_key] = $response->response;
					unset($tweets_outstanding[$tweet_key]);
				}
			} catch(Exception $e) {}
		}
		
		if(count($tweets_outstanding)==0) {
			$all_responses_served = true;
		}
		
		$max_requests--;
	} // end while: $all_responses_served
	
	if(!$all_responses_served) {
		$lines[] = 'Warning: failed to retrieve 1 or more tweets from Twitter.';
	}
	
	if(count($final_data)==0) return array('No replied to tweets were returned by Twitter.');
	
	$sql_inserts = array();
	foreach($final_data as $raw_tweet) {
		$sql_inserts[] = $wpdb->prepare('(%s, %d, %s, %s, %s, %s, %s, %s, %s, %s, %d, %s, %f, %f)',
			$raw_tweet['id_str'], $raw_tweet['user']['id'], $raw_tweet['user']['name'], $raw_tweet['user']['screen_name'], $raw_tweet['user']['url'], $raw_tweet['user']['profile_image_url'],
			$raw_tweet['text'], date_i18n('Y-m-d H:i:s', strtotime($raw_tweet['created_at'])),
			strip_tags($raw_tweet['source']), $raw_tweet['in_reply_to_status_id_str'], $raw_tweet['in_reply_to_user_id'], $raw_tweet['in_reply_to_screen_name'],
			$raw_tweet['geo']['coordinates'][0], $raw_tweet['geo']['coordinates'][1]
		);
	}
	
	$sql = '
		INSERT IGNORE INTO '.HL_TWITTER_DB_PREFIX.'replies 
		(twitter_tweet_id, twitter_user_id, twitter_user_name, twitter_user_screen_name, twitter_user_url, twitter_user_avatar, tweet, created, source, reply_tweet_id, reply_user_id, reply_screen_name, lat, lon)
		VALUES '.implode(', ',$sql_inserts);
	$wpdb->query($sql);
	
	$new_tweets = (count($sql_inserts)==0)?0:$wpdb->rows_affected;
	$lines[] = $new_tweets.' replied to tweet(s) were added to your database.';
	return $lines;
	
} // end func: hl_twitter_import_tweet_replies













/*
	Updates profile information e.g. name, num_followers etc
	 # TODO: grab this info from recent tweets rather than as a separate call :/
*/
function hl_twitter_import_user_data($api, $users) {
	global $wpdb;
	$lines = array();
	$user_ids = array();
	foreach($users as $user) $user_ids[$user->twitter_user_id] = $user->twitter_user_id;
	
	$request_served = false;
	$max_requests = 3;
	
	// while we don't have a valid response...
	while(!$request_served) {
		
		// If max number of retries has been hit, fail out
		if($max_requests<=0) {
			return array('Could not retrieve user data from Twitter. Reached maximum number of retries.');
		}
		
		// get user data for all users
		try {
			$user_data = $api->get('/users/lookup.json', array('user_id'=>implode(',',$user_ids)));
		} catch(Exception $e) {
			return array('An exception was thrown by Twitter API. Please try again later.');
		}
		
		// if we have a valid response, say so!
		if($user_data->code==200) {
			$request_served = true;
		}
		
		$max_requests--;
		
	} // end while: request_served
	
	// One final check to make sure we have a valid response
	if($user_data->code!=200) {
		return array('No user data was returned by Twitter. Please try again later.');
	}
	
	foreach($user_data as $user) {
		if(!array_key_exists($user->id, $user_ids)) {
			continue;
		}
		$wpdb->update(
			HL_TWITTER_DB_PREFIX.'users',
			array(
				'screen_name'=>$user->screen_name,
				'name'=>$user->name,
				'num_friends'=>$user->friends_count,
				'num_followers'=>$user->followers_count,
				'num_tweets'=>$user->statuses_count,
				'url'=>$user->url,
				'description'=>$user->description,
				'location'=>$user->location,
				'avatar'=>$user->profile_image_url,
				'last_updated'=>date_i18n('Y-m-d H:i:s')
			),
			array('twitter_user_id'=>$user->id),
			array('%s','%s','%d','%d','%d','%s','%s','%s','%s','%s'),
			'%d'
		);
		$lines[] = 'Profile information updated for: <strong>'.$user->screen_name.'</strong>';
	}
	
	return $lines;
	
} // end func: hl_twitter_import_user_data

