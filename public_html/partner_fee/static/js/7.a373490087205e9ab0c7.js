webpackJsonp([7],{"3vSQ":function(t,a,s){"use strict";Object.defineProperty(a,"__esModule",{value:!0});var e=s("mvHQ"),i=s.n(e),n=s("Xxa5"),r=s.n(n),o=s("exGp"),u=s.n(o),c=s("bOdI"),_=s.n(c),d=(s("eqfM"),s("/QYm")),l=s("mF0L"),v={name:"",data:function(){return{userInfo:"",userMoney:"0.00",showDisplay:""}},components:_()({},d.a.name,d.a),created:function(){},mounted:function(){this.getUserInfo()},methods:{getUserInfo:function(){var t=this;return u()(r.a.mark(function a(){var s;return r.a.wrap(function(a){for(;;)switch(a.prev=a.next){case 0:s=t,l.a.isAndroid()?(s.userInfo=JSON.parse(window.jsbridge.getUserInfo()),s.getOtherUserMoney(),s.getShowDisplay()):l.a.isIos()&&s.$bridge.callhandler("getUserInfo",{},function(t){s.userInfo=JSON.parse(t),s.getOtherUserMoney(),s.getShowDisplay()});case 2:case"end":return a.stop()}},a,t)}))()},getOtherUserMoney:function(){var t=this;return u()(r.a.mark(function a(){var s,e,i;return r.a.wrap(function(a){for(;;)switch(a.prev=a.next){case 0:return(s=t).bus.$emit("loading",!0),e='{"app_id":"'+s.userInfo.id+'"}',a.next=5,s.ajaxGet("other_user_money",e,{});case 5:200==(i=a.sent).data.code?s.userMoney=i.data.data.money:Object(d.a)(i.data.msg),s.bus.$emit("loading",!1);case 8:case"end":return a.stop()}},a,t)}))()},getShowDisplay:function(){var t=this;return u()(r.a.mark(function a(){var s,e,i;return r.a.wrap(function(a){for(;;)switch(a.prev=a.next){case 0:return(s=t).bus.$emit("loading",!0),e='{"app_id":"'+s.userInfo.id+'"}',a.next=5,s.ajaxPost("mini_get_hot_shop_estimated_income",e,{});case 5:200==(i=a.sent).data.code?s.showDisplay=i.data.data:Object(d.a)(i.data.msg),s.bus.$emit("loading",!1);case 8:case"end":return a.stop()}},a,t)}))()},toHarryCall:function(){var t=this;return u()(r.a.mark(function a(){var s,e,n;return r.a.wrap(function(a){for(;;)switch(a.prev=a.next){case 0:return(s=t).bus.$emit("loading",!0),e='{"app_id":"'+s.userInfo.id+'"}',a.next=5,s.ajaxPost("out_harry_t_is_call",e,{});case 5:200==(n=a.sent).data.code?s.$router.push({name:"Payment",query:{userInfo:i()(s.userInfo)}}):4410==n.data.code?s.$router.push({name:"AddInfo",query:{app_id:s.userInfo.id}}):4411==n.data.code?(Object(d.a)(n.data.msg),setTimeout(function(){s.$router.push({name:"AddInfo",query:{app_id:s.userInfo.id}})},1500)):Object(d.a)(n.data.msg),s.bus.$emit("loading",!1);case 8:case"end":return a.stop()}},a,t)}))()},toTip:function(){Object(d.a)({duration:1e3,message:"正在开发中..."})}}},p={render:function(){var t=this,a=t.$createElement,s=t._self._c||a;return s("div",{staticClass:"wrap"},[s("article",{staticClass:"main"},[s("div",{staticClass:"index_top"},[s("div",{staticClass:"img"},[s("img",{attrs:{src:t.userInfo.avatar?t.userInfo.avatar:"https://a119112.oss-cn-beijing.aliyuncs.com/bxl_item/vueImages/iconNullName.jpg"}})]),t._v(" "),s("div",{staticClass:"text"},[s("h3",[t._v(t._s(t.userInfo.user_name))]),t._v(" "),s("p",[t._v("ID："+t._s(t.userInfo.show_id))])])]),t._v(" "),s("div",{staticClass:"pay_box"},[s("div",{staticClass:"text"},[s("h3",[t._v(t._s(t.userMoney))]),t._v(" "),t._m(0)]),t._v(" "),s("div",{staticClass:"btn"},[s("a",{on:{click:t.toHarryCall}})])]),t._v(" "),s("div",{staticClass:"nav_box"},[t._m(1),t._v(" "),s("div",{staticClass:"list"},[s("router-link",{staticClass:"taobao",attrs:{to:{path:"TeamOrders",query:{urlType:"1",app_id:t.userInfo.id}}}},[t._v("淘宝")]),t._v(" "),s("router-link",{staticClass:"jingdong",attrs:{to:{path:"TeamOrders",query:{urlType:"2",app_id:t.userInfo.id}}}},[t._v("京东")]),t._v(" "),s("router-link",{staticClass:"pinduoduo",attrs:{to:{path:"TeamOrders",query:{urlType:"3",app_id:t.userInfo.id}}}},[t._v("拼多多")]),t._v(" "),s("router-link",{staticClass:"eleme",attrs:{to:{path:"TeamOrders",query:{urlType:"4",app_id:t.userInfo.id}}}},[t._v("饿了么")]),t._v(" "),s("router-link",{staticClass:"meituan",attrs:{to:{path:"TeamOrders",query:{urlType:"5",app_id:t.userInfo.id}}}},[t._v("美团")])],1)]),t._v(" "),s("div",{staticClass:"nav_box"},[t._m(2),t._v(" "),s("div",{staticClass:"list"},[s("a",{staticClass:"baobiao",on:{click:t.toTip}},[t._v("团队收益报表")]),t._v(" "),s("a",{staticClass:"quanzi",on:{click:t.toTip}},[t._v("我的圈子")]),t._v(" "),s("a",{staticClass:"toutiao",on:{click:t.toTip}},[t._v("我的头条")]),t._v(" "),s("a",{staticClass:"xinyongka",on:{click:t.toTip}},[t._v("办信用卡")])])]),t._v(" "),s("div",{staticClass:"nav_box",staticStyle:{"margin-bottom":".75rem"}},[t._m(3),t._v(" "),s("div",{staticClass:"shop_order_price"},[s("a",{staticClass:"quanzi"},[s("p",[t._v("今日预估收入(元)")]),t._v(" "),s("h3",[t._v(t._s(t.showDisplay.num_today_data))])]),t._v(" "),s("a",{staticClass:"quanzi"},[s("p",[t._v("今月预估收入(元)")]),t._v(" "),s("h3",[t._v(t._s(t.showDisplay.num_month_data))])]),t._v(" "),s("a",{staticClass:"quanzi"},[s("p",[t._v("累计预估收入(元)")]),t._v(" "),s("h3",[t._v(t._s(t.showDisplay.num_all_data))])])])])])])},staticRenderFns:[function(){var t=this.$createElement,a=this._self._c||t;return a("p",[this._v("管理费余额"),a("i",[this._v("(元)")])])},function(){var t=this.$createElement,a=this._self._c||t;return a("div",{staticClass:"title"},[a("span",[this._v("电商平台团队订单")]),a("em",[this._v("(京东、淘宝、拼多多等订单)")])])},function(){var t=this.$createElement,a=this._self._c||t;return a("div",{staticClass:"title"},[a("span",[this._v("其他团队订单")])])},function(){var t=this.$createElement,a=this._self._c||t;return a("div",{staticClass:"title"},[a("span",[this._v("爆款商城订单")])])}]};var f=s("VU/8")(v,p,!1,function(t){s("w0aD")},"data-v-0aa4f756",null);a.default=f.exports},mvHQ:function(t,a,s){t.exports={default:s("qkKv"),__esModule:!0}},qkKv:function(t,a,s){var e=s("FeBl"),i=e.JSON||(e.JSON={stringify:JSON.stringify});t.exports=function(t){return i.stringify.apply(i,arguments)}},w0aD:function(t,a){}});