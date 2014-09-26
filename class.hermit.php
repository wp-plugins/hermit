<?php
class hermit{
	public function __construct(){
		$this->config = get_option('hermit_settings');
		
		/**
		** 事件绑定
		**/
		add_action('admin_menu', array($this, 'menu'));
		add_shortcode('hermit',array($this,'shortcode'));
		add_action('admin_init', array($this, 'page_init'));
		add_action('wp_enqueue_scripts', array($this, 'hermit_scripts'));
		add_action('media_buttons_context', array($this,'custom_button'));
		add_filter('plugin_action_links', array($this, 'plugin_action_link'), 10, 4);
		add_action( 'wp_ajax_nopriv_hermit', array($this, 'hermit_callback'));
		add_action( 'wp_ajax_hermit', array($this, 'hermit_callback'));

		add_action( 'wp_ajax_nopriv_hermit_source', array($this, 'hermit_source_callback'));
		add_action( 'wp_ajax_hermit_source', array($this, 'hermit_source_callback'));
	}
	
	/**
	 * 载入所需要的CSS和js文件
	 */	
	 
	public function hermit_scripts() {
		global $post,$posts;
		foreach ($posts as $post) {
			if ( has_shortcode( $post->post_content, 'hermit') ){
				wp_enqueue_style('hermit-css', HERMIT_URL . '/assets/style/hermit.min-'.HERMIT_VERSION.'.css', array(), HERMIT_VERSION, 'screen');

				// JS文件在最底部加载
				wp_enqueue_script( 'hermit-js', HERMIT_URL . '/assets/script/hermit.min-'.HERMIT_VERSION.'.js', array(), HERMIT_VERSION, true);
				wp_localize_script( 'hermit-js', 'hermit', 
					array(
						"url" => HERMIT_URL . '/assets/swf/',
						"nonce" => wp_create_nonce("hermit-nonce"),
						"ajax_url" =>  admin_url() . "admin-ajax.php"
				));
				break;
			}
		}
	}
	
	/**
	 * 添加文章短代码
	 */
	public function shortcode($atts, $content=null){
		extract(shortcode_atts(array(
			'auto' => 0,
			'loop' => 0,
			'unexpand' => 0
		), $atts));

		$hermit_options = get_option('hermit_options');

		$expandClass = ($unexpand==1) ? "hermit-list unexpand" : "hermit-list";
		
		return '<!--Hermit for wordpress v'.HERMIT_VERSION.' start--><div class="hermit" auto="'.$auto.'" loop="'.$loop.'" songs="'.$content.'"><div class="hermit-box"><div class="hermit-controls"><div class="hermit-button"></div><div class="hermit-detail">单击鼠标左键播放或暂停。</div><div class="hermit-duration"></div><div class="hermit-volume"></div><div class="hermit-listbutton"></div></div><div class="hermit-prosess"><div class="hermit-loaded"></div><div class="hermit-prosess-bar"><div class="hermit-prosess-after"></div></div></div></div><div class="'.$expandClass.'"></div></div><!--Hermit for wordpress v'.HERMIT_VERSION.' end-->';
	}
	
	/**
	 * 添加写文章按钮
	 */
	public function custom_button($context) {
		$context .= "<a id='gohermit' class='button' href='javascript:;' title='添加音乐'><span class=\"wp-media-buttons-icon\"></span> 添加音乐</a>";
		return $context;
	}
	
	/**
	 * JSON请求虾米数据
	 */
	public function hermit_callback() {
		global $HMTJSON;

		$scope = $_GET['scope'];
		$id = $_GET['id'];
		$nonce = $_SERVER['HTTP_NONCE'];

		if ( !wp_verify_nonce($nonce, "hermit-nonce") ) {
			$result = array(
				'status' =>  500,
				'msg' =>  '非法请求'
			);
		}else{
			switch ($scope) {
				case 'songs' :
					$result = array(
						'status' => 200,
						'msg' => $HMTJSON->song_list($id)
					);
					break;

				case 'album':
					$result = array(
						'status' =>  200,
						'msg' => $HMTJSON->album($id)
					);
					break;

				case 'collect':
					$result = array(
						'status' =>  200,
						'msg' =>  $HMTJSON->collect($id)
					);
					break;

				case 'remote':
					$result = array(
						'status' =>  200,
						'msg' =>  $this->music_remote($id)
					);
					break;						
				
				default:
					$result = array(
						'status' =>  400,
						'msg' =>  null
					);
			}
		}

		header('Content-type: application/json');
		echo json_encode($result);
		exit;
	}	
	

	/**
	 * 
	 * @return [type] [description]
	 */
	function hermit_source_callback(){
		$type = $_POST['type'];

		$result = array(
			'msg' => 500
		);

		switch ($type) {
			case 'new':
				$this->music_new();
				$result['msg'] = 200;
				break;

			case 'delete':
				$this->music_delete();
				$result['msg'] = 200;
				break;	

			case 'update':
				$this->music_update();
				$result['msg'] = 200;
				break;

			case 'list':
				$data = $this->music_list();
				$result['msg'] = 200;
				$result['data'] = $data;
				break;	
		}

		header('Content-type: application/json');
		echo json_encode($result);
		exit;
	}

