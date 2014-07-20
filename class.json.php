<?php
class HermitJson{
	const API_URL_PREFIX = "http://www.xiami.com/app";
	const SONG_URL = "/android/song/id/";
	const ALBUM_URL = "/iphone/album/id/";
	const COLLECT_URL = "/android/collect?id=";
	const SONG_KEY_PREFIX = "/song/";
	const ALBUM_KEY_PREFIX = "/album/";
	const COLLECT_KEY_PREFIX = "/collect/";

	public function __construct(){
	}

	public function song($song_id){
		$key = self::SONG_KEY_PREFIX . $song_id;
		$url = self::API_URL_PREFIX . self::SONG_URL . $song_id;

		$cache = $this->get_cache($key);
		if( $cache ) return $cache;

		$response = $this->http($url);

		if(  $response && $response['status'] == "ok" ){
		    $result = array(
			    "song_id" => $response["song"]["song_id"],
			    "song_title" => $response["song"]["song_name"],
				"song_author" => $response["song"]["artist_name"],
				"song_cover" => $response["song"]["song_logo"],
				"song_src" => $response["song"]["song_location"]
			);

		    $this->set_cache($key, $result);

		    return $result;
		}

		return false;
	}

	public function song_list($song_list){
		if( !$song_list ) return false;

		$songs_array = explode(",", $song_list);
		$songs_array = array_unique($songs_array);

		if( !empty($songs_array) ){
			$result = array();
			foreach( $songs_array as $song_id ){
				$result['songs'][]  = $this->song($song_id);
			}
			$this->set_cache($key, $result);
			return $result;
		}

	    return false;
	}

	public function album($album_id){
		$key = self::ALBUM_KEY_PREFIX . $album_id;
		$url = self::API_URL_PREFIX . self::ALBUM_URL . $album_id;

		$cache = $this->get_cache($key);
		if( $cache ) return $cache;

		$response = $this->http($url); 

		if(  $response["status"]=="ok" && $response["album"] ){
			$result = $response["album"];
			$count = count($result["songs"]);

			if(  $count < 1 ) return false;

			$album = array(
				"album_id" => $result["album_id"],
				"album_title" => $result["title"],
				"album_author" => '',
				"album_type" => "albums",
				"album_cover" => $result["album_logo"],
				"album_count" => $count
			);

			foreach($result["songs"] as $key => $value){
				$song_id = $value["song_id"];
				$album["songs"][] = array(
					"song_id" => $song_id,
					"song_title" => $value["name"],
					"song_length" => $value["length"],
					"song_src" => $value["location"],
					"song_author" => $value["singers"],
					"song_cover" => $result["album_logo"]
				);
				$album["album_author"] = $value["singers"];
			}

			$this->set_cache($key, $album);
			return $album;
		}

		return false;	
	}

	public function collect($collect_id){
		$key = self::COLLECT_KEY_PREFIX . $collect_id;
		$url = self::API_URL_PREFIX . self::COLLECT_URL . $collect_id;

		$cache = $this->get_cache($key);
		if( $cache ) return $cache;

		$response = $this->http($url); 

		if(  $response["status"]=="ok" && $response["collect"] ){
			$result = $response["collect"];
			$count = count($result["songs"]);

			if(  $count < 1 ) return false;

			$collect = array(
				"collect_id" => $result["id"],
				"collect_title" => $result["name"],
				"collect_author" => $result["nick_name"],
				"collect_type" => "collects",
				"collect_cover" => $result["logo"],
				"collect_count" => $count
			);

			foreach($result["songs"] as $key => $value){
				$song_id = $value["song_id"];
				$collect["songs"][] = array(
					"song_id" => $song_id,
					"song_title" => $value["name"],
					"song_length" => 0,
					"song_src" => $value["location"],
					"song_author" => $value["singers"],
					"song_cover" => $result["logo"]
				);
			}
			$this->set_cache($key, $collect);
			return $collect;
		}

		return false;		
	}

	private function http($url){
		if( !$url ){
			return false;
		}
		
		$ip = "42.156.140.238";

        $header = array(
            'Host: www.xiami.com',
            'User-Agent: Mozilla/5.0 (Linux; Android 4.2.1; en-us; Nexus 5 Build/JOP40D) AppleWebKit/535.19 (KHTML, like Gecko) Chrome/18.0.1025.166 Mobile Safari/535.19',
            'X-FORWARDED-FOR:'.$ip,
			'CLIENT-IP:'.$ip,
			'Proxy-Connection:keep-alive',
			'X-Requested-With:XMLHttpRequest'
        );

		$ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_REFERER, 'http://www.xiami.com/web/spark');
        $cexecute = curl_exec($ch);
		@curl_close($ch);

		if ($cexecute) {
			$result = json_decode($cexecute, true);
			return $result;
		}else{
			return false;
		}
	}

	public function get_cache($key){
		$cache = get_transient($key);
		return $cache === false ? false : json_decode($cache);
	}

	public function set_cache($key, $value){
		$value  = json_encode($value);
		set_transient($key, $value, 60*60*6);
	}

	public function clear_cache($key){
		delete_transient($key);
	}
}