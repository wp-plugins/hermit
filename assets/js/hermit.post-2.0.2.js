/** 
* @name Hermit
* @version 1.9.3
* @create 2014-02-07
* @lastmodified 2015-08-20 14:32
* @description Hermit Plugin
* @author MuFeng (http://mufeng.me)
* @url http://mufeng.me/hermit-for-wordpress.html
**/
jQuery(document).ready(function(c) {
    function l(b, a) {
        if (d.array = "", "xiami" == b) switch (d.type) {
            case "songlist":
                if ((a = a.match(/song\/(\d+)/gi)) && 0 < a.length) {
                    var e = [];
                    c.each(a, function(a, b) {
                        -1 === c.inArray(b, e) && e.push(b)
                    });
                    d.array = e.join(",").replace(/song\//g, "")
                }
                break;
            case "album":
                (a = a.match(/album\/(\d+)/gi)) && 0 < a.length && (d.array = a[0].replace(/album\//g, ""));
                break;
            case "collect":
                (a = a.match(/collect\/(\d+)/gi)) && 0 < a.length && (d.array = a[0].replace(/collect\//g, ""))
        }
        if ("netease" == b) switch (d.type) {
            case "netease_songs":
                (a = a.match(/song\?id=(\d+)/gi)) && 0 < a.length && (e = [], c.each(a, function(a, b) {
                    -1 === c.inArray(b, e) && e.push(b)
                }), d.array = e.join(",").replace(/song\?id=/g, ""));
                break;
            case "netease_radio":
                (a = a.match(/djradio\?id=(\d+)/gi)) && 0 < a.length && (d.array = a[0].replace(/djradio\?id=/g, ""));
                break;
            case "netease_album":
                (a = a.match(/album\?id=(\d+)/gi)) && 0 < a.length && (d.array = a[0].replace(/album\?id=/g, ""));
                break;
            case "netease_playlist":
                (a = a.match(/playlist\?id=(\d+)/gi)) && 0 < a.length && (d.array = a[0].replace(/playlist\?id=/g, ""))
        }
        "remote" == b && (d.type = "remote", d.array = a);
        d.array ? c("#hermit-shell-insert").removeAttr("disabled") : c("#hermit-shell-insert").attr("disabled", "disabled");
        g(b, d)
    }
    function g(b, a) {
        h = '[hermit auto="' + a.auto + '" loop="' + a.loop + '" unexpand="' + a.unexpand + '"]' + a.type + "#:" + a.array + "[/hermit]";
        c("#hermit-preview").text(h).addClass("texted")
    }
    var f, h, k, q = c("#gohermit"),
        e = c("body"),
        m = c("#hermit-template").html(),
        r = Handlebars.compile(m),
        m = c("#hermit-remote-template").html(),
        n = Handlebars.compile(m),
        d = {
            type: "",
            array: "",
            auto: 0,
            loop: 0,
            unexpand: 0
        },
        p = 1;
    q.click(function() {
        f = "netease";
        d = {
            type: "",
            array: "",
            auto: 0,
            loop: 0,
            unexpand: 0
        };
        h = "";
        e.append(r())
    });
    e.on("click", "#hermit-shell-close", function() {
        c("#hermit-shell").remove()
    });
    e.on("click", "#hermit-shell-insert", function() {
        "disabled" != c(this).attr("disabled") && (send_to_editor(h), c("#hermit-shell").remove())
    });
    e.on("click", "#hermit-remote-content ul li", function() {
        var b = c(this);
        b.hasClass("selected") ? b.removeClass("selected") : b.addClass("selected")
    });
    e.on("click", ".media-router a", function() {
        var b = c(this),
            a = c(".media-router a").index(b);
        b.hasClass("active") || (c(".media-router a.active,.hermit-li.active").removeClass("active"), b.addClass("active"), c(".hermit-li").eq(a).addClass("active"), f = c(".hermit-li").eq(a).attr("data-type"), "remote" == f && (k ? c("#hermit-remote-content ul").html(n(k)) : c.ajax({
            url: hermit.ajax_url,
            data: {
                action: "hermit_source",
                type: "index",
                paged: p
            },
            success: function(a) {
                k = a;
                c("#hermit-remote-content ul").html(n(k));
                p++
            },
            error: function() {
                alert("\u83b7\u53d6\u5931\u8d25, \u8bf7\u7a0d\u5019\u91cd\u8bd5")
            }
        })))
    });
    e.on("click", "#hermit-auto", function() {
        var b = c(this);
        d.auto = b.prop("checked") ? 1 : 0;
        g(f, d)
    });
    e.on("click", "#hermit-loop", function() {
        var b = c(this);
        d.loop = b.prop("checked") ? 1 : 0;
        g(f, d)
    });
    e.on("change", "#hermit-unexpand", function() {
        var b = c(this);
        d.unexpand = b.prop("checked") ? 1 : 0;
        g(f, d)
    });
    e.on("click", "#hermit-remote-sure", function() {
        var b = [];
        c("#hermit-remote-content ul li.selected").each(function() {
            b.push(c(this).attr("data-id"))
        });
        b = b.join(",");
        console.log(f);
        l(f, b)
    });
    e.on("change", ".hermit-li.active input", function() {
        var b = c(".hermit-li.active .hermit-textarea").val();
        d.type = c(".hermit-li.active input:checked").val();
        l(f, b)
    });
    e.on("focus keyup input paste", ".hermit-textarea", function() {
        var b = c(this).val();
        d.type = c(".hermit-li.active input:checked").val();
        l(f, b)
    })
});