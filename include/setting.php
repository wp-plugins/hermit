<div class="wrap">
	<h2>插件设置</h2>
	<?php if ( isset($_REQUEST['settings-updated']) ) echo '<div id="setting-error-settings_updated" class="updated settings-error"><p><strong>设置已保存。</strong></p></div>';?>
	<form method="post" action="options.php">
		<?php settings_fields( 'hermit_setting_group' ); ?>
		<?php $settings = get_option('hermit_setting');?>
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
						<p><label title="按需加载"><input type="radio" name="hermit_setting[strategy]" value="1" <?php if($settings['strategy']==1) echo 'checked="checked"';?>> <span>按需加载</span></label><br>
							<label title="全局加载"><input type="radio" name="hermit_setting[strategy]" value="2" <?php if($settings['strategy']==2) echo 'checked="checked"';?>> <span>全局加载</span></label><br></p>
						<p class="description">默认：<strong>按需加载</strong>，只有文章列表中使用了短代码才会加载CSS、JS资源。<br />全局加载：无论是否使用了短代码都会加载，适合侧边栏。</p>
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