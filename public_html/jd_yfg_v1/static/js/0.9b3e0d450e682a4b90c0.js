webpackJsonp([0],{"+tPU":function(t,e,n){n("xGkn");for(var r=n("7KvD"),o=n("hJx8"),i=n("/bQp"),s=n("dSzd")("toStringTag"),a="CSSRuleList,CSSStyleDeclaration,CSSValueList,ClientRectList,DOMRectList,DOMStringList,DOMTokenList,DataTransferItemList,FileList,HTMLAllCollection,HTMLCollection,HTMLFormElement,HTMLSelectElement,MediaList,MimeTypeArray,NamedNodeMap,NodeList,PaintRequestList,Plugin,PluginArray,SVGLengthList,SVGNumberList,SVGPathSegList,SVGPointList,SVGStringList,SVGTransformList,SourceBufferList,StyleSheetList,TextTrackCueList,TextTrackList,TouchList".split(","),c=0;c<a.length;c++){var u=a[c],l=r[u],f=l&&l.prototype;f&&!f[s]&&o(f,s,u),i[u]=i.Array}},"//Fk":function(t,e,n){t.exports={default:n("U5ju"),__esModule:!0}},"/bQp":function(t,e){t.exports={}},"2KxR":function(t,e){t.exports=function(t,e,n,r){if(!(t instanceof e)||void 0!==r&&r in t)throw TypeError(n+": incorrect invocation!");return t}},"3Eo+":function(t,e){var n=0,r=Math.random();t.exports=function(t){return"Symbol(".concat(void 0===t?"":t,")_",(++n+r).toString(36))}},"3fs2":function(t,e,n){var r=n("RY/4"),o=n("dSzd")("iterator"),i=n("/bQp");t.exports=n("FeBl").getIteratorMethod=function(t){if(void 0!=t)return t[o]||t["@@iterator"]||i[r(t)]}},"4mcu":function(t,e){t.exports=function(){}},"52gC":function(t,e){t.exports=function(t){if(void 0==t)throw TypeError("Can't call method on  "+t);return t}},"5zde":function(t,e,n){n("zQR9"),n("qyJz"),t.exports=n("FeBl").Array.from},"82Mu":function(t,e,n){var r=n("7KvD"),o=n("L42u").set,i=r.MutationObserver||r.WebKitMutationObserver,s=r.process,a=r.Promise,c="process"==n("R9M2")(s);t.exports=function(){var t,e,n,u=function(){var r,o;for(c&&(r=s.domain)&&r.exit();t;){o=t.fn,t=t.next;try{o()}catch(r){throw t?n():e=void 0,r}}e=void 0,r&&r.enter()};if(c)n=function(){s.nextTick(u)};else if(!i||r.navigator&&r.navigator.standalone)if(a&&a.resolve){var l=a.resolve(void 0);n=function(){l.then(u)}}else n=function(){o.call(r,u)};else{var f=!0,v=document.createTextNode("");new i(u).observe(v,{characterData:!0}),n=function(){v.data=f=!f}}return function(r){var o={fn:r,next:void 0};e&&(e.next=o),t||(t=o,n()),e=o}}},"880/":function(t,e,n){t.exports=n("hJx8")},"94VQ":function(t,e,n){"use strict";var r=n("Yobk"),o=n("X8DO"),i=n("e6n0"),s={};n("hJx8")(s,n("dSzd")("iterator"),function(){return this}),t.exports=function(t,e,n){t.prototype=r(s,{next:o(1,n)}),i(t,e+" Iterator")}},CXw9:function(t,e,n){"use strict";var r,o,i,s,a=n("O4g8"),c=n("7KvD"),u=n("+ZMJ"),l=n("RY/4"),f=n("kM2E"),v=n("EqjI"),p=n("lOnJ"),h=n("2KxR"),d=n("NWt+"),_=n("t8x9"),g=n("L42u").set,m=n("82Mu")(),y=n("qARP"),x=n("dNDb"),w=n("iUbK"),b=n("fJUb"),L=c.TypeError,S=c.process,k=S&&S.versions,C=k&&k.v8||"",E=c.Promise,P="process"==l(S),T=function(){},j=o=y.f,O=!!function(){try{var t=E.resolve(1),e=(t.constructor={})[n("dSzd")("species")]=function(t){t(T,T)};return(P||"function"==typeof PromiseRejectionEvent)&&t.then(T)instanceof e&&0!==C.indexOf("6.6")&&-1===w.indexOf("Chrome/66")}catch(t){}}(),M=function(t){var e;return!(!v(t)||"function"!=typeof(e=t.then))&&e},R=function(t,e){if(!t._n){t._n=!0;var n=t._c;m(function(){for(var r=t._v,o=1==t._s,i=0,s=function(e){var n,i,s,a=o?e.ok:e.fail,c=e.resolve,u=e.reject,l=e.domain;try{a?(o||(2==t._h&&G(t),t._h=1),!0===a?n=r:(l&&l.enter(),n=a(r),l&&(l.exit(),s=!0)),n===e.promise?u(L("Promise-chain cycle")):(i=M(n))?i.call(n,c,u):c(n)):u(r)}catch(t){l&&!s&&l.exit(),u(t)}};n.length>i;)s(n[i++]);t._c=[],t._n=!1,e&&!t._h&&A(t)})}},A=function(t){g.call(c,function(){var e,n,r,o=t._v,i=F(t);if(i&&(e=x(function(){P?S.emit("unhandledRejection",o,t):(n=c.onunhandledrejection)?n({promise:t,reason:o}):(r=c.console)&&r.error&&r.error("Unhandled promise rejection",o)}),t._h=P||F(t)?2:1),t._a=void 0,i&&e.e)throw e.v})},F=function(t){return 1!==t._h&&0===(t._a||t._c).length},G=function(t){g.call(c,function(){var e;P?S.emit("rejectionHandled",t):(e=c.onrejectionhandled)&&e({promise:t,reason:t._v})})},D=function(t){var e=this;e._d||(e._d=!0,(e=e._w||e)._v=t,e._s=2,e._a||(e._a=e._c.slice()),R(e,!0))},I=function(t){var e,n=this;if(!n._d){n._d=!0,n=n._w||n;try{if(n===t)throw L("Promise can't be resolved itself");(e=M(t))?m(function(){var r={_w:n,_d:!1};try{e.call(t,u(I,r,1),u(D,r,1))}catch(t){D.call(r,t)}}):(n._v=t,n._s=1,R(n,!1))}catch(t){D.call({_w:n,_d:!1},t)}}};O||(E=function(t){h(this,E,"Promise","_h"),p(t),r.call(this);try{t(u(I,this,1),u(D,this,1))}catch(t){D.call(this,t)}},(r=function(t){this._c=[],this._a=void 0,this._s=0,this._d=!1,this._v=void 0,this._h=0,this._n=!1}).prototype=n("xH/j")(E.prototype,{then:function(t,e){var n=j(_(this,E));return n.ok="function"!=typeof t||t,n.fail="function"==typeof e&&e,n.domain=P?S.domain:void 0,this._c.push(n),this._a&&this._a.push(n),this._s&&R(this,!1),n.promise},catch:function(t){return this.then(void 0,t)}}),i=function(){var t=new r;this.promise=t,this.resolve=u(I,t,1),this.reject=u(D,t,1)},y.f=j=function(t){return t===E||t===s?new i(t):o(t)}),f(f.G+f.W+f.F*!O,{Promise:E}),n("e6n0")(E,"Promise"),n("bRrM")("Promise"),s=n("FeBl").Promise,f(f.S+f.F*!O,"Promise",{reject:function(t){var e=j(this);return(0,e.reject)(t),e.promise}}),f(f.S+f.F*(a||!O),"Promise",{resolve:function(t){return b(a&&this===s?E:this,t)}}),f(f.S+f.F*!(O&&n("dY0y")(function(t){E.all(t).catch(T)})),"Promise",{all:function(t){var e=this,n=j(e),r=n.resolve,o=n.reject,i=x(function(){var n=[],i=0,s=1;d(t,!1,function(t){var a=i++,c=!1;n.push(void 0),s++,e.resolve(t).then(function(t){c||(c=!0,n[a]=t,--s||r(n))},o)}),--s||r(n)});return i.e&&o(i.v),n.promise},race:function(t){var e=this,n=j(e),r=n.reject,o=x(function(){d(t,!1,function(t){e.resolve(t).then(n.resolve,r)})});return o.e&&r(o.v),n.promise}})},EGZi:function(t,e){t.exports=function(t,e){return{value:e,done:!!t}}},EqBC:function(t,e,n){"use strict";var r=n("kM2E"),o=n("FeBl"),i=n("7KvD"),s=n("t8x9"),a=n("fJUb");r(r.P+r.R,"Promise",{finally:function(t){var e=s(this,o.Promise||i.Promise),n="function"==typeof t;return this.then(n?function(n){return a(e,t()).then(function(){return n})}:t,n?function(n){return a(e,t()).then(function(){throw n})}:t)}})},Gu7T:function(t,e,n){"use strict";e.__esModule=!0;var r,o=n("c/Tr"),i=(r=o)&&r.__esModule?r:{default:r};e.default=function(t){if(Array.isArray(t)){for(var e=0,n=Array(t.length);e<t.length;e++)n[e]=t[e];return n}return(0,i.default)(t)}},Ibhu:function(t,e,n){var r=n("D2L2"),o=n("TcQ7"),i=n("vFc/")(!1),s=n("ax3d")("IE_PROTO");t.exports=function(t,e){var n,a=o(t),c=0,u=[];for(n in a)n!=s&&r(a,n)&&u.push(n);for(;e.length>c;)r(a,n=e[c++])&&(~i(u,n)||u.push(n));return u}},L42u:function(t,e,n){var r,o,i,s=n("+ZMJ"),a=n("knuC"),c=n("RPLV"),u=n("ON07"),l=n("7KvD"),f=l.process,v=l.setImmediate,p=l.clearImmediate,h=l.MessageChannel,d=l.Dispatch,_=0,g={},m=function(){var t=+this;if(g.hasOwnProperty(t)){var e=g[t];delete g[t],e()}},y=function(t){m.call(t.data)};v&&p||(v=function(t){for(var e=[],n=1;arguments.length>n;)e.push(arguments[n++]);return g[++_]=function(){a("function"==typeof t?t:Function(t),e)},r(_),_},p=function(t){delete g[t]},"process"==n("R9M2")(f)?r=function(t){f.nextTick(s(m,t,1))}:d&&d.now?r=function(t){d.now(s(m,t,1))}:h?(i=(o=new h).port2,o.port1.onmessage=y,r=s(i.postMessage,i,1)):l.addEventListener&&"function"==typeof postMessage&&!l.importScripts?(r=function(t){l.postMessage(t+"","*")},l.addEventListener("message",y,!1)):r="onreadystatechange"in u("script")?function(t){c.appendChild(u("script")).onreadystatechange=function(){c.removeChild(this),m.call(t)}}:function(t){setTimeout(s(m,t,1),0)}),t.exports={set:v,clear:p}},M6a0:function(t,e){},MU5D:function(t,e,n){var r=n("R9M2");t.exports=Object("z").propertyIsEnumerable(0)?Object:function(t){return"String"==r(t)?t.split(""):Object(t)}},Mhyx:function(t,e,n){var r=n("/bQp"),o=n("dSzd")("iterator"),i=Array.prototype;t.exports=function(t){return void 0!==t&&(r.Array===t||i[o]===t)}},"NWt+":function(t,e,n){var r=n("+ZMJ"),o=n("msXi"),i=n("Mhyx"),s=n("77Pl"),a=n("QRG4"),c=n("3fs2"),u={},l={};(e=t.exports=function(t,e,n,f,v){var p,h,d,_,g=v?function(){return t}:c(t),m=r(n,f,e?2:1),y=0;if("function"!=typeof g)throw TypeError(t+" is not iterable!");if(i(g)){for(p=a(t.length);p>y;y++)if((_=e?m(s(h=t[y])[0],h[1]):m(t[y]))===u||_===l)return _}else for(d=g.call(t);!(h=d.next()).done;)if((_=o(d,m,h.value,e))===u||_===l)return _}).BREAK=u,e.RETURN=l},O4g8:function(t,e){t.exports=!0},PzxK:function(t,e,n){var r=n("D2L2"),o=n("sB3e"),i=n("ax3d")("IE_PROTO"),s=Object.prototype;t.exports=Object.getPrototypeOf||function(t){return t=o(t),r(t,i)?t[i]:"function"==typeof t.constructor&&t instanceof t.constructor?t.constructor.prototype:t instanceof Object?s:null}},QRG4:function(t,e,n){var r=n("UuGF"),o=Math.min;t.exports=function(t){return t>0?o(r(t),9007199254740991):0}},Qt9A:function(t,e,n){"use strict";Object.defineProperty(e,"__esModule",{value:!0});var r=n("x2x/"),o={render:function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("div",{directives:[{name:"wechat-title",rawName:"v-wechat-title",value:this.title,expression:"this.title"}],staticClass:"wrap"},[n("header",[t._m(0),t._v(" "),n("router-link",{staticClass:"explain_mao",attrs:{to:{name:"orders",query:{app_id:t.app_id}}}},[t._v("10.31订单")])],1),t._v(" "),n("article",{staticClass:"main activity_box",attrs:{id:"scrollTop"}},[n("div",{staticClass:"act_box"},[n("div",{staticClass:"title"},[t._v("0.01元包邮商品")]),t._v(" "),t.category.length?n("div",{ref:"viewBox",staticClass:"sort_navs"},t._l(t.category,function(e,r){return n("em",{key:r,class:{active:r==t.current},on:{click:function(n){return t.switchNav(r,e.cid)}}},[t._v(t._s(e.cname))])}),0):t._e(),t._v(" "),n("keep-alive",[t.goodsList.length?n("ul",{staticClass:"jd_list"},t._l(t.goodsList,function(e,r){return n("li",{key:r,staticClass:"clearfix",on:{click:function(n){return t.toWeixin(e)}}},[n("div",{staticClass:"img"},[n("img",{attrs:{src:e.picurl}})]),t._v(" "),n("div",{staticClass:"right"},[n("div",{staticClass:"info"},[n("h3",{staticClass:"overflowTextTwo"},[t._v(t._s(e.goods_name))])]),t._v(" "),n("div",{staticClass:"bottom clearfix"},[n("div",{staticClass:"price"},[n("p",{staticClass:"origin"},[t._v("销量 "+t._s(e.sales))]),t._v(" "),n("p",{staticClass:"origin"},[t._v("京东价 ¥"+t._s(e.price))]),t._v(" "),n("p",{staticClass:"pay"},[t._v("\n                  券后价 "),n("span",{staticClass:"num"},[t._v("¥9.90")])])]),t._v(" "),n("div",{staticClass:"btn"},[t._v("返 "),n("i",[t._v("9.89")]),t._v("元")])])])])}),0):n("ul",{staticClass:"jd_list null_value"},[n("li",[t._v("没有该品类的活动商品")])])]),t._v(" "),n("div",{directives:[{name:"show",rawName:"v-show",value:t.goodsList.length,expression:"goodsList.length"}],staticClass:"more",attrs:{id:"ruleBox"},on:{click:t.getList}},[t._v(t._s(t.loadText))])],1),t._v(" "),t._m(1),t._v(" "),n("div",{directives:[{name:"show",rawName:"v-show",value:t.topShow,expression:"topShow"}],staticClass:"scrollTop",on:{click:t.goTop}})]),t._v(" "),t.popupShow?n("div",{staticClass:"popup"},[n("div",{staticClass:"box"},[n("div",{staticClass:"title"},[t._v("温馨提示")]),t._v(" "),t._m(2),t._v(" "),n("div",{staticClass:"button"},[n("em",{on:{click:t.close}},[t._v("关闭")]),t._v(" "),n("em",{on:{click:t.toRule}},[t._v("查看规则")])])])]):t._e()])},staticRenderFns:[function(){var t=this.$createElement,e=this._self._c||t;return e("div",{staticClass:"activity_head"},[e("span",[this._v("活动时间：10月31日")])])},function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("div",{staticClass:"act_box",staticStyle:{background:"#fff"}},[n("div",{staticClass:"title"},[t._v("活动规则")]),t._v(" "),n("ul",{staticClass:"jd_rule"},[n("li",[n("span",[t._v("1")]),t._v(" "),n("p",[t._v("活动日期：10月31日(仅1天)；")])]),t._v(" "),n("li",[n("span",[t._v("2")]),t._v(" "),n("p",[t._v("参与范围：我的浏览器所有用户；")])]),t._v(" "),n("li",[n("span",[t._v("3")]),t._v(" "),n("p",{staticClass:"red"},[t._v("必须由我的浏览器-“京东一分购”模块进入，并最后通过【京喜小程序】，微信支付下单成功的商品，才算有效订单；")])]),t._v(" "),n("li",[n("span",[t._v("4")]),t._v(" "),n("p",[t._v("活动仅限活动区域商品，必须按默认商品规格购买，不要更改颜色、大小等，否则不参与补贴；")])]),t._v(" "),n("li",[n("span",[t._v("5")]),t._v(" "),n("p",[t._v("请勿使用礼品卡、E卡、京豆、京东红包、京东账号余额，否则不参与补贴；")])]),t._v(" "),n("li",[n("span",[t._v("6")]),t._v(" "),n("p",{staticClass:"red"},[t._v("一个京东账号限购6单，同一个京东账号同一件商品仅限购买1单，每个有效订单补贴返款￥9.89；")])]),t._v(" "),n("li",[n("span",[t._v("7")]),t._v(" "),n("p",[t._v("选择默认选项购买，不要更改颜色、大小等，否则不参与补贴；")])]),t._v(" "),n("li",[n("span",[t._v("8")]),t._v(" "),n("p",[t._v("非微信支付、未付款、退款、退货订单，均不参与补贴；")])]),t._v(" "),n("li",[n("span",[t._v("9")]),t._v(" "),n("p",[n("em",{staticClass:"red"},[t._v("下单必须先领券，且单个商品单独下单付款，")]),t._v("多个商品加入购物车一起结算只能算一个有效订单；")])]),t._v(" "),n("li",[n("span",[t._v("10")]),t._v(" "),n("p",[t._v("关于订单不同步：活动期间新增加用户量大，订单量激增，数据同步会比较慢。如遇到订单不显示，请严格按照玩法规则，勿擅自操作退货，耐心等待同步,24小时后如还未在“我的订单”内查看到订单请及时去京东退款。最高延迟一般会控制在1小时以内。")])]),t._v(" "),n("li",[n("p",{staticStyle:{"padding-top":".14rem",color:"#000","font-weight":"bold"}},[t._v("补贴返款说明：")])]),t._v(" "),n("li",[n("p",[t._v("1、"),n("em",{staticClass:"red"},[t._v("补贴返款方式：")]),t._v("我的币")])]),t._v(" "),n("li",[n("p",[t._v("2、在11月份确认收货的有效订单，将于12月25日转入我的币；")])]),t._v(" "),n("li",[n("p",[t._v("3、收货后请尽快在京东确认收货，否则将会影响补贴返款时间；")])]),t._v(" "),n("li",[n("p",{staticStyle:{"padding-top":".14rem",color:"#000","font-weight":"bold"}},[t._v("如何参与“京东一分购”")])]),t._v(" "),n("li",[n("p",[t._v("1、在我的浏览器首页-点击“京东一分购”模块进入，"),n("em",{staticClass:"red"},[t._v("在活动页选择心仪商品，必须先领券后下单，实际付款金额必须是通过【京喜小程序】微信支付9.9，")]),t._v("完成下单；")])]),t._v(" "),n("li",[n("p",[t._v('2、微信支付成功后及时进入“京东一分购页”-“我的订单”内核查订单，订单延迟一小时内，一小时后订单任未显示，可复制京东订单号在“我的订单"上输入框粘贴提交，若查无订单，请及时退款，以免造成不必要的损失；')])]),t._v(" "),n("li",[n("p",[t._v("3、显示有效订单后，商品不做退款操作情况下，将￥9.89补贴返款根据活动时间以我的币补贴返款；")])]),t._v(" "),n("li",[n("p",[t._v("注：没有京东账号，请提前注册好京东账号，并设置好收货地址，方便抢购；")])])])])},function(){var t=this.$createElement,e=this._self._c||t;return e("div",{staticClass:"content"},[e("p",[this._v("1、活动时间为10月31号当天")]),this._v(" "),e("p",[this._v("2、每个用户限购6单")]),this._v(" "),e("p",[this._v("3、支付时必须使用微信支付")]),this._v(" "),e("p",[this._v("4、...")])])}]};var i=function(t){n("nnAG")},s=n("VU/8")(r.a,o,!1,i,"data-v-f7c9bd5c",null);e.default=s.exports},R9M2:function(t,e){var n={}.toString;t.exports=function(t){return n.call(t).slice(8,-1)}},RPLV:function(t,e,n){var r=n("7KvD").document;t.exports=r&&r.documentElement},"RY/4":function(t,e,n){var r=n("R9M2"),o=n("dSzd")("toStringTag"),i="Arguments"==r(function(){return arguments}());t.exports=function(t){var e,n,s;return void 0===t?"Undefined":null===t?"Null":"string"==typeof(n=function(t,e){try{return t[e]}catch(t){}}(e=Object(t),o))?n:i?r(e):"Object"==(s=r(e))&&"function"==typeof e.callee?"Arguments":s}},SldL:function(t,e){!function(e){"use strict";var n,r=Object.prototype,o=r.hasOwnProperty,i="function"==typeof Symbol?Symbol:{},s=i.iterator||"@@iterator",a=i.asyncIterator||"@@asyncIterator",c=i.toStringTag||"@@toStringTag",u="object"==typeof t,l=e.regeneratorRuntime;if(l)u&&(t.exports=l);else{(l=e.regeneratorRuntime=u?t.exports:{}).wrap=x;var f="suspendedStart",v="suspendedYield",p="executing",h="completed",d={},_={};_[s]=function(){return this};var g=Object.getPrototypeOf,m=g&&g(g(O([])));m&&m!==r&&o.call(m,s)&&(_=m);var y=S.prototype=b.prototype=Object.create(_);L.prototype=y.constructor=S,S.constructor=L,S[c]=L.displayName="GeneratorFunction",l.isGeneratorFunction=function(t){var e="function"==typeof t&&t.constructor;return!!e&&(e===L||"GeneratorFunction"===(e.displayName||e.name))},l.mark=function(t){return Object.setPrototypeOf?Object.setPrototypeOf(t,S):(t.__proto__=S,c in t||(t[c]="GeneratorFunction")),t.prototype=Object.create(y),t},l.awrap=function(t){return{__await:t}},k(C.prototype),C.prototype[a]=function(){return this},l.AsyncIterator=C,l.async=function(t,e,n,r){var o=new C(x(t,e,n,r));return l.isGeneratorFunction(e)?o:o.next().then(function(t){return t.done?t.value:o.next()})},k(y),y[c]="Generator",y[s]=function(){return this},y.toString=function(){return"[object Generator]"},l.keys=function(t){var e=[];for(var n in t)e.push(n);return e.reverse(),function n(){for(;e.length;){var r=e.pop();if(r in t)return n.value=r,n.done=!1,n}return n.done=!0,n}},l.values=O,j.prototype={constructor:j,reset:function(t){if(this.prev=0,this.next=0,this.sent=this._sent=n,this.done=!1,this.delegate=null,this.method="next",this.arg=n,this.tryEntries.forEach(T),!t)for(var e in this)"t"===e.charAt(0)&&o.call(this,e)&&!isNaN(+e.slice(1))&&(this[e]=n)},stop:function(){this.done=!0;var t=this.tryEntries[0].completion;if("throw"===t.type)throw t.arg;return this.rval},dispatchException:function(t){if(this.done)throw t;var e=this;function r(r,o){return a.type="throw",a.arg=t,e.next=r,o&&(e.method="next",e.arg=n),!!o}for(var i=this.tryEntries.length-1;i>=0;--i){var s=this.tryEntries[i],a=s.completion;if("root"===s.tryLoc)return r("end");if(s.tryLoc<=this.prev){var c=o.call(s,"catchLoc"),u=o.call(s,"finallyLoc");if(c&&u){if(this.prev<s.catchLoc)return r(s.catchLoc,!0);if(this.prev<s.finallyLoc)return r(s.finallyLoc)}else if(c){if(this.prev<s.catchLoc)return r(s.catchLoc,!0)}else{if(!u)throw new Error("try statement without catch or finally");if(this.prev<s.finallyLoc)return r(s.finallyLoc)}}}},abrupt:function(t,e){for(var n=this.tryEntries.length-1;n>=0;--n){var r=this.tryEntries[n];if(r.tryLoc<=this.prev&&o.call(r,"finallyLoc")&&this.prev<r.finallyLoc){var i=r;break}}i&&("break"===t||"continue"===t)&&i.tryLoc<=e&&e<=i.finallyLoc&&(i=null);var s=i?i.completion:{};return s.type=t,s.arg=e,i?(this.method="next",this.next=i.finallyLoc,d):this.complete(s)},complete:function(t,e){if("throw"===t.type)throw t.arg;return"break"===t.type||"continue"===t.type?this.next=t.arg:"return"===t.type?(this.rval=this.arg=t.arg,this.method="return",this.next="end"):"normal"===t.type&&e&&(this.next=e),d},finish:function(t){for(var e=this.tryEntries.length-1;e>=0;--e){var n=this.tryEntries[e];if(n.finallyLoc===t)return this.complete(n.completion,n.afterLoc),T(n),d}},catch:function(t){for(var e=this.tryEntries.length-1;e>=0;--e){var n=this.tryEntries[e];if(n.tryLoc===t){var r=n.completion;if("throw"===r.type){var o=r.arg;T(n)}return o}}throw new Error("illegal catch attempt")},delegateYield:function(t,e,r){return this.delegate={iterator:O(t),resultName:e,nextLoc:r},"next"===this.method&&(this.arg=n),d}}}function x(t,e,n,r){var o=e&&e.prototype instanceof b?e:b,i=Object.create(o.prototype),s=new j(r||[]);return i._invoke=function(t,e,n){var r=f;return function(o,i){if(r===p)throw new Error("Generator is already running");if(r===h){if("throw"===o)throw i;return M()}for(n.method=o,n.arg=i;;){var s=n.delegate;if(s){var a=E(s,n);if(a){if(a===d)continue;return a}}if("next"===n.method)n.sent=n._sent=n.arg;else if("throw"===n.method){if(r===f)throw r=h,n.arg;n.dispatchException(n.arg)}else"return"===n.method&&n.abrupt("return",n.arg);r=p;var c=w(t,e,n);if("normal"===c.type){if(r=n.done?h:v,c.arg===d)continue;return{value:c.arg,done:n.done}}"throw"===c.type&&(r=h,n.method="throw",n.arg=c.arg)}}}(t,n,s),i}function w(t,e,n){try{return{type:"normal",arg:t.call(e,n)}}catch(t){return{type:"throw",arg:t}}}function b(){}function L(){}function S(){}function k(t){["next","throw","return"].forEach(function(e){t[e]=function(t){return this._invoke(e,t)}})}function C(t){var e;this._invoke=function(n,r){function i(){return new Promise(function(e,i){!function e(n,r,i,s){var a=w(t[n],t,r);if("throw"!==a.type){var c=a.arg,u=c.value;return u&&"object"==typeof u&&o.call(u,"__await")?Promise.resolve(u.__await).then(function(t){e("next",t,i,s)},function(t){e("throw",t,i,s)}):Promise.resolve(u).then(function(t){c.value=t,i(c)},s)}s(a.arg)}(n,r,e,i)})}return e=e?e.then(i,i):i()}}function E(t,e){var r=t.iterator[e.method];if(r===n){if(e.delegate=null,"throw"===e.method){if(t.iterator.return&&(e.method="return",e.arg=n,E(t,e),"throw"===e.method))return d;e.method="throw",e.arg=new TypeError("The iterator does not provide a 'throw' method")}return d}var o=w(r,t.iterator,e.arg);if("throw"===o.type)return e.method="throw",e.arg=o.arg,e.delegate=null,d;var i=o.arg;return i?i.done?(e[t.resultName]=i.value,e.next=t.nextLoc,"return"!==e.method&&(e.method="next",e.arg=n),e.delegate=null,d):i:(e.method="throw",e.arg=new TypeError("iterator result is not an object"),e.delegate=null,d)}function P(t){var e={tryLoc:t[0]};1 in t&&(e.catchLoc=t[1]),2 in t&&(e.finallyLoc=t[2],e.afterLoc=t[3]),this.tryEntries.push(e)}function T(t){var e=t.completion||{};e.type="normal",delete e.arg,t.completion=e}function j(t){this.tryEntries=[{tryLoc:"root"}],t.forEach(P,this),this.reset(!0)}function O(t){if(t){var e=t[s];if(e)return e.call(t);if("function"==typeof t.next)return t;if(!isNaN(t.length)){var r=-1,i=function e(){for(;++r<t.length;)if(o.call(t,r))return e.value=t[r],e.done=!1,e;return e.value=n,e.done=!0,e};return i.next=i}}return{next:M}}function M(){return{value:n,done:!0}}}(function(){return this}()||Function("return this")())},TcQ7:function(t,e,n){var r=n("MU5D"),o=n("52gC");t.exports=function(t){return r(o(t))}},U5ju:function(t,e,n){n("M6a0"),n("zQR9"),n("+tPU"),n("CXw9"),n("EqBC"),n("jKW+"),t.exports=n("FeBl").Promise},UuGF:function(t,e){var n=Math.ceil,r=Math.floor;t.exports=function(t){return isNaN(t=+t)?0:(t>0?r:n)(t)}},Xxa5:function(t,e,n){t.exports=n("jyFz")},Yobk:function(t,e,n){var r=n("77Pl"),o=n("qio6"),i=n("xnc9"),s=n("ax3d")("IE_PROTO"),a=function(){},c=function(){var t,e=n("ON07")("iframe"),r=i.length;for(e.style.display="none",n("RPLV").appendChild(e),e.src="javascript:",(t=e.contentWindow.document).open(),t.write("<script>document.F=Object<\/script>"),t.close(),c=t.F;r--;)delete c.prototype[i[r]];return c()};t.exports=Object.create||function(t,e){var n;return null!==t?(a.prototype=r(t),n=new a,a.prototype=null,n[s]=t):n=c(),void 0===e?n:o(n,e)}},ax3d:function(t,e,n){var r=n("e8AB")("keys"),o=n("3Eo+");t.exports=function(t){return r[t]||(r[t]=o(t))}},bRrM:function(t,e,n){"use strict";var r=n("7KvD"),o=n("FeBl"),i=n("evD5"),s=n("+E39"),a=n("dSzd")("species");t.exports=function(t){var e="function"==typeof o[t]?o[t]:r[t];s&&e&&!e[a]&&i.f(e,a,{configurable:!0,get:function(){return this}})}},"c/Tr":function(t,e,n){t.exports={default:n("5zde"),__esModule:!0}},dNDb:function(t,e){t.exports=function(t){try{return{e:!1,v:t()}}catch(t){return{e:!0,v:t}}}},dSzd:function(t,e,n){var r=n("e8AB")("wks"),o=n("3Eo+"),i=n("7KvD").Symbol,s="function"==typeof i;(t.exports=function(t){return r[t]||(r[t]=s&&i[t]||(s?i:o)("Symbol."+t))}).store=r},dY0y:function(t,e,n){var r=n("dSzd")("iterator"),o=!1;try{var i=[7][r]();i.return=function(){o=!0},Array.from(i,function(){throw 2})}catch(t){}t.exports=function(t,e){if(!e&&!o)return!1;var n=!1;try{var i=[7],s=i[r]();s.next=function(){return{done:n=!0}},i[r]=function(){return s},t(i)}catch(t){}return n}},e6n0:function(t,e,n){var r=n("evD5").f,o=n("D2L2"),i=n("dSzd")("toStringTag");t.exports=function(t,e,n){t&&!o(t=n?t:t.prototype,i)&&r(t,i,{configurable:!0,value:e})}},e8AB:function(t,e,n){var r=n("FeBl"),o=n("7KvD"),i=o["__core-js_shared__"]||(o["__core-js_shared__"]={});(t.exports=function(t,e){return i[t]||(i[t]=void 0!==e?e:{})})("versions",[]).push({version:r.version,mode:n("O4g8")?"pure":"global",copyright:"© 2019 Denis Pushkarev (zloirock.ru)"})},exGp:function(t,e,n){"use strict";e.__esModule=!0;var r,o=n("//Fk"),i=(r=o)&&r.__esModule?r:{default:r};e.default=function(t){return function(){var e=t.apply(this,arguments);return new i.default(function(t,n){return function r(o,s){try{var a=e[o](s),c=a.value}catch(t){return void n(t)}if(!a.done)return i.default.resolve(c).then(function(t){r("next",t)},function(t){r("throw",t)});t(c)}("next")})}}},fBQ2:function(t,e,n){"use strict";var r=n("evD5"),o=n("X8DO");t.exports=function(t,e,n){e in t?r.f(t,e,o(0,n)):t[e]=n}},fJUb:function(t,e,n){var r=n("77Pl"),o=n("EqjI"),i=n("qARP");t.exports=function(t,e){if(r(t),o(e)&&e.constructor===t)return e;var n=i.f(t);return(0,n.resolve)(e),n.promise}},fkB2:function(t,e,n){var r=n("UuGF"),o=Math.max,i=Math.min;t.exports=function(t,e){return(t=r(t))<0?o(t+e,0):i(t,e)}},h65t:function(t,e,n){var r=n("UuGF"),o=n("52gC");t.exports=function(t){return function(e,n){var i,s,a=String(o(e)),c=r(n),u=a.length;return c<0||c>=u?t?"":void 0:(i=a.charCodeAt(c))<55296||i>56319||c+1===u||(s=a.charCodeAt(c+1))<56320||s>57343?t?a.charAt(c):i:t?a.slice(c,c+2):s-56320+(i-55296<<10)+65536}}},iUbK:function(t,e,n){var r=n("7KvD").navigator;t.exports=r&&r.userAgent||""},"jKW+":function(t,e,n){"use strict";var r=n("kM2E"),o=n("qARP"),i=n("dNDb");r(r.S,"Promise",{try:function(t){var e=o.f(this),n=i(t);return(n.e?e.reject:e.resolve)(n.v),e.promise}})},jyFz:function(t,e,n){var r=function(){return this}()||Function("return this")(),o=r.regeneratorRuntime&&Object.getOwnPropertyNames(r).indexOf("regeneratorRuntime")>=0,i=o&&r.regeneratorRuntime;if(r.regeneratorRuntime=void 0,t.exports=n("SldL"),o)r.regeneratorRuntime=i;else try{delete r.regeneratorRuntime}catch(t){r.regeneratorRuntime=void 0}},knuC:function(t,e){t.exports=function(t,e,n){var r=void 0===n;switch(e.length){case 0:return r?t():t.call(n);case 1:return r?t(e[0]):t.call(n,e[0]);case 2:return r?t(e[0],e[1]):t.call(n,e[0],e[1]);case 3:return r?t(e[0],e[1],e[2]):t.call(n,e[0],e[1],e[2]);case 4:return r?t(e[0],e[1],e[2],e[3]):t.call(n,e[0],e[1],e[2],e[3])}return t.apply(n,e)}},lktj:function(t,e,n){var r=n("Ibhu"),o=n("xnc9");t.exports=Object.keys||function(t){return r(t,o)}},mF0L:function(t,e,n){"use strict";n.d(e,"a",function(){return r});var r={isWeixin:function(){return!!/MicroMessenger/i.test(navigator.userAgent)},isAndroid:function(){return!!/android/i.test(navigator.userAgent)},isIos:function(){return!!/(iPhone|iPad|iPod|iOS)/i.test(navigator.userAgent)}}},msXi:function(t,e,n){var r=n("77Pl");t.exports=function(t,e,n,o){try{return o?e(r(n)[0],n[1]):e(n)}catch(e){var i=t.return;throw void 0!==i&&r(i.call(t)),e}}},nnAG:function(t,e){},qARP:function(t,e,n){"use strict";var r=n("lOnJ");t.exports.f=function(t){return new function(t){var e,n;this.promise=new t(function(t,r){if(void 0!==e||void 0!==n)throw TypeError("Bad Promise constructor");e=t,n=r}),this.resolve=r(e),this.reject=r(n)}(t)}},qio6:function(t,e,n){var r=n("evD5"),o=n("77Pl"),i=n("lktj");t.exports=n("+E39")?Object.defineProperties:function(t,e){o(t);for(var n,s=i(e),a=s.length,c=0;a>c;)r.f(t,n=s[c++],e[n]);return t}},qyJz:function(t,e,n){"use strict";var r=n("+ZMJ"),o=n("kM2E"),i=n("sB3e"),s=n("msXi"),a=n("Mhyx"),c=n("QRG4"),u=n("fBQ2"),l=n("3fs2");o(o.S+o.F*!n("dY0y")(function(t){Array.from(t)}),"Array",{from:function(t){var e,n,o,f,v=i(t),p="function"==typeof this?this:Array,h=arguments.length,d=h>1?arguments[1]:void 0,_=void 0!==d,g=0,m=l(v);if(_&&(d=r(d,h>2?arguments[2]:void 0,2)),void 0==m||p==Array&&a(m))for(n=new p(e=c(v.length));e>g;g++)u(n,g,_?d(v[g],g):v[g]);else for(f=m.call(v),n=new p;!(o=f.next()).done;g++)u(n,g,_?s(f,d,[o.value,g],!0):o.value);return n.length=g,n}})},sB3e:function(t,e,n){var r=n("52gC");t.exports=function(t){return Object(r(t))}},t8x9:function(t,e,n){var r=n("77Pl"),o=n("lOnJ"),i=n("dSzd")("species");t.exports=function(t,e){var n,s=r(t).constructor;return void 0===s||void 0==(n=r(s)[i])?e:o(n)}},"vFc/":function(t,e,n){var r=n("TcQ7"),o=n("QRG4"),i=n("fkB2");t.exports=function(t){return function(e,n,s){var a,c=r(e),u=o(c.length),l=i(s,u);if(t&&n!=n){for(;u>l;)if((a=c[l++])!=a)return!0}else for(;u>l;l++)if((t||l in c)&&c[l]===n)return t||l||0;return!t&&-1}}},"vIB/":function(t,e,n){"use strict";var r=n("O4g8"),o=n("kM2E"),i=n("880/"),s=n("hJx8"),a=n("/bQp"),c=n("94VQ"),u=n("e6n0"),l=n("PzxK"),f=n("dSzd")("iterator"),v=!([].keys&&"next"in[].keys()),p=function(){return this};t.exports=function(t,e,n,h,d,_,g){c(n,e,h);var m,y,x,w=function(t){if(!v&&t in k)return k[t];switch(t){case"keys":case"values":return function(){return new n(this,t)}}return function(){return new n(this,t)}},b=e+" Iterator",L="values"==d,S=!1,k=t.prototype,C=k[f]||k["@@iterator"]||d&&k[d],E=C||w(d),P=d?L?w("entries"):E:void 0,T="Array"==e&&k.entries||C;if(T&&(x=l(T.call(new t)))!==Object.prototype&&x.next&&(u(x,b,!0),r||"function"==typeof x[f]||s(x,f,p)),L&&C&&"values"!==C.name&&(S=!0,E=function(){return C.call(this)}),r&&!g||!v&&!S&&k[f]||s(k,f,E),a[e]=E,a[b]=p,d)if(m={values:L?E:w("values"),keys:_?E:w("keys"),entries:P},g)for(y in m)y in k||i(k,y,m[y]);else o(o.P+o.F*(v||S),e,m);return m}},"x2x/":function(t,e,n){"use strict";(function(t){var r=n("Gu7T"),o=n.n(r),i=n("Xxa5"),s=n.n(i),a=n("exGp"),c=n.n(a),u=n("mF0L");e.a={name:"",data:function(){return{title:"京东10.31一分购",current:0,category:[],goodsList:[],app_id:"",cid:"",pageindex:"1",pagesize:"10",loading:!0,loadText:"",scrollTop:"",topShow:!1,popupShow:!0}},components:{},mounted:function(){this.bus.$emit("loading",!0),this.app_id=this.$route.query.app_id,this.getCategory(),window.addEventListener("scroll",this.handleScroll),this.isShowYes()},methods:{getCategory:function(){var t=this;return c()(s.a.mark(function e(){var n,r,o,i;return s.a.wrap(function(e){for(;;)switch(e.prev=e.next){case 0:return n=t,e.next=3,n.post("one_go_category");case 3:if(200==(r=e.sent).data.code){for(o=r.data.data,i=0;i<o.length;i++)""!=o[i].cname&&n.category.push(o[i]);n.cid=n.category[0].cid}setTimeout(function(){n.getGoodsList()},500);case 6:case"end":return e.stop()}},e,t)}))()},switchNav:function(e,n){var r=this.$refs.viewBox.scrollWidth/this.category.length*(e-2);if(this.current==e)return!1;this.current=e,t(".sort_navs").animate({scrollLeft:r},500),this.cid=n,this.goodsList=[],this.pageindex=1,this.getGoodsList()},getGoodsList:function(){var t=this;return c()(s.a.mark(function e(){var n,r,i;return s.a.wrap(function(e){for(;;)switch(e.prev=e.next){case 0:if((n=t).bus.$emit("loading",!0),!n.app_id){e.next=9;break}return e.next=5,n.post("one_go_goods_list",{data:'{"app_id":"'+n.app_id+'","pageindex":"'+n.pageindex+'","pagesize":"'+n.pagesize+'","cid":"'+n.cid+'"}'});case 5:200==(r=e.sent).data.code&&(i=r.data.data.data,n.goodsList=[].concat(o()(n.goodsList),o()(i)),n.loadText="点击加载更多商品~",0==i.length&&(n.loadText="该品类商品已加载全部~"),setTimeout(function(){t.bus.$emit("loading",!1)},500)),e.next=10;break;case 9:alert("没获取到登录信息！");case 10:case"end":return e.stop()}},e,t)}))()},getList:function(){this.pageindex++,this.loadText="加载中...",this.getGoodsList()},toWeixin:function(t){var e=this;return c()(s.a.mark(function n(){var r,o,i,a,c,l;return s.a.wrap(function(n){for(;;)switch(n.prev=n.next){case 0:return(r=e).bus.$emit("loading",!0),n.next=4,r.get("get_time");case 4:if(!(n.sent.data>="1572537599")){n.next=9;break}alert("该活动已经结束！"),n.next=21;break;case 9:if(!r.app_id){n.next=20;break}return n.next=12,r.post("one_go_union_uri",{data:'{"app_id":"'+r.app_id+'","goods_id":"'+t.goods_id+'"}'});case 12:o=n.sent,i=encodeURIComponent(o.data.data),a=encodeURIComponent(t.picurl),c=encodeURIComponent(t.goods_name),l="src/pages/activity/activity?buyurl="+i+"&picurl="+a+"&goods_name="+c,200==o.data.code?u.a.isAndroid()?window.jsbridge.wxminiprogram(l):u.a.isIos()&&(window.location.href="jsbridge://wxminiprogram?src/pages/activity/activity?buyurl="+i+"&picurl="+a+"&goods_name="+c):alert("没有获得商品连接！"),n.next=21;break;case 20:alert("没获取到登录信息！");case 21:setTimeout(function(){e.bus.$emit("loading",!1)},500);case 22:case"end":return n.stop()}},n,e)}))()},handleScroll:function(){this.scrollTop=window.pageYOffset||document.documentElement.scrollTop||document.body.scrollTop,this.scrollTop>500?this.topShow=!0:this.topShow=!1},goTop:function(){document.getElementById("scrollTop").scrollIntoView()},close:function(){this.popupShow=!1},toRule:function(){localStorage.setItem("isShowYes",!0),this.popupShow=!1,document.getElementById("ruleBox").scrollIntoView()},isShowYes:function(){localStorage.getItem("isShowYes")?this.popupShow=!1:this.popupShow=!0}}}}).call(e,n("7t+N"))},xGkn:function(t,e,n){"use strict";var r=n("4mcu"),o=n("EGZi"),i=n("/bQp"),s=n("TcQ7");t.exports=n("vIB/")(Array,"Array",function(t,e){this._t=s(t),this._i=0,this._k=e},function(){var t=this._t,e=this._k,n=this._i++;return!t||n>=t.length?(this._t=void 0,o(1)):o(0,"keys"==e?n:"values"==e?t[n]:[n,t[n]])},"values"),i.Arguments=i.Array,r("keys"),r("values"),r("entries")},"xH/j":function(t,e,n){var r=n("hJx8");t.exports=function(t,e,n){for(var o in e)n&&t[o]?t[o]=e[o]:r(t,o,e[o]);return t}},xnc9:function(t,e){t.exports="constructor,hasOwnProperty,isPrototypeOf,propertyIsEnumerable,toLocaleString,toString,valueOf".split(",")},zQR9:function(t,e,n){"use strict";var r=n("h65t")(!0);n("vIB/")(String,"String",function(t){this._t=String(t),this._i=0},function(){var t,e=this._t,n=this._i;return n>=e.length?{value:void 0,done:!0}:(t=r(e,n),this._i+=t.length,{value:t,done:!1})})}});