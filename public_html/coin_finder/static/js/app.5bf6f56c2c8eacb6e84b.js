webpackJsonp([9],{"02pT":function(e,t){},"1H7Z":function(e,t){},BwtF:function(e,t){},NHnr:function(e,t,n){"use strict";Object.defineProperty(t,"__esModule",{value:!0});n("eqfM");var i=n("/QYm"),a=n("7+uW"),o={render:function(){var e=this.$createElement;return(this._self._c||e)("div",{directives:[{name:"show",rawName:"v-show",value:this.loading,expression:"loading"}],staticClass:"loading"},[this._m(0)])},staticRenderFns:[function(){var e=this.$createElement,t=this._self._c||e;return t("div",{staticClass:"icon"},[t("div",{staticClass:"hourglass"})])}]},r={name:"App",components:{loading:n("VU/8")({name:"",data:function(){return{loading:!1}},components:{},created:function(){var e=this;this.bus.$on("loading",function(t){e.loading=!!t})},computed:{}},o,!1,null,null,null).exports},mounted:function(){}},c={render:function(){var e=this.$createElement,t=this._self._c||e;return t("div",{staticClass:"fullPosition",attrs:{id:"app","window.document.title":"to.meta.title"}},[t("transition",{attrs:{name:"router-fade",mode:"out-in"}},[t("keep-alive",[this.$route.meta.keepAlive?t("router-view"):this._e()],1)],1),this._v(" "),t("transition",{attrs:{name:"router-fade",mode:"out-in"}},[this.$route.meta.keepAlive?this._e():t("router-view")],1),this._v(" "),t("loading")],1)},staticRenderFns:[]};var l=n("VU/8")(r,c,!1,function(e){n("pXuj"),n("BwtF")},null,null).exports,u=n("/ocq");a.a.use(u.a);var s=new u.a({routes:[{path:"/",name:"Index",component:function(e){Promise.all([n.e(0),n.e(5)]).then(function(){var t=[n("3vSQ")];e.apply(null,t)}.bind(this)).catch(n.oe)},meta:{title:"幸运大转盘",keepAlive:!1}},{path:"/winrecord",name:"Winrecord",component:function(e){Promise.all([n.e(0),n.e(1)]).then(function(){var t=[n("gaNV")];e.apply(null,t)}.bind(this)).catch(n.oe)},meta:{title:"中奖记录",keepAlive:!1}},{path:"/exchange",name:"Exchange",component:function(e){Promise.all([n.e(0),n.e(4)]).then(function(){var t=[n("LgPH")];e.apply(null,t)}.bind(this)).catch(n.oe)},meta:{title:"兑换次数",keepAlive:!1}},{path:"/order",name:"Order",component:function(e){Promise.all([n.e(0),n.e(7)]).then(function(){var t=[n("s184")];e.apply(null,t)}.bind(this)).catch(n.oe)},meta:{title:"订单结算",keepAlive:!1}},{path:"/orderDetails",name:"OrderDetails",component:function(e){Promise.all([n.e(0),n.e(6)]).then(function(){var t=[n("zpIj")];e.apply(null,t)}.bind(this)).catch(n.oe)},meta:{title:"订单详情",keepAlive:!1}},{path:"/address",name:"Address",component:function(e){Promise.all([n.e(0),n.e(2)]).then(function(){var t=[n("uM3d")];e.apply(null,t)}.bind(this)).catch(n.oe)},meta:{title:"选择收货地址",keepAlive:!1}},{path:"/addsite",name:"Addsite",component:function(e){Promise.all([n.e(0),n.e(3)]).then(function(){var t=[n("96mQ")];e.apply(null,t)}.bind(this)).catch(n.oe)},meta:{title:"选择收货地址",keepAlive:!1}}]});s.afterEach(function(e){if(e.meta.title&&(document.title=e.meta.title,navigator.userAgent.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/))){var t=document.createElement("iframe");t.style.display="none",t.src="/static/html/fixIosTitle.html?r="+Math.random(),document.body.appendChild(t),setTimeout(function(e){document.body.removeChild(t)},300)}});var d=s,p=n("mtWM"),m=n.n(p),f=n("//Fk"),h=n.n(f),v="-----BEGIN PUBLIC KEY-----MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCEPRnL/rMAM45SQ4StNhUU81rsMZ13S2rZgh7d9/++UWzXjWM3IhaIa8GSDDfrXAnEjCUeRBMG5zu1yMfQauwAyGzO8dYJKQn8Y3DDRqiFQU7auJTiO9KLZbabyYnpNunGIUoTB7RDerZvcJ5+8HLHbruxGwC/ZLrWVN84TTribQIDAQAB-----END PUBLIC KEY-----",w="/api/",g=n("BUS2").sha256,b=void 0,A=void 0,y=void 0;function E(e,t,n){var i,a;return b=Math.round((new Date).getTime()/1e3).toString(),i=t,(a=new JSEncrypt).setPublicKey(v),A=a.encryptLong(i),y=g(""+w+e+b+A),"GET"==n?new h.a(function(t,n){m.a.get(""+w+e,{headers:{"Content-Type":"application/json","Accept-Sign":A,"Accept-Timestamp":b,"Accept-Token":y}}).then(function(e){t(e.data),525==e.data.code&&T()}).catch(function(e){n(e)})}):"POST"==n?new h.a(function(t,n){m.a.post(""+w+e,A,{headers:{"Content-Type":"application/json","Accept-Timestamp":b,"Accept-Token":y}}).then(function(e){t(e.data),525==e.data.code&&T()}).catch(function(e){n(e)})}):"DELETE"==n?new h.a(function(t,n){m.a.delete(""+w+e,{data:A,headers:{"Content-Type":"application/json","Accept-Timestamp":b,"Accept-Token":y}}).then(function(e){t(e.data),525==e.data.code&&T()}).catch(function(e){n(e)})}):void 0}function T(){Dialog.alert({title:"温馨提示",message:"请校正您的手机时间，校正方法：手机设置-日期与时间，开启“自动设置”"}).then(function(){})}var C=n("YqKu"),D=n.n(C),k=n("LJm1");n("QKTF");function B(e,t,n){if(window.bridge)window.bridge.callHandler(e,t,n);else{if(window.WebViewJavascriptBridge)return n(window.WebViewJavascriptBridge);if(window.WVJBCallbacks)return window.WVJBCallbacks.push(n);window.WVJBCallbacks=[n];var i=document.createElement("iframe");i.style.display="none",i.src="https://__BRIDGE_LOADED__",document.documentElement.appendChild(i),setTimeout(function(){document.documentElement.removeChild(i)},0)}}var P={callhandler:function(e,t,n){B(e,t,function(i){window.bridge||(window.bridge=i),window.bridge.callHandler(e,t,n)})},registerhandler:function(e,t){B(function(n){window.bridge||(window.bridge=n),window.bridge.registerHandler(e,function(e,n){t(e,n)})})}};a.a.prototype.ajaxGet=function(e,t){return E(e,t,"GET")},a.a.prototype.ajaxPost=function(e,t){return E(e,t,"POST")},a.a.prototype.ajaxDelete=function(e,t){return E(e,t,"DELETE")},a.a.prototype.$ajax=m.a,a.a.use(D.a),a.a.prototype.bus=new a.a,a.a.prototype.$jsEncrypt=k.JsEncrypt,a.a.prototype.$bridge=P,a.a.config.productionTip=!1,i.a.setDefaultOptions({forbidClick:!0}),a.a.filter("addZero",function(e){return isNaN(e)&&(e=0),e?parseFloat(e).toFixed(2):"0.00"}),new a.a({el:"#app",router:d,components:{App:l},template:"<App/>"})},T2s0:function(e,t){},WpgC:function(e,t){},eh36:function(e,t){},hW8u:function(e,t){},nsZj:function(e,t){},pXuj:function(e,t){}},["NHnr"]);