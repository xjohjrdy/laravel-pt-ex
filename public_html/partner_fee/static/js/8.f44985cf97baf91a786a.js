webpackJsonp([8],{"3vSQ":function(t,a,e){"use strict";Object.defineProperty(a,"__esModule",{value:!0});var s=e("Xxa5"),r=e.n(s),i=e("exGp"),n=e.n(i),o=e("bOdI"),u=e.n(o),c=(e("eqfM"),e("/QYm")),d=e("mF0L"),_={name:"",data:function(){return{userInfo:"",userMoney:"0.00",showDisplay:""}},components:u()({},c.a.name,c.a),created:function(){},mounted:function(){this.getUserInfo()},methods:{getUserInfo:function(){var t=this;return n()(r.a.mark(function a(){var e;return r.a.wrap(function(a){for(;;)switch(a.prev=a.next){case 0:e=t,d.a.isAndroid()?(e.userInfo=JSON.parse(window.jsbridge.getUserInfo()),e.getOtherUserMoney(),e.getShowDisplay()):d.a.isIos()&&e.$bridge.callhandler("getUserInfo",{},function(t){e.userInfo=JSON.parse(t),e.getOtherUserMoney(),e.getShowDisplay()});case 2:case"end":return a.stop()}},a,t)}))()},getOtherUserMoney:function(){var t=this;return n()(r.a.mark(function a(){var e,s,i;return r.a.wrap(function(a){for(;;)switch(a.prev=a.next){case 0:return(e=t).bus.$emit("loading",!0),s='{"app_id":"'+e.userInfo.id+'"}',a.next=5,e.ajaxGet("other_user_money",s,{});case 5:200==(i=a.sent).data.code?e.userMoney=i.data.data.money:Object(c.a)(i.data.msg),e.bus.$emit("loading",!1);case 8:case"end":return a.stop()}},a,t)}))()},getShowDisplay:function(){var t=this;return n()(r.a.mark(function a(){var e,s,i;return r.a.wrap(function(a){for(;;)switch(a.prev=a.next){case 0:return(e=t).bus.$emit("loading",!0),s='{"app_id":"'+e.userInfo.id+'"}',a.next=5,e.ajaxPost("mini_get_hot_shop_estimated_income",s,{});case 5:200==(i=a.sent).data.code?e.showDisplay=i.data.data:Object(c.a)(i.data.msg),e.bus.$emit("loading",!1);case 8:case"end":return a.stop()}},a,t)}))()},toHarryCall:function(){var t=this;return n()(r.a.mark(function a(){var e,s,i;return r.a.wrap(function(a){for(;;)switch(a.prev=a.next){case 0:return(e=t).bus.$emit("loading",!0),s='{"app_id":"'+e.userInfo.id+'"}',a.next=5,e.ajaxPost("out_harry_t_is_call",s,{});case 5:200==(i=a.sent).data.code?e.$router.push({path:"Payment"}):4410==i.data.code?e.$router.push({path:"AddInfo",query:{app_id:e.userInfo.id}}):4411==i.data.code?(Object(c.a)(i.data.msg),setTimeout(function(){e.$router.push({path:"AddInfo",query:{app_id:e.userInfo.id}})},1500)):Object(c.a)(i.data.msg),e.bus.$emit("loading",!1);case 8:case"end":return a.stop()}},a,t)}))()},toTip:function(){Object(c.a)({duration:1e3,message:"正在开发中..."})}}},l={render:function(){var t=this,a=t.$createElement,e=t._self._c||a;return e("div",{staticClass:"wrap"},[e("article",{staticClass:"main"},[e("div",{staticClass:"index_top"},[e("div",{staticClass:"img"},[e("img",{attrs:{src:t.userInfo.avatar?t.userInfo.avatar:"https://a119112.oss-cn-beijing.aliyuncs.com/bxl_item/vueImages/iconNullName.png"}})]),t._v(" "),e("div",{staticClass:"text"},[e("h3",[t._v(t._s(t.userInfo.user_name))]),t._v(" "),e("p",[t._v("ID："+t._s(t.userInfo.show_id))])])]),t._v(" "),e("div",{staticClass:"pay_box"},[e("div",{staticClass:"text"},[e("h3",[t._v(t._s(t._f("addZero")(t.userMoney)))]),t._v(" "),t._m(0)]),t._v(" "),e("div",{staticClass:"btn"},[e("a",{on:{click:t.toHarryCall}})])]),t._v(" "),e("div",{staticClass:"nav_box"},[t._m(1),t._v(" "),e("div",{staticClass:"list"},[e("router-link",{staticClass:"taobao",attrs:{to:{path:"TeamOrders",query:{rewardType:"0",urlType:"1",app_id:t.userInfo.id}}}},[t._v("淘宝")]),t._v(" "),e("router-link",{staticClass:"jingdong",attrs:{to:{path:"TeamOrders",query:{rewardType:"0",urlType:"2",app_id:t.userInfo.id}}}},[t._v("京东")]),t._v(" "),e("router-link",{staticClass:"pinduoduo",attrs:{to:{path:"TeamOrders",query:{rewardType:"0",urlType:"3",app_id:t.userInfo.id}}}},[t._v("拼多多")]),t._v(" "),e("router-link",{staticClass:"eleme",attrs:{to:{path:"TeamOrders",query:{rewardType:"0",urlType:"4",app_id:t.userInfo.id}}}},[t._v("饿了么")]),t._v(" "),e("router-link",{staticClass:"meituan",attrs:{to:{path:"TeamOrders",query:{rewardType:"0",urlType:"5",app_id:t.userInfo.id}}}},[t._v("美团")])],1)]),t._v(" "),e("div",{staticClass:"nav_box"},[t._m(2),t._v(" "),e("div",{staticClass:"list"},[e("router-link",{staticClass:"baobiao",attrs:{to:{path:"ManageExpenseRecord",query:{app_id:t.userInfo.id}}}},[t._v("收益报表")]),t._v(" "),e("a",{staticClass:"quanzi",on:{click:t.toTip}},[t._v("我的圈子")]),t._v(" "),e("a",{staticClass:"toutiao",on:{click:t.toTip}},[t._v("我的头条")]),t._v(" "),e("a",{staticClass:"xinyongka",on:{click:t.toTip}},[t._v("办信用卡")])],1)]),t._v(" "),e("div",{staticClass:"nav_box"},[t._m(3),t._v(" "),e("div",{staticClass:"list"},[e("router-link",{staticClass:"reward",attrs:{to:{path:"RewardIndex",query:{app_id:t.userInfo.id}}}},[t._v("公司奖励")])],1)]),t._v(" "),e("div",{staticClass:"nav_box",staticStyle:{"margin-bottom":".75rem"}},[t._m(4),t._v(" "),e("div",{staticClass:"shop_order_price"},[e("a",{staticClass:"quanzi"},[e("p",[t._v("今日预估收入(元)")]),t._v(" "),e("h3",[t._v(t._s(t._f("addZero")(t.showDisplay.num_today_data)))])]),t._v(" "),e("a",{staticClass:"quanzi"},[e("p",[t._v("今月预估收入(元)")]),t._v(" "),e("h3",[t._v(t._s(t._f("addZero")(t.showDisplay.num_month_data)))])]),t._v(" "),e("a",{staticClass:"quanzi"},[e("p",[t._v("累计预估收入(元)")]),t._v(" "),e("h3",[t._v(t._s(t._f("addZero")(t.showDisplay.num_all_data)))])])])])])])},staticRenderFns:[function(){var t=this.$createElement,a=this._self._c||t;return a("p",[this._v("奖励余额"),a("i",[this._v("(元)")])])},function(){var t=this.$createElement,a=this._self._c||t;return a("div",{staticClass:"title"},[a("span",[this._v("电商平台订单")]),a("em",[this._v("(京东、淘宝、拼多多等订单)")])])},function(){var t=this.$createElement,a=this._self._c||t;return a("div",{staticClass:"title"},[a("span",[this._v("其他订单")])])},function(){var t=this.$createElement,a=this._self._c||t;return a("div",{staticClass:"title"},[a("span",[this._v("公司奖励")])])},function(){var t=this.$createElement,a=this._self._c||t;return a("div",{staticClass:"title"},[a("span",[this._v("爆款商城订单")])])}]};var v=e("VU/8")(_,l,!1,function(t){e("Zm6X")},"data-v-1eee3710",null);a.default=v.exports},Zm6X:function(t,a){}});