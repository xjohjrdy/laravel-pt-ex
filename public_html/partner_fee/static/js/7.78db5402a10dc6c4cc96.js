webpackJsonp([7],{"3vSQ":function(t,a,s){"use strict";Object.defineProperty(a,"__esModule",{value:!0});var e=s("Xxa5"),i=s.n(e),n=s("exGp"),r=s.n(n),o=s("bOdI"),c=s.n(o),_=(s("eqfM"),s("/QYm")),u=s("mF0L"),l={name:"",data:function(){return{userInfo:"",userMoney:"0.00",showDisplay:""}},components:c()({},_.a.name,_.a),created:function(){},mounted:function(){this.userInfo=u.a.getAppUserInfo(),this.getOtherUserMoney(),this.getShowDisplay()},methods:{getOtherUserMoney:function(){var t=this;return r()(i.a.mark(function a(){var s,e,n;return i.a.wrap(function(a){for(;;)switch(a.prev=a.next){case 0:return(s=t).bus.$emit("loading",!0),e='{"app_id":"'+s.userInfo.id+'"}',a.next=5,s.ajaxGet("other_user_money",e,{});case 5:200==(n=a.sent).data.code?s.userMoney=n.data.data.money:Object(_.a)(n.data.msg),s.bus.$emit("loading",!1);case 8:case"end":return a.stop()}},a,t)}))()},getShowDisplay:function(){var t=this;return r()(i.a.mark(function a(){var s,e,n;return i.a.wrap(function(a){for(;;)switch(a.prev=a.next){case 0:return(s=t).bus.$emit("loading",!0),e='{"app_id":"'+s.userInfo.id+'"}',a.next=5,s.ajaxPost("mini_get_hot_shop_estimated_income",e,{});case 5:200==(n=a.sent).data.code?s.showDisplay=n.data.data:Object(_.a)(n.data.msg),s.bus.$emit("loading",!1);case 8:case"end":return a.stop()}},a,t)}))()},toHarryCall:function(){var t=this;return r()(i.a.mark(function a(){var s,e,n;return i.a.wrap(function(a){for(;;)switch(a.prev=a.next){case 0:return(s=t).bus.$emit("loading",!0),e='{"app_id":"'+s.userInfo.id+'"}',a.next=5,s.ajaxPost("out_harry_t_is_call",e,{});case 5:200==(n=a.sent).data.code?s.$router.push({name:"Payment"}):4410==n.data.code?s.$router.push({name:"AddInfo"}):Object(_.a)(n.data.msg),s.bus.$emit("loading",!1);case 8:case"end":return a.stop()}},a,t)}))()},toTip:function(){Object(_.a)({duration:1e3,message:"正在开发中..."})}}},d={render:function(){var t=this,a=t.$createElement,s=t._self._c||a;return s("div",{staticClass:"wrap"},[s("article",{staticClass:"main"},[s("div",{staticClass:"index_top"},[s("div",{staticClass:"img"},[s("img",{attrs:{src:t.userInfo.avatar?t.userInfo.avatar:"https://a119112.oss-cn-beijing.aliyuncs.com/bxl_item/vueImages/iconNullName.jpg"}})]),t._v(" "),s("div",{staticClass:"text"},[s("h3",[t._v(t._s(t.userInfo.user_name))]),t._v(" "),s("p",[t._v("ID："+t._s(t.userInfo.show_id))])])]),t._v(" "),s("div",{staticClass:"pay_box"},[s("div",{staticClass:"text"},[s("h3",[t._v(t._s(t.userMoney))]),t._v(" "),t._m(0)]),t._v(" "),s("div",{staticClass:"btn"},[s("a",{on:{click:t.toHarryCall}})])]),t._v(" "),s("div",{staticClass:"nav_box"},[t._m(1),t._v(" "),s("div",{staticClass:"list"},[s("router-link",{staticClass:"taobao",attrs:{to:{path:"TeamOrders",query:{urlType:"1",app_id:t.userInfo.id}}}},[t._v("淘宝")]),t._v(" "),s("router-link",{staticClass:"jingdong",attrs:{to:{path:"TeamOrders",query:{urlType:"2",app_id:t.userInfo.id}}}},[t._v("京东")]),t._v(" "),s("router-link",{staticClass:"pinduoduo",attrs:{to:{path:"TeamOrders",query:{urlType:"3",app_id:t.userInfo.id}}}},[t._v("拼多多")]),t._v(" "),s("router-link",{staticClass:"eleme",attrs:{to:{path:"TeamOrders",query:{urlType:"4",app_id:t.userInfo.id}}}},[t._v("饿了么")]),t._v(" "),s("router-link",{staticClass:"meituan",attrs:{to:{path:"TeamOrders",query:{urlType:"5",app_id:t.userInfo.id}}}},[t._v("美团")])],1)]),t._v(" "),s("div",{staticClass:"nav_box"},[t._m(2),t._v(" "),s("div",{staticClass:"list"},[s("a",{staticClass:"baobiao",on:{click:t.toTip}},[t._v("团队收益报表")]),t._v(" "),s("a",{staticClass:"quanzi",on:{click:t.toTip}},[t._v("我的圈子")]),t._v(" "),s("a",{staticClass:"toutiao",on:{click:t.toTip}},[t._v("我的头条")]),t._v(" "),s("a",{staticClass:"xinyongka",on:{click:t.toTip}},[t._v("办信用卡")])])]),t._v(" "),s("div",{staticClass:"nav_box",staticStyle:{"margin-bottom":".75rem"}},[t._m(3),t._v(" "),s("div",{staticClass:"shop_order_price"},[s("a",{staticClass:"quanzi"},[s("p",[t._v("今日预估收入(元)")]),t._v(" "),s("h3",[t._v(t._s(t.showDisplay.num_today_data?t.showDisplay.num_today_data:"0"))])]),t._v(" "),s("a",{staticClass:"quanzi"},[s("p",[t._v("今月预估收入(元)")]),t._v(" "),s("h3",[t._v(t._s(t.showDisplay.num_month_data?t.showDisplay.num_month_data:"0"))])]),t._v(" "),s("a",{staticClass:"quanzi"},[s("p",[t._v("累计预估收入(元)")]),t._v(" "),s("h3",[t._v(t._s(t.showDisplay.num_all_data?t.showDisplay.num_all_data:"0"))])])])])])])},staticRenderFns:[function(){var t=this.$createElement,a=this._self._c||t;return a("p",[this._v("管理费余额"),a("i",[this._v("(元)")])])},function(){var t=this.$createElement,a=this._self._c||t;return a("div",{staticClass:"title"},[a("span",[this._v("电商平台团队订单")]),a("em",[this._v("(京东、淘宝、拼多多等订单)")])])},function(){var t=this.$createElement,a=this._self._c||t;return a("div",{staticClass:"title"},[a("span",[this._v("其他团队订单")])])},function(){var t=this.$createElement,a=this._self._c||t;return a("div",{staticClass:"title"},[a("span",[this._v("爆款商城订单")])])}]};var v=s("VU/8")(l,d,!1,function(t){s("lC19")},"data-v-75dcd4c0",null);a.default=v.exports},lC19:function(t,a){}});