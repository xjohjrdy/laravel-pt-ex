webpackJsonp([2],{B7dO:function(t,e){},NHnr:function(t,e,n){"use strict";Object.defineProperty(e,"__esModule",{value:!0});var o=n("7+uW"),a={render:function(){var t=this.$createElement,e=this._self._c||t;return e("div",{staticClass:"fullPosition",attrs:{id:"app"}},[e("transition",{attrs:{name:"router-fade",mode:"out-in"}},[e("router-view")],1)],1)},staticRenderFns:[]};var r=n("VU/8")({name:"App",mounted:function(){}},a,!1,function(t){n("B7dO"),n("w15f")},null,null).exports,i=n("/ocq");o.a.use(i.a);var u=new i.a({routes:[{path:"/",name:"articleShare",meta:{},component:function(t){n.e(0).then(function(){var e=[n("xLfl")];t.apply(null,e)}.bind(this)).catch(n.oe)}}]}),c=(n("UAgs"),n("mtWM")),p=n.n(c),d=n("YqKu"),s=n.n(d);o.a.prototype.get=function(t,e){return p()({method:"get",url:"/"+t,params:e})},o.a.prototype.post=function(t,e){return p()({method:"post",url:"/"+t,data:e,transformRequest:[function(t){var e="";for(var n in t)e+=encodeURIComponent(n)+"="+encodeURIComponent(t[n])+"&";return e}],headers:{"Content-Type":"application/x-www-form-urlencoded"}})},o.a.prototype.$ajax=p.a,o.a.config.productionTip=!1,o.a.use(s.a),u.beforeEach(function(t,e,n){t.meta.title&&(document.title=t.meta.title),n()}),new o.a({el:"#app",router:u,components:{App:r},template:"<App/>"})},UAgs:function(t,e){var n,o,a,r,i;n=document,o=window,a=n.documentElement,r="orientationchange"in window?"orientationchange":"resize",i=function(){var t=a.clientWidth;t&&(a.style.fontSize=t>=1080?"144px":t/750*100+"px")},n.addEventListener&&(o.addEventListener(r,i,!1),n.addEventListener("DOMContentLoaded",i,!1))},w15f:function(t,e){}},["NHnr"]);