<div class="wrap">
	<h2>插件设置</h2>
	<?php if ( isset($_REQUEST['settings-updated']) ) echo '<div id="setting-error-settings_updated" class="updated settings-error"><p><strong>设置已保存。</strong></p></div>';?>
	<form method="post" action="options.php">
		<?php settings_fields( 'hermit_setting_group' ); ?>
		<?php $settings = $this->config;?>
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row"><label>播放器提示</label></th>
					<td>
						<p><input type="text" class="regular-text" name="hermit_setting[tips]" value="<?php echo $settings['tips']; ?>" /></p>
						<p class="description">默认显示：<strong>单击鼠标左键播放或暂停。</strong>为空则不显示任何文字。</p>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><label>资源加载策略</label></th>
					<td>
						<p>
                            <label title="按需加载">
                                <input type="radio" name="hermit_setting[strategy]" value="1" <?php if($settings['strategy']==1) echo 'checked="checked"';?>> <span>按需加载</span>
                            </label>
                        </p>
                        <p>
							<label title="全局加载">
                                <input type="radio" name="hermit_setting[strategy]" value="2" <?php if($settings['strategy']==2) echo 'checked="checked"';?>> <span>全局加载</span>
                            </label>
                        </p>
						<p class="description">默认：<strong>按需加载</strong>，只有文章列表中使用了短代码才会加载CSS、JS资源。<br />全局加载：无论是否使用了短代码都会加载，适合侧边栏。</p>
					</td>
				</tr>
                <tr valign="top">
                    <th scope="row"><label>JavaScript 位置</label></th>
                    <td>
                        <p>
                            <label title="页面顶部">
                                <input type="radio" name="hermit_setting[jsplace]" value="0" <?php if($settings['jsplace']==0) echo 'checked="checked"';?>/> <span>页面顶部</span>
                            </label>
                        </p>
                        <p>
                            <label title="页面底部">
                                <input type="radio" name="hermit_setting[jsplace]" value="1" <?php if($settings['jsplace']==1) echo 'checked="checked"';?>/> <span>页面底部</span>
                            </label>
                        </p>
                        <p class="description">默认：<strong>页面顶部</strong></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label>颜色选择</label></th>
                    <td>
                        <p>
                            <label title="默认颜色">
                                <input type="radio" name="hermit_setting[color]" value="default" <?php if($settings['color']=='default') echo 'checked="checked"';?>/> <span>默认</span>
                            </label>
                        </p>
                        <p>
                            <label title="新年红">
                                <input type="radio" name="hermit_setting[color]" value="red" <?php if($settings['color']=='red') echo 'checked="checked"';?>/> <span>新年红</span>
                            </label>
                        </p>
                        <p>
                            <label title="青葱绿">
                                <input type="radio" name="hermit_setting[color]" value="blue" <?php if($settings['color']=='blue') echo 'checked="checked"';?>/> <span>青葱绿</span>
                            </label>
                        </p>
                        <p>
                            <label title="淡淡黄">
                                <input type="radio" name="hermit_setting[color]" value="yellow" <?php if($settings['color']=='yellow') echo 'checked="checked"';?>/> <span>淡淡黄</span>
                            </label>
                        </p>
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
</div>