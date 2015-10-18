<?php

class hermit
{
    public function __construct()
    {
        /**
         ** 事件绑定
         **/
        add_action('admin_menu', array($this, 'menu'));
        add_shortcode('hermit', array($this, 'shortcode'));
        add_action('admin_init', array($this, 'page_init'));
        add_action('wp_enqueue_scripts', array($this, 'hermit_scripts'));
        add_filter('plugin_action_links', array($this, 'plugin_action_link'), 10, 4);
        add_action('wp_ajax_nopriv_hermit', array($this, 'hermit_callback'));
        add_action('wp_ajax_hermit', array($this, 'hermit_callback'));
        add_action('in_admin_footer', array($this, 'music_footer'));
        add_action('wp_ajax_hermit_source', array($this, 'hermit_source_callback'));
    }

    /**
     * 载入所需要的CSS和js文件
     */
    public function hermit_scripts()
    {
        $strategy = $this->settings('strategy');

        if ($strategy == 1) {
            global $post, $posts;
            foreach ($posts as $post) {
                if (has_shortcode($post->post_content, 'hermit')) {
                    $this->_load_scripts();
                    break;
                }
            }
        } else {
            $this->_load_scripts();
        }
    }

    /**
     * 加载资源
     */
    private function _load_scripts()
    {
        $this->_css('hermit');
        $this->_js('hermit', $this->settings('jsplace'));

        wp_localize_script('hermit', 'hermit', array(
            "url" => HERMIT_URL . '/assets/swf/',
            "ajax_url" => admin_url() . "admin-ajax.php",
            "text_tips" => $this->settings('tips'),
            "remain_time" => $this->settings('remainTime'),
            "debug" => $this->settings('debug'),
            "version" => HERMIT_VERSION
        ));
    }

    /**
     * 添加文章短代码
     */
    public function shortcode($atts, $content = NULL)
    {
        extract(shortcode_atts(array(
                                   'auto' => 0,
                                   'loop' => 0,
                                   'unexpand' => 0,
                                   'fullheight' => 0
                               ), $atts));

        $color = $this->settings('color');
        $exClass = sprintf('hermit hermit-%s hermit-unexpand-%s hermit-fullheight-%s', $color, $unexpand, $fullheight);
        $cover = HERMIT_URL . "/assets/images/cover@3x.png";

        return '<!--Hermit v' . HERMIT_VERSION . ' start--><div class="'.$exClass.'" auto="' . $auto . '" loop="' . $loop . '" " songs="' . $content . '"><div class="hermit-box hermit-clear"><div class="hermit-cover"><img class="hermit-cover-image" src="' . $cover . '" width="80" height="80" /><div class="hermit-button"></div></div><div class="hermit-info"><div class="hermit-title"><div class="hermit-detail"></div></div><div class="hermit-controller"><div class="hermit-author"></div><div class="hermit-additive"><div class="hermit-duration">00:00/00:00</div><div class="hermit-volume"></div><div class="hermit-listbutton"></div></div></div><div class="hermit-prosess"><div class="hermit-loaded"></div><div class="hermit-prosess-bar"><div class="hermit-prosess-after"></div></div></div></div></div><div class="hermit-list"></div></div><!--Hermit  v' . HERMIT_VERSION . ' end-->';
    }

    /**
     * 添加写文章按钮
     */
    public function custom_button($context)
    {
        $context .= "<a id='hermit-create' class='button' href='javascript:;' title='添加音乐'><img src='" . HERMIT_URL . "/assets/images/logo@2x.png' width='16' height='16' /> 添加音乐</a>";
        return $context;
    }

    /**
     * JSON 音乐数据
     */
    public function hermit_callback()
    {
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
                    'status' => 200,
                    'msg' => $HMTJSON->album($id)
                );
                break;

            case 'collect':
                $result = array(
                    'status' => 200,
                    'msg' => $HMTJSON->collect($id)
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
                    'status' => 200,
                    'msg' => $this->music_remote($id)
                );
                break;

