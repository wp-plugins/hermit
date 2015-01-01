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

		add_action('in_admin_footer', array($this, 'music_footer'));

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
		
		return '<!--Hermit for wordpress v'.HERMIT_VERSION.' start--><div class="hermit" auto="'.$auto.'" loop="'.$loop.'" songs="'.$content.'"><div class="hermit-box"><div class="hermit-controls"><div class="hermit-button"></div><div class="hermit-detail"></div><div class="hermit-duration"></div><div class="hermit-volume"></div><div class="hermit-listbutton"></div></div><div class="hermit-prosess"><div class="hermit-loaded"></div><div class="hermit-prosess-bar"><div class="hermit-prosess-after"></div></div></div></div><div class="'.$expandClass.'"></div></div><!--Hermit for wordpress v'.HERMIT_VERSION.' end-->';
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
				//虾米部分
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

				//网易音乐部分
				case 'netease_songs' :
					$result = array(
						'status' => 200,
						'msg' => $HMTJSON->netease_songs($id)
					);
					break;

				case 'netease_album':
					$result = array(
						'status' => 200,
						'msg' => $HMTJSON->netease_album($id)
					);
					break;

				case 'netease_playlist':
					$result = array(
						'status' => 200,
						'msg' => $HMTJSON->netease_playlist($id)
					);
					break;	

				//本地音乐部分
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
	 * 输出json数据
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
			wp_enqueue_script('handlebars', HERMIT_URL . '/assets/script/handlebars.js', false, HERMIT_VERSION, false);
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

	/**
	* 音乐库
	*/
	public function main(){
		@include 'include/main.php';
	}
	
	/**
	* 帮助
	*/
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

	/**
	 * Handlebars 模板
	 */	
	public function music_footer(){
		global $pagenow;
	    if( $pagenow == "post-new.php" || $pagenow == "post.php" ){
			?>
		        <script id="hermit-template" type="text/x-handlebars-template">
					<div id="hermit-shell">
		        		<div id="hermit-shell-content" class="media-modal">
			        		<div class="media-modal-content">
			        			<a id="hermit-shell-close" class="media-modal-close" href="javascript:;"><span class="media-modal-icon"><span class="screen-reader-text">关闭媒体面板</span></span></a>
			        			<div id="hermit-shell-body">
			        				<div class="media-frame-title">
			        					<h1>插入音乐<span class="dashicons dashicons-arrow-down"></span></h1>
			        				</div>
			        				<div class="media-frame-router clearfix">
			        					<div class="media-router">
			        						<a href="javascript:;" class="media-menu-item active">虾米音乐</a>
			        						<a href="javascript:;" class="media-menu-item">网易音乐</a>
			        						<a href="javascript:;" class="media-menu-item">本地音乐</a>
			        					</div>
			        					<a class="hermit-help" href="<?php echo admin_url("admin.php?page=hermit-help");?>" target="_blank">帮助?</a>
			        				</div>
			        				<div class="media-frame-content">
			        					<ul class="hermit-ul">
			        						<li class="hermit-li active" data-type="xiami">
			        							<div>
			        								<label><input type="radio" name="type" value="songlist" checked="checked">单曲</label>
			        								<label><input type="radio" name="type" value="album">专辑</label>
			        								<label><input type="radio" name="type" value="collect">精选集</label>
			        							</div>
				        						<textarea class="hermit-textarea large-text code" cols="30" rows="9"></textarea>
			        						</li>
			        						<li class="hermit-li" data-type="netease">
			        							<div>
			        								<label><input type="radio" name="netease_type" value="netease_songs" checked="checked">单曲</label>
			        								<label><input type="radio" name="netease_type" value="netease_album">专辑</label>
			        								<label><input type="radio" name="netease_type" value="netease_playlist">歌单</label>
			        							</div>
				        						<textarea class="hermit-textarea large-text code" cols="30" rows="9"></textarea>
			        						</li>
			        						<li class="hermit-li" data-type="remote">
			        							<div id="hermit-remote-content"><ul></ul><a id="hermit-remote-sure" class="button" href="javascript:;">确认选择</a></div>
			        						</li>
			        					</ul>
			        					<div>
			        						<label for="hermit-auto"><input type="checkbox" id="hermit-auto">自动播放</label>
			        						<label for="hermit-loop"><input type="checkbox" id="hermit-loop">循环播放</label>
			        						<label for="hermit-unexpand"><input type="checkbox" id="hermit-unexpand">折叠播放列表</label>
			        					</div>
			        					<div id="hermit-preview">
			        					</div>
			        				</div>
			        				<div class="media-frame-toolbar">
			        					<div class="media-toolbar">
			        						<div class="media-toolbar-primary search-form">
			        							<a id="hermit-shell-insert" href="javascript:;" class="button media-button button-primary button-large media-button-insert" disabled="disabled">插入至文章</a>
			        						</div>
			        					</div>
			        				</div>
			        			</div>
		        			</div>
		        		</div>
		        		<div id="hermit-shell-backdrop" class="media-modal-backdrop">
		        		</div>
		        	</div>
		        </script>
		        <script id="hermit-remote-template" type="text/x-handlebars-template">
				    {{#data}}
				    	<li data-id="{{id}}">{{song_name}} - {{song_author}}</li>
		            {{/data}}
		        </script>
			<?php 
		}
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

		$result = $wpdb->get_results("SELECT id,song_name,song_author,song_url,created FROM {$hermit_table_name} ORDER BY `created` DESC");
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