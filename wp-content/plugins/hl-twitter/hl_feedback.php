<?php
/*
	A generalised class for providing Feedback about HL plugins
	Author: Luke Lanchester <luke@lukelanchester.com>
	Version: 2010.9.12
*/

if(!class_exists('hl_feedback')):
class hl_feedback {
	
	
	private $url_endpoint = 'http://hybridlogic.co.uk/hl-plugin-feedback.php';
	private $feedback_types = array(
		'general' => 'General feedback',
		'bug' => 'Bug report',
		'request' => 'Feature request',
		'question' => 'Plugin question',
		'other' => 'Other'
	);
	private $data;
	
	
	/*
		Generate HL Feedback model
	*/
	public function __construct($plugin_key, $plugin_root_file) {
		global $current_user;
		get_currentuserinfo();
		$plugin_data = get_plugin_data($plugin_root_file);
		$this->data = new stdClass;
		$this->data->name = $current_user->display_name;
		$this->data->email = $current_user->user_email;
		$this->data->plugin_key = $plugin_key;
		$this->data->plugin_name = $plugin_data['Name'];
		$this->data->plugin_version = $plugin_data['Version'];
		$this->data->site_hash = md5(get_bloginfo('url')); // Non-reversible hash, purely used to stop spam abuse
		$this->data->diagnostics = $this->get_diagnostics(); 
	} // end func: __construct
	
	
	/*
		Display the HL Feedback page
	*/
	public function render() {
		$result = array();
		if($_POST['submit']) {
			$result = $this->send();
			if($result['status']=='success') {
				return $this->success_page();
			}
		}
		$this->form($result);
	} // end func: render
	
	
	/*
		Send a feedback request
	*/
	private function send() {
		
		$this->data->name = stripslashes($_POST['object']['name']);
		$this->date->email = filter_var($_POST['object']['email'], FILTER_VALIDATE_EMAIL);
		$this->data->type = stripslashes($_POST['object']['type']);
		$this->data->message = stripslashes($_POST['object']['message']);
		
		$errors = array();
		if($this->data->name=='') $errors[] = 'Please make sure you have entered a name.';
		if($this->date->email=='') $errors[] = 'Please make sure you have entered a valid email address.';
		if(!array_key_exists($this->data->type, $this->feedback_types)) $errors[] = 'Please make sure you have selected a feedback type.';
		if($this->data->message=='') $errors[] = 'Please make sure you have entered a message.';
		if(strlen($this->data->message)>2500) $errors[] = 'Please make sure your message is less than 2500 characters in length.';
		
		if(count($errors)>0) {
			return array('status'=>'error', 'errors'=>$errors);
		}
		
		$response = wp_remote_post(
			$this->url_endpoint,
			array(
				'body' => $this->data
			)
		);
		
		if(!$response or is_wp_error($response) or $response['response']['code']!=200 or $response['body']=='') {
			return array('status'=>'error', 'errors'=>array('Could not connect to remote server. Please try again later.'));
		}
		
		$json = json_decode($response['body']);
		if(!$json or !is_object($json) or $json->status=='') {
			return array('status'=>'error', 'errors'=>array('Could not process response from server. Please try again later.'));
		}
		
		if($json->status=='success') {
			return array('status'=>'success');
		}
		
		return array('status'=>'error', 'errors'=>array('An error occurred on the remote server and your feedback could not be saved. Please try again later.'));
		
	} // end func: send
	
	
	/*
		Thank you page
	*/
	private function success_page() {
		?>
		<div class="wrap">
			<div class="updated"><p>Feedback sent successfully.</p></div>
			<h2><?php echo $this->data->plugin_name; ?> Feedback</h2>
			<p>Thank you for getting in touch, all communications are welcome. Your message has been received by the plugin developer(s) and will be viewed as soon as possible however due to differing timezones and other constraints an immediate response cannot be guaranteed.</p>
		</div>
		<?php
	} // end func: success_page
	
	
	/*
		Display Feedback form
	*/
	private function form($result=array()) {
		?>
		<div class="wrap">
			
			<?php if($result['status']=='error'): ?><div class="error"><ul style="padding-top:6px;"><li><?php echo implode('</li><li>',$result['errors']); ?></li></ul></div><?php endif; ?>
			
			<h2><?php echo $this->data->plugin_name; ?> Feedback</h2>
			<p>Welcome to the feedback page for <?php echo $this->data->plugin_name; ?>. Here you can quickly and easily send messages to the original plugin developer(s). If you are having trouble getting something to work, would like to know how to perform a certain action or have a new feature request just use the form below to get in touch.</p>
			
			<form method="post" action="">
				<table class="form-table">
					<tr>
						<th scope="row">Plugin</th>
						<td><strong><?php echo $this->data->plugin_name; ?></strong> (version <?php echo $this->data->plugin_version; ?>)</td>
					</tr>
					<tr>
						<th scope="row">Your name</th>
						<td><input type="text" name="object[name]" value="<?php echo $this->e($this->data->name); ?>" class="regular-text" /></td>
					</tr>
					<tr>
						<th scope="row">Your email address</th>
						<td>
							<input type="text" name="object[email]" value="<?php echo $this->e($this->data->email); ?>" class="regular-text" />
							<br /><span class="description">This will only be used to reply to you</span>
						</td>
					</tr>
					<tr>
						<th scope="row">Feedback type</th>
						<td>
							<select name="object[type]">
								<?php foreach($this->feedback_types as $k=>$v): ?>
									<option value="<?php echo $k; ?>" <?php if($k==$this->data->type) echo 'selected="selected"'; ?>><?php echo $v; ?></option>
								<?php endforeach; ?>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row">Your message<br /><span class="description">Max 2500 chars</span></th>
						<td>
							<textarea name="object[message]" class="large-text" rows="10" cols="50"><?php echo $this->data->message; ?></textarea>
						</td>
					</tr>
					<?php if(is_array($this->data->diagnostics) and count($this->data->diagnostics)>0): ?>
						<tr>
							<th scope="row">Diagnostic data</th>
							<td>
								The following information will also be sent with your message, it allows us to more easily identify any potential problems. No personally identifiable data will be transmitted except for what you enter above.
								<textarea name="object[message]" class="large-text" rows="5" cols="50" disabled="disabled"><?php foreach($this->data->diagnostics as $k=>$v): ?><?php echo $this->e($k.': '.$v."\n"); ?><?php endforeach; ?></textarea>
							</td>
						</tr>
					<?php endif; ?>
					
				</table>
				<div class="submit">
					<input type="submit" name="submit" value="Send" class="button-primary" />
				</div>
			</form>
			<p class="description">All data sent is confidential and will not be shared with third parties. We endeavour to respond to all enquiries as quickly as possible but delays may be encountered.</p>
		</div>
		<?php
	} // end func: form
	
	
	/*
		Sanitise output
	*/
	private function e($str) {
		return htmlspecialchars($str);
	} // end func: e
	
	
	/*
		Returns an array of system variables including OS, versions etc
	*/
	private function get_diagnostics() {
		global $wpdb, $wp_version;
		$p = $this->phpinfo_array();
		
		$d = array();
		$d['host_os'] = $p['PHP Configuration']['System'];
		$d['server_api'] = $p['PHP Configuration']['Server API'];
		$d['php_version'] = $p['Core']['PHP Version'];
		$d['safe_mode'] = $p['Core']['safe_mode'];
		$d['mysql_version'] = $wpdb->get_var('SELECT version()');
		$d['wordpress_version'] = $wp_version;
		$d['timezone'] = $p['date']['date.timezone'];
		$d['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
		
		return $d;
	} // end func: get_diagnostics
	
	
	/*
		Returns phpinfo() as an array (why doesn't PHP offer this as a built-in?)
		  * Reproduced from http://www.php.net/manual/en/function.phpinfo.php#87463
	*/
	function phpinfo_array() {
		ob_start(); 
		phpinfo(-1);
		
		$pi = preg_replace(
		array('#^.*<body>(.*)</body>.*$#ms', '#<h2>PHP License</h2>.*$#ms',
		'#<h1>Configuration</h1>#',  "#\r?\n#", "#</(h1|h2|h3|tr)>#", '# +<#',
		"#[ \t]+#", '#&nbsp;#', '#  +#', '# class=".*?"#', '%&#039;%',
		'#<tr>(?:.*?)" src="(?:.*?)=(.*?)" alt="PHP Logo" /></a>'
		.'<h1>PHP Version (.*?)</h1>(?:\n+?)</td></tr>#',
		'#<h1><a href="(?:.*?)\?=(.*?)">PHP Credits</a></h1>#',
		'#<tr>(?:.*?)" src="(?:.*?)=(.*?)"(?:.*?)Zend Engine (.*?),(?:.*?)</tr>#',
		"# +#", '#<tr>#', '#</tr>#'),
		array('$1', '', '', '', '</$1>' . "\n", '<', ' ', ' ', ' ', '', ' ',
		'<h2>PHP Configuration</h2>'."\n".'<tr><td>PHP Version</td><td>$2</td></tr>'.
		"\n".'<tr><td>PHP Egg</td><td>$1</td></tr>',
		'<tr><td>PHP Credits Egg</td><td>$1</td></tr>',
		'<tr><td>Zend Engine</td><td>$2</td></tr>' . "\n" .
		'<tr><td>Zend Egg</td><td>$1</td></tr>', ' ', '%S%', '%E%'),
		ob_get_clean());
		
		$sections = explode('<h2>', strip_tags($pi, '<h2><th><td>'));
		unset($sections[0]);
		
		$pi = array();
		foreach($sections as $section){
			$n = substr($section, 0, strpos($section, '</h2>'));
			preg_match_all('#%S%(?:<td>(.*?)</td>)?(?:<td>(.*?)</td>)?(?:<td>(.*?)</td>)?%E%#',$section, $askapache, PREG_SET_ORDER);
			foreach($askapache as $m) $pi[$n][$m[1]]=(!isset($m[3])||$m[2]==$m[3])?$m[2]:array_slice($m,2);
		}
		
		return $pi;
	} // end func: phpinfo_array
	
	
} // end class: hl_feedback
endif;

