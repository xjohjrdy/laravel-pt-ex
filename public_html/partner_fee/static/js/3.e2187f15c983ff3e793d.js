webpackJsonp([3],{"3IMD":function(t,e){},"4rJS":function(t,e){},JKiR:function(t,e){},Jlar:function(t,e){},Vj2t:function(t,e,n){"use strict";n.d(e,"a",function(){return r});var i=n("+2ln"),a=n("X6Tt"),o=n("1SJR"),s=n("o69Z"),r=function(t){var e=t.parent,n=t.bem,r=t.role;return{mixins:[Object(o.a)(e),a.a],props:{name:null,value:null,disabled:Boolean,iconSize:[Number,String],checkedColor:String,labelPosition:String,labelDisabled:Boolean,shape:{type:String,default:"round"},bindGroup:{type:Boolean,default:!0}},computed:{disableBindRelation:function(){return!this.bindGroup},isDisabled:function(){return this.parent&&this.parent.disabled||this.disabled},direction:function(){return this.parent&&this.parent.direction||null},iconStyle:function(){var t=this.checkedColor||this.parent&&this.parent.checkedColor;if(t&&this.checked&&!this.isDisabled)return{borderColor:t,backgroundColor:t}},tabindex:function(){return this.isDisabled||"radio"===r&&!this.checked?-1:0}},methods:{onClick:function(t){var e=t.target,n=this.$refs.icon,i=n===e||n.contains(e);this.isDisabled||!i&&this.labelDisabled||this.toggle(),this.$emit("click",t)},genIcon:function(){var t=this.$createElement,e=this.checked,a=this.iconSize||this.parent&&this.parent.iconSize;return t("div",{ref:"icon",class:n("icon",[this.shape,{disabled:this.isDisabled,checked:e}]),style:{fontSize:Object(s.a)(a)}},[this.slots("icon",{checked:e})||t(i.a,{attrs:{name:"success"},style:this.iconStyle})])},genLabel:function(){var t=this.$createElement,e=this.slots();if(e)return t("span",{class:n("label",[this.labelPosition,{disabled:this.isDisabled}])},[e])}},render:function(){var t=arguments[0],e=[this.genIcon()];return"left"===this.labelPosition?e.unshift(this.genLabel()):e.push(this.genLabel()),t("div",{attrs:{role:r,tabindex:this.tabindex,"aria-checked":String(this.checked)},class:n([{disabled:this.isDisabled,"label-disabled":this.labelDisabled},this.direction]),on:{click:this.onClick}},[e])}}}},X6Tt:function(t,e,n){"use strict";n.d(e,"a",function(){return i});var i={inject:{vanField:{default:null}},watch:{value:function(){var t=this.vanField;t&&(t.resetValidation(),t.validateWithTrigger("onChange"))}},created:function(){var t=this.vanField;t&&!t.children&&(t.children=this)}}},XmAh:function(t,e,n){"use strict";var i=n("nsZj"),a=(n.n(i),n("02pT")),o=(n.n(a),n("T2s0")),s=(n.n(o),n("1H7Z")),r=(n.n(s),n("hW8u")),l=(n.n(r),n("WpgC")),c=(n.n(l),n("3IMD")),d=(n.n(c),n("JKiR"));n.n(d)},ZfdV:function(t,e){},il3B:function(t,e,n){"use strict";var i=n("RfZZ"),a=n("7+uW"),o=n("o69Z"),s=n("V+2B"),r=n("CsZI"),l=n("AA6R"),c=n.n(l),d=n("rhik"),u=n("n8HW"),h=n("+2ln"),f=n("pIDD"),p=Object(o.b)("button"),m=p[0],v=p[1];function g(t,e,n,i){var a,o=e.tag,r=e.icon,l=e.type,p=e.color,m=e.plain,g=e.disabled,b=e.loading,y=e.hairline,C=e.loadingText,B={};p&&(B.color=m?p:s.f,m||(B.background=p),-1!==p.indexOf("gradient")?B.border=0:B.borderColor=p);var O,S,k=[v([l,e.size,{plain:m,loading:b,disabled:g,hairline:y,block:e.block,round:e.round,square:e.square}]),(a={},a[s.b]=y,a)];return t(o,c()([{style:B,class:k,attrs:{type:e.nativeType,disabled:g},on:{click:function(t){b||g||(Object(d.a)(i,"click",t),Object(u.a)(i))},touchstart:function(t){Object(d.a)(i,"touchstart",t)}}},Object(d.b)(i)]),[(S=[],b?S.push(t(f.a,{class:v("loading"),attrs:{size:e.loadingSize,type:e.loadingType,color:"currentColor"}})):r&&S.push(t(h.a,{attrs:{name:r},class:v("icon")})),(O=b?C:n.default?n.default():e.text)&&S.push(t("span",{class:v("text")},[O])),S)])}g.props=Object(i.a)({},u.c,{text:String,icon:String,color:String,block:Boolean,plain:Boolean,round:Boolean,square:Boolean,loading:Boolean,hairline:Boolean,disabled:Boolean,nativeType:String,loadingText:String,loadingType:String,tag:{type:String,default:"button"},type:{type:String,default:"default"},size:{type:String,default:"normal"},loadingSize:{type:String,default:"20px"}});var b,y=m(g),C=Object(o.b)("dialog"),B=C[0],O=C[1],S=C[2],k=B({mixins:[Object(r.a)()],props:{title:String,width:[Number,String],message:String,className:null,callback:Function,beforeClose:Function,messageAlign:String,cancelButtonText:String,cancelButtonColor:String,confirmButtonText:String,confirmButtonColor:String,showCancelButton:Boolean,transition:{type:String,default:"van-dialog-bounce"},showConfirmButton:{type:Boolean,default:!0},overlay:{type:Boolean,default:!0},closeOnClickOverlay:{type:Boolean,default:!1}},data:function(){return{loading:{confirm:!1,cancel:!1}}},methods:{onClickOverlay:function(){this.handleAction("overlay")},handleAction:function(t){var e=this;this.$emit(t),this.value&&(this.beforeClose?(this.loading[t]=!0,this.beforeClose(t,function(n){!1!==n&&e.loading[t]&&e.onClose(t),e.loading.confirm=!1,e.loading.cancel=!1})):this.onClose(t))},onClose:function(t){this.close(),this.callback&&this.callback(t)},onOpened:function(){this.$emit("opened")},onClosed:function(){this.$emit("closed")},genButtons:function(){var t,e=this,n=this.$createElement,i=this.showCancelButton&&this.showConfirmButton;return n("div",{class:[s.c,O("footer",{buttons:i})]},[this.showCancelButton&&n(y,{attrs:{size:"large",loading:this.loading.cancel,text:this.cancelButtonText||S("cancel")},class:O("cancel"),style:{color:this.cancelButtonColor},on:{click:function(){e.handleAction("cancel")}}}),this.showConfirmButton&&n(y,{attrs:{size:"large",loading:this.loading.confirm,text:this.confirmButtonText||S("confirm")},class:[O("confirm"),(t={},t[s.a]=i,t)],style:{color:this.confirmButtonColor},on:{click:function(){e.handleAction("confirm")}}})])}},render:function(){var t,e=arguments[0];if(this.shouldRender){var n=this.message,i=this.messageAlign,a=this.slots(),s=this.slots("title")||this.title,r=s&&e("div",{class:O("header",{isolated:!n&&!a})},[s]),l=(a||n)&&e("div",{class:O("content")},[a||e("div",{domProps:{innerHTML:n},class:O("message",(t={"has-title":s},t[i]=i,t))})]);return e("transition",{attrs:{name:this.transition},on:{afterEnter:this.onOpened,afterLeave:this.onClosed}},[e("div",{directives:[{name:"show",value:this.value}],attrs:{role:"dialog","aria-labelledby":this.title||n},class:[O(),this.className],style:{width:Object(o.a)(this.width)}},[r,l,this.genButtons()])])}}});function x(t){return o.g?Promise.resolve():new Promise(function(e,n){var o;b&&(o=b.$el,document.body.contains(o))||(b&&b.$destroy(),(b=new(a.a.extend(k))({el:document.createElement("div"),propsData:{lazyRender:!1}})).$on("input",function(t){b.value=t})),Object(i.a)(b,x.currentOptions,t,{resolve:e,reject:n})})}x.defaultOptions={value:!0,title:"",width:"",message:"",overlay:!0,className:"",lockScroll:!0,transition:"van-dialog-bounce",beforeClose:null,overlayClass:"",overlayStyle:null,messageAlign:"",getContainer:"body",cancelButtonText:"",cancelButtonColor:null,confirmButtonText:"",confirmButtonColor:null,showConfirmButton:!0,showCancelButton:!1,closeOnPopstate:!1,closeOnClickOverlay:!1,callback:function(t){b["confirm"===t?"resolve":"reject"](t)}},x.alert=x,x.confirm=function(t){return x(Object(i.a)({showCancelButton:!0},t))},x.close=function(){b&&(b.value=!1)},x.setDefaultOptions=function(t){Object(i.a)(x.currentOptions,t)},x.resetDefaultOptions=function(){x.currentOptions=Object(i.a)({},x.defaultOptions)},x.resetDefaultOptions(),x.install=function(){a.a.use(k)},x.Component=k,a.a.prototype.$dialog=x;e.a=x},wvq0:function(t,e,n){"use strict";Object.defineProperty(e,"__esModule",{value:!0});var i,a=n("Xxa5"),o=n.n(a),s=n("exGp"),r=n.n(s),l=n("bOdI"),c=n.n(l),d=(n("nsZj"),n("T2s0"),n("1H7Z"),n("4rJS"),n("o69Z")),u=n("Vj2t"),h=Object(d.b)("radio"),f=h[0],p=h[1],m=f({mixins:[Object(u.a)({bem:p,role:"radio",parent:"vanRadio"})],computed:{currentValue:{get:function(){return this.parent?this.parent.value:this.value},set:function(t){(this.parent||this).$emit("input",t)}},checked:function(){return this.currentValue===this.name}},methods:{toggle:function(){this.currentValue=this.name}}}),v=(n("ZfdV"),n("X6Tt")),g=n("1SJR"),b=Object(d.b)("radio-group"),y=b[0],C=b[1],B=y({mixins:[Object(g.b)("vanRadio"),v.a],props:{value:null,disabled:Boolean,direction:String,checkedColor:String,iconSize:[Number,String]},watch:{value:function(t){this.$emit("change",t)}},render:function(){return(0,arguments[0])("div",{class:C([this.direction]),attrs:{role:"radiogroup"}},[this.slots()])}}),O=(n("XmAh"),n("il3B")),S=(n("eqfM"),n("/QYm")),k=n("mF0L"),x={name:"",data:function(){return{app_id:"",userName:"",identityId:"",identitysId:"",radio:"0"}},components:(i={},c()(i,S.a.name,S.a),c()(i,O.a.name,O.a),c()(i,B.name,B),c()(i,m.name,m),i),mounted:function(){this.app_id=this.$route.query.app_id},methods:{subBtn:function(){var t=this;""!=t.userName?""!=t.identityId?/\d{17}[\d|x]|\d{15}/.test(t.identityId)&&(15===t.identityId.length||18===t.identityId.length)||0!=t.radio?t.identityId==t.identitysId?O.a.confirm({title:"提示",message:"是否确认将姓名为“<strong style='color:#1989fa'>"+t.userName+"</strong>”，证件号码为“<strong style='color:#1989fa'>"+t.identityId+"</strong>”的信息用于管理费的提现？ 确认后将不可修改！"}).then(function(){t.addHarryAgreement()}).catch(function(){}):Object(S.a)("您输入的证件号不一致！"):Object(S.a)("您输入的身份证号不正确"):Object(S.a)("请输入您的证件号码"):Object(S.a)("请输入您的姓名")},addHarryAgreement:function(){var t=this;return r()(o.a.mark(function e(){var n,i,a,s,r;return o.a.wrap(function(e){for(;;)switch(e.prev=e.next){case 0:return(n=t).bus.$emit("loading",!0),i=k.b.encodeUnicode(n.userName),a="",a="3"==n.radio?"4":n.radio,s='{"app_id":"'+n.app_id+'", "name":"'+i+'", "citizenship":"'+a+'", "identityId":"'+n.identityId+'"}',e.next=8,n.ajaxPost("out_harry_agreement_put",s,{});case 8:200==(r=e.sent).data.code?(Object(S.a)(r.data.data),setTimeout(function(){n.$router.go(-1)},1500)):Object(S.a)(r.data.msg),n.bus.$emit("loading",!1);case 11:case"end":return e.stop()}},e,t)}))()}}},_={render:function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("div",{staticClass:"wrap"},[n("article",{staticClass:"main"},[n("div",{staticClass:"add_info_box"},[n("div",{staticClass:"line"},[n("p",[t._v("姓名")]),t._v(" "),n("input",{directives:[{name:"model",rawName:"v-model",value:t.userName,expression:"userName"}],attrs:{type:"text",placeholder:"请输入您的姓名"},domProps:{value:t.userName},on:{input:function(e){e.target.composing||(t.userName=e.target.value)}}})]),t._v(" "),n("div",{staticClass:"line"},[n("p",[t._v("证件类型")]),t._v(" "),n("div",{staticClass:"radio"},[n("van-radio-group",{attrs:{"checked-color":"#ff3e3e",direction:"horizontal"},model:{value:t.radio,callback:function(e){t.radio=e},expression:"radio"}},[n("van-radio",{attrs:{name:"0"}},[t._v("中国大陆")]),t._v(" "),n("van-radio",{attrs:{name:"3"}},[t._v("中国港澳台")]),t._v(" "),n("van-radio",{attrs:{name:"4"}},[t._v("海外")])],1)],1)]),t._v(" "),n("div",{staticClass:"line"},[n("p",[t._v("证件号码")]),t._v(" "),n("input",{directives:[{name:"model",rawName:"v-model",value:t.identityId,expression:"identityId"}],attrs:{type:"text",placeholder:"请输入您的证件号码"},domProps:{value:t.identityId},on:{input:function(e){e.target.composing||(t.identityId=e.target.value)}}})]),t._v(" "),n("div",{staticClass:"line"},[n("input",{directives:[{name:"model",rawName:"v-model",value:t.identitysId,expression:"identitysId"}],attrs:{type:"text",placeholder:"请再次输入您的证件号码"},domProps:{value:t.identitysId},on:{input:function(e){e.target.composing||(t.identitysId=e.target.value)}}})]),t._v(" "),t._m(0),t._v(" "),n("div",{staticClass:"btn",on:{click:t.subBtn}})])])])},staticRenderFns:[function(){var t=this.$createElement,e=this._self._c||t;return e("div",{staticClass:"tips"},[e("p",{staticStyle:{color:"#666"}},[this._v("说明：")]),e("p",[this._v("该资料将用于管理费提现，本平台将严格保密您的个人资料")])])}]};var j=n("VU/8")(x,_,!1,function(t){n("Jlar")},"data-v-2acdfd3c",null);e.default=j.exports}});