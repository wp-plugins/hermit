<div class="wrap">
	<h2>Hermit 插件设置</h2>
	<?php if (isset($_REQUEST['settings-updated'])) echo '<div id="setting-error-settings_updated" class="updated settings-error"><p><strong>设置已保存。</strong></p></div>'; ?>
	<form method="post" action="options.php">
		<?php settings_fields('hermit_setting_group'); ?>
		<table class="form-table">
			<tbody>
			<tr valign="top">
				<th scope="row"><label>播放器提示</label></th>
				<td>
					<p><input type="text" class="regular-text" name="hermit_setting[tips]"
							  value="<?php echo $this->settings('tips'); ?>"/></p>

					<p class="description">默认显示：<strong>点击播放或暂停</strong> 为空则不显示任何文字。</p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label>资源加载策略</label></th>
				<td>
					<p>
						<label title="按需加载">
							<input type="radio" name="hermit_setting[strategy]"
								   value="1" <?php if ($this->settings('strategy') == 1) echo 'checked="checked"'; ?>>
							<span>按需加载</span>
						</label>
					</p>

					<p>
						<label title="全局加载">
							<input type="radio" name="hermit_setting[strategy]"
								   value="2" <?php if ($this->settings('strategy') == 2) echo 'checked="checked"'; ?>>
							<span>全局加载</span>
						</label>
					</p>

					<p class="description">默认：<strong>按需加载</strong>，只有文章列表中使用了短代码才会加载CSS、JS资源。<br/>全局加载：无论是否使用了短代码都会加载，适合侧边栏。</p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label>JavaScript 位置</label></th>
				<td>
					<p>
						<label title="页面顶部">
							<input type="radio" name="hermit_setting[jsplace]"
								   value="0" <?php if ($this->settings('jsplace') == 0) echo 'checked="checked"'; ?>/>
							<span>页面顶部</span>
						</label>
					</p>

					<p>
						<label title="页面底部">
							<input type="radio" name="hermit_setting[jsplace]"
								   value="1" <?php if ($this->settings('jsplace') == 1) echo 'checked="checked"'; ?>/>
							<span>页面底部</span>
						</label>
					</p>

					<p class="description">默认：<strong>页面顶部</strong></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label>颜色选择</label></th>
				<td>
					<?php $color_array = array(
						'default' => '默认',
						'red'     => '新年红',
						'blue'    => '青葱绿',
						'yellow'  => '淡淡黄',
						'pink'    => '少女粉',
						'purple'  => '基情紫',
						'black'   => '暗色灰'
					);
					foreach ($color_array as $key => $title) { ?>
						<p>
							<label title="<?php echo $title; ?>">
								<input class="hermit-radio-<?php echo $key;?>" type="radio" name="hermit_setting[color]"
									   value="<?php echo $key; ?>" <?php if ($this->settings('color') == $key) echo 'checked="checked"'; ?>/>
								<span><?php echo $title; ?></span>
							</label>
						</p>
					<?php }
					?>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label>音乐库每页数量</label></th>
				<td>
					<p><input type="text" class="regular-text" name="hermit_setting[prePage]"
							  value="<?php echo $this->settings('prePage'); ?>"/></p>

					<p class="description">默认数量：20。</p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label>浏览器缓存时间</label></th>
				<td>
					<p><input type="text" class="small-text" name="hermit_setting[remainTime]"
							  value="<?php echo $this->settings('remainTime'); ?>"/>小时</p>

					<p class="description">默认数量：10小时，最大不宜超过48小时。</p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label>新建权限</label></th>
				<td>
					<?php
					$role_array = array(
						'subscriber'    => '订阅者',
						'author'        => '作者',
						'contributor'   => '投稿者',
						'editor'        => '编辑',
						'administrator' => '管理员'
					);

					foreach ($role_array as $key => $val) {
						?>
						<label title="开启调试信息">
							<input type="checkbox" name="hermit_setting[roles][]"
								   value="<?php echo $key; ?>" <?php if (in_array($key, $this->settings('roles'))) echo 'checked="checked"'; ?>/>
							<span><?php echo $val; ?></span>
						</label>
					<?php }
					?>
					<p class="description">默认：<strong>管理员权限</strong> 才可以在新建或编辑文章时添加音乐</p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><label>开发调试</label></th>
				<td>
					<p>
						<label title="开启调试信息">
							<input type="checkbox" name="hermit_setting[debug]"
								   value="1" <?php if ($this->settings('debug') == 1) echo 'checked="checked"'; ?>/>
							<span>开启调试信息</span>
						</label>
					</p>

					<p class="description">开发调试信息，默认关闭，如需要定位错误信息，可开启此项。<br/>开启后所有错误信息，会在开发者工具面板打印。（例如 Chrome：Ctrl+Shift+I）</p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"></th>
				<td>
					<input type="submit" class="button-primary" name="save" value="<?php _e('Save Changes') ?>"/>
				</td>
			</tr>
			</tbody>
		</table>
	</form>
	<style>input.hermit-radio-default[type=radio]{border-color:#5895be}input.hermit-radio-default[type=radio]:checked:before{background-color:#5895be}input.hermit-radio-red[type=radio]{border-color:#dd4b39}input.hermit-radio-red[type=radio]:checked:before{background-color:#dd4b39}input.hermit-radio-blue[type=radio]{border-color:#5cb85c}input.hermit-radio-blue[type=radio]:checked:before{background-color:#5cb85c}input.hermit-radio-yellow[type=radio]{border-color:#f0ad4e}input.hermit-radio-yellow[type=radio]:checked:before{background-color:#f0ad4e}input.hermit-radio-pink[type=radio]{border-color:#f489ad}input.hermit-radio-pink[type=radio]:checked:before{background-color:#f489ad}input.hermit-radio-purple[type=radio]{border-color:orchid}input.hermit-radio-purple[type=radio]:checked:before{background-color:orchid}input.hermit-radio-black[type=radio]:checked:before{background-color:#aaa}</style>
</div>