<?php
/*
Plugin Name: Easy Featured Post Widget
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

// Load Widget Class
require_once dirname(__FILE__) . '/easy-featured-post-widget-class.php';

// Initialize Widget
add_action('widgets_init', create_function('', 'return register_widget("Easy_Featured_Post_Widget");'));

?>