<?php
class HermitJson{
	const API_URL_PREFIX = "http://m.xiami.com/web/get-songs?type=0&rtype=";
	const SONG_URL = "song&id=";
	const ALBUM_URL = "album&id=";
	const COLLECT_URL = "collect&id=";
	const TOKEN_KEY = "&_xiamitoken=";
	const SONG_KEY_PREFIX = "xiami/song/";
	const ALBUM_KEY_PREFIX = "xiami/album/";
	const COLLECT_KEY_PREFIX = "xiami/collect/";
	const XIAMI_TOKEN_KEY = "_xiamitoken";

	private $token;

	public function __construct(){
		$this->get_token();
	}

	public function song($song_id){
		$key = self::SONG_KEY_PREFIX . $song_id;
		$url = self::API_URL_PREFIX . self::SONG_URL . $song_id . self::TOKEN_KEY . $this->token;

		$cache = $this->get_cache($key);
		if( $cache ) return $cache;

		$response = $this->http($url);

		if(  $response && $response['status'] == "ok" ){
		    $result = array(
			    "song_id" => $response["data"][0]["id"],
			    "song_title" => $response["data"][0]["title"],
				"song_author" => $response["data"][0]["author"],
				"song_src" => $response["data"][0]["src"]
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
			//$this->set_cache($key, $result);
			return $result;
		}

	    return false;
	}

	public function album($album_id){
		$key = self::ALBUM_KEY_PREFIX . $album_id;
		$url = self::API_URL_PREFIX . self::ALBUM_URL . $album_id . self::TOKEN_KEY . $this->token;

		$cache = $this->get_cache($key);
		if( $cache ) return $cache;

		$response = $this->http($url); 

		if(  $response["status"]=="ok" && $response["data"] ){
			$result = $response["data"];
			$count = count($result);

			if( $count < 1 ) return false;

			$album = array(
				"album_id" => $album_id,
				"album_title" => "",
				"album_author" => "",
				"album_type" => "albums",
				"album_count" => $count
			);

			foreach($result as $key => $value){

				$album["songs"][] = array(
					"song_id" => $value["id"],
					"song_title" => $value["title"],
					"song_length" => "",
					"song_src" => $value["src"],
					"song_author" => $value["author"]
				);
				$album["album_author"] = $value["author"];
			}

			$this->set_cache($key, $album);
			return $album;
		}

		return false;	
	}

	public function collect($collect_id){
		$key = self::COLLECT_KEY_PREFIX . $collect_id;
		$url = self::API_URL_PREFIX . self::COLLECT_URL . $collect_id . self::TOKEN_KEY . $this->token;

		$cache = $this->get_cache($key);
		if( $cache ) return $cache;

		$response = $this->http($url); 

		if(  $response["status"]=="ok" && $response["data"] ){
			$result = $response["data"];
			$count = count($result);

			if(  $count < 1 ) return false;

			$collect = array(
				"collect_id" => $rcollect_id,
				"collect_title" => '',
				"collect_author" => '',
				"collect_type" => "collects",
				"collect_count" => $count
			);

			foreach($result as $key => $value){

				$collect["songs"][] = array(
					"song_id" => $value["id"],
					"song_title" => $value["title"],
					"song_length" => 0,
					"song_src" => $value["src"],
					"song_author" => $value["author"]
				);

				$collect["collect_author"] = $value["author"];
			}
			$this->set_cache($key, $collect);
			return $collect;
		}

		return false;		
	}

	public function netease_song($music_id)
	{
		$key = "/netease/song/$music_id";

		$cache = $this->get_cache($key);
		if( $cache ) return $cache;

		$url = "http://music.163.com/api/song/detail/?id=" . $music_id . "&ids=%5B" . $music_id . "%5D";
    	$response = $this->netease_http($url);

		if( $response["code"]==200 && $response["songs"] ){
			//处理音乐信息
			$mp3_url = $response["songs"][0]["mp3Url"];
			$mp3_url = str_replace("http://m", "http://p", $mp3_url);
			$music_name = $response["songs"][0]["name"];
			$artists = array();

			foreach ($response["songs"][0]["artists"] as $artist) {
			    $artists[] = $artist["name"];
			}

			$artists = implode(",", $artists);

		    $result = array(
			    "song_id" => $music_id,
			    "song_title" => $music_name,
				"song_author" => $artists,
				"song_src" => $mp3_url
			);

		    $this->set_cache($key, $result);

		    return $result;
		}

		return false;	
	}

	public function netease_songs($song_list)
	{
		if( !$song_list ) return false;

		$songs_array = explode(",", $song_list);
		
		if( !is_array($songs_array) || count($songs_array) < 1){
			return false;
		}
		
		$songs_array = array_unique($songs_array);

		if( !empty($songs_array) ){
			$result = array();
			foreach( $songs_array as $song_id ){
				$result['songs'][]  = $this->netease_song($song_id);
			}
			return $result;
		}

	    return false;
	}    

	public function netease_album($album_id)
	{
		$key = "/netease/album/$album_id";

		$cache = $this->get_cache($key);
		if( $cache ) return $cache;

		$url = "http://music.163.com/api/album/" . $album_id;
    	$response = $this->netease_http($url);

    	//var_dump($response);

		if( $response["code"]==200 && $response["album"] ){
			//处理音乐信息
			$result = $response["album"]["songs"];
			$count = count($result);

			if( $count < 1 ) return false;

			$album_name = $response["album"]["name"];
			$album_author = $response["album"]["artist"]["name"];

			$album = array(
				"album_id" => $album_id,
				"album_title" => $album_name,
				"album_author" => $album_author,
				"album_type" => "albums",
				"album_count" => $count
			);

			foreach($result as $k => $value){
				$mp3_url = str_replace("http://m", "http://p", $value["mp3Url"]);
				$album["songs"][] = array(
					"song_id" => $value["id"],
					"song_title" => $value["name"],
					"song_length" => "",
					"song_src" => $mp3_url,
					"song_author" => $album_author
				);
			}

			$this->set_cache($key, $album);
			return $album;
		}

		return false;
	}

	public function netease_playlist($playlist_id)
	{
		$key = "/netease/playlist/$playlist_id";

		//$cache = $this->get_cache($key);
		//if( $cache ) return $cache;

		$url = "http://music.163.com/api/playlist/detail?id=" . $playlist_id;
    	$response = $this->netease_http($url);

    	//var_dump($response);

		if( $response["code"]==200 && $response["result"] ){
			//处理音乐信息
			$result = $response["result"]["tracks"];
			$count = count($result);

			if( $count < 1 ) return false;

			$collect_name = $response["result"]["name"];
			$collect_author = $response["result"]["creator"]["nickname"];

			$collect = array(
				"collect_id" => $collect_id,
				"collect_title" => $collect_name,
				"collect_author" => $collect_author,
				"collect_type" => "collects",
				"collect_count" => $count
			);

			foreach($result as $k => $value){
				$mp3_url = str_replace("http://m", "http://p", $value["mp3Url"]);
				$artists = array();
				foreach ($value["artists"] as $artist) {
				    $artists[] = $artist["name"];
				}

				$artists = implode(",", $artists);

				$collect["songs"][] = array(
					"song_id" => $value["id"],
					"song_title" => $value["name"],
					"song_length" => "",
					"song_src" => $mp3_url,
					"song_author" => $artists
				);
			}

			$this->set_cache($key, $collect);
			return $collect;
		}

		return false;
	}

	private function netease_http($url)
	{
	    $refer = "http://music.163.com/";
	    $header[] = "Cookie: " . "appver=1.5.0.75771;";
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
	    curl_setopt($ch, CURLOPT_REFERER, $refer);
	    $cexecute = curl_exec($ch);
	    curl_close($ch);

		if ($cexecute) {
			$result = json_decode($cexecute, true);
			return $result;
		}else{
			return false;
		}
	}

	private function http($url, $json=true){
		if( !$url ){
			return false;
		}

        $header = array(
            'Host: m.xiami.com',
            'User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 7_1_2 like Mac OS X) AppleWebKit/537.51.2 (KHTML, like Gecko) Version/7.0 Mobile/11D257 Safari/9537.53',
            'Cookie: _xiamitoken='.$this->token.'; visit=1',
            'Proxy-Connection:keep-alive',
            'X-Requested-With:XMLHttpRequest',
        	'X-FORWARDED-FOR:42.156.140.238', 
			'CLIENT-IP:42.156.140.238'
        );

		$ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_REFERER, 'http://m.xiami.com/');
        $cexecute = curl_exec($ch);
		@curl_close($ch);

		if ($cexecute) {
			if($json) $result = json_decode($cexecute, true);
			return $result;
		}else{
			return false;
		}
	}

