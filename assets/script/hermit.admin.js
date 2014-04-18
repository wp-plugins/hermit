jQuery(document).ready(function(a) {
	var i = ["songlist", "album", 'collect'],
		c = function() {
			a("#gohermit, #wp-content-media-buttons").removeClass("selected");
			a("#hermit-box").remove()
		};
	a("#gohermit").click(function() {
		0 < a("#hermit-box").length ? (a(this).add("#wp-content-media-buttons").removeClass("selected"), a("#hermit-box").remove()) : (0 < a(".wp-editor-tabs").length && a("#wp-content-media-buttons").addClass("selected"), a(this).addClass("selected"), a(0 < a(".wp-editor-tabs").length ? "#wp-content-media-buttons" : "#wp-content-editor-tools").after('<div id="hermit-box" class="postbox"><div id="hermit-content"><div id="hermit-tab"><ul id="hermit-tabul"><li class="hermit-tabli current">\u5355\u66f2</li><li class="hermit-tabli">\u4e13\u8f91</li><li class="hermit-tabli">精选集</li></ul></div><div id="hermit-body"><ul id="hermit-bodyul"><li class="hermit-bodyli current"><textarea id="hermit-song" placeholder="\u8f93\u5165\u867e\u7c73\u6b4c\u66f2\u5730\u5740\uff0c\u591a\u4e2a\u5730\u5740\u8bf7\u56de\u8f66\u6362\u884c"></textarea></li><li class="hermit-bodyli"><input type="text" id="hermit-album" placeholder="\u8f93\u5165\u867e\u7c73\u4e13\u8f91\u5730\u5740" /></li><li class="hermit-bodyli"><input type="text" id="hermit-collect" placeholder="输入虾米精选集地址" /></li></ul></div></div><div id="hermit-action" class="clear"><a id="hermit-delete" class="submitdelete deletion" href="javascript:;">\u53d6\u6d88</a><label for="hermit-auto"><input type="checkbox" id="hermit-auto">自动播放</label><label for="hermit-loop"><input type="checkbox" id="hermit-loop">循环播放</label><div id="hermit-publish" class="button button-primary">\u6dfb\u52a0\u5230\u6587\u7ae0\u4e2d</div></div></div>'))
	});
	a("#wp-content-wrap").on("click", "#hermit-delete", function() {
		c()
	});
	a("#wp-content-wrap").on("click", ".hermit-tabli", function() {
		if (!a(this).hasClass("current")) {
			var b = a(".hermit-tabli").index(a(this));
			a(".hermit-tabli, .hermit-bodyli").removeClass("current");
			a(".hermit-tabli:eq(" + b + "), .hermit-bodyli:eq(" + b + ")").addClass("current")
		}
		return !1
	});
	a("#wp-content-wrap").on("click", "#hermit-publish", function() {
		var b = a(".hermit-tabli").index(a(".hermit-tabli.current"));
		switch (i[b]) {
		case "songlist":
			var b = a("#hermit-song").val(),
				e = [],
				f = /http:\/\/www.xiami.com\/song\/(\d+).*?/,
				b = b.split(/\r?\n/g),
				b = a.grep(b, function(a) {
					return f.test(a) ? !0 : !1
				});
			if (0 < b.length) {
				var d = [],
					auto = Number( a('#hermit-auto').prop("checked") ),
					loop = Number( a('#hermit-loop').prop("checked") );

				a.each(b, function(b, g) {
					0 > a.inArray(g, d) && d.push(g)
				});
				a.each(d, function(a, b) {
					e.push(b.match(f)[1])
				});
				b = '[hermit auto="'+auto+'" loop="'+loop+'"]songlist#:' + e.join(",") + '[/hermit]';
				send_to_editor(b);
				c()
			} else alert("\u8bf7\u8f93\u5165\u6b63\u786e\u7684\u867e\u7c73\u6b4c\u66f2\u5730\u5740");
			break;
			
		case "album":
			var b = a("#hermit-album").val(),
				h = /http:\/\/www.xiami.com\/album\/(\d+).*?/,
				auto = Number( a('#hermit-auto').prop("checked")),
				loop = Number( a('#hermit-loop').prop("checked"));
			h.test(b) ? (b = b.match(h)[1], send_to_editor('[hermit auto="'+auto+'" loop="'+loop+'"]album#:' + b + '[/hermit]'), c()) : alert("\u8bf7\u8f93\u5165\u6b63\u786e\u7684\u867e\u7c73\u4e13\u8f91\u5730\u5740");
			break;
			
		case "collect":
			var b = a("#hermit-collect").val(),
				h = /http:\/\/www.xiami.com\/song\/showcollect\/id\/(\d+).*?/,
				auto = Number( a('#hermit-auto').prop("checked")),
				loop = Number( a('#hermit-loop').prop("checked"));
			h.test(b) ? (b = b.match(h)[1], send_to_editor('[hermit auto="'+auto+'" loop="'+loop+'"]collect#:' + b + '[/hermit]'), c()) : alert("请输入正确的虾米精选集地址");
			break;			
		}
	})
});