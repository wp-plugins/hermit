<?php
/*
Plugin Name: Hermit
Plugin URI: http://mufeng.me/hermit-for-wordpress.html
Description: 虾米音乐播放器 Hermit for wordpress xiami music player
Version: 1.1.1
Author: Mufeng
Author URI: http://mufeng.me
*/

define('VERSION', '1.1.1');

global $HMT;
require dirname(__FILE__).'/class.hermit.php';
if(!isset($HMT)){
	$HMT = new hermit();
}
?>