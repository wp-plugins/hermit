<?php
/*
Plugin Name: Hermit
Plugin URI: http://mufeng.me/hermit-for-wordpress.html
Description: 虾米音乐播放器 Hermit for wordpress xiami music player
Version: 1.5.0
Author: Mufeng
Author URI: http://mufeng.me
*/

define('VERSION', '1.5.0');
define('HERMIT_PLUGIN_URL', dirname(__FILE__));

global $HMT, $HMTJSON;

require HERMIT_PLUGIN_URL . '/class.json.php';
require HERMIT_PLUGIN_URL . '/class.hermit.php';

if(!isset($HMT)){
	$HMT = new hermit();
}

if(!isset($HMTJSON)){
	$HMTJSON = new HermitJson();
}

