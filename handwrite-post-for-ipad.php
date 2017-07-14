<?php
/*
Plugin Name: handwrite-post-for-ipad
Plugin URI: http://ytkm.naobun.com/wordpress
Description: exclusive for iPad to use handwriting post
Version: 0.1
Author: yutaka
Author URI: http://ytkm.naobun.com/wordpress
*/

/*  Copyright 2010 yutaka miyawaki
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

add_action('admin_menu', 'my_plugin_menu');

function my_plugin_menu() {

$defaultCanvasFrames = 4;
$defaultImageSizeW = 440;
$defaultImageSizeH = 330;

if(!get_option('hpfi-canvasFrames')){
    add_option('hpfi-canvasFrames', $defaultCanvasFrames);
    add_option('hpfi-ImageSizeW',$defaultImageSizeW);
    add_option('hpfi-ImageSizeH', $defaultImageSizeH);
}
  add_posts_page('handwrite post for ipad', 'handwrite post', 8, __FILE__, 'hpfi_postPage');
  add_options_page('handwrite post for ipad', 'handwrite post', 8, __FILE__, 'hpfi_configPage');
}

function hpfi_postPage() {
/** Load WordPress Administration Bootstrap */
require_once(ABSPATH.'wp-admin/admin.php');
$parent_file = 'edit.php';
$submenu_file = 'post-new.php';

$post_type = 'post';

$post_type_object = get_post_type_object($post_type);

$title = $post_type_object->labels->add_new_item;

$editing = true;

$post = get_default_post_to_edit( $post_type, true );
$post_ID = $post->ID;
$form_action = 'editpost';
include('postform.php');

}

function hpfi_configPage(){
    include(WP_PLUGIN_DIR.'/handwrite-post-for-ipad/options.php');
}

?>