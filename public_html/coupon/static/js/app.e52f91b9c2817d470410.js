webpackJsonp([8],{"02pT":function(e,t){},"1H7Z":function(e,t){},BwtF:function(e,t){},NHnr:function(e,t,n){"use strict";Object.defineProperty(t,"__esModule",{value:!0});n("eqfM");var i=n("/QYm"),a=n("7+uW"),o={render:function(){var e=this.$createElement;return(this._self._c||e)("div",{directives:[{name:"show",rawName:"v-show",value:this.loading,expression:"loading"}],staticClass:"loading"},[this._m(0)])},staticRenderFns:[function(){var e=this.$createElement,t=this._self._c||e;return t("div",{staticClass:"icon"},[t("div",{staticClass:"hourglass"})])}]},r={name:"App",components:{loading:n("VU/8")({name:"",data:function(){return{loading:!1}},components:{},created:function(){var e=this;this.bus.$on("loading",function(t){e.loading=!!t})},computed:{}},o,!1,null,null,null).exports},mounted:function(){}},c={render:function(){var e=this.$createElement,t=this._self._c||e;return t("div",{staticClass:"fullPosition",attrs:{id:"app","window.document.title":"to.meta.title"}},[t("transition",{attrs:{name:"router-fade",mode:"out-in"}},[t("keep-alive",[this.$route.meta.keepAlive?t("router-view"):this._e()],1)],1),this._v(" "),t("transition",{attrs:{name:"router-fade",mode:"out-in"}},[this.$route.meta.keepAlive?this._e():t("router-view")],1),this._v(" "),t("loading")],1)},staticRenderFns:[]};var l=n("VU/8")(r,c,!1,function(e){n("pXuj"),n("BwtF")},null,null).exports,u=n("/ocq");a.a.use(u.a);var s=new u.a({routes:[{path:"/",name:"Index",component:function(e){Promise.all([n.e(0),n.e(4)]).then(function(){var t=[n("3vSQ")];e.apply(null,t)}.bind(this)).catch(n.oe)},meta:{title:"生活优惠券",keepAlive:!1}},{path:"/goodsDetail",name:"GoodsDetail",component:function(e){Promise.all([n.e(0),n.e(3)]).then(function(){var t=[n("PGkQ")];e.apply(null,t)}.bind(this)).catch(n.oe)},meta:{title:"生活优惠券",keepAlive:!1}},{path:"/order",name:"Order",component:function(e){Promise.all([n.e(0),n.e(1)]).then(function(){var t=[n("s184")];e.apply(null,t)}.bind(this)).catch(n.oe)},meta:{title:"我的卡券",keepAlive:!1}},{path:"/orderDetail",name:"OrderDetail",component:function(e){Promise.all([n.e(0),n.e(2)]).then(function(){var t=[n("3FdM")];e.apply(null,t)}.bind(this)).catch(n.oe)},meta:{title:"我的卡券",keepAlive:!1}},{path:"/extract",name:"Extract",component:function(e){Promise.all([n.e(0),n.e(5)]).then(function(){var t=[n("BPVH")];e.apply(null,t)}.bind(this)).catch(n.oe)},meta:{title:"提取卡密",keepAlive:!1}},{path:"/result",name:"Result",component:function(e){n.e(6).then(function(){var t=[n("y+mE")];e.apply(null,t)}.bind(this)).catch(n.oe)},meta:{title:"支付结果",keepAlive:!1}}]});s.afterEach(function(e){if(e.meta.title&&(document.title=e.meta.title,navigator.userAgent.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/))){var t=document.createElement("iframe");t.style.display="none",t.src="/static/html/fixIosTitle.html?r="+Math.random(),document.body.appendChild(t),setTimeout(function(e){document.body.removeChild(t)},300)}});var d=s,p=n("mtWM"),m=n.n(p),h="-----BEGIN PUBLIC KEY-----MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCEPRnL/rMAM45SQ4StNhUU81rsMZ13S2rZgh7d9/++UWzXjWM3IhaIa8GSDDfrXAnEjCUeRBMG5zu1yMfQauwAyGzO8dYJKQn8Y3DDRqiFQU7auJTiO9KLZbabyYnpNunGIUoTB7RDerZvcJ5+8HLHbruxGwC/ZLrWVN84TTribQIDAQAB-----END PUBLIC KEY-----",f=n("BUS2").sha256,v="";function w(e){var t=new JSEncrypt;return t.setPublicKey(h),t.encryptLong(e)}var g=n("LJm1"),b=n.n(g);n("QKTF");function y(e,t,n){if(window.bridge)window.bridge.callHandler(e,t,n);else{if(window.WebViewJavascriptBridge)return n(window.WebViewJavascriptBridge);if(window.WVJBCallbacks)return window.WVJBCallbacks.push(n);window.WVJBCallbacks=[n];var i=document.createElement("iframe");i.style.display="none",i.src="https://__BRIDGE_LOADED__",document.documentElement.appendChild(i),setTimeout(function(){document.documentElement.removeChild(i)},0)}}var A={callhandler:function(e,t,n){y(e,t,function(i){window.bridge||(window.bridge=i),window.bridge.callHandler(e,t,n)})},registerhandler:function(e,t){y(function(n){window.bridge||(window.bridge=n),window.bridge.registerHandler(e,function(e,n){t(e,n)})})}};a.a.prototype.ajaxGet=function(e,t){var n=w(t);v=Math.round((new Date).getTime()/1e3).toString();var i=f("/api/"+e+v+n);return m()({method:"get",url:"/api/"+e,headers:{"Content-Type":"application/json","Accept-Sign":n,"Accept-Timestamp":v,"Accept-Token":i}})},a.a.prototype.ajaxPost=function(e,t){var n=w(t);v=Math.round((new Date).getTime()/1e3).toString();var i=f("/api/"+e+v+n);return m()({method:"post",url:"/api/"+e,data:n,headers:{"Content-Type":"application/json","Accept-Timestamp":v,"Accept-Token":i}})},a.a.prototype.$ajax=m.a,a.a.prototype.bus=new a.a,a.a.prototype.$jsEncrypt=b.a,a.a.prototype.$bridge=A,a.a.config.productionTip=!1,a.a.use(i.a),i.a.setDefaultOptions({forbidClick:!0}),a.a.filter("addZero",function(e){return isNaN(e)&&(e=0),e?parseFloat(e).toFixed(2):"0.00"}),new a.a({el:"#app",router:d,components:{App:l},template:"<App/>"})},T2s0:function(e,t){},WpgC:function(e,t){},eh36:function(e,t){},hW8u:function(e,t){},nsZj:function(e,t){},pXuj:function(e,t){}},["NHnr"]);