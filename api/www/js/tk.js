(function() {
	var obj = {
		_sengin: ["www.baidu.com", "www.baidu.com", "www1.baidu.com", "www1.baidu.com", "www.google.com", "www.iask.com", "www.sogou.com", "p.zhongsou.com", "so.163.com", "so.qq.com", "search.yahoo.com", "www.yahoo.com.cn", "cns.3721.com", "www.yisou.com", "www.google.cn", "www.soso.com", "search.cn.yahoo.com", "search.114.vnet.cn", "search.live.com", "www.yodao.com"],
        _sword: ["word", "wd", "word", "wd", "q", "k", "query", "w", "q", "w", "p", "p", "p", "p", "q", "w", "p", "kw", "q", "q"],

		_time: function() {
			var d = new Date();
			return "tm=" + parseInt(Date.parse(d) / 1000) + "&tz=" + d.getTimezoneOffset();
		},
		_cookie: function() {
			return "ck=" + (navigator.cookieEnabled ? 1: 0);		
		},
		_lang: function() {
			var _l;
            var nav = navigator;
            if (nav.systemLanguage) _l = nav.systemLanguage;
            else if (nav.browserLanguage) _l = nav.browserLanguage;
            else if (nav.language) _l = nav.language;
            else if (nav.userLanguage) _l = nav.userLanguage;
            else _l = '-';
            return "ln=" + _l.toLowerCase();
		},
		_refer: function() {
			var rf = document.referrer;
			if (!rf) {
				return 'rf=&sw=';
			}
			rf = this._domain(rf).toLowerCase();
			rf = (rf == document.domain) ? '_local' : rf;
			var rt = 'rf=' + this._escape(rf);			
			for (var i = 0; i < this._sengin.length; i++) {
				if (rf == this._sengin[i] && this._value(this._sword[i], rf)) {
					return rt + '&sw=' + this._value(this._sword[i], rf);
				}
			}
			
			return rt + '&sw=';
		},
		_value: function(v, s) {
			var d = s.indexOf(v + "=");
            if (d == -1) {
                return "";
            }
            var e = s.indexOf("&", d);
            if (e == -1) {
                e = s.length;
            }
            return this._escape(s.substring(d, e).split("=")[1]);
		},
		_escape: function(s) {
            return window.encodeURIComponent ? encodeURIComponent(s) : escape(s);
        },
        _domain: function(s) {
        	var d = s.split('/');
        	return d[2];
        },
		tracker: function() {
			var a = [];			
			a.push(this._time());
			a.push(this._cookie());
			a.push(this._lang());
			a.push(this._refer());

			var i = document.createElement('script');
			i.setAttribute('src', "http://api.aleafs.com/?controller=antispam&" + a.join("&"));
			document.body.appendChild(i);
		}
	}
	obj.tracker();
})();
