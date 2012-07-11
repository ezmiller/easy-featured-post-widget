<?php
/*
Plugin Name: Feature Post Widget
Plugin URI: 
Description: Plugin provides a widget to display portions of posts in widgetized areas.
Version: 0.1
Author: eThan 
Author URI: 
License: GPL2

Copyright 2012  Ethan Miller  (email : ethanzanemiller@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 2, 
    as published by the Free Software Foundation. 
    
    You may NOT assume that you can use any other version of the GPL.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    
    The license for this software can likely be found here: 
    http://www.gnu.org/licenses/gpl-2.0.html

*/

class Feature_Post_Widget extends WP_Widget {

	function Feature_Post_Widget() {
		$widget_ops = array('classname' => 'feature_post_widget', 'description' => __('Display preview of selected posts in widget area.'));
		$this->WP_Widget('feature_post_widget', __('Feature Post Widget'), $widget_ops );
	}

	function widget( $args, $instance ) {
		extract($args);

		// get user settings
		$category_slug = empty($instance['category']) ? 'news' : $instance['category'];
		$mf_width = empty($instance['media-frame-width']) ? '220' : $instance['media-frame-width'];
		$mf_height = empty($instance['media-frame-height']) ? '120' : $instance['media-frame-height'];
		$img_offset_x = empty($instance['img-offset-x']) ? '0' : $instance['img-offset-x'];
		$img_offset_y = empty($instance['img-offset-y']) ? '0' : $instance['img-offset-y'];

		$query = new WP_Query( array( 'category_name' => $category_slug, 'posts_per_page' => 1 ) );

		// declare more variable so can turn read more on
		global $more;

		if ( $query->have_posts() ) : while ( $query->have_posts() ) : $query->the_post();

		    // turn on read more functionality
			$more = 0;

		    // try to extract a url from content
			$matches = feature_post_widget_extract_url(get_the_content($query->post->ID));
			$url = $matches[0][0];
		    $url = strtok($url, '?');  // remove any get variables from the url

		    // try to extract an image from the post content
		    $img = feature_post_widget_get_first_image_url($query->post->ID);

		    $content = $before_widget;

		    // construct video or image content
		    if ( strpos($url, 'youtu.be') || strpos($url,'youtube') ) { // a youtube vid
		    	$content .= '<iframe id="media-frame" width="' . $mf_width . '" height="' . $mf_height . '" src="';
		    	$content .= $url . '?rel=0;controls=0;wmode=opaque" frameborder="0"';
		    	$content .= ' webkitAllowFullScreen mozallowfullscreen';
		    	$content .= '  allowFullScreen></iframe>' . "\n";              
		    } 
		    elseif ( strpos($url, 'vimeo') ) { // a vimeo vid
		    	$content = '<iframe id="media-frame" width="' . $mf_width . '" height="' . $mf_height . '" src="';
		    	$content .= $matches[0][0] . '?rel=0;controls=0" frameborder="0"';
		    	$content .= ' webkitAllowFullScreen mozallowfullscreen';
		    	$content .= '  allowFullScreen></iframe>' . "\n";              
		    }
		    elseif ( strpos($url, 'soundcloud') ) { // a sound cloud widget
        		$content .= '<iframe id="media-frame" width"' . $mf_width . '" height="' . $mf_height . '" src="' . $url .'"></iframe>';
        	}
		    elseif ( strlen($img) > 0 ) { // it's an image
			    $content = '<div id="media-frame" style="overflow:hidden; width:' . $mf_width . 'px; height:' . $mf_height . 'px;">';
			    $content .= '<img src="' . $img . '" width="'. $mf_width . '"';
			    $content .= ' style="margin-top:' . $img_offset_y . 'px; margin-left:' . $img_offset_x . 'px;"/>';
			    $content .= '</div>';
			}

			// create content within div
			$content .= '<div class="feature-content">';
			$content .= '<h1>' . get_the_title($query->post->ID) . '</h1>';
			$pattern = '#(<img[^>]+\>)|(<iframe[^>]+\>)|(</iframe>)|(</img>)#i';
			$content .= '<p>' . preg_replace($pattern, '', get_the_content('View all...')) . '</p></div>'; // remove iframes or imgs from content

			$content .= $after_widget;

			echo $content;

		endwhile; endif;		
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['category'] = strip_tags($new_instance['category']);
		$instance['media-frame-width'] = strip_tags($new_instance['media-frame-width']);
		$instance['media-frame-height'] = strip_tags($new_instance['media-frame-height']);
		$instance['img-offset-x'] = strip_tags($new_instance['img-offset-x']);
		$instance['img-offset-y'] = strip_tags($new_instance['img-offset-y']);

		return $instance;
	}

