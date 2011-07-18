<?php if(!HL_TWITTER_LOADED) die('Direct script access denied.');

class hl_twitter_widget extends WP_Widget {
	
	
	function widget($args, $instance) {
		hl_twitter_display_widget(
			$instance['num_tweets'], $instance['user_id'], $instance['widget_title'],
			$instance['show_avatars'], $instance['show_powered_by'], $instance['show_more_link'],
			$args['before_widget'], $args['after_widget'], $args['before_title'], $args['after_title'], $widget_file='hl_twitter_widget.php'
		);
	} // end func: widget
	
	
	function __construct() {
		parent::__construct(false, $name = 'HL Twitter', array('description'=>'Shows a list of recent tweets on your website.'));	
	} // end func: __construct
	
	
	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['widget_title'] = stripslashes($new_instance['widget_title']);
		$instance['num_tweets'] = intval($new_instance['num_tweets']);
		$instance['user_id'] = intval($new_instance['user_id']);
		$instance['show_avatars'] = ($new_instance['show_avatars']=='on')?1:0;
		$instance['show_powered_by'] = ($new_instance['show_powered_by']=='on')?1:0;
		$instance['show_more_link'] = ($new_instance['show_more_link']=='on')?1:0;
		return $instance;
	} // end func: update
	
	
	function form($instance) {
		global $wpdb;
		$users = $wpdb->get_results('SELECT twitter_user_id, screen_name FROM '.HL_TWITTER_DB_PREFIX.'users ORDER BY screen_name ASC');
		$poss_num_tweets = range(1,10);
		$poss_yesno = array(1=>'Yes', 0=>'No');
		
		$widget_title = esc_attr($instance['widget_title']);
		$user_id = intval(esc_attr($instance['user_id']));
		$num_tweets = intval(esc_attr($instance['num_tweets']));
		$show_avatars = (bool) esc_attr($instance['show_avatars']);
		$show_powered_by = (bool) esc_attr($instance['show_powered_by']);
		$show_more_link = (bool) esc_attr($instance['show_more_link']);
		
		?>
		
		<p>
			<label for="<?php echo $this->get_field_id('widget_title'); ?>"><?php _e('Title'); ?></label><br />
			<input type="text" id="<?php echo $this->get_field_id('widget_title'); ?>" name="<?php echo $this->get_field_name('widget_title'); ?>" value="<?php echo $widget_title; ?>" />
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id('user_id'); ?>"><?php _e('User'); ?></label><br />
			<select id="<?php echo $this->get_field_id('user_id'); ?>" name="<?php echo $this->get_field_name('user_id'); ?>">
				<option value="0">All users</option>
				<?php foreach($users as $user): ?>
					<option value="<?php echo $user->twitter_user_id; ?>" <?php if($user->twitter_user_id==$user_id) echo 'selected="selected"'; ?>><?php echo hl_e($user->screen_name); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id('num_tweets'); ?>"><?php _e('Tweets to show'); ?></label><br />
			<select id="<?php echo $this->get_field_id('num_tweets'); ?>" name="<?php echo $this->get_field_name('num_tweets'); ?>">
				<?php foreach($poss_num_tweets as $num): ?>
					<option value="<?php echo $num; ?>" <?php if($num_tweets==$num) echo 'selected="selected"'; ?>><?php echo $num; ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id('show_avatars'); ?>"><?php _e('Show avatars?'); ?></label><br />
			<input type="checkbox" <?php if($show_avatars) echo 'checked="checked"'; ?> id="<?php echo $this->get_field_id('show_avatars'); ?>" name="<?php echo $this->get_field_name('show_avatars'); ?>" />
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id('show_powered_by'); ?>"><?php _e('Show powered by?'); ?></label><br />
			<input type="checkbox" <?php if($show_powered_by) echo 'checked="checked"'; ?> id="<?php echo $this->get_field_id('show_powered_by'); ?>" name="<?php echo $this->get_field_name('show_powered_by'); ?>" />
		</p>
		
		<p>
			<label for="<?php echo $this->get_field_id('show_more_link'); ?>"><?php _e('Show more tweets link?'); ?></label><br />
			<input type="checkbox" <?php if($show_more_link) echo 'checked="checked"'; ?> id="<?php echo $this->get_field_id('show_more_link'); ?>" name="<?php echo $this->get_field_name('show_more_link'); ?>" />
		</p>
		
		<?php 
	} // end func: form
	
} // end class: hl_twitter_widget