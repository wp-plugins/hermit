<?php
class hermit{
	public function __construct(){
		$this->config = get_option('hermit_setting');

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
		$strategy = $this->config['strategy'];

		if( $strategy == 1 ){
			global $post,$posts;
			foreach ($posts as $post) {
				if ( has_shortcode( $post->post_content, 'hermit') ){
					$this->_load_scripts();
					break;
				}
			}
		}else{
			$this->_load_scripts();
		}
	}

	/**
	 * 加载资源
	 */
	private function _load_scripts(){
		$tips = $this->config['tips'];
        $jsplace = (bool) $this->config['jsplace'];

		wp_enqueue_style('hermit-css', HERMIT_URL . "/assets/css/hermit.min-" . HERMIT_VERSION . ".css", array(), HERMIT_VERSION, 'screen');


		wp_enqueue_script( 'hermit-js', HERMIT_URL . '/assets/js/hermit.min-'.HERMIT_VERSION.'.js', array(), HERMIT_VERSION, $jsplace);
		wp_localize_script( 'hermit-js', 'hermit', array(
														"url" => HERMIT_URL . '/assets/swf/',
														"ajax_url" =>  admin_url() . "admin-ajax.php",
														"text_tips" => $tips
													));
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

        $color = $this->config['color'];
		$expandClass = ($unexpand==1) ? "hermit-list unexpand" : "hermit-list";

		return '<!--Hermit for wordpress v'.HERMIT_VERSION.' start--><div class="hermit hermit-'.$color.'" auto="'.$auto.'" loop="'.$loop.'" songs="'.$content.'"><div class="hermit-box"><div class="hermit-controls"><div class="hermit-button"></div><div class="hermit-detail"></div><div class="hermit-duration"></div><div class="hermit-volume"></div><div class="hermit-orderbutton"></div><div class="hermit-listbutton"></div></div><div class="hermit-prosess"><div class="hermit-loaded"></div><div class="hermit-prosess-bar"><div class="hermit-prosess-after"></div></div></div></div><div class="'.$expandClass.'"></div></div><!--Hermit for wordpress v'.HERMIT_VERSION.' end-->';
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

			case 'netease_radio':
				$result = array(
					'status' => 200,
					'msg' => $HMTJSON->netease_radio($id)
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

		header('Content-type: application/json');
		echo json_encode($result);
		exit;
	}	
	

	/**
	 * 输出json数据
	 */
	function hermit_source_callback(){
        $type = $_REQUEST['type'];

        switch ($type) {
            case 'new':
                $result = $this->music_new();
                $this->success_response($result);
                break;

            case 'delete':
                $this->music_delete();
                $this->success_response(array());
                break;

            case 'move':
                $this->music_cat_move();
                $this->success_response(array());
                break;

            case 'update':
                $result = $this->music_update();
                $this->success_response($result);
                break;

            case 'index':
                $paged = intval($this->get('paged'));
                $limit = 20;

                $data = $this->music_list($paged);
                $count = intval($this->music_count());
                $max_page = ceil($count/$limit);

                $result = compact('data', 'paged', 'max_page', 'count');
                $this->success_response($result);
                break;

            case 'cat':
                $paged = intval($this->get('paged'));
                $catid = $this->get('catid');
                $limit = 20;

                $data = $this->music_list($paged, $catid);
                $count = intval($this->music_count($catid));
                $max_page = ceil($count/$limit);

                $result = compact('data', 'paged', 'max_page', 'count');
                $this->success_response($result);
                break;

            case 'catlist':
                $data = $this->music_cat_list();
                $this->success_response($data);
                break;

            case 'catnew':
                $title = $this->post('title');

                if( $this->music_cat_existed($title) ){
                    $data = "分类名称已存在";
                    $this->error_response(500, $data);
                }else{
                    $data = $this->music_cat_new($title);
                    $this->success_response($data);
                }
                break;

            default:
                $data = "不存在的请求.";
                $this->error_response(400, $data);
        }
	}

	/**
	 * 添加写文章所需要的js和css
	 */
	function page_init(){
		global $pagenow;

		if( $pagenow == "post-new.php" || $pagenow == "post.php" ){
			wp_enqueue_style('hermit-post', HERMIT_URL . '/assets/css/hermit.post-' . HERMIT_VERSION . '.css');
			wp_enqueue_script('handlebars', HERMIT_URL . '/assets/js/handlebars.js', false, HERMIT_VERSION, false);
			wp_enqueue_script('hermit-post', HERMIT_URL . '/assets/js/hermit.post-' . HERMIT_VERSION . '.js', false, HERMIT_VERSION, false);

			wp_localize_script( 'hermit-post', 'hermit', 
				array(
					"ajax_url" =>  admin_url() . "admin-ajax.php"
			));
		}

		if( $pagenow == "admin.php" && $_GET['page'] == 'hermit' ){
            wp_enqueue_style('hermit-library', HERMIT_URL . '/assets/css/hermit-library.css');
            wp_enqueue_script('hermit-library', HERMIT_URL . '/assets/js/hermit-library.js', false, HERMIT_VERSION, false);

            wp_localize_script( 'hermit-library', 'hermit',
                array(
                    "ajax_url" => admin_url() . "admin-ajax.php",
                    "tmpl_url" => HERMIT_URL . '/assets/tmpl/'
                ));
        }
	}
	
	/**
	 * 显示后台菜单
	 */
	public function menu() {
		add_menu_page('Hermit 播放器', 'Hermit 播放器', 'manage_options', 'hermit', array($this, 'library'), HERMIT_URL . '/assets/images/page-icon.png');
		add_submenu_page('hermit', '音乐库', '音乐库', 'manage_options', 'hermit', array($this, 'library'));
		add_submenu_page('hermit', '设置', '设置', 'manage_options', 'hermit-setting', array($this, 'setting'));
        add_submenu_page('hermit', '说明', '说明', 'manage_options', 'hermit-help', array($this, 'help'));

		add_action( 'admin_init', array($this, 'hermit_setting') );
	}

	/**
	 * 音乐库 library
	 */
	public function library(){
		@require_once('include/library.php');
	}

	/**
	* 设置
	*/
	public function setting(){
		@require_once('include/setting.php');
	}

	/**
	* 注册设置数组
	*/
	public function hermit_setting(){
		register_setting('hermit_setting_group', 'hermit_setting');
	}	
	
	/**
	* 帮助
	*/
	public function help(){
		@require_once('include/help.php');
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
			@require_once('include/template.php');
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

    /**
     * 新增本地音乐
     */
	private function music_new(){
		global $wpdb, $hermit_table_name;

		$song_name = stripslashes($this->post('song_name'));
		$song_author = stripslashes($this->post('song_author'));
		$song_url = esc_attr(esc_html($this->post('song_url')));
        $song_cat = $this->post('song_cat');
		$created = date('Y-m-d H:i:s');

		$wpdb->insert($hermit_table_name, compact('song_name', 'song_author', 'song_url', 'song_cat', 'created'), array('%s', '%s', '%s', '%d', '%s'));
        $id = $wpdb->insert_id;

        $song_cat_name = $this->music_cat($song_cat);
        return compact('id', 'song_name', 'song_author', 'song_cat', 'song_cat_name', 'song_url');
	}

    /**
     * 升级本地音乐信息
     */
	private function music_update(){
		global $wpdb, $hermit_table_name;

		$id = $this->post('id');
		$song_name = stripslashes($this->post('song_name'));
		$song_author = stripslashes($this->post('song_author'));
        $song_url = esc_attr(esc_html($this->post('song_url')));
        $song_cat = $this->post('song_cat');

		$wpdb->update( 
			$hermit_table_name, 
			compact('song_name', 'song_author', 'song_cat', 'song_url'),
			array( 'id' => $id ), 
			array( '%s', '%s', '%d', '%s'),
			array( '%d' ) 
		);

        $song_cat_name = $this->music_cat($song_cat);
        return compact('id', 'song_name', 'song_author', 'song_cat', 'song_cat_name', 'song_url');
	}

    /**
     * 删除本地音乐
     */
	private function music_delete(){
		global $wpdb, $hermit_table_name;

		$ids = $this->post('ids');

        $wpdb->query("DELETE FROM {$hermit_table_name} WHERE id IN ({$ids})");
	}

    /**
     * 移动分类
     */
    private function music_cat_move(){
        global $wpdb, $hermit_table_name;

        $ids = $this->post('ids');
        $catid = $this->post('catid');

        $wpdb->query("UPDATE {$hermit_table_name} SET song_cat = {$catid} WHERE id IN ({$ids})");
    }

    /**
     * 本地音乐列表
     *
     * @param $paged
     * @param null $catid
     * @return mixed
     */
	private function music_list($paged, $catid=null){
		global $wpdb, $hermit_table_name;

		$limit = 20;
		$offset = ($paged -1)*$limit;

        if( $catid ){
            $query_str = "SELECT id,song_name,song_author,song_cat,song_url,created FROM {$hermit_table_name} WHERE `song_cat` = '{$catid}' ORDER BY `created` DESC LIMIT {$limit} OFFSET {$offset}";
        }else{
            $query_str = "SELECT id,song_name,song_author,song_cat,song_url,created FROM {$hermit_table_name} ORDER BY `created` DESC LIMIT {$limit} OFFSET {$offset}";
        }

		$result = $wpdb->get_results($query_str);

        if( !empty($result) ){
            foreach($result as $key => $val){
                $result[$key]->song_cat_name = $this->music_cat($val->song_cat);
            }
        }

		return $result;
	}

    /**
     * 本地音乐分类列表
     *
     * @return mixed
     */
    private function  music_cat_list(){
        global $wpdb, $hermit_cat_name;

        $query_str = "SELECT id,title FROM {$hermit_cat_name}";
        $result = $wpdb->get_results($query_str);

        if( !empty($result) ){
            foreach($result as $key => $val){
                $result[$key]->url = '/cat/'.$val->id;
                $result[$key]->count = intval($this->music_count($val->id));
            }

            array_unshift($result, array(
                "id" => '',
                "title" => "全部音乐",
                "url" => "/",
                "count" => intval($this->music_count())
            ));
        }

        return $result;
    }

    /**
     * 本地分类名称
     *
     * @param $cat_id
     * @return mixed
     */
    private function music_cat($cat_id){
        global $wpdb, $hermit_cat_name;

        $cat_name = $wpdb->get_var("SELECT title FROM {$hermit_cat_name} WHERE id = '{$cat_id}'");
        return $cat_name;
    }

    /**
     * 判断分类是否存在
     *
     * @param $title
     * @return mixed
     */
    private function music_cat_existed($title){
        global $wpdb, $hermit_cat_name;

        $id = $wpdb->get_var("SELECT id FROM {$hermit_cat_name} WHERE title = '{$title}'");
        return $id;
    }

    /**
     * 新建分类
     */
    private function music_cat_new($title){
        global $wpdb, $hermit_cat_name;

        $title = stripslashes($title);

        $wpdb->insert($hermit_cat_name, compact('title'), array('%s'));

        $new_cat_id = $wpdb->insert_id;

        return array(
            'id' => $new_cat_id,
            'title' => $title,
            'url' => '/cat/'.$new_cat_id,
            'count' => intval($this->music_count($new_cat_id))
        );
    }

    /**
     * 本地音乐数量
     * 音乐库分类
     *
     * @param null $catid
     * @return mixed
     */
    private function music_count($catid=null){
        global $wpdb, $hermit_table_name;

        if( $catid ){
            $query_str = "SELECT COUNT(id) AS count FROM {$hermit_table_name} WHERE song_cat = '{$catid}'";
        }else{
            $query_str = "SELECT COUNT(id) AS count FROM {$hermit_table_name}";
        }

        $music_count = $wpdb->get_var($query_str);
        return $music_count;
    }

	private function post($key){
		$key = $_POST[$key];
		return $key;
	}

	private function get($key){
		$key = esc_attr(esc_html($_GET[$key]));
		return $key;
	}

    private function error_response($code, $error_message){
        if( $code == 404 ){
            header('HTTP/1.1 404 Not Found');
        }else if( $code == 301 ){
            header('HTTP/1.1 301 Moved Permanently');
        }else{
            header('HTTP/1.0 500 Internal Server Error');
        }
        header('Content-Type: text/plain;charset=UTF-8');
        echo $error_message;
        exit;
    }

    private function success_response($result){
        header('HTTP/1.1 200 OK');
        header('Content-type: application/json;charset=UTF-8');
        echo json_encode($result);
        exit;
    }
}