            default:
                $result = array(
                    'status' => 400,
                    'msg' => NULL
                );
        }

        //输出 JSON
        $output = json_encode($result);
        header('Content-type: application/json;charset=UTF-8');
        exit($output);
    }


    /**
     * 输出json数据
     */
    function hermit_source_callback()
    {
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

            case 'list':
                $paged = intval($this->get('paged'));
                $catid = $this->get('catid');
                $prePage = $this->settings('prePage');

                $catid = $catid ? $catid : NULL;

                $data = $this->music_list($paged, $catid);
                $count = intval($this->music_count());
                $maxPage = ceil($count / $prePage);

                $result = compact('data', 'paged', 'maxPage', 'count');
                $this->success_response($result);
                break;

            case 'catlist':
                $data = $this->music_catList();
                $this->success_response($data);
                break;

            case 'catnew':
                $title = $this->post('title');

                if ($this->music_cat_existed($title)) {
                    $data = "分类名称已存在";
                    $this->error_response(500, $data);
                } else {
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
    function page_init()
    {
        global $pagenow;

        $allowed_roles = $this->settings('roles');
        $user = wp_get_current_user();

        if( array_intersect($allowed_roles, $user->roles) ){
            if ($pagenow == "post-new.php" || $pagenow == "post.php") {
                add_action('media_buttons_context', array($this, 'custom_button'));

                $this->_css('hermit-post');
                $this->_libjs('handlebars');
                $this->_js('hermit-post');

                $prePage = $this->settings('prePage');
                $count = $this->music_count();
                $maxPage = ceil($count / $prePage);

                wp_localize_script('hermit-post', 'hermit', array(
                    "ajax_url" => admin_url() . "admin-ajax.php",
                    "max_page" => $maxPage
                ));
            }

            if ($pagenow == "admin.php" && $_GET['page'] == 'hermit') {
                //上传音乐支持
                wp_enqueue_media();
                $this->_css('hermit-library');
                $this->_libjs('watch,handlebars,jquery.mxloader,jquery.mxpage,jquery.mxlayer');
                $this->_js('hermit-library');
            }
        }
    }

    /**
     * 显示后台菜单
     */
    public function menu()
    {
        add_menu_page('Hermit 播放器', 'Hermit 播放器', 'manage_options', 'hermit', array($this, 'library'), HERMIT_URL . '/assets/images/logo.png');
        add_submenu_page('hermit', '音乐库', '音乐库', 'manage_options', 'hermit', array($this, 'library'));
        add_submenu_page('hermit', '设置', '设置', 'manage_options', 'hermit-setting', array($this, 'setting'));
        add_submenu_page('hermit', '帮助', '帮助', 'manage_options', 'hermit-help', array($this, 'help'));

        add_action('admin_init', array($this, 'hermit_setting'));
    }

    /**
     * 音乐库 library
     */
    public function library()
    {
        @require_once('include/library.php');
    }

    /**
     * 设置
     */
    public function setting()
    {
        @require_once('include/setting.php');
    }

    /**
     * 注册设置数组
     */
    public function hermit_setting()
    {
        register_setting('hermit_setting_group', 'hermit_setting');
    }

    /**
     * 帮助
     */
    public function help()
    {
        @require_once('include/help.php');
    }

    /**
     * 添加<音乐库>按钮
     */
    public function plugin_action_link($actions, $plugin_file, $plugin_data)
    {
        if (strpos($plugin_file, 'hermit') !== FALSE && is_plugin_active($plugin_file)) {
            $myactions = array('option' => '<a href="' . HERMIT_ADMIN_URL . 'admin.php?page=hermit">音乐库</a>');
            $actions = array_merge($myactions, $actions);
        }
        return $actions;
    }

    /**
     * Handlebars 模板
     */
    public function music_footer()
    {
        global $pagenow;
        if ($pagenow == "post-new.php" || $pagenow == "post.php") {
            @require_once('include/template.php');
        }
    }

    /**
     * setting - 插件设置
     *
     * @param $key
     * @return bool
     */
    public function settings($key)
    {
        $defaults = array(
            'tips' => '点击播放或暂停',
            'strategy' => 1,
            'color' => 'default',
            'jsplace' => 0,
            'prePage' => 20,
            'remainTime' => 10,
            'roles' => array('administrator'),
            'debug' => 0
        );

        $settings = get_option('hermit_setting');
        $settings = wp_parse_args( $settings, $defaults );

        return $settings[$key];
    }

    private function music_remote($ids)
    {
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
    private function music_new()
    {
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
    private function music_update()
    {
        global $wpdb, $hermit_table_name;

        $id = $this->post('id');
        $song_name = stripslashes($this->post('song_name'));
        $song_author = stripslashes($this->post('song_author'));
        $song_url = esc_attr(esc_html($this->post('song_url')));
        $song_cat = $this->post('song_cat');

        $wpdb->update(
            $hermit_table_name,
            compact('song_name', 'song_author', 'song_cat', 'song_url'),
            array('id' => $id),
            array('%s', '%s', '%d', '%s'),
            array('%d')
        );

        $song_cat_name = $this->music_cat($song_cat);
        return compact('id', 'song_name', 'song_author', 'song_cat', 'song_cat_name', 'song_url');
    }

    /**
     * 删除本地音乐
     */
    private function music_delete()
    {
        global $wpdb, $hermit_table_name;

        $ids = $this->post('ids');

        $wpdb->query("DELETE FROM {$hermit_table_name} WHERE id IN ({$ids})");
    }

    /**
     * 移动分类
     */
    private function music_cat_move()
    {
        global $wpdb, $hermit_table_name;

        $ids = $this->post('ids');
        $catid = $this->post('catid');

        $wpdb->query("UPDATE {$hermit_table_name} SET song_cat = {$catid} WHERE id IN ({$ids})");
    }

    /**
     * 本地音乐列表
     *
     * @param      $paged
     * @param null $catid
     * @return mixed
     */
    private function music_list($paged, $catid = NULL)
    {
        global $wpdb, $hermit_table_name;

        $limit = $this->settings('prePage');
        $offset = ($paged - 1) * $limit;

        if ($catid) {
            $query_str = "SELECT id,song_name,song_author,song_cat,song_url,created FROM {$hermit_table_name} WHERE `song_cat` = '{$catid}' ORDER BY `created` DESC LIMIT {$limit} OFFSET {$offset}";
        } else {
            $query_str = "SELECT id,song_name,song_author,song_cat,song_url,created FROM {$hermit_table_name} ORDER BY `created` DESC LIMIT {$limit} OFFSET {$offset}";
        }

        $result = $wpdb->get_results($query_str);

        return $result;
    }

    /**
     * 本地音乐分类列表
     *
     * @return mixed
     */
    private function  music_catList()
    {
        global $wpdb, $hermit_cat_name;

        $query_str = "SELECT id,title FROM {$hermit_cat_name}";
        $result = $wpdb->get_results($query_str);

        if (!empty($result)) {
            foreach ($result as $key => $val) {
                $result[$key]->count = intval($this->music_count($val->id));
            }
        }

        return $result;
    }

    /**
     * 本地分类名称
     *
     * @param $cat_id
     * @return mixed
     */
    private function music_cat($cat_id)
    {
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
    private function music_cat_existed($title)
    {
        global $wpdb, $hermit_cat_name;

        $id = $wpdb->get_var("SELECT id FROM {$hermit_cat_name} WHERE title = '{$title}'");
        return $id;
    }

    /**
     * 新建分类
     */
    private function music_cat_new($title)
    {
        global $wpdb, $hermit_cat_name;

        $title = stripslashes($title);

        $wpdb->insert($hermit_cat_name, compact('title'), array('%s'));

        $new_cat_id = $wpdb->insert_id;

        return array(
            'id' => $new_cat_id,
            'title' => $title,
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
    private function music_count($catid = NULL)
    {
        global $wpdb, $hermit_table_name;

        if ($catid) {
            $query_str = "SELECT COUNT(id) AS count FROM {$hermit_table_name} WHERE song_cat = '{$catid}'";
        } else {
            $query_str = "SELECT COUNT(id) AS count FROM {$hermit_table_name}";
        }

        $music_count = $wpdb->get_var($query_str);
        return $music_count;
    }

    private function _css($css_str)
    {
        $css_arr = explode(',', $css_str);

        foreach ($css_arr as $key => $val) {
            $css_path = sprintf('%s/assets/css/%s/%s.css', HERMIT_URL, HERMIT_VERSION, $val);
            wp_enqueue_style($val, $css_path);
        }
    }

    private function _libjs($js_str, $js_place = FALSE)
    {
        $js_arr = explode(',', $js_str);

        foreach ($js_arr as $key => $val) {
            $js_path = sprintf('%s/assets/js/lib/%s.js', HERMIT_URL, $val);
            wp_enqueue_script($val, $js_path, FALSE, HERMIT_VERSION, $js_place);
        }
    }

    private function _js($js_str, $js_place = FALSE)
    {
        $js_arr = explode(',', $js_str);

        foreach ($js_arr as $key => $val) {
            $js_path = sprintf('%s/assets/js/%s/%s.js', HERMIT_URL, HERMIT_VERSION, $val);
            wp_enqueue_script($val, $js_path, FALSE, HERMIT_VERSION, $js_place);
        }
    }

    private function post($key)
    {
        $key = $_POST[$key];
        return $key;
    }

    private function get($key)
    {
        $key = esc_attr(esc_html($_GET[$key]));
        return $key;
    }

    private function error_response($code, $error_message)
    {
        if ($code == 404) {
            header('HTTP/1.1 404 Not Found');
        } else if ($code == 301) {
            header('HTTP/1.1 301 Moved Permanently');
        } else {
            header('HTTP/1.0 500 Internal Server Error');
        }
        header('Content-Type: text/plain;charset=UTF-8');
        echo $error_message;
        exit;
    }

    private function success_response($result)
    {
        header('HTTP/1.1 200 OK');
        header('Content-type: application/json;charset=UTF-8');
        echo json_encode($result);
        exit;
    }
}