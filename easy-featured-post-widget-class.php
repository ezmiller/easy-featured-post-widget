<?php
/**
 * feature-post-widget-class.php
 * 
 * Contains the Feature Post Widget class.
 *
 */

class Easy_Featured_Post_Widget extends WP_Widget {

	function Easy_Featured_Post_Widget() {
		$widget_ops = array('classname' => 'easy_featured_post_widget', 'description' => __('Display preview of selected post in widget area.'));
		$this->WP_Widget('easy_featured_post_widget', __('Easy Featured Post Widget'), $widget_ops );
	}

	function widget( $args, $instance ) {
		extract($args);

		// get user settings
		$post_to_display = empty($instance['post-to-display']) ? 'news' : $instance['post-to-display'];
		$set_width_in_css = $instance['set-width-in-css'];
		$set_height_in_css = $instance['set-height-in-css'];
		$mf_width = empty($instance['media-frame-width']) ? '220' : $instance['media-frame-width'];
		$mf_height = empty($instance['media-frame-height']) ? '120' : $instance['media-frame-height'];
		$img_offset_x = empty($instance['img-offset-x']) ? '0' : $instance['img-offset-x'];
		$img_offset_y = empty($instance['img-offset-y']) ? '0' : $instance['img-offset-y'];
		
		//$query = new WP_Query( array( 'category_name' => $category_slug, 'posts_per_page' => 1 ) );
		$query = new WP_Query( 'p=' . $post_to_display );

		// declare more variable so can turn read more on
		global $more;

		if ( $query->have_posts() ) : while ( $query->have_posts() ) : $query->the_post();

		    // turn on read more functionality
			$more = 0;

		    // try to extract a url from content
			$matches = easy_featured_post_widget_extract_url(get_the_content($query->post->ID));
			$url = $matches[0][0];
		    $stripped_url = strtok($url, '?');  // remove any get variables from the url

		    // try to extract an image from the post content
		    $img = easy_featured_post_widget_get_first_image_url($query->post->ID);

		    $content = $before_widget;

		    // construct video or image content
		    if ( strpos($url, 'youtu.be') || strpos($url,'youtube') ) { // a youtube vid
		    	$content .= '<iframe id="media-frame" width="' . $mf_width . 'px" height="' . $mf_height . 'px" src="';
		    	$content .= $stripped_url . '?rel=0;controls=0;wmode=opaque" frameborder="0"';
		    	$content .= ' webkitAllowFullScreen mozallowfullscreen';
		    	$content .= '  allowFullScreen></iframe>' . "\n";              
		    } 
		    elseif ( strpos($url, 'vimeo') ) { // a vimeo vid
		    	$content = '<iframe id="media-frame" width="' . $mf_width . '" height="' . $mf_height . '" src="';
		    	$content .= $stripped_url . '?rel=0;controls=0" frameborder="0"';
		    	$content .= ' webkitAllowFullScreen mozallowfullscreen';
		    	$content .= '  allowFullScreen></iframe>' . "\n";              
		    }
		    elseif ( strpos($url, 'soundcloud') ) { // a sound cloud widget
        		$content .= '<iframe id="media-frame" ';
				$content .= 'width="' . $mf_width . '"';
			    $content .= ' height="' . $mf_height . '"';
        		$content .= ' src="' . $url .'"></iframe>';
        	}
		    elseif ( strlen($img) > 0 ) { // it's an image
			    $content = '<div id="media-frame" style="overflow:hidden;';
			    $content .= 'width:' . $mf_width . 'px;';
			    $content .= 'height:' . $mf_height . 'px;';
			    $content .= '"><img src="' . $img . '" width="'. $mf_width . '"';
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
		
		$instance['post-to-display'] = strip_tags($new_instance['post-to-display']);
		$instance['media-frame-width'] = strip_tags($new_instance['media-frame-width']);
		$instance['media-frame-height'] = strip_tags($new_instance['media-frame-height']);
		$instance['img-offset-x'] = strip_tags($new_instance['img-offset-x']);
		$instance['img-offset-y'] = strip_tags($new_instance['img-offset-y']);

		return $instance;
	}

	function form( $instance ) {
		// Set up some default widget settings.
		$defaults = array(  'post-to-display' => '',
						 	'media-frame-width' => '220',
						 	'media-frame-height' => '120',
						 	'img-offset-x' => '0',
						 	'img-offset-y' => '0' );
		$instance = wp_parse_args( (array) $instance, $defaults ); 

		
		// Build form.
		/*// Get a list of the existing categories.
		$existing_categories = get_categories();
		$content = "<p>";
		$content .= '<label for="' . $this->get_field_id('category') . '">Post Category to Selec: </label><br/>';
		$content .= '<select id="' . $this->get_field_id('category') . '"'; 
		$content .= ' name="' . $this->get_field_name('category') . '">';
		$content .= <option value="All Categories"
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
		$content .= "</p>";*/

		$existing_posts = get_posts();
		$content = "<p>";
		$content .= '<label for="' . $this->get_field_id('post-to-display') . '">Post to Display: </label><br/>';
		$content .= '<select id="' . $this->get_field_id('post-to-display') . '"';
		$content .= ' name="' . $this->get_field_name('post-to-display') . '" style="width:100%;">';
		foreach ($existing_posts as $p) {
			$option = '<option value="' . $p->ID . '"';
			if ( $p->ID == $instance['post-to-display'] ) 
				$option .= 'selected';
			$option .= '>' . $p->post_title . '</option>';
			$content .= $option;
		}
		$content .= '</select>';
		$content .= '</p>';

		// Width input
		$content .= "<p>";
		$content .= '<label for="' . $this->get_field_id('media-frame-width') . '">Image/Video/Embed Width (in px): </label><br/>';
		$content .= '<input style="width:50%" type="number" id="' . $this->get_field_id('media-frame-width') . '"';
		$content .= ' name="' . $this->get_field_name('media-frame-width') . '"'; 
		$content .= ' value="' . $instance['media-frame-width'] . '"';
		if ( $instance['set-width-in-css'] ) {
			$content .= ' readonly="readonly"';
		}
		$content .= '></input>';
		$content .= '</p>';

		// Height input
		$content .= "<p>";
		$content .= '<label for="' . $this->get_field_id('media-frame-height') . '">Image/Video/Embed Height (in px): </label><br/>';
		$content .= '<input style="width:50%" type="number" id="' . $this->get_field_id('media-frame-height') . '"';
		$content .= ' name="' . $this->get_field_name('media-frame-height') . '"';
		$content .= ' value="' . $instance['media-frame-height'] . '"';
		if ( $instance['set-height-in-css'] ) {
			$content .= ' readonly="readonly"';
		}
		$content .= '></input>';
		$content .= '</p>';

		$content .= "<p>If the post contains an image, use the settings below to position the image within the frame size set above:</p>";

		// Image Offset-X
		$content .= "<p>";
		$content .= '<label for="' . $this->get_field_id('img-offset-x') . '">Image Offset X-axis (in px): </label>';
		$content .= '<input style="width:50%" type="number" id="' . $this->get_field_id('img-offset-x') . '"';
		$content .= ' name="' . $this->get_field_name('img-offset-x') . '"';
		$content .= ' value="' . $instance['img-offset-x'] . '"/>';
		$content .= '</p>';

		// Image Offset-Y
		$content .= "<p>";
		$content .= '<label for="' . $this->get_field_id('img-offset-y') . '">Image Offset Y-axis (in px): </label>';
		$content .= '<input style="width:50%" type="number" id="' . $this->get_field_id('img-offset-y') . '"';
		$content .= ' name="' . $this->get_field_name('img-offset-y') . '"';
		$content .= ' value="' . $instance['img-offset-y'] . '"/>';
		$content .= '</p>';

		// Output form.
		echo $content;
	}
}


// Helper Functions //

/**
* easy_featured_post_widget_extract_url
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
function easy_featured_post_widget_extract_url($input) {
	$pattern = "/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i";
	preg_match_all($pattern, get_the_content(), $matches);
	return $matches;
}

/**
* easy_featured_post_widget_get_first_youtube_video_url
* 
* Will return the first video url within the specified post.
* 
* @param int    $postID - the id number of the post from which to extract the video url
*
*/
function easy_featured_post_widget_get_first_youtube_video_url($postID) {

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
* easy_featured_post_widget_get_first_image_url
i
*
* Will return the first image url within the specified post.
*
* @param int $postID - the id number of the post from which to extract the image url
* 
*/
function easy_featured_post_widget_get_first_image_url ($postID) {					
	

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