	/**
	 * 添加写文章所需要的js和css
	 */
	function page_init(){
		global $pagenow;

		wp_enqueue_style('hermit-icon', HERMIT_URL . '/assets/style/hermit.icon.css', false, HERMIT_VERSION, false);

		if( $pagenow == "post-new.php" || $pagenow == "post.php" ){
			wp_enqueue_style('hermit-post', HERMIT_URL . '/assets/style/hermit.post.css', false, HERMIT_VERSION, false);
			wp_enqueue_script('hermit-post', HERMIT_URL . '/assets/script/hermit.post.js', false, HERMIT_VERSION, false);

			wp_localize_script( 'hermit-post', 'hermit', 
				array(
					"ajax_url" =>  admin_url() . "admin-ajax.php"
			));
		}

		if( $pagenow == "admin.php" && $_GET['page'] == 'hermit' ){
			wp_enqueue_style('hermit-page', HERMIT_URL . '/assets/style/hermit.page.css', false, HERMIT_VERSION, false);
			wp_enqueue_script('handlebars', HERMIT_URL . '/assets/script/handlebars.js', false, HERMIT_VERSION, false);
			wp_enqueue_script('hermit-page', HERMIT_URL . '/assets/script/hermit.page.js', false, HERMIT_VERSION, false);

			wp_localize_script( 'hermit-page', 'hermit', 
				array(
					"ajax_url" =>  admin_url() . "admin-ajax.php"
			));
		}
	}
	
	/**
	 * 显示后台菜单
	 */
	 
	public function menu() {
		add_menu_page('Hermit 播放器', 'Hermit 播放器', 'manage_options', 'hermit');
		add_submenu_page('hermit', '音乐库', '音乐库', 'manage_options', 'hermit', array($this, 'main'));
        add_submenu_page('hermit', '说明', '说明', 'manage_options', 'hermit-help', array($this, 'help'));
	}

	public function main(){
		@include 'include/main.php';
	}
	
	public function help(){
		@include 'include/help.php';
	}
	
	/**
	 * 添加<音乐库>按钮
	 */	
	public function plugin_action_link($actions, $plugin_file, $plugin_data){
		if(strpos($plugin_file, 'hermit')!==false && is_plugin_active($plugin_file)){
			$myactions = array('option'=>'<a href="'.HERMIT_ADMIN_URL.'admin.php?page=hermit">音乐库</a>');
			$actions = array_merge($myactions,$actions);
		}
		return $actions;
	}

	private function music_remote($ids){
		global $wpdb, $hermit_table_name;

		$result = array();
		$data = $wpdb->get_results("SELECT id,song_name,song_author,song_url FROM {$hermit_table_name} WHERE id in ({$ids})");

		foreach ($data as $key => $value) {
			$result['songs'][] = array(
			    "song_id" => $value->id,
			    "song_title" => $value->song_name,
				"song_author" => $value->song_author,
				"song_src" => $value->song_url
			);
		}
		
		return $result;
	}

	private function music_new(){
		global $wpdb, $hermit_table_name;

		$song_name = $this->post('song_name');
		$song_author = $this->post('song_author');
		$song_url = $this->post('song_url');
		$created = date('Y-m-d H:i:s');

		$wpdb->insert($hermit_table_name, compact('song_name', 'song_author', 'song_url', 'created'), array('%s', '%s', '%s', '%s'));
	}

	private function music_update(){
		global $wpdb, $hermit_table_name;

		$id = $this->post('id');
		$song_name = $this->post('song_name');
		$song_author = $this->post('song_author');
		$song_url = $this->post('song_url');

		$wpdb->update( 
			$hermit_table_name, 
			compact('song_name', 'song_author', 'song_url'),
			array( 'id' => $id ), 
			array( '%s', '%s', '%s'), 
			array( '%d' ) 
		);
	}

	private function music_delete(){
		global $wpdb, $hermit_table_name;

		$idarr = $this->post('ids');
		$idarr = explode(',', $idarr);

		foreach ($idarr as $id) {
			$wpdb->delete( $hermit_table_name, compact('id'), array( '%d' ) );
		}

	}

	private function music_list(){
		global $wpdb, $hermit_table_name;

		$result = $wpdb->get_results("SELECT id,song_name,song_author,song_url,created FROM {$hermit_table_name} ORDER BY `id` DESC");
		return $result;
	}

	private function post($key){
		$key = esc_attr(esc_html($_POST[$key]));
		return $key;
	}

	private function get($key){
		$key = esc_attr(esc_html($_GET[$key]));
		return $key;
	}
}

?>