<?php
class HermitJson{
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

		    $this->set_cache($key, $result, 24);

		    return $result;
		}

		return false;	
	}

	public function netease_songs($song_list)
	{
		if( !$song_list ) return false;

		$songs_array = explode(",", $song_list);
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

			$this->set_cache($key, $album, 24);
			return $album;
		}

		return false;
	}

	public function netease_playlist($playlist_id)
	{
		$key = "/netease/playlist/$playlist_id";

		$cache = $this->get_cache($key);
		if( $cache ) return $cache;

		$url = "http://music.163.com/api/playlist/detail?id=" . $playlist_id;
    	$response = $this->netease_http($url);

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

			$this->set_cache($key, $collect, 24);
			return $collect;
		}

		return false;
	}

	private function netease_http($url)
	{
	    $refer = "http://music.163.com/";
	    $header[] = "Cookie: " . "appver=2.0.2;";
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

	public function get_cache($key){
		$cache = get_transient($key);
		return $cache === false ? false : json_decode($cache, true);
	}

	public function set_cache($key, $value, $hour=1){
		$value  = json_encode($value);
		set_transient($key, $value, 60*60*$hour);
	}

	public function clear_cache($key){
		delete_transient($key);
	}
}