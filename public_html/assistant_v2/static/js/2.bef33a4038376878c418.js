webpackJsonp([2],{"+c27":function(t,e){},"3IMD":function(t,e){},"3vSQ":function(t,e,n){"use strict";Object.defineProperty(e,"__esModule",{value:!0});n("eqfM");var a=n("/QYm"),i=n("Xxa5"),o=n.n(i),s=n("mvHQ"),r=n.n(s),c=n("exGp"),l=n.n(c),u=n("bOdI"),d=n.n(u),p=(n("XmAh"),n("il3B")),f=(n("mMXg"),n("qYlo")),h=n("mF0L"),v=(n("nsZj"),n("T2s0"),n("1H7Z"),n("+c27"),n("o69Z")),g=n("Vj2t"),_=Object(v.b)("checkbox"),m=_[0],b=_[1],x=m({mixins:[Object(g.a)({bem:b,role:"checkbox",parent:"vanCheckbox"})],computed:{checked:{get:function(){return this.parent?-1!==this.parent.value.indexOf(this.name):this.value},set:function(t){this.parent?this.setParentValue(t):this.$emit("input",t)}}},watch:{value:function(t){this.$emit("change",t)}},methods:{toggle:function(t){var e=this;void 0===t&&(t=!this.checked),clearTimeout(this.toggleTask),this.toggleTask=setTimeout(function(){e.checked=t})},setParentValue:function(t){var e=this.parent,n=e.value.slice();if(t){if(e.max&&n.length>=e.max)return;-1===n.indexOf(this.name)&&(n.push(this.name),e.$emit("input",n))}else{var a=n.indexOf(this.name);-1!==a&&(n.splice(a,1),e.$emit("input",n))}}}}),k={name:"",data:function(){return{app_id:"",checked:!0,times:5}},props:[],components:d()({},x.name,x),created:function(){this.app_id=JSON.parse(localStorage.getItem("userInfo")).id,localStorage.getItem(this.app_id+"times")?this.times=0:this.countdown()},computed:{},methods:{countdown:function(){var t=this,e=window.setInterval(function(){t.times--<=1&&(clearInterval(e),localStorage.setItem(t.app_id+"times",!0))},1e3)},getWxrobotWay:function(){var t=this;return l()(o.a.mark(function e(){var n,a;return o.a.wrap(function(e){for(;;)switch(e.prev=e.next){case 0:return(n=t).bus.$emit("loading",!0),a='{"app_id":"'+n.app_id+'", "method":"agree.tip"}',e.next=5,n.ajaxPost("wx_robot_get_way",a,{});case 5:e.sent,n.bus.$emit("loading",!1);case 7:case"end":return e.stop()}},e,t)}))()},confirm:function(){if(0==this.times){if(0==this.checked)return Object(a.a)("您未同意该协议"),!1;this.getWxrobotWay(),localStorage.setItem(this.app_id+"c",this.app_id+"c"),this.$emit("closePopup",!1)}},cancel:function(){h.a.isAndroid()?window.jsbridge.back():h.a.isIos()&&(window.location.href="jsbridge://back")}}},w={render:function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("div",{staticClass:"tips_popup"},[n("div",{staticClass:"title"},[t._v("温馨提示")]),t._v(" "),t._m(0),t._v(" "),n("div",{staticClass:"label"},[n("van-checkbox",{attrs:{"icon-size":".34rem",shape:"square"},model:{value:t.checked,callback:function(e){t.checked=e},expression:"checked"}},[t._v("我已阅读并同意")]),t._v(" "),n("a",{attrs:{href:"https://a119112.oss-cn-beijing.aliyuncs.com/bxl_item/staticHtml/pages/xin_aide_treaty_del.html"}},[t._v("《智能助理使用免责声明》")])],1),t._v(" "),n("div",{staticClass:"button"},[n("span",{class:0==t.times?"active":"",on:{click:t.confirm}},[t._v("\n      同意并进入\n      "),n("i",[t._v("（"+t._s(t.times)+"s）")])]),t._v(" "),n("em",{on:{click:t.cancel}},[t._v("暂不同意")])])])},staticRenderFns:[function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("div",{staticClass:"content"},[n("p",[t._v("您好，欢迎您注册并使用智能助理模块（以下简称为“本平台”）。任何用户在使用智能助理之前，均应仔细阅读本声明，用户可选择不使用智能助理服务，一旦使用，即被视为对本声明全部内容的认可和接受。")]),t._v(" "),n("h3",[t._v("使用前提")]),t._v(" "),n("p",[t._v("1. 用户必须同意本协议条款后才能使用本平台;")]),t._v(" "),n("p",[t._v("2. 若您在18周岁以下，您必须确保在父母或监护人的监护下使用本平台;")]),t._v(" "),n("p",[t._v("3. 您同意搜索互动在法律允许范围内有权独自决定关闭您的账户、清除账户内信息;")]),t._v(" "),n("p",[t._v("4. 您必须能够自行通过有法律资格的第三方对您提供互联网接入服务，并自行承担以下内容:自行配备上网设备，包括个人电脑，调制解调器及其他必要的设备装置;自行承担上网费用，如网络接入费用、电话费用、流量费用等。")]),t._v(" "),n("h3",[t._v("第三方网页内容免责")]),t._v(" "),n("p",[t._v('任何通过 群助手服务所得的搜索结果链接的网页，以及网页中之所有内容，均系该网页所属第三方网站的所有者制作和提供(以下简称"第三方网页")。该等搜索结果和第三方网页均系搜索引擎技术自动搜录所得，并不是也不反映本公司之任何意见和主张，也不表本公司同意或支持该等第三方网页上的任何内容、主张或立场。本公司对第三方网页中内容之合法性、准确性、真实性、适用性、安全性等概不负责，也无法负责。')]),t._v(" "),n("h3",[t._v("责任限制及不承诺担保")]),t._v(" "),n("p",[t._v("您同意在自己承担风险的情况下，按本平台的现状使用相关产品及服务。搜索互动特此申明对本平台之使用不提供任何保证(包含但不限于商业担保和特殊目的的适用性担保)亦不对您因服务暂停或终止而遭受的任何直接或间接损失承担责任。搜索互动不保证您一定能够获得服务，搜索互动有权随时暂停或终止全部或部分服务；")]),t._v(" "),n("p",[t._v("您理解本平台中可能包含由第三方提供的服务，搜索互动只是为了您的便利而提供功能模块。您使用第三方提供的服务，需与第三方另行达成服务协议，支付相应的费用并承担可能的风险。搜索互动对第三方所提供的服务不承担任何形式的担保。")]),t._v(" "),n("p",[t._v("感谢您使用智能助理模块，我们将致力为您提供更贴心的服务！")])])}]};var y,C=n("VU/8")(k,w,!1,function(t){n("BdUx")},"data-v-2480adbb",null).exports,S={name:"",data:function(){return{userInfo:"",popupShow:!1,tipShow:!1,userd:"",packaged:"",times:20,interval:"",auditLink:""}},components:(y={},d()(y,f.a.name,f.a),d()(y,p.a.name,p.a),d()(y,"tipsPopup",C),y),mounted:function(){this.getUserInfo()},computed:{},methods:{getUserInfo:function(){var t=this;return l()(o.a.mark(function e(){var n;return o.a.wrap(function(e){for(;;)switch(e.prev=e.next){case 0:n=t,h.a.isAndroid()?(n.userInfo=JSON.parse(window.jsbridge.getUserInfo()),localStorage.setItem("userInfo",r()(n.userInfo)),localStorage.getItem(n.userInfo.id+"c")==n.userInfo.id+"c"?(n.popupShow=!1,setTimeout(function(){n.getWxrobotInfo()},400)):n.popupShow=!0):h.a.isIos()&&n.$bridge.callhandler("getUserInfo",{},function(t){n.userInfo=JSON.parse(t),localStorage.setItem("userInfo",r()(n.userInfo)),localStorage.getItem(n.userInfo.id+"c")==n.userInfo.id+"c"?(n.popupShow=!1,setTimeout(function(){n.getWxrobotInfo()},400)):n.popupShow=!0});case 2:case"end":return e.stop()}},e,t)}))()},getWxrobotInfo:function(){var t=this;return l()(o.a.mark(function e(){var n,i,s;return o.a.wrap(function(e){for(;;)switch(e.prev=e.next){case 0:return(n=t).bus.$emit("loading",!0),i='{"app_id":"'+n.userInfo.id+'"}',e.next=5,n.ajaxGet("wx_robot_info",i,{});case 5:200==(s=e.sent).data.code?(n.userd=s.data.data.user,n.packaged=s.data.data.package,1==n.userd.is_expire&&p.a.confirm({title:"温馨提示",message:"很抱歉！您的智能助理已经过期了，<br/>点击去续费后，即可重新开始使用！",confirmButtonText:"去续费",cancelButtonText:"暂不续费",width:"7.5rem"}).then(function(){t.$router.push({path:"Open",query:{app_id:n.userInfo.id,packaged:r()(n.packaged)}})}).catch(function(){})):Object(a.a)(s.data.msg),n.bus.$emit("loading",!1);case 8:case"end":return e.stop()}},e,t)}))()},tocgList:function(t){var e=this,n=this;if(0==n.userd.user_flag)n.getWxrobotInfo();else if(1==n.userd.user_flag)p.a.confirm({title:"温馨提示",message:"本功能需要开通智能助手后，<br/>才能正常使用！是否前往开通？",confirmButtonText:"马上开通",cancelButtonText:"暂不开通",width:"7.5rem"}).then(function(){e.$router.push({path:"Open",query:{app_id:n.userInfo.id,packaged:r()(n.packaged)}})}).catch(function(){});else if(n.userd.user_flag>=2){if(!n.userd.wx_id&&n.userd.robot_id)return void p.a.confirm({title:"温馨提示",message:"您还没有绑定发单微信哦！<br/>请立即绑定开始发单吧！",confirmButtonText:"去绑定",cancelButtonText:"取消",width:"7.5rem"}).then(function(){e.$router.push({path:"Qrcode",query:{app_id:n.userInfo.id}})}).catch(function(){});1==n.userd.login_status?0==t?this.$router.push({path:"GroupList",query:{userd:r()(n.userd),packaged:r()(n.packaged)}}):1==t&&this.$router.push({path:"CircleList",query:{userd:r()(n.userd),packaged:r()(n.packaged)}}):p.a.confirm({title:"温馨提示",message:"您的助理当前已离线，<br/>请您重新登录后再使用该功能！",confirmButtonText:"重新登录",cancelButtonText:"取消",width:"7.5rem"}).then(function(){n.secondLoginWay()}).catch(function(){})}},signout:function(){var t=this;p.a.confirm({title:"温馨提示",message:"您将退出【"+t.userd.nickName+"】的智能助理，退出后 智能群助理与智能发圈助理将立即停止工作！确定退出吗？",confirmButtonText:"确定退出",cancelButtonText:"取消",width:"7.5rem"}).then(function(){t.offlineWxrobotWay()}).catch(function(){})},offlineWxrobotWay:function(){var t=this;return l()(o.a.mark(function e(){var n,i,s;return o.a.wrap(function(e){for(;;)switch(e.prev=e.next){case 0:return(n=t).bus.$emit("loading",!0),i='{"app_id":"'+n.userd.app_id+'", "method":"offline"}',e.next=5,n.ajaxPost("wx_robot_get_way",i,{});case 5:200==(s=e.sent).data.code?(Object(a.a)("退出登录成功"),n.getWxrobotInfo()):Object(a.a)(s.data.msg),n.bus.$emit("loading",!1);case 8:case"end":return e.stop()}},e,t)}))()},secondLoginWay:function(t){var e=this;return l()(o.a.mark(function n(){var i,s;return o.a.wrap(function(n){for(;;)switch(n.prev=n.next){case 0:if(!(i=e).isMore){n.next=3;break}return n.abrupt("return");case 3:if(i.isMore=!0,1!=t){n.next=9;break}return s='{"app_id":"'+i.userd.app_id+'", "method":"offline"}',n.next=8,i.ajaxPost("wx_robot_get_way",s,{});case 8:n.sent;case 9:s='{"app_id":"'+i.userd.app_id+'", "method":"second.login"}',a.a.loading({message:"请前往微信端点击“登录”",duration:0}),i.ajaxPost("wx_robot_get_way",s,{}).then(function(t){a.a.clear(),200==t.data.code?1001==t.data.data.code?p.a.alert({title:"温馨提示",message:t.data.data.message,width:"7.5rem"}).then(function(){i.$router.push({path:"Qrcode",query:{app_id:i.userd.app_id}})}):(Object(a.a)("重新登录成功"),i.getWxrobotInfo()):2026==t.data.code?(Object(a.a)(t.data.msg),i.getWxrobotInfo()):p.a.alert({title:"温馨提示",message:t.data.msg,width:"7.5rem"}).then(function(){i.$router.push({path:"Qrcode",query:{app_id:i.userd.app_id}})})}).catch(function(){a.a.clear(),p.a.alert({title:"温馨提示",message:"很抱歉！智能助理重新登录失败，<br/>点击【重新登录】后，到微信端点击确定登录！",width:"7.5rem"}).then(function(){})}),i.isMore=!1;case 13:case"end":return n.stop()}},n,e)}))()},closePopup:function(t){this.popupShow=t,this.getWxrobotInfo()}}},O={render:function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("div",{staticClass:"wrap"},[n("article",{staticClass:"main index_box"},[n("div",{staticClass:"head_banner"},[t.userd.wx_id?n("div",{staticClass:"content"},[n("div",{staticClass:"image"},[n("img",{attrs:{src:t.userd.headUrl}})]),t._v(" "),n("div",{staticClass:"text"},[n("h3",[t._v(t._s(t.userd.nickName))]),t._v(" "),0==t.userd.login_status?n("h4",{staticClass:"offline"},[t._v("助理已离线")]):t._e(),t._v(" "),1==t.userd.login_status?n("h4",{staticClass:"online"},[t._v("助理在线中...")]):t._e(),t._v(" "),n("p",{staticClass:"overflowTextOne"},[t._v("微信号："+t._s(t.userd.wx_id))])]),t._v(" "),1==t.userd.login_status?n("div",[n("div",{staticClass:"btn",on:{click:t.signout}},[t._v("退出登录")])]):n("div",[n("div",{staticClass:"btn",on:{click:t.secondLoginWay}},[t._v("重新登录")]),t._v(" "),n("router-link",{staticClass:"btn",staticStyle:{top:"1.42rem"},attrs:{to:{path:"Qrcode",query:{app_id:t.userInfo.id}}}},[t._v("切换账号")])],1),t._v(" "),n("div",{staticClass:"overdue"})]):n("div",{staticClass:"content"},[t._m(0),t._v(" "),t._m(1),t._v(" "),t.userd.robot_id&&!t.userd.wx_id&&t.userd.user_flag>=2?n("router-link",{staticClass:"btn",attrs:{to:{path:"Qrcode",query:{app_id:t.userInfo.id}}}},[t._v("绑定微信")]):t._e()],1)]),t._v(" "),t.userd.user_flag>1?n("div",{staticClass:"open_box"},[0==t.userd.is_expire?n("div",{staticClass:"text"},[t._v("有效期至"+t._s(t.userd.expiry_time.substring(0,10)))]):n("div",{staticClass:"text"},[t._v("智能助理已过期")]),t._v(" "),n("router-link",{staticClass:"btn",staticStyle:{background:"#ff7400","box-shadow":"0 0 4px rgba(255, 116, 0, .5)"},attrs:{to:{path:"Open",query:{app_id:t.userInfo.id,packaged:JSON.stringify(t.packaged)}}}},[t._v("立即续费")])],1):n("div",{staticClass:"open_box"},[n("div",{staticClass:"text"},[t._v("智能助理仅"+t._s(t.userInfo.group_id>=23?t.packaged.vip_price:t.packaged.common_price)+"元/月")]),t._v(" "),n("router-link",{staticClass:"btn",attrs:{to:{path:"Open",query:{app_id:t.userInfo.id,packaged:JSON.stringify(t.packaged)}}}},[t._v("立即开通")])],1),t._v(" "),n("div",{staticClass:"ind1_box"},[n("div",{staticClass:"intitle"},[t._v("智能助理列表 ")]),t._v(" "),n("div",{staticClass:"content"},[n("div",{staticClass:"btn",on:{click:function(e){return t.tocgList(0)}}},[n("p",[t._v(t._s(1==t.userd.group_flag?"正在智能发群中":"智能发群已关闭"))]),t._v(" "),n("h3",[t._v("智能发群")]),t._v(" "),n("h4",[t._v(t._s(1==t.userd.group_flag?"已开启":"未开启"))])]),t._v(" "),n("div",{staticClass:"btn",on:{click:function(e){return t.tocgList(1)}}},[n("p",[t._v(t._s(1==t.userd.circle_flag?"正在智能发圈中":"智能发圈已关闭"))]),t._v(" "),n("h3",[t._v("智能发圈")]),t._v(" "),n("h4",[t._v(t._s(1==t.userd.circle_flag?"已开启":"未开启"))])])])]),t._v(" "),n("van-popup",{attrs:{"close-on-click-overlay":!1},model:{value:t.popupShow,callback:function(e){t.popupShow=e},expression:"popupShow"}},[n("tips-popup",{on:{closePopup:t.closePopup}})],1),t._v(" "),n("van-popup",{attrs:{"close-on-click-overlay":!1},model:{value:t.tipShow,callback:function(e){t.tipShow=e},expression:"tipShow"}},[n("div",{staticClass:"tip_box"},[t._v("请前往微信端点击“登录”")])])],1)])},staticRenderFns:[function(){var t=this.$createElement,e=this._self._c||t;return e("div",{staticClass:"image"},[e("img",{attrs:{src:"https://a119112.oss-cn-beijing.aliyuncs.com/bxl_item/vueImages/iconNullName.png"}})])},function(){var t=this.$createElement,e=this._self._c||t;return e("div",{staticClass:"text"},[e("h3",[this._v("我要智能发圈")]),this._v(" "),e("h4",[this._v("微信未登录")]),this._v(" "),e("p",{staticStyle:{width:"auto"}},[this._v("开通智能助理 登录微信后即可智能发圈")])])}]};var I=n("VU/8")(S,O,!1,function(t){n("C3j8")},"data-v-8ba69df8",null);e.default=I.exports},BdUx:function(t,e){},C3j8:function(t,e){},JKiR:function(t,e){},XmAh:function(t,e,n){"use strict";var a=n("nsZj"),i=(n.n(a),n("02pT")),o=(n.n(i),n("T2s0")),s=(n.n(o),n("1H7Z")),r=(n.n(s),n("hW8u")),c=(n.n(r),n("WpgC")),l=(n.n(c),n("3IMD")),u=(n.n(l),n("JKiR"));n.n(u)},il3B:function(t,e,n){"use strict";var a=n("RfZZ"),i=n("7+uW"),o=n("o69Z"),s=n("V+2B"),r=n("CsZI"),c=n("AA6R"),l=n.n(c),u=n("rhik");function d(t){!function(t,e){var n=e.to,a=e.url,i=e.replace;if(n&&t){var o=t[i?"replace":"push"](n);o&&o.catch&&o.catch(function(t){if(t&&"NavigationDuplicated"!==t.name)throw t})}else a&&(i?location.replace(a):location.href=a)}(t.parent&&t.parent.$router,t.props)}var p={url:String,replace:Boolean,to:[String,Object]},f=n("+2ln"),h=n("pIDD"),v=Object(o.b)("button"),g=v[0],_=v[1];function m(t,e,n,a){var i,o=e.tag,r=e.icon,c=e.type,p=e.color,v=e.plain,g=e.disabled,m=e.loading,b=e.hairline,x=e.loadingText,k={};p&&(k.color=v?p:s.e,v||(k.background=p),-1!==p.indexOf("gradient")?k.border=0:k.borderColor=p);var w,y,C=[_([c,e.size,{plain:v,loading:m,disabled:g,hairline:b,block:e.block,round:e.round,square:e.square}]),(i={},i[s.c]=b,i)];return t(o,l()([{style:k,class:C,attrs:{type:e.nativeType,disabled:g},on:{click:function(t){m||g||(Object(u.a)(a,"click",t),d(a))},touchstart:function(t){Object(u.a)(a,"touchstart",t)}}},Object(u.b)(a)]),[(y=[],m?y.push(t(h.a,{class:_("loading"),attrs:{size:e.loadingSize,type:e.loadingType,color:"currentColor"}})):r&&y.push(t(f.a,{attrs:{name:r},class:_("icon")})),(w=m?x:n.default?n.default():e.text)&&y.push(t("span",{class:_("text")},[w])),y)])}m.props=Object(a.a)({},p,{text:String,icon:String,color:String,block:Boolean,plain:Boolean,round:Boolean,square:Boolean,loading:Boolean,hairline:Boolean,disabled:Boolean,nativeType:String,loadingText:String,loadingType:String,tag:{type:String,default:"button"},type:{type:String,default:"default"},size:{type:String,default:"normal"},loadingSize:{type:String,default:"20px"}});var b,x=g(m),k=Object(o.b)("dialog"),w=k[0],y=k[1],C=k[2],S=w({mixins:[Object(r.a)()],props:{title:String,width:[Number,String],message:String,className:null,callback:Function,beforeClose:Function,messageAlign:String,cancelButtonText:String,cancelButtonColor:String,confirmButtonText:String,confirmButtonColor:String,showCancelButton:Boolean,transition:{type:String,default:"van-dialog-bounce"},showConfirmButton:{type:Boolean,default:!0},overlay:{type:Boolean,default:!0},closeOnClickOverlay:{type:Boolean,default:!1}},data:function(){return{loading:{confirm:!1,cancel:!1}}},methods:{onClickOverlay:function(){this.handleAction("overlay")},handleAction:function(t){var e=this;this.$emit(t),this.value&&(this.beforeClose?(this.loading[t]=!0,this.beforeClose(t,function(n){!1!==n&&e.loading[t]&&e.onClose(t),e.loading.confirm=!1,e.loading.cancel=!1})):this.onClose(t))},onClose:function(t){this.close(),this.callback&&this.callback(t)},onOpened:function(){this.$emit("opened")},onClosed:function(){this.$emit("closed")},genButtons:function(){var t,e=this,n=this.$createElement,a=this.showCancelButton&&this.showConfirmButton;return n("div",{class:[s.d,y("footer",{buttons:a})]},[this.showCancelButton&&n(x,{attrs:{size:"large",loading:this.loading.cancel,text:this.cancelButtonText||C("cancel")},class:y("cancel"),style:{color:this.cancelButtonColor},on:{click:function(){e.handleAction("cancel")}}}),this.showConfirmButton&&n(x,{attrs:{size:"large",loading:this.loading.confirm,text:this.confirmButtonText||C("confirm")},class:[y("confirm"),(t={},t[s.b]=a,t)],style:{color:this.confirmButtonColor},on:{click:function(){e.handleAction("confirm")}}})])}},render:function(){var t,e=arguments[0];if(this.shouldRender){var n=this.message,a=this.messageAlign,i=this.slots(),s=this.slots("title")||this.title,r=s&&e("div",{class:y("header",{isolated:!n&&!i})},[s]),c=(i||n)&&e("div",{class:y("content")},[i||e("div",{domProps:{innerHTML:n},class:y("message",(t={"has-title":s},t[a]=a,t))})]);return e("transition",{attrs:{name:this.transition},on:{afterEnter:this.onOpened,afterLeave:this.onClosed}},[e("div",{directives:[{name:"show",value:this.value}],attrs:{role:"dialog","aria-labelledby":this.title||n},class:[y(),this.className],style:{width:Object(o.a)(this.width)}},[r,c,this.genButtons()])])}}});function O(t){return o.g?Promise.resolve():new Promise(function(e,n){var o;b&&(o=b.$el,document.body.contains(o))||(b&&b.$destroy(),(b=new(i.a.extend(S))({el:document.createElement("div"),propsData:{lazyRender:!1}})).$on("input",function(t){b.value=t})),Object(a.a)(b,O.currentOptions,t,{resolve:e,reject:n})})}O.defaultOptions={value:!0,title:"",width:"",message:"",overlay:!0,className:"",lockScroll:!0,transition:"van-dialog-bounce",beforeClose:null,overlayClass:"",overlayStyle:null,messageAlign:"",getContainer:"body",cancelButtonText:"",cancelButtonColor:null,confirmButtonText:"",confirmButtonColor:null,showConfirmButton:!0,showCancelButton:!1,closeOnPopstate:!1,closeOnClickOverlay:!1,callback:function(t){b["confirm"===t?"resolve":"reject"](t)}},O.alert=O,O.confirm=function(t){return O(Object(a.a)({showCancelButton:!0},t))},O.close=function(){b&&(b.value=!1)},O.setDefaultOptions=function(t){Object(a.a)(O.currentOptions,t)},O.resetDefaultOptions=function(){O.currentOptions=Object(a.a)({},O.defaultOptions)},O.resetDefaultOptions(),O.install=function(){i.a.use(S)},O.Component=S,i.a.prototype.$dialog=O;e.a=O},mF0L:function(t,e,n){"use strict";n.d(e,"a",function(){return a});var a={isWeixin:function(){return!!/MicroMessenger/i.test(navigator.userAgent)},isAndroid:function(){return!!/android/i.test(navigator.userAgent)},isIos:function(){return!!/(iPhone|iPad|iPod|iOS)/i.test(navigator.userAgent)}}},mvHQ:function(t,e,n){t.exports={default:n("qkKv"),__esModule:!0}},qkKv:function(t,e,n){var a=n("FeBl"),i=a.JSON||(a.JSON={stringify:JSON.stringify});t.exports=function(t){return i.stringify.apply(i,arguments)}}});