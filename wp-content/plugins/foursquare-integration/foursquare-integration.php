<?php
/*
Plugin Name: FourSquare Integration
Plugin URI: http://arpitshah.com/plugins/foursquare-integration/
Description: Very First FourSquare Wordpress integration plugin. Includes FourSquare checkins on Google Maps or as a List. Widget Option in Pro release (soon).
Version: 3.3.5
Author: Arpit Shah
Author URI: http://arpitshah.com
*/

/*
    Copyright (C) 2004-11 ArpitShah.com

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


// Some default options

add_option('wp_foursquare_integration_feed_url', '');
add_option('wp_foursquare_integration_feed_count', '');

add_option('wp_foursquare_integration_map_enable', '-1');
add_option('wp_foursquare_integration_height', '300');
add_option('wp_foursquare_integration_width', '400');

add_option('wp_foursquare_integration_widget_map_enable', '-1');
add_option('wp_foursquare_integration_widget_title', '4sq Checkins');
add_option('wp_foursquare_integration_widget_width', '200');
add_option('wp_foursquare_integration_widget_height', '200');

function filter_wp_foursquare_integration_profile($content)
{
    if (strpos($content, "<!--wp_foursquare_integration-->") !== FALSE)
    {
        $content = preg_replace('/<p>\s*<!--(.*)-->\s*<\/p>/i', "<!--$1-->", $content);
        $content = str_replace('<!--wp_foursquare_integration-->', wp_foursquare_integration_profile(), $content);
    }
    return $content;
}

function wp_foursquare_integration_profile()
{
	$map_enable = get_option('wp_foursquare_integration_map_enable');
	$foursq_height = get_option('wp_foursquare_integration_height');
	$foursq_width = get_option('wp_foursquare_integration_width');
	$foursq_feed_URL = get_option('wp_foursquare_integration_feed_url');
	$foursq_counts = get_option('wp_foursquare_integration_feed_count');
	$foursq_final_feed_URL = 'http://feeds.foursquare.com/history/'.$foursq_feed_URL.'.rss';
	$foursq_object = simplexml_load_file($foursq_final_feed_URL . '?count=' . $foursq_counts);
	$items = $foursq_object->channel;
	$foursq_checkin = $items->item;

	$postheader = "FourSquare Check-ins:";

	$foursq_credit = "FourSquare Integraton";

	$final_output = '<b>' . $postheader . '</b><br><ul>';
	$count = 0;
	foreach ($foursq_checkin as $item) {
		if ($item->link != '') {
			$final_output = $final_output . '<li><a href="'. $item->link .'">' . $item->title . '</a></li>';
		$count++;
		if ($count == $foursq_counts) {break;}
		}
	}
	$final_output = $final_output . '</ul>';

	if ($map_enable == 1)
	{

		$final_map = '<iframe width="' . $foursq_width . '" height="' . $foursq_height . '" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="http://maps.google.com/maps?f=q&amp;source=s_q&amp;hl=en&amp;geocode=&amp;q=http://feeds.foursquare.com/history/'.$foursq_feed_URL.'.kml&amp;ie=UTF8&amp;output=embed"></iframe><br>';

		$final_output = $final_output . $final_map;
	}

	$final_output = $final_output;

	return $final_output;

}

function wp_foursquare_integration_widget()
{

	$foursq_widget_map_enable = get_option('wp_foursquare_integration_widget_map_enable');
	$foursq_widget_height = get_option('wp_foursquare_integration_widget_height');
	$foursq_widget_width = get_option('wp_foursquare_integration_widget_width');
	$foursq_feed_URL = get_option('wp_foursquare_integration_feed_url');
	$foursq_counts = get_option('wp_foursquare_integration_feed_count');
	$foursq_final_feed_URL = 'http://feeds.foursquare.com/history/'.$foursq_feed_URL.'.rss';
	$foursq_object = simplexml_load_file($foursq_final_feed_URL . '?count=' . $foursq_counts);
	$items = $foursq_object->channel;
	$foursq_checkin = $items->item;

	$foursq_credit = "FourSquare Integraton";

	$final_output = '<ul>';
	$count = 0;
	foreach ($foursq_checkin as $item) {
		if ($item->link != '') {
			$final_output = $final_output . '<li><a href="'. $item->link .'">' . $item->title . '</a></li>';
		$count++;
		if ($count == $foursq_counts) {break;}
		}
	}
	$final_output = $final_output . '</ul>';

	if ($foursq_widget_map_enable == 1)
	{

		$final_map = '<iframe width="' . $foursq_widget_width . '" height="' . $foursq_widget_height . '" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="http://maps.google.com/maps?f=q&amp;source=s_q&amp;hl=en&amp;geocode=&amp;q=http://feeds.foursquare.com/history/'.$foursq_feed_URL.'.kml&amp;ie=UTF8&amp;output=embed"></iframe><br>';

		$final_output = $final_output . $final_map;
	}

	$final_output = $final_output;

	return $final_output;
}




function wp_foursquare_integration_add_option_page() {
    if (function_exists('add_options_page')) {
        add_options_page('FourSquare Integration', '4sq Integration', 8, __FILE__, 'wp_foursquare_integration_options_page');
    }
}

function wp_foursquare_integration_options_page() {

    if (isset($_POST['info_update']))
    {
		update_option('wp_foursquare_integration_feed_url', stripslashes_deep((string)$_POST["wp_foursquare_integration_feed_url"]));
        update_option('wp_foursquare_integration_feed_count', (string)$_POST["wp_foursquare_integration_feed_count"]);

		update_option('wp_foursquare_integration_map_enable', ($_POST['wp_foursquare_integration_map_enable']=='1') ? '1':'-1' );
		update_option('wp_foursquare_integration_height', (string)$_POST['wp_foursquare_integration_height']);
		update_option('wp_foursquare_integration_width', (string)$_POST['wp_foursquare_integration_width']);

		update_option('wp_foursquare_integration_widget_map_enable', ($_POST['wp_foursquare_integration_widget_map_enable']=='1') ? '1':'-1' );
        update_option('wp_foursquare_integration_widget_title', (string)$_POST["wp_foursquare_integration_widget_title"]);
        update_option('wp_foursquare_integration_widget_width', (string)$_POST["wp_foursquare_integration_widget_width"]);
        update_option('wp_foursquare_integration_widget_height', (string)$_POST["wp_foursquare_integration_widget_height"]);

        echo '<div id="message" class="updated fade"><p><strong>Settings Updated.</strong></p></div>';
        echo '</strong></p></div>';
    }

	$icon_url = get_bloginfo( 'wpurl' );
  	$foursq_icon = '<img border="0" src="'.$icon_url.'/wp-content/plugins/foursquare-integration/4sq.jpg" /> ';
   	$new_icon = '<img border="0" src="'.$icon_url.'/wp-content/plugins/foursquare-integration/new.gif" /> ';

    ?>


    <div class=wrap>

    <form method="post" action="<?php echo $_SERVER["REQUEST_URI"]; ?>">
    <input type="hidden" name="info_update" id="info_update" value="true" />


    <u><h2>FourSquare Integration Options</h2></u>


	<div id="poststuff" class="metabox-holder has-right-sidebar">

		<div style="float:left;width:67%;">

			<div class="postbox">
				<h3>FourSquare Feed Information</h3>
					<div>
					<table class="form-table">

					<tr valign="top">
						<th scope="row" style="width:35%;"><label>FourSquare Feed Value?</label></th>
						<td>
						 <input name="wp_foursquare_integration_feed_url" type="text" size="50" value="<?php echo get_option('wp_foursquare_integration_feed_url'); ?>" /> Get <a href="http://foursquare.com/feeds" target="_blank">Feed Value</a><br>
						 (http://feeds.foursquare.com/history/<font color="red"><b>DEW0K1.......K5Y1</b></font>.rss)
						</td>
					</tr>

						<tr valign="top" class="alternate">
							<th scope="row" style="width:35%;"><label>Number of feed entries?</label></th>
							<td>
							 <input name="wp_foursquare_integration_feed_count" type="text" size="15" value="<?php echo get_option('wp_foursquare_integration_feed_count'); ?>" />
							 <br>
							 <code>NOTE:</code> Only applicable to List view.
							</td>
						</tr>
						</table>
					</div>
			</div>
			<div class="postbox">
			<h3>Map details for Post/Page <?=$new_icon?></h3>
				<div>
				<table class="form-table">

				<tr valign="top" class="alternate">
					<th scope="row" style="width:35%;"><label>Enable Map on <code>Post/Page?</code></label></th>
					<td>
					<input name="wp_foursquare_integration_map_enable" type="checkbox"<?php if(get_option('wp_foursquare_integration_map_enable')!='-1') echo 'checked="checked"'; ?> value="1" /> <code>Check</code> to Enable Map
					<br>
					<code>NOTE:</code> List view always enabled
					</td>
				</tr>

				<tr valign="top">
					<th scope="row" style="width:35%;"><label>Map Width</label></th>
					<td>
					 <input name="wp_foursquare_integration_width" type="text" size="15" value="<?php echo get_option('wp_foursquare_integration_width'); ?>" />
					</td>
				</tr>

				<tr valign="top" class="alternate">
					<th scope="row" style="width:35%;"><label>Map Height</label></th>
					<td>
					 <input name="wp_foursquare_integration_height" type="text" size="15" value="<?php echo get_option('wp_foursquare_integration_height'); ?>" />
					</td>
				</tr>
				</table>
				</div>
			</div>

			<div class="postbox">
			<h3>Map details for Widgets <?=$new_icon?></h3>
				<div>
				<table class="form-table">
				<tr valign="top" class="alternate">
					<th scope="row" style="width:35%;"><label>Enable Map on <code>Widget Area?</code></label></th>
					<td>
					<input name="wp_foursquare_integration_widget_map_enable" type="checkbox"<?php if(get_option('wp_foursquare_integration_widget_map_enable')!='-1') echo 'checked="checked"'; ?> value="1" /> <code>Check</code> to Enable Map
					<br>
					<code>NOTE:</code> List view always enabled
					</td>
				</tr>

				<tr valign="top" class="alternate">
					<th scope="row" style="width:35%;"><label>Widget Title</label></th>
					<td>
					  <input name="wp_foursquare_integration_widget_title" type="text" size="25" value="<?php echo get_option('wp_foursquare_integration_widget_title'); ?>" />
					  </td>
				</tr>

				<tr valign="top">
					<th scope="row" style="width:35%;"><label>Widget Width</label></th>
					<td>
					 <input name="wp_foursquare_integration_widget_width" type="text" size="15" value="<?php echo get_option('wp_foursquare_integration_widget_width'); ?>" />
					</td>
				</tr>

				<tr valign="top" class="alternate">
					<th scope="row" style="width:35%;"><label>Widget Height</label></th>
					<td>
					 <input name="wp_foursquare_integration_widget_height" type="text" size="15" value="<?php echo get_option('wp_foursquare_integration_widget_height'); ?>" />
					</td>
				</tr>
				</table>
				</div>
			</div>

   		 <div class="submit">
	        <input type="submit" name="info_update" class="button-primary" value="<?php _e('Update options'); ?> &raquo;" />

	    </div>
    </form>

</div>

	</div>
    </div><?php
}

function show_wp_foursquare_integration_profile_widget($args)
{
	extract($args);
	$wp_foursquare_integration_widget_title1 = get_option('wp_foursquare_integration_widget_title');
	echo $before_widget;
	echo $before_title . $wp_foursquare_integration_widget_title1 . $after_title;
    echo wp_foursquare_integration_widget();
    echo $after_widget;
}

function wp_foursquare_integration_profile_widget_control()
{
    ?>
    <p>
    <? _e("Please go to <b>Settings -> 4sq Integration</b> for options. <br><br> Available options: <br> 1) Widget Title <br> 2) Widget Width  <br> 3) Widget Height <br> 4) Enable Map Also??"); ?>
    </p>
    <?php
}

function widget_wp_foursquare_integration_profile_init()
{
    $widget_options = array('classname' => 'widget_wp_foursquare_integration_profile', 'description' => __( "Display FourSquare Widget") );
    wp_register_sidebar_widget('wp_foursquare_integration_profile_widgets', __('4sq Integration'), 'show_wp_foursquare_integration_profile_widget', $widget_options);
    wp_register_widget_control('wp_foursquare_integration_profile_widgets', __('4sq Integration'), 'wp_foursquare_integration_profile_widget_control' );
}

add_filter('the_content', 'filter_wp_foursquare_integration_profile');
add_action('init', 'widget_wp_foursquare_integration_profile_init');
add_action('admin_menu', 'wp_foursquare_integration_add_option_page');

?>