<?php
/*
Plugin Name: Hermit
Plugin URI: http://mufeng.me/hermit-for-wordpress.html
Description: 虾米音乐播放器 Hermit for wordpress xiami music player
Version: 1.2.0
Author: Mufeng
Author URI: http://mufeng.me
*/

define('VERSION', '1.2.0');
define('HERMIT_PLUGIN_URL', dirname(__FILE__));

global $HMT;

require HERMIT_PLUGIN_URL . '/function.php';
require HERMIT_PLUGIN_URL . '/class.hermit.php';

if(!isset($HMT)){
	$HMT = new hermit();
}

