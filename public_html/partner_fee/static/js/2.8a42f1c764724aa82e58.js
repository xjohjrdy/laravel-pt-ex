webpackJsonp([2],{"+c27":function(t,e){},"3IMD":function(t,e){},JKiR:function(t,e){},KGtz:function(t,e){},Vj2t:function(t,e,a){"use strict";a.d(e,"a",function(){return l});var n=a("+2ln"),i=a("X6Tt"),s=a("1SJR"),o=a("o69Z"),l=function(t){var e=t.parent,a=t.bem,l=t.role;return{mixins:[Object(s.a)(e),i.a],props:{name:null,value:null,disabled:Boolean,iconSize:[Number,String],checkedColor:String,labelPosition:String,labelDisabled:Boolean,shape:{type:String,default:"round"},bindGroup:{type:Boolean,default:!0}},computed:{disableBindRelation:function(){return!this.bindGroup},isDisabled:function(){return this.parent&&this.parent.disabled||this.disabled},direction:function(){return this.parent&&this.parent.direction||null},iconStyle:function(){var t=this.checkedColor||this.parent&&this.parent.checkedColor;if(t&&this.checked&&!this.isDisabled)return{borderColor:t,backgroundColor:t}},tabindex:function(){return this.isDisabled||"radio"===l&&!this.checked?-1:0}},methods:{onClick:function(t){var e=t.target,a=this.$refs.icon,n=a===e||a.contains(e);this.isDisabled||!n&&this.labelDisabled||this.toggle(),this.$emit("click",t)},genIcon:function(){var t=this.$createElement,e=this.checked,i=this.iconSize||this.parent&&this.parent.iconSize;return t("div",{ref:"icon",class:a("icon",[this.shape,{disabled:this.isDisabled,checked:e}]),style:{fontSize:Object(o.a)(i)}},[this.slots("icon",{checked:e})||t(n.a,{attrs:{name:"success"},style:this.iconStyle})])},genLabel:function(){var t=this.$createElement,e=this.slots();if(e)return t("span",{class:a("label",[this.labelPosition,{disabled:this.isDisabled}])},[e])}},render:function(){var t=arguments[0],e=[this.genIcon()];return"left"===this.labelPosition?e.unshift(this.genLabel()):e.push(this.genLabel()),t("div",{attrs:{role:l,tabindex:this.tabindex,"aria-checked":String(this.checked)},class:a([{disabled:this.isDisabled,"label-disabled":this.labelDisabled},this.direction]),on:{click:this.onClick}},[e])}}}},X6Tt:function(t,e,a){"use strict";a.d(e,"a",function(){return n});var n={inject:{vanField:{default:null}},watch:{value:function(){var t=this.vanField;t&&(t.resetValidation(),t.validateWithTrigger("onChange"))}},created:function(){var t=this.vanField;t&&!t.children&&(t.children=this)}}},XmAh:function(t,e,a){"use strict";var n=a("nsZj"),i=(a.n(n),a("02pT")),s=(a.n(i),a("T2s0")),o=(a.n(s),a("1H7Z")),l=(a.n(o),a("hW8u")),c=(a.n(l),a("WpgC")),r=(a.n(c),a("3IMD")),u=(a.n(r),a("JKiR"));a.n(u)},il3B:function(t,e,a){"use strict";var n=a("RfZZ"),i=a("7+uW"),s=a("o69Z"),o=a("V+2B"),l=a("CsZI"),c=a("AA6R"),r=a.n(c),u=a("rhik"),d=a("n8HW"),h=a("+2ln"),p=a("pIDD"),f=Object(s.b)("button"),v=f[0],m=f[1];function y(t,e,a,n){var i,s=e.tag,l=e.icon,c=e.type,f=e.color,v=e.plain,y=e.disabled,b=e.loading,g=e.hairline,_=e.loadingText,x={};f&&(x.color=v?f:o.e,v||(x.background=f),-1!==f.indexOf("gradient")?x.border=0:x.borderColor=f);var C,k,O=[m([c,e.size,{plain:v,loading:b,disabled:y,hairline:g,block:e.block,round:e.round,square:e.square}]),(i={},i[o.b]=g,i)];return t(s,r()([{style:x,class:O,attrs:{type:e.nativeType,disabled:y},on:{click:function(t){b||y||(Object(u.a)(n,"click",t),Object(d.a)(n))},touchstart:function(t){Object(u.a)(n,"touchstart",t)}}},Object(u.b)(n)]),[(k=[],b?k.push(t(p.a,{class:m("loading"),attrs:{size:e.loadingSize,type:e.loadingType,color:"currentColor"}})):l&&k.push(t(h.a,{attrs:{name:l},class:m("icon")})),(C=b?_:a.default?a.default():e.text)&&k.push(t("span",{class:m("text")},[C])),k)])}y.props=Object(n.a)({},d.c,{text:String,icon:String,color:String,block:Boolean,plain:Boolean,round:Boolean,square:Boolean,loading:Boolean,hairline:Boolean,disabled:Boolean,nativeType:String,loadingText:String,loadingType:String,tag:{type:String,default:"button"},type:{type:String,default:"default"},size:{type:String,default:"normal"},loadingSize:{type:String,default:"20px"}});var b,g=v(y),_=Object(s.b)("dialog"),x=_[0],C=_[1],k=_[2],O=x({mixins:[Object(l.a)()],props:{title:String,width:[Number,String],message:String,className:null,callback:Function,beforeClose:Function,messageAlign:String,cancelButtonText:String,cancelButtonColor:String,confirmButtonText:String,confirmButtonColor:String,showCancelButton:Boolean,transition:{type:String,default:"van-dialog-bounce"},showConfirmButton:{type:Boolean,default:!0},overlay:{type:Boolean,default:!0},closeOnClickOverlay:{type:Boolean,default:!1}},data:function(){return{loading:{confirm:!1,cancel:!1}}},methods:{onClickOverlay:function(){this.handleAction("overlay")},handleAction:function(t){var e=this;this.$emit(t),this.value&&(this.beforeClose?(this.loading[t]=!0,this.beforeClose(t,function(a){!1!==a&&e.loading[t]&&e.onClose(t),e.loading.confirm=!1,e.loading.cancel=!1})):this.onClose(t))},onClose:function(t){this.close(),this.callback&&this.callback(t)},onOpened:function(){this.$emit("opened")},onClosed:function(){this.$emit("closed")},genButtons:function(){var t,e=this,a=this.$createElement,n=this.showCancelButton&&this.showConfirmButton;return a("div",{class:[o.c,C("footer",{buttons:n})]},[this.showCancelButton&&a(g,{attrs:{size:"large",loading:this.loading.cancel,text:this.cancelButtonText||k("cancel")},class:C("cancel"),style:{color:this.cancelButtonColor},on:{click:function(){e.handleAction("cancel")}}}),this.showConfirmButton&&a(g,{attrs:{size:"large",loading:this.loading.confirm,text:this.confirmButtonText||k("confirm")},class:[C("confirm"),(t={},t[o.a]=n,t)],style:{color:this.confirmButtonColor},on:{click:function(){e.handleAction("confirm")}}})])}},render:function(){var t,e=arguments[0];if(this.shouldRender){var a=this.message,n=this.messageAlign,i=this.slots(),o=this.slots("title")||this.title,l=o&&e("div",{class:C("header",{isolated:!a&&!i})},[o]),c=(i||a)&&e("div",{class:C("content")},[i||e("div",{domProps:{innerHTML:a},class:C("message",(t={"has-title":o},t[n]=n,t))})]);return e("transition",{attrs:{name:this.transition},on:{afterEnter:this.onOpened,afterLeave:this.onClosed}},[e("div",{directives:[{name:"show",value:this.value}],attrs:{role:"dialog","aria-labelledby":this.title||a},class:[C(),this.className],style:{width:Object(s.a)(this.width)}},[l,c,this.genButtons()])])}}});function w(t){return s.g?Promise.resolve():new Promise(function(e,a){var s;b&&(s=b.$el,document.body.contains(s))||(b&&b.$destroy(),(b=new(i.a.extend(O))({el:document.createElement("div"),propsData:{lazyRender:!1}})).$on("input",function(t){b.value=t})),Object(n.a)(b,w.currentOptions,t,{resolve:e,reject:a})})}w.defaultOptions={value:!0,title:"",width:"",message:"",overlay:!0,className:"",lockScroll:!0,transition:"van-dialog-bounce",beforeClose:null,overlayClass:"",overlayStyle:null,messageAlign:"",getContainer:"body",cancelButtonText:"",cancelButtonColor:null,confirmButtonText:"",confirmButtonColor:null,showConfirmButton:!0,showCancelButton:!1,closeOnPopstate:!1,closeOnClickOverlay:!1,callback:function(t){b["confirm"===t?"resolve":"reject"](t)}},w.alert=w,w.confirm=function(t){return w(Object(n.a)({showCancelButton:!0},t))},w.close=function(){b&&(b.value=!1)},w.setDefaultOptions=function(t){Object(n.a)(w.currentOptions,t)},w.resetDefaultOptions=function(){w.currentOptions=Object(n.a)({},w.defaultOptions)},w.resetDefaultOptions(),w.install=function(){i.a.use(O)},w.Component=O,i.a.prototype.$dialog=w;e.a=w},ohdb:function(t,e,a){"use strict";Object.defineProperty(e,"__esModule",{value:!0});var n,i=a("Xxa5"),s=a.n(i),o=a("exGp"),l=a.n(o),c=a("bOdI"),r=a.n(c),u=(a("mMXg"),a("qYlo")),d=(a("XmAh"),a("il3B")),h=(a("nsZj"),a("T2s0"),a("1H7Z"),a("+c27"),a("o69Z")),p=a("Vj2t"),f=Object(h.b)("checkbox"),v=f[0],m=f[1],y=v({mixins:[Object(p.a)({bem:m,role:"checkbox",parent:"vanCheckbox"})],computed:{checked:{get:function(){return this.parent?-1!==this.parent.value.indexOf(this.name):this.value},set:function(t){this.parent?this.setParentValue(t):this.$emit("input",t)}}},watch:{value:function(t){this.$emit("change",t)}},methods:{toggle:function(t){var e=this;void 0===t&&(t=!this.checked),clearTimeout(this.toggleTask),this.toggleTask=setTimeout(function(){e.checked=t})},setParentValue:function(t){var e=this.parent,a=e.value.slice();if(t){if(e.max&&a.length>=e.max)return;-1===a.indexOf(this.name)&&(a.push(this.name),e.$emit("input",a))}else{var n=a.indexOf(this.name);-1!==n&&(a.splice(n,1),e.$emit("input",a))}}}}),b=(a("eqfM"),a("/QYm")),g=a("mF0L"),_={name:"",data:function(){return{userInfo:"",userMoney:"",lastMoney:"",taxMoney:"",checked:!0,cur:"2",alipayText:"支付宝",alipay:"",alipayVal:"",alipayConfirmVal:"",alipayShow:!1,salary_account:""}},components:(n={},r()(n,b.a.name,b.a),r()(n,y.name,y),r()(n,d.a.name,d.a),r()(n,u.a.name,u.a),n),mounted:function(){this.getUserInfo()},methods:{getUserInfo:function(){var t=this;return l()(s.a.mark(function e(){var a;return s.a.wrap(function(e){for(;;)switch(e.prev=e.next){case 0:a=t,g.a.isAndroid()?(a.userInfo=JSON.parse(window.jsbridge.getUserInfo()),a.getOtherUserMoney()):g.a.isIos()&&a.$bridge.callhandler("getUserInfo",{},function(t){a.userInfo=JSON.parse(t),a.getOtherUserMoney()});case 2:case"end":return e.stop()}},e,t)}))()},handleInput:function(t){this.taxMoney="0.00",this.userMoney<10?(Object(b.a)("您的奖励余额少于10元！"),this.lastMoney=""):Number(this.lastMoney)>Number(this.userMoney)?Object(b.a)("提现金额已超出可提现金额！"):this.lastMoney=t.target.value.match(/^\d*(\.?\d{0,2})/g)[0]||null,this.lastMoney>=10&&this.lastMoney<34?this.taxMoney=this.lastMoney-1:this.lastMoney>=34&&(this.taxMoney=this.lastMoney-.03*this.lastMoney)},getOtherUserMoney:function(){var t=this;return l()(s.a.mark(function e(){var a,n,i;return s.a.wrap(function(e){for(;;)switch(e.prev=e.next){case 0:return(a=t).bus.$emit("loading",!0),/^(\d{4})\d+(\d{4})$/,n='{"app_id":"'+a.userInfo.id+'"}',e.next=6,a.ajaxGet("other_user_money",n,{});case 6:200==(i=e.sent).data.code?(a.userMoney=i.data.data.money,a.alipay=i.data.data.alipay,a.salary_account=i.data.data.salary_account):Object(b.a)(i.data.msg),a.bus.$emit("loading",!1);case 9:case"end":return e.stop()}},e,t)}))()},otherAddLog:function(){var t=this;if(t.checked)if(t.lastMoney<10)Object(b.a)("单次最少提现10元！");else if(Number(t.userMoney)<Number(t.lastMoney))Object(b.a)("已超出可提现金额！");else if(1!=t.cur||t.alipay)if(2!=t.cur||t.salary_account){var e="您将提现"+t.lastMoney+"元,减服务费后到账"+t.taxMoney.toFixed(2)+"元！";d.a.confirm({title:"提示",message:e}).then(function(){t.getApplyWithdrawAli()}).catch(function(){})}else d.a.confirm({title:"提示",message:"您还没有绑定银行卡账号！",confirmButtonText:"去绑定"}).then(function(){t.alipayShow=!0,t.alipayText="银行卡"}).catch(function(){});else d.a.confirm({title:"提示",message:"您还没有绑定支付宝账号！",confirmButtonText:"去绑定"}).then(function(){t.alipayShow=!0,t.alipayText="支付宝"}).catch(function(){});else Object(b.a)("您未同意提现相关条款！")},getApplyWithdrawAli:function(){var t=this;return l()(s.a.mark(function e(){var a,n,i;return s.a.wrap(function(e){for(;;)switch(e.prev=e.next){case 0:return(a=t).bus.$emit("loading",!0),n='{"app_id":"'+a.userInfo.id+'", "money":"'+a.lastMoney+'", "type":"'+a.cur+'"}',e.next=5,a.ajaxPost("other_apply_withdraw_ali",n,{});case 5:200==(i=e.sent).data.code?(Object(b.a)(i.data.data),a.userMoney=Number(a.userMoney)-Number(a.lastMoney)):Object(b.a)(i.data.msg),a.bus.$emit("loading",!1);case 8:case"end":return e.stop()}},e,t)}))()},bindAlipay:function(){var t=this,e=g.b.luhmCheck(t.alipayVal);t.alipayVal==t.alipayConfirmVal?1==t.cur?d.a.confirm({title:"提示",message:"是否确认绑定支付宝账号\n“<strong style='color:#1989fa'>"+t.alipayVal+"</strong>”\n为奖励提现账号！"}).then(function(){t.alipayShow=!1,t.getOrderAccount(t.alipayVal),t.alipay=t.alipayVal}).catch(function(){}):2==t.cur&&(t.alipayVal==t.salary_account?Object(b.a)("您输入的银行卡号已绑定！"):e?d.a.confirm({title:"提示",message:"是否确认绑定银行卡账号\n“<strong style='color:#1989fa'>"+t.alipayVal+"</strong>”\n为奖励提现账号！"}).then(function(){t.alipayShow=!1,t.getOrderAccount(t.alipayVal),t.salary_account=t.alipayVal}).catch(function(){}):Object(b.a)("您输入的银行卡号格式有误！")):Object(b.a)("您输入的账号不一致！")},getOrderAccount:function(t){var e=this;return l()(s.a.mark(function a(){var n,i,o;return s.a.wrap(function(a){for(;;)switch(a.prev=a.next){case 0:return(n=e).bus.$emit("loading",!0),i='{"app_id":"'+n.userInfo.id+'", "account":"'+t+'", "type":"'+n.cur+'"}',a.next=5,n.ajaxPost("other_bind_account",i,{});case 5:200==(o=a.sent).data.code?Object(b.a)(o.data.data):Object(b.a)(o.data.msg),n.bus.$emit("loading",!1);case 8:case"end":return a.stop()}},a,e)}))()},bindAlipayShow:function(){this.alipayShow=!0,this.alipayVal="",this.alipayConfirmVal="",1==this.cur?this.alipayText="支付宝":2==this.cur&&(this.alipayText="银行卡")}}},x={render:function(){var t=this,e=t.$createElement,a=t._self._c||e;return a("div",{staticClass:"wrap"},[a("article",{staticClass:"main"},[a("div",{staticClass:"index_box"},[a("div",{staticClass:"top_info"},[a("div",{staticClass:"account"},[a("div",{staticClass:"img"},[a("img",{attrs:{src:t.userInfo.avatar?t.userInfo.avatar:"https://a119112.oss-cn-beijing.aliyuncs.com/bxl_item/vueImages/iconNullName.png"}})]),t._v(" "),a("div",{staticClass:"cont"},[a("h3",[t._v(t._s(t.userInfo.user_name))]),t._v(" "),a("p",[t._v("ID:"+t._s(t.userInfo.show_id))])]),t._v(" "),a("router-link",{staticClass:"btn",attrs:{to:{path:"PaymentDetail",query:{app_id:t.userInfo.id}}}},[t._v("提现记录>")])],1),t._v(" "),a("div",{staticClass:"balance"},[a("span",[t._v("奖励余额")]),a("em",[t._v(t._s(t._f("addZero")(t.userMoney))+" "),a("i",[t._v("元")])])])]),t._v(" "),a("div",{staticClass:"indcont"},[a("div",{staticClass:"tab_box"},[a("ul",{staticClass:"title"},[a("li",{class:{active:2==t.cur},on:{click:function(e){t.cur=2}}},[a("span",[t._v("银行卡")])]),t._v(" "),a("li",{class:{active:1==t.cur},on:{click:function(e){t.cur=1}}},[a("span",[t._v("支付宝")])])]),t._v(" "),a("div",{staticClass:"content"},[a("div",{directives:[{name:"show",rawName:"v-show",value:1==t.cur,expression:"cur==1"}]},[a("div",{staticClass:"line"},[a("span",[t._v("支付宝账号")]),t._v(" "),a("div",{staticClass:"bind",on:{click:t.bindAlipayShow}},[t._v(t._s(t.alipay?"更改":"绑定"))]),t._v(" "),a("font",[t._v(t._s(t.alipay))])],1)]),t._v(" "),a("div",{directives:[{name:"show",rawName:"v-show",value:2==t.cur,expression:"cur==2"}]},[a("div",{staticClass:"line"},[a("span",[t._v("银行卡账号")]),t._v(" "),a("div",{staticClass:"bind",on:{click:t.bindAlipayShow}},[t._v(t._s(t.salary_account?"更改":"绑定"))]),t._v(" "),a("font",[t._v(t._s(t.salary_account))])],1)])])]),t._v(" "),a("div",{staticClass:"line"},[a("span",[t._v("提现金额")]),t._v(" "),a("input",{directives:[{name:"model",rawName:"v-model",value:t.lastMoney,expression:"lastMoney"}],attrs:{type:"number",placeholder:"单次最少提现10元"},domProps:{value:t.lastMoney},on:{input:[function(e){e.target.composing||(t.lastMoney=e.target.value)},t.handleInput]}})]),t._v(" "),a("div",{staticClass:"text"},[a("h3",[a("span",[t._v("减服务费后到账：")]),a("em",[a("i",[t._v(t._s(t._f("addZero")(t.lastMoney?t.taxMoney:0)))]),t._v(" 元")])]),t._v(" "),t._m(0),t._v(" "),t._m(1),t._v(" "),a("p",[t._v("3、第一个审核时间为10号：统一审核上月27号~本月10号的提现；")]),t._v(" "),a("p",[t._v("4、第二个审核时间为27号：统一审核本月10号~本月27号的提现；")]),t._v(" "),t._m(2),t._v(" "),t._m(3)]),t._v(" "),a("div",{staticClass:"btn",on:{click:t.otherAddLog}}),t._v(" "),a("div",{staticClass:"checkbox"},[a("van-checkbox",{attrs:{"icon-size":"18px","checked-color":"#ff3e3e"},model:{value:t.checked,callback:function(e){t.checked=e},expression:"checked"}}),t._v(" "),t._m(4)],1)])]),t._v(" "),a("van-popup",{attrs:{round:"",closeable:""},model:{value:t.alipayShow,callback:function(e){t.alipayShow=e},expression:"alipayShow"}},[a("div",{staticClass:"alipay_popup"},[a("div",{staticClass:"title"},[t._v("绑定"+t._s(t.alipayText)+"账号")]),t._v(" "),a("div",{staticClass:"input"},[a("input",{directives:[{name:"model",rawName:"v-model",value:t.alipayVal,expression:"alipayVal"}],attrs:{placeholder:"请输入"+t.alipayText+"账号"},domProps:{value:t.alipayVal},on:{input:function(e){e.target.composing||(t.alipayVal=e.target.value)}}})]),t._v(" "),a("div",{staticClass:"input"},[a("input",{directives:[{name:"model",rawName:"v-model",value:t.alipayConfirmVal,expression:"alipayConfirmVal"}],attrs:{placeholder:"请再次输入"+t.alipayText+"账号"},domProps:{value:t.alipayConfirmVal},on:{input:function(e){e.target.composing||(t.alipayConfirmVal=e.target.value)}}})]),t._v(" "),a("a",{staticClass:"button",on:{click:t.bindAlipay}},[t._v("绑 定")])])])],1)])},staticRenderFns:[function(){var t=this.$createElement,e=this._self._c||t;return e("p",[this._v("1、最低提现金额："),e("i",[this._v("10元")]),this._v("；")])},function(){var t=this.$createElement,e=this._self._c||t;return e("p",[this._v("2、奖励每个月仅限提现"),e("i",[this._v("2")]),this._v("次；")])},function(){var t=this.$createElement,e=this._self._c||t;return e("p",[this._v("5、审核日当天"),e("i",[this._v("18:00")]),this._v("前提交申请，可在当天审核；")])},function(){var t=this.$createElement,e=this._self._c||t;return e("p",[this._v("6、提现审核成功后，预计"),e("i",[this._v("3")]),this._v("个工作日内到账。")])},function(){var t=this.$createElement,e=this._self._c||t;return e("a",{attrs:{href:"https://a119112.oss-cn-beijing.aliyuncs.com/bxl_item/staticHtml/pages/xin_rule_pay_ment.html"}},[this._v("同意"),e("em",[this._v("《共享经济合作伙伴协议》")]),this._v("的所有条款")])}]};var C=a("VU/8")(_,x,!1,function(t){a("KGtz")},"data-v-0b51d9c2",null);e.default=C.exports}});