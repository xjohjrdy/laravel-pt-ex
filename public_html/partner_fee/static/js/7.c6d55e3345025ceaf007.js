webpackJsonp([7],{"3vSQ":function(t,a,e){"use strict";Object.defineProperty(a,"__esModule",{value:!0});var s=e("mvHQ"),i=e.n(s),n=e("Xxa5"),r=e.n(n),o=e("exGp"),u=e.n(o),c=e("bOdI"),_=e.n(c),d=(e("eqfM"),e("/QYm")),l=e("mF0L"),p={name:"",data:function(){return{userInfo:"",userMoney:"0.00",showDisplay:"",tipType:!0}},components:_()({},d.a.name,d.a),created:function(){},mounted:function(){this.getUserInfo(),this.tipType=localStorage.getItem("tipType")},methods:{getUserInfo:function(){var t=this;return u()(r.a.mark(function a(){var e;return r.a.wrap(function(a){for(;;)switch(a.prev=a.next){case 0:e=t,l.a.isAndroid()?(e.userInfo=JSON.parse(window.jsbridge.getUserInfo()),e.getOtherUserMoney(),e.getShowDisplay()):l.a.isIos()&&e.$bridge.callhandler("getUserInfo",{},function(t){e.userInfo=JSON.parse(t),e.getOtherUserMoney(),e.getShowDisplay()});case 2:case"end":return a.stop()}},a,t)}))()},getOtherUserMoney:function(){var t=this;return u()(r.a.mark(function a(){var e,s,i;return r.a.wrap(function(a){for(;;)switch(a.prev=a.next){case 0:return(e=t).bus.$emit("loading",!0),s='{"app_id":"'+e.userInfo.id+'"}',a.next=5,e.ajaxGet("other_user_money",s,{});case 5:200==(i=a.sent).data.code?e.userMoney=i.data.data.money:Object(d.a)(i.data.msg),e.bus.$emit("loading",!1);case 8:case"end":return a.stop()}},a,t)}))()},getShowDisplay:function(){var t=this;return u()(r.a.mark(function a(){var e,s,i;return r.a.wrap(function(a){for(;;)switch(a.prev=a.next){case 0:return(e=t).bus.$emit("loading",!0),s='{"app_id":"'+e.userInfo.id+'"}',a.next=5,e.ajaxPost("mini_get_hot_shop_estimated_income",s,{});case 5:200==(i=a.sent).data.code?e.showDisplay=i.data.data:Object(d.a)(i.data.msg),e.bus.$emit("loading",!1);case 8:case"end":return a.stop()}},a,t)}))()},toHarryCall:function(){var t=this;return u()(r.a.mark(function a(){var e,s,n;return r.a.wrap(function(a){for(;;)switch(a.prev=a.next){case 0:return(e=t).bus.$emit("loading",!0),s='{"app_id":"'+e.userInfo.id+'"}',a.next=5,e.ajaxPost("out_harry_t_is_call",s,{});case 5:200==(n=a.sent).data.code?e.$router.push({name:"Payment",query:{userInfo:i()(e.userInfo)}}):4410==n.data.code?e.$router.push({name:"AddInfo",query:{app_id:e.userInfo.id}}):4411==n.data.code?(Object(d.a)(n.data.msg),setTimeout(function(){e.$router.push({name:"AddInfo",query:{app_id:e.userInfo.id}})},1500)):Object(d.a)(n.data.msg),e.bus.$emit("loading",!1);case 8:case"end":return a.stop()}},a,t)}))()},toTip:function(){Object(d.a)({duration:1e3,message:"正在开发中..."})}}},v={render:function(){var t=this,a=t.$createElement,e=t._self._c||a;return e("div",{staticClass:"wrap"},[e("article",{staticClass:"main"},[e("div",{staticClass:"index_top"},[e("div",{staticClass:"img"},[e("img",{attrs:{src:t.userInfo.avatar?t.userInfo.avatar:"https://a119112.oss-cn-beijing.aliyuncs.com/bxl_item/vueImages/iconNullName.jpg"}})]),t._v(" "),e("div",{staticClass:"text"},[e("h3",[t._v(t._s(t.userInfo.user_name))]),t._v(" "),e("p",[t._v("ID："+t._s(t.userInfo.show_id))])])]),t._v(" "),e("div",{staticClass:"pay_box"},[e("div",{staticClass:"text"},[e("h3",[t._v(t._s(t.userMoney))]),t._v(" "),t._m(0)]),t._v(" "),e("div",{staticClass:"btn"},[e("a",{on:{click:t.toHarryCall}})])]),t._v(" "),e("div",{staticClass:"nav_box"},[t._m(1),t._v(" "),e("div",{staticClass:"list"},[e("router-link",{staticClass:"taobao",attrs:{to:{path:"TeamOrders",query:{urlType:"1",app_id:t.userInfo.id}}}},[t._v("淘宝")]),t._v(" "),e("router-link",{staticClass:"jingdong",attrs:{to:{path:"TeamOrders",query:{urlType:"2",app_id:t.userInfo.id}}}},[t._v("京东")]),t._v(" "),e("router-link",{staticClass:"pinduoduo",attrs:{to:{path:"TeamOrders",query:{urlType:"3",app_id:t.userInfo.id}}}},[t._v("拼多多")]),t._v(" "),e("router-link",{staticClass:"eleme",attrs:{to:{path:"TeamOrders",query:{urlType:"4",app_id:t.userInfo.id}}}},[t._v("饿了么")]),t._v(" "),e("router-link",{staticClass:"meituan",attrs:{to:{path:"TeamOrders",query:{urlType:"5",app_id:t.userInfo.id}}}},[t._v("美团")])],1)]),t._v(" "),e("div",{staticClass:"nav_box"},[t._m(2),t._v(" "),e("div",{staticClass:"list"},[e("router-link",{staticClass:"baobiao",class:t.tipType?"":"box",attrs:{to:{path:"ManageExpenseRecord",query:{app_id:t.userInfo.id}}}},[t._v("团队收益报表")]),t._v(" "),e("a",{staticClass:"quanzi",on:{click:t.toTip}},[t._v("葡萄圈子")]),t._v(" "),e("a",{staticClass:"toutiao",on:{click:t.toTip}},[t._v("葡萄头条")]),t._v(" "),e("a",{staticClass:"xinyongka",on:{click:t.toTip}},[t._v("办信用卡")])],1)]),t._v(" "),e("div",{staticClass:"nav_box",staticStyle:{"margin-bottom":".75rem"}},[t._m(3),t._v(" "),e("div",{staticClass:"shop_order_price"},[e("a",{staticClass:"quanzi"},[e("p",[t._v("今日预估收入(元)")]),t._v(" "),e("h3",[t._v(t._s(t.showDisplay.num_today_data))])]),t._v(" "),e("a",{staticClass:"quanzi"},[e("p",[t._v("今月预估收入(元)")]),t._v(" "),e("h3",[t._v(t._s(t.showDisplay.num_month_data))])]),t._v(" "),e("a",{staticClass:"quanzi"},[e("p",[t._v("累计预估收入(元)")]),t._v(" "),e("h3",[t._v(t._s(t.showDisplay.num_all_data))])])])])])])},staticRenderFns:[function(){var t=this.$createElement,a=this._self._c||t;return a("p",[this._v("管理费余额"),a("i",[this._v("(元)")])])},function(){var t=this.$createElement,a=this._self._c||t;return a("div",{staticClass:"title"},[a("span",[this._v("电商平台团队订单")]),a("em",[this._v("(京东、淘宝、拼多多等订单)")])])},function(){var t=this.$createElement,a=this._self._c||t;return a("div",{staticClass:"title"},[a("span",[this._v("其他团队订单")])])},function(){var t=this.$createElement,a=this._self._c||t;return a("div",{staticClass:"title"},[a("span",[this._v("爆款商城订单")])])}]};var f=e("VU/8")(p,v,!1,function(t){e("JO38")},"data-v-3b1edf09",null);a.default=f.exports},JO38:function(t,a){},mvHQ:function(t,a,e){t.exports={default:e("qkKv"),__esModule:!0}},qkKv:function(t,a,e){var s=e("FeBl"),i=s.JSON||(s.JSON={stringify:JSON.stringify});t.exports=function(t){return i.stringify.apply(i,arguments)}}});