	function form( $instance ) {
		
		// Set up some default widget settings.
		$defaults = array(  'category' => 'news', 
						 	'media-frame-width' => '220', 
						 	'media-frame-height' => '120', 
						 	'img-offset-x' => '0',
						 	'img-offset-y' => '0' );
		$instance = wp_parse_args( (array) $instance, $defaults ); 

		// Get a list of the existing categories.
		$existing_categories = get_categories();

		// Build form.
		$content = "<p>";
		$content .= '<label for="' . $this->get_field_id('category') . '">Post Category: </label>';
		$content .= '<select id="' . $this->get_field_id('category') . '"'; 
		$content .= ' name="' . $this->get_field_name('category') . '">';
		foreach ($existing_categories as $c) {
			$option = '<option value="' . $c->slug . '"';
			if ( $c->slug == $instance['category'] ) {
				$option .= 'selected';
			}
			$option .= '>';
			$option .= $c->cat_name;
			$option .= '</option>';
			$content .= $option;
		}
		$content .= '</select>';
		$content .= "</p>";

		$content .= "<p>";
		$content .= '<label for="' . $this->get_field_id('media-frame-width') . '">Media Frame Width (in px): </label>';
		$content .= '<input type="number" id="' . $this->get_field_id('media-frame-width') . '"';
		$content .= ' name="' . $this->get_field_name('media-frame-width') . '"'; 
		$content .= ' value="' . $instance['media-frame-width'] . '"></input>';
		$content .= '</p>';

		$content .= "<p>";
		$content .= '<label for="' . $this->get_field_id('media-frame-height') . '">Media Frame Height (in px): </label>';
		$content .= '<input type="number" id="' . $this->get_field_id('media-frame-height') . '"';
		$content .= ' name="' . $this->get_field_name('media-frame-height') . '"';
		$content .= ' value="' . $instance['media-frame-height'] . '"/>';
		$content .= '</p>';

		$content .= "<p>";
		$content .= '<label for="' . $this->get_field_id('img-offset-x') . '">Image Offset X-axis (in px): </label>';
		$content .= '<input type="number" id="' . $this->get_field_id('img-offset-x') . '"';
		$content .= ' name="' . $this->get_field_name('img-offset-x') . '"';
		$content .= ' value="' . $instance['img-offset-x'] . '"/>';
		$content .= '</p>';

		$content .= "<p>";
		$content .= '<label for="' . $this->get_field_id('img-offset-y') . '">Image Offset Y-axis (in px): </label>';
		$content .= '<input type="number" id="' . $this->get_field_id('img-offset-y') . '"';
		$content .= ' name="' . $this->get_field_name('img-offset-y') . '"';
		$content .= ' value="' . $instance['img-offset-y'] . '"/>';
		$content .= '</p>';

		// Output form.
		echo $content;
	}
}
add_action('widgets_init', create_function('', 'return register_widget("Feature_Post_Widget");'));

/*// donate link on manage plugin page
add_filter('plugin_row_meta', 'feature_post_widget_donate_link', 10, 2);
function feature_post_widget_donate_link($links, $file) {
	if ($file == plugin_basename(__FILE__)) {
		$donate_link = '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=otto%40ottodestruct%2ecom">Donate</a>';
		$links[] = $donate_link;
	}
	return $links;
}*/

// Helper Functions //

/**
* feature_post_widget_extract_url
* 
* Extracts url from $input string.  
* Returns array urls or empty array if none found.
*
* The regex pattern to match a url can be tricky.  This pattern 
* will work in most cases.  For discussion of the possibilities: 
* http://stackoverflow.com/questions/206059/php-validation-regex-for-url
*
* @param string $input - the string from which to extract the url 
* 
*/
function feature_post_widget_extract_url($input) {
	$pattern = "/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i";
	preg_match_all($pattern, get_the_content(), $matches);
	return $matches;
}

/**
* feature_post_widget_get_first_video_url
* 
* Will return the first video url within the specified post.
* 
* @param int    $postID - the id number of the post from which to extract the video url
*
*/
function feature_post_widget_get_first_youtube_video_url($postID) {

	// See if there are any iframe tags
	preg_match_all('/<iframe[^>]+>/i', get_page($postID)->post_content, $result);

	// Were there any iframe tags?
	if ( $result ) { // Yes...
		
		// Now see if the first one has a video src (ie youtube or vimeo) 
		if ( strpos($result[0][0],'youtube') || strpos($result[0][0],'youtu.be') ) { 

			// Extract URL and return
			$vid_url = feature_post_widget_extract_url($result[0][0]);
			return $vid_url[0][0];

		}
	}
}

/**
* feature_post_widget_get_first_image_url
*
* Will return the first image url within the specified post.
*
* @param int $postID - the id number of the post from which to extract the image url
* 
*/
function feature_post_widget_get_first_image_url ($postID) {					

	// Try to get the first attachment...
	$args = array(
		'numberposts' => 1,
		'order'=> 'ASC',
		'post_mime_type' => 'image',
		'post_parent' => $postID,
		'post_status' => null,
		'post_type' => 'attachment'
	);	
	$attachments = get_children( $args );
	
	// See if there are any <img> elements in the content
	preg_match_all('/<img[^>]+>/i', get_page($postID)->post_content, $img_result);
	
	// if there are attachments get the first image...
	if ($attachments) {
		foreach($attachments as $a) {
			$img_atts = wp_get_attachment_image_src( $a->ID, 'thumbnail' )  ? wp_get_attachment_image_src( $a->ID, 'thumbnail' ) : wp_get_attachment_image_src( $attachment->ID, 'full' );
			return wp_get_attachment_url( $a->ID, 'full' );
		}
	}
	elseif ($img_result) { // otherwise if there was an image result...
		preg_match_all('/"https?[^"]*"/i',$img_result[0][0], $img_atts);
		return trim($img_atts[0][0],'"');
	}
}

?>