webpackJsonp([1],{"+YKu":function(t,e){},DGjh:function(t,e,n){t.exports=n.p+"static/img/rule_body1.89947c2.png"},L6mN:function(t,e){},VEnX:function(t,e){},VJlW:function(t,e,n){t.exports=n.p+"static/img/home_banner.d1738e2.png"},eOhD:function(t,e,n){"use strict";Object.defineProperty(e,"__esModule",{value:!0});var s=n("3cXf"),i=n.n(s),r=n("84iU"),a=n.n(r),c={render:function(){this.$createElement;this._self._c;return this._m(0)},staticRenderFns:[function(){var t=this.$createElement,e=this._self._c||t;return e("div",{staticClass:"rule_banner"},[e("img",{staticClass:"hm_w_100",attrs:{src:n("VJlW")}})])}]};var o={render:function(){this.$createElement;this._self._c;return this._m(0)},staticRenderFns:[function(){var t=this.$createElement,e=this._self._c||t;return e("div",{staticClass:"rule_body"},[e("img",{staticClass:"hm_w_100",attrs:{src:n("DGjh")}}),this._v(" "),e("img",{staticClass:"hm_w_100",attrs:{src:n("wfW1")}}),this._v(" "),e("img",{staticClass:"hm_w_100",attrs:{src:n("hOPE")}})])}]};var l={name:"Rule",components:{RuleHeader:n("C7Lr")({name:"RuleHeader"},c,!1,function(t){n("VEnX")},null,null).exports,RuleBody:n("C7Lr")({name:"RuleBody"},o,!1,function(t){n("L6mN")},null,null).exports},data:function(){return{}},methods:{getDate:function(){a.a.post("/api/new_jd_get_change_url",{data:i()({app_id:localStorage.getItem("app_id"),goods_id:"123"})}).then(this.getInfo)},getInfo:function(t){}},mounted:function(){console.log("app_id："+localStorage.getItem("app_id"))}},u={render:function(){var t=this.$createElement,e=this._self._c||t;return e("div",[e("rule-header"),this._v(" "),e("rule-body")],1)},staticRenderFns:[]};var _=n("C7Lr")(l,u,!1,function(t){n("+YKu")},null,null);e.default=_.exports},hOPE:function(t,e,n){t.exports=n.p+"static/img/rule_body3.8392175.png"},wfW1:function(t,e,n){t.exports=n.p+"static/img/rule_body2.95b7898.png"}});