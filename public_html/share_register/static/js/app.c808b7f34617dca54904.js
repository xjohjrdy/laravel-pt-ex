webpackJsonp([2],{"02pT":function(e,t){},"1H7Z":function(e,t){},BwtF:function(e,t){},NHnr:function(e,t,n){"use strict";Object.defineProperty(t,"__esModule",{value:!0});n("eqfM");var i=n("/QYm"),a=n("7+uW"),o={render:function(){var e=this.$createElement;return(this._self._c||e)("div",{directives:[{name:"show",rawName:"v-show",value:this.loading,expression:"loading"}],staticClass:"loading"},[this._m(0)])},staticRenderFns:[function(){var e=this.$createElement,t=this._self._c||e;return t("div",{staticClass:"icon"},[t("div",{staticClass:"hourglass"})])}]},r={name:"App",components:{loading:n("VU/8")({name:"",data:function(){return{loading:!1}},components:{},created:function(){var e=this;this.bus.$on("loading",function(t){e.loading=!!t})},computed:{}},o,!1,null,null,null).exports},mounted:function(){}},s={render:function(){var e=this.$createElement,t=this._self._c||e;return t("div",{staticClass:"fullPosition",attrs:{id:"app","window.document.title":"to.meta.title"}},[t("transition",{attrs:{name:"router-fade",mode:"out-in"}},[t("keep-alive",[this.$route.meta.keepAlive?t("router-view"):this._e()],1)],1),this._v(" "),t("transition",{attrs:{name:"router-fade",mode:"out-in"}},[this.$route.meta.keepAlive?this._e():t("router-view")],1),this._v(" "),t("loading")],1)},staticRenderFns:[]};var u=n("VU/8")(r,s,!1,function(e){n("pXuj"),n("BwtF")},null,null).exports,c=n("/ocq");a.a.use(c.a);var d=new c.a({routes:[{path:"/",name:"Index",component:function(e){n.e(0).then(function(){var t=[n("3vSQ")];e.apply(null,t)}.bind(this)).catch(n.oe)},meta:{title:"立即注册",keepAlive:!1}}]}),l=n("mtWM"),p=n.n(l),f="-----BEGIN PUBLIC KEY-----MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCEPRnL/rMAM45SQ4StNhUU81rsMZ13S2rZgh7d9/++UWzXjWM3IhaIa8GSDDfrXAnEjCUeRBMG5zu1yMfQauwAyGzO8dYJKQn8Y3DDRqiFQU7auJTiO9KLZbabyYnpNunGIUoTB7RDerZvcJ5+8HLHbruxGwC/ZLrWVN84TTribQIDAQAB-----END PUBLIC KEY-----",m=n("BUS2").sha256,w="";function h(e){var t=new JSEncrypt;return t.setPublicKey(f),t.encryptLong(e)}var v=n("YqKu"),g=n.n(v),b=n("LJm1"),A=n.n(b);n("QKTF");function C(e,t,n){if(window.bridge)window.bridge.callHandler(e,t,n);else{if(window.WebViewJavascriptBridge)return n(window.WebViewJavascriptBridge);if(window.WVJBCallbacks)return window.WVJBCallbacks.push(n);window.WVJBCallbacks=[n];var i=document.createElement("iframe");i.style.display="none",i.src="https://__BRIDGE_LOADED__",document.documentElement.appendChild(i),setTimeout(function(){document.documentElement.removeChild(i)},0)}}var y={callhandler:function(e,t,n){C(e,t,function(i){window.bridge||(window.bridge=i),window.bridge.callHandler(e,t,n)})},registerhandler:function(e,t){C(function(n){window.bridge||(window.bridge=n),window.bridge.registerHandler(e,function(e,n){t(e,n)})})}};a.a.prototype.ajaxPost=function(e,t){var n=h(t);w=Math.round((new Date).getTime()/1e3).toString();var i=m("/api/"+e+w+n);return p()({method:"post",url:"/api/"+e,data:n,headers:{"Content-Type":"application/json","Accept-Timestamp":w,"Accept-Token":i}})},a.a.prototype.ajaxGet=function(e,t){var n=h(t);w=Math.round((new Date).getTime()/1e3).toString();var i=m("/api/"+e+w+n);return p()({method:"get",url:"/api/"+e,headers:{"Content-Type":"application/json","Accept-Sign":n,"Accept-Timestamp":w,"Accept-Token":i}})},a.a.prototype.$ajax=p.a,a.a.use(g.a),a.a.prototype.bus=new a.a,a.a.prototype.$jsEncrypt=A.a,a.a.prototype.$bridge=y,a.a.config.productionTip=!1,a.a.use(i.a),i.a.setDefaultOptions({forbidClick:!0}),new a.a({el:"#app",router:d,components:{App:u},template:"<App/>"})},T2s0:function(e,t){},WpgC:function(e,t){},eh36:function(e,t){},hW8u:function(e,t){},nsZj:function(e,t){},pXuj:function(e,t){}},["NHnr"]);