	private function get_token(){
		$token = get_transient(self::XIAMI_TOKEN_KEY);

		if( $token ){
			$this->token = $token;
		}else{
			$XM_head = wp_remote_head('http://m.xiami.com', array(
				'headers' => array(
				    'Host: m.xiami.com',
				    'User-Agent: Mozilla/5.0 (iPhone; CPU iPhone OS 7_1_2 like Mac OS X) AppleWebKit/537.51.2 (KHTML, like Gecko) Version/7.0 Mobile/11D257 Safari/9537.53',
				    'Proxy-Connection:keep-alive',
				    'X-Requested-With:XMLHttpRequest',
        			'X-FORWARDED-FOR:42.156.140.238', 
					'CLIENT-IP:42.156.140.238'
				)
			));

			$cookies = $XM_head['cookies'];

			foreach ($cookies as $key => $cookie) {
				if( $cookie->name == '_xiamitoken' ){
					$this->token = $cookie->value;

					set_transient(self::XIAMI_TOKEN_KEY, $this->token, 60*60*100);
					break;
				}
			}
		}
	}

	public function get_cache($key){
		$cache = get_transient($key);
		return $cache === false ? false : json_decode($cache, true);
	}

	public function set_cache($key, $value, $hour=6){
		$value  = json_encode($value);
		set_transient($key, $value, 60*60*$hour);
	}

	public function clear_cache($key){
		delete_transient($key);
	}
}