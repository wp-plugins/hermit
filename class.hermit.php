<?php
class hermit{
	public function __construct(){
		$this->config = get_option('hermit_settings');
		$this->base_dir = plugins_url('', __FILE__);
		$this->admin_dir = admin_url('/options-general.php?page=class.hermit.php');
		
		/**
		** 事件绑定
		**/
		add_action('admin_menu', array($this, 'menu'));
		add_shortcode('hermit',array($this,'shortcode'));
		add_action('admin_init', array($this, 'page_init'));
		add_action('wp_enqueue_scripts', array($this, 'hermit_scripts'));
		add_action('media_buttons_context', array($this,'custom_button'));
		add_filter('plugin_action_links', array($this, 'plugin_action_link'), 10, 4);
	}
	
	/**
	 * 载入所需要的CSS和js文件
	 */	
	 
	public function hermit_scripts() {
		$hermit_options = get_option('hermit_options');
		if(!$hermit_options["css"]){
			wp_enqueue_style('hermit-css', $this->base_dir . '/assets/style/hermit.min.css', array(), VERSION, 'screen');
		}
		
		// JS文件在最底部加载
		wp_enqueue_script( 'hermit-js', $this->base_dir . '/assets/script/hermit.min.js', array(), VERSION, true);
		wp_localize_script( 'hermit-js', 'hermit', 
			array(
				"url" => $this->base_dir . '/assets/swf/'
		));		
	}
	
	/**
	 * 添加文章短代码
	 */
	public function shortcode($atts, $content=null){
		extract(shortcode_atts(array(
			'auto' => 0,
			'loop' => 0
		), $atts));
		return '<!--Hermit for wordpress v'.VERSION.' start--><div class="hermit" auto="'.$auto.'" loop="'.$loop.'" songs="'.$content.'"><div class="hermit-box"><div class="hermit-controls"><div class="hermit-button"></div><div class="hermit-detail">单击鼠标左键播放或暂停。</div><div class="hermit-duration"></div><div class="hermit-listbutton"></div></div><div class="hermit-prosess"><div class="hermit-loaded"></div><div class="hermit-prosess-bar"><div class="hermit-prosess-after"></div></div></div></div><div class="hermit-list"></div></div><!--Hermit for wordpress v'.VERSION.' end-->';
	}
	
	/**
	 * 添加写文章按钮
	 */
	public function custom_button($context) {
		$icon_url = $this->base_dir . '/assets/images/iconx.png';
		$context .= "<a id='gohermit' class='button' href='javascript:;' title='添加虾米音乐'><img src='{$icon_url}' width='16' height='16' /></a>";
		return $context;
	}	
	
	/**
	 * 添加写文章所需要的js和css
	 */
	function page_init(){
		global $pagenow;
		if( $pagenow == "post-new.php" || $pagenow == "post.php" ){
			wp_enqueue_style('hermit-admin-css', $this->base_dir . '/assets/style/hermit.admin.css', false, VERSION, false);
			wp_enqueue_script('hermit-admin-js', $this->base_dir . '/assets/script/hermit.admin.js', false, VERSION, false);
		}		
	}
	
	/**
	 * 显示后台菜单
	 */
	 
	public function menu() {
		add_options_page('虾米播放器设置', '虾米播放器设置', 'manage_options', basename(__FILE__), array($this, 'settings_page'));
		add_action( 'admin_init', array($this, 'settings'));
	}
	
	/**
	 * 添加设置按钮
	 */	
	 
	public function plugin_action_link($actions, $plugin_file, $plugin_data){
		if(strpos($plugin_file, 'hermit')!==false && is_plugin_active($plugin_file)){
			$myactions = array('option'=>'<a href="'.$this->admin_dir.'">设置</a>');
			$actions = array_merge($myactions,$actions);
		}
		return $actions;
	}
	
	/**
	 * 注册插件设置
	 */	
	 	
	public function settings() {
		register_setting( 'hermit-settings-group', 'hermit_options' );
	}
	
	/**
	 * 插件设置页面
	 */	
	public function settings_page() {?>
		<div class="wrap">
			<div id="icon-options-general" class="icon32"><br></div><h2>虾米播放器设置</h2><br>
			<form method="post" action="options.php">
				<?php settings_fields( 'hermit-settings-group' ); ?>
				<?php $options = get_option('hermit_options'); ?>
				<table class="form-table">
					<tbody>
						<tr valign="top">
							<th scope="row"><label for="blogname">食用方法</label></th>
							<td>
								<ul>
									<li>
										<b>单曲：</b><br /><br />
										<p><img src="http://ww2.sinaimg.cn/large/6115ac8fgw1edplnqie9rj20m80atjsc.jpg" width="800" height="389" /></p>							
									</li>
									<li>
										<b>专辑：</b><br />
										<p><img src="http://ww4.sinaimg.cn/large/6115ac8fgw1edplnqxl89j20m805g3yu.jpg" width="800" height="196" /></p>
									</li>
									<li>
										<b>精选集：</b><br />
										<p><img src="http://ww1.sinaimg.cn/large/6115ac8fgw1edplnr7ao5j20l605ymxd.jpg" width="800" height="225" /></p>
									</li>									
								</ul>
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><label for="blogname">CSS 文件</label></th>
							<td>
								<fieldset>
									<?php $array = array("0" => "随着插件载入到顶部", "1" => "自行处理");
									foreach($array as $key => $value){?>
										<label><input type="radio" name="hermit_options[css]" value="<?php echo (int) $key;?>" <?php if($options['css']==$key) echo 'checked="checked"'; ?>> <span><?php echo $value;?></span></label><br>
									<?php };?>
									<p>默认 CSS文件加载到网页头部。</p>
								</fieldset>						
							</td>
						</tr>					
					</tbody>
				</table>
				<div class="muhermit_submit_form">
					<input type="submit" class="button-primary muhermit_submit_form_btn" name="save" value="<?php _e('Save Changes') ?>"/>
				</div>
			</form>
		</div>
	<?php }	
}

?>