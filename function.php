<?php
global $JSON;

require HERMIT_PLUGIN_URL . '/class.fetchjson.php';

if(!isset($JSON)){
	$JSON = new fetchjson();
}

add_action( 'wp_ajax_nopriv_hermit', 'hermit_callback' );
add_action( 'wp_ajax_hermit', 'hermit_callback' );
function hermit_callback() {
	global $JSON;

	$scope = $_GET['scope'];
	$callback = $_GET['callback'];
	$id = $_GET['id'];

	switch ($scope) {
		case 'songs' :
			$result = array(
				'status' => 200,
				'msg' => $JSON->song_list($id)
			);
			break;

		case 'album':
			$result = array(
				'status' =>  200,
				'msg' => $JSON->album($id)
			);
			break;

		case 'collect':
			$result = array(
				'status' =>  200,
				'msg' =>  $JSON->collect($id)
			);
			break;						
		
		default:
			$result = array(
				'status' =>  400,
				'msg' =>  null
			);
	}

	header('Content-type: application/javascript');
	echo $callback. "(" . json_encode($result) . ")" ;
	exit;
}