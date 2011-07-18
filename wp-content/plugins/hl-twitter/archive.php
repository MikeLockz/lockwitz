<?php if(!HL_TWITTER_LOADED) die('Direct script access denied.');


function hl_twitter_display_archive_page($arg_username=false, $arg_year=false, $arg_month=false, $arg_day=false, $arg_page=false, $arg_search=false) {
	global $wpdb;
	global $hl_twitter_params;
	
	$hl_twitter_params = (object) array(
		'username' => false,
		'twitteruserid' => false,
		'year' => false,
		'month' => false,
		'day' => false,
		'search' => false,
		'order_by' => 'created',
		'order' => 'desc',
		'page' => 1,
		'per_page' => 20
	);
	
	# Parse args
	if($arg_username!='-' and $arg_username!='') {
		$temp_username = preg_replace('/[^a-z0-9_]/i', '', $arg_username);
		$temp_user_id = $wpdb->get_var($wpdb->prepare('SELECT twitter_user_id FROM '.HL_TWITTER_DB_PREFIX.'users WHERE screen_name=%s LIMIT 1', $temp_username));
		if($temp_user_id and $temp_user_id>0) {
			$hl_twitter_params->username = $temp_username;
			$hl_twitter_params->twitteruserid = absint($temp_user_id);
		}
	}
	if($arg_search!='') {
		$temp_search = stripslashes(trim($arg_search));
		if($temp_search=='') break;
		$hl_twitter_params->search = $temp_search;
	}
	if($arg_year>=2000 and $arg_year<=date('Y')) $hl_twitter_params->year = absint($arg_year);
	if($arg_month>=1 and $arg_month<=12) $hl_twitter_params->month = absint($arg_month);
	if($arg_day>=1 and $arg_day<=31) $hl_twitter_params->day = absint($arg_day);
	if($arg_page>=1) $hl_twitter_params->page = absint($arg_page);
	
	
	# Redirect if form submission
	if($_GET['username']!='' or $_GET['month']!='') {
		$change = array('page'=>1);
		if($_GET['username']) $change['username'] = $_GET['username'];
		if($_GET['month']!='') {
			if($_GET['month']!='-') {
				$change['year'] = substr($_GET['month'],0,4);
				$change['month'] = substr($_GET['month'],5);
			} else {
				$change['year'] = false;
				$change['month'] = false;
			}
		}
		$url = hl_twitter_archive_link($change);
		wp_redirect($url);
		die();
	}
	
	
	# Generate query object
	$query = hl_twitter_build_tweets_query_object($hl_twitter_params);
	
	# Output object
	$data = new stdClass;
	$data->users = $wpdb->get_results('SELECT screen_name, name, num_tweets, avatar FROM '.HL_TWITTER_DB_PREFIX.'users ORDER BY screen_name ASC');
	$data->num_users = $wpdb->num_rows;
	$distinct_months = get_transient(HL_TWITTER_TRANSIENT_DISTINCT_MONTHS_KEY);
	if(!$distinct_months) {
		$distinct_months = $wpdb->get_results('SELECT YEAR(created) AS year, MONTH(created) AS month, COUNT(*) AS num FROM '.HL_TWITTER_DB_PREFIX.'tweets GROUP BY YEAR(created), MONTH(created) ORDER BY created DESC');
		set_transient(HL_TWITTER_TRANSIENT_DISTINCT_MONTHS_KEY, $distinct_months, 86400); # cache for a day
	}
	$data->distinct_months = (array) $distinct_months;
	
	$data->total_tweets = 0; # all tweets available
	$data->num_tweets = 0; # all tweets on page
	$data->tweets = array();
	$data->order_by = $hl_twitter_params->order_by;
	$data->order = $hl_twitter_params->order;
	
	$data->total_pages = 0;
	$data->current_page = $hl_twitter_params->page;
	$data->has_previous_page = false;
	$data->has_next_page = false;
	
	$data->is_single_user = $hl_twitter_params->username;
	$data->is_search = $hl_twitter_params->search;
	$data->is_year = $hl_twitter_params->year;
	$data->is_month = $hl_twitter_params->month;
	$data->is_day = $hl_twitter_params->day;
	if($data->is_year) $data->timestamp = strtotime($data->is_year .'-'. (($data->is_month)?$data->is_month:1) .'-'. (($data->is_day)?$data->is_day:1) );
	
	$data->total_tweets = (int) $wpdb->get_var('SELECT COUNT(*) '.$query->from.' '.$query->where); # get total tweets
	$data->total_pages = ceil($data->total_tweets/$hl_twitter_params->per_page);
	if($data->current_page>1) $data->has_previous_page = $data->current_page-1;
	if($data->current_page<$data->total_pages) $data->has_next_page = $data->current_page+1;
	if($data->total_tweets>0 and $data->current_page <= $data->total_pages) {
		$data->tweets = $wpdb->get_results($query->sql); # get tweets!
		$data->num_tweets = $wpdb->num_rows;
	}
	
	# Output template
	$current_template_directory = get_template_directory();
	if(file_exists($current_template_directory.'/hl_twitter_archive.php')) {
		include $current_template_directory.'/hl_twitter_archive.php';
	} else {
		include HL_TWITTER_DIR.'/hl_twitter_archive.php';
	}
	die();
	
} // end func: hl_twitter_display_archive_page




function hl_twitter_archive_link($change=array()) {
	global $hl_twitter_params;
	$opts = (object) array_merge((array) $hl_twitter_params, $change);
	
	$url = hl_twitter_get_archives_root().'/';
	$url .= ($opts->username)?$opts->username:'-';
	$url .= '/';
	
	if($opts->year) {
		$url .= $opts->year.'/';
		if($opts->month) {
			$url .= $opts->month.'/';
			if($opts->day) {
				$url .= $opts->day.'/';
			}
		}
	}
	
	$query_strings = array();
	if($opts->search) $query_strings['s'] = $opts->search;
	if($opts->page>1) $query_strings['page'] = $opts->page;
	if(count($query_strings)>0) $url .= '?'.http_build_query($query_strings);
	
	
	return $url;
} // end func: hl_twitter_archive_link



