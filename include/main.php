<div class="wrap">
	<h2>资源管理</h2>
	<div class="hermit-controls">
		<a id="hermit-music-new" class="action success" href="javascript:;">新增歌曲</a>
		<a id="hermit-music-delete" class="action" href="javascript:;">删除</a>
		<span class="hermit-music-delete-tips"></span>
	</div>
	<div class="hermit-music">
		<div class="hermit-music-header">
			<div class="hermit-music-li">
			    <ul>
					<li class="col-1"><input id="hermit-music-checkall" type="checkbox" value="{{id}}" name="check"> 歌曲</li>
					<li class="col-2">作者</li>
					<li class="col-3">地址</li>
					<li class="col-4">操作</li>
				</ul>
	    	</div>
		</div>
		<div class="hermit-music-body">
			<form class="hermit-music-body-form"></form>
		</div>
	</div>
	<script id="hermit-new-template" type="text/x-handlebars-template">
		<form class="hermit-music-new-form">
	    	<div class="hermit-music-li">
			    <ul>
					<li class="col-1">
						<input type="text" name="song_name" placeholder="歌曲名称" value="{{song_name}}" />
					</li>
					<li class="col-2">
						<input type="text" name="song_author" placeholder="歌手或专辑名称" value="{{song_author}}" />
					</li>
					<li class="col-3">
						<input type="text" name="song_url" placeholder="音乐地址" value="{{song_url}}" />
					</li>
					<li class="col-4">
						{{#if id}}
							<a class="action tiny secondary hermit-new-form-edit" href="javascript:;">更新</a>
							<input type="hidden" name="id" value="{{id}}" />
						{{else}}
							<a class="action tiny secondary hermit-new-form-sure" href="javascript:;">添加</a>
						{{/if}}
						<a class="action tiny hermit-new-form-cancel" href="javascript:;">取消</a>
					</li>
				</ul>
	    	</div>
	    	<div class="hermit-new-form-tips"></div>
	    </form>
	</script>
	<script id="hermit-music-template" type="text/x-handlebars-template">
	    {{#data}}
	    	<div class="hermit-music-li">
			    <ul>
					<li class="col-1"><input class="hermit-music-checkbox" type="checkbox" value="{{id}}" name="song-check"> {{song_name}}</li>
					<li class="col-2">{{song_author}}</li>
					<li class="col-3">{{song_url}}</li>
					<li class="col-4"><a href="javascript:;" class="hermit-music-edit action tiny" data-json='{{#json this}}{{/json}}'>编辑</a></li>
				</ul>
	    	</div>
	    {{/data}}
	</script>
</div>