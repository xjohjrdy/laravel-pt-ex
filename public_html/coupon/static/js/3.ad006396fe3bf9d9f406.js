webpackJsonp([3],{"3sdn":function(t,e){},"5Hoj":function(t,e){},PGkQ:function(t,e,n){"use strict";Object.defineProperty(e,"__esModule",{value:!0});var i=n("Xxa5"),s=n.n(i),o=(n("eqfM"),n("/QYm")),a=n("exGp"),r=n.n(a),u=n("bOdI"),c=n.n(u),l=(n("nsZj"),n("WpgC"),n("5Hoj"),n("RfZZ")),h=n("o69Z"),d=n("RP/J"),f=n("V+2B"),m={title:String,loading:Boolean,showToolbar:Boolean,cancelButtonText:String,confirmButtonText:String,allowHtml:{type:Boolean,default:!0},visibleItemCount:{type:[Number,String],default:5},itemHeight:{type:[Number,String],default:44},swipeDuration:{type:[Number,String],default:1e3}},p=n("pIDD"),v=n("AA6R"),g=n.n(v),b=n("54/E");function x(t){return Array.isArray(t)?t.map(function(t){return x(t)}):"object"==typeof t?Object(b.a)({},t):t}function _(t,e,n){return Math.min(Math.max(t,e),n)}var y=n("vwLT"),C=Object(h.b)("picker-column"),I=C[0],O=C[1];function T(t){return Object(h.f)(t)&&t.disabled}var w,k=I({mixins:[y.a],props:{valueKey:String,allowHtml:Boolean,className:String,itemHeight:[Number,String],defaultIndex:Number,swipeDuration:[Number,String],visibleItemCount:[Number,String],initialOptions:{type:Array,default:function(){return[]}}},data:function(){return{offset:0,duration:0,options:x(this.initialOptions),currentIndex:this.defaultIndex}},created:function(){this.$parent.children&&this.$parent.children.push(this),this.setIndex(this.currentIndex)},mounted:function(){this.bindTouchEvent(this.$el)},destroyed:function(){var t=this.$parent.children;t&&t.splice(t.indexOf(this),1)},watch:{initialOptions:"setOptions",defaultIndex:function(t){this.setIndex(t)}},computed:{count:function(){return this.options.length},baseOffset:function(){return this.itemHeight*(this.visibleItemCount-1)/2}},methods:{setOptions:function(t){JSON.stringify(t)!==JSON.stringify(this.options)&&(this.options=x(t),this.setIndex(this.defaultIndex))},onTouchStart:function(t){if(this.touchStart(t),this.moving){var e=function(t){var e=window.getComputedStyle(t),n=e.transform||e.webkitTransform,i=n.slice(7,n.length-1).split(", ")[5];return Number(i)}(this.$refs.wrapper);this.offset=Math.min(0,e-this.baseOffset),this.startOffset=this.offset}else this.startOffset=this.offset;this.duration=0,this.transitionEndTrigger=null,this.touchStartTime=Date.now(),this.momentumOffset=this.startOffset},onTouchMove:function(t){this.touchMove(t),"vertical"===this.direction&&(this.moving=!0,Object(d.c)(t,!0)),this.offset=_(this.startOffset+this.deltaY,-this.count*this.itemHeight,this.itemHeight);var e=Date.now();e-this.touchStartTime>300&&(this.touchStartTime=e,this.momentumOffset=this.offset)},onTouchEnd:function(){var t=this,e=this.offset-this.momentumOffset,n=Date.now()-this.touchStartTime;if(n<300&&Math.abs(e)>15)this.momentum(e,n);else{var i=this.getIndexByOffset(this.offset);this.duration=200,this.setIndex(i,!0),setTimeout(function(){t.moving=!1},0)}},onTransitionEnd:function(){this.stopMomentum()},onClickItem:function(t){this.moving||(this.duration=200,this.setIndex(t,!0))},adjustIndex:function(t){for(var e=t=_(t,0,this.count);e<this.count;e++)if(!T(this.options[e]))return e;for(var n=t-1;n>=0;n--)if(!T(this.options[n]))return n},getOptionText:function(t){return Object(h.f)(t)&&this.valueKey in t?t[this.valueKey]:t},setIndex:function(t,e){var n=this,i=-(t=this.adjustIndex(t)||0)*this.itemHeight,s=function(){t!==n.currentIndex&&(n.currentIndex=t,e&&n.$emit("change",t))};this.moving&&i!==this.offset?this.transitionEndTrigger=s:s(),this.offset=i},setValue:function(t){for(var e=this.options,n=0;n<e.length;n++)if(this.getOptionText(e[n])===t)return this.setIndex(n)},getValue:function(){return this.options[this.currentIndex]},getIndexByOffset:function(t){return _(Math.round(-t/this.itemHeight),0,this.count-1)},momentum:function(t,e){var n=Math.abs(t/e);t=this.offset+n/.002*(t<0?-1:1);var i=this.getIndexByOffset(t);this.duration=+this.swipeDuration,this.setIndex(i,!0)},stopMomentum:function(){this.moving=!1,this.duration=0,this.transitionEndTrigger&&(this.transitionEndTrigger(),this.transitionEndTrigger=null)},genOptions:function(){var t=this,e=this.$createElement,n={height:this.itemHeight+"px"};return this.options.map(function(i,s){var o=t.getOptionText(i),a=T(i),r={style:n,attrs:{role:"button",tabindex:a?-1:0},class:["van-ellipsis",O("item",{disabled:a,selected:s===t.currentIndex})],on:{click:function(){t.onClickItem(s)}}};return t.allowHtml&&(r.domProps={innerHTML:o}),e("li",g()([{},r]),[t.allowHtml?"":o])})}},render:function(){var t=arguments[0],e={transform:"translate3d(0, "+(this.offset+this.baseOffset)+"px, 0)",transitionDuration:this.duration+"ms",transitionProperty:this.duration?"all":"none",lineHeight:this.itemHeight+"px"};return t("div",{class:[O(),this.className]},[t("ul",{ref:"wrapper",style:e,class:O("wrapper"),on:{transitionend:this.onTransitionEnd}},[this.genOptions()])])}}),S=Object(h.b)("picker"),D=S[0],H=S[1],N=S[2],j=D({props:Object(l.a)({},m,{defaultIndex:{type:[Number,String],default:0},columns:{type:Array,default:function(){return[]}},toolbarPosition:{type:String,default:"top"},valueKey:{type:String,default:"text"}}),data:function(){return{children:[],formattedColumns:[]}},computed:{dataType:function(){var t=this.columns[0]||{};return t.children?"cascade":t.values?"object":"text"}},watch:{columns:{handler:"format",immediate:!0}},methods:{format:function(){var t=this.columns,e=this.dataType;"text"===e?this.formattedColumns=[{values:t}]:"cascade"===e?this.formatCascade():this.formattedColumns=t},formatCascade:function(){for(var t=this,e=[],n={children:this.columns};n&&n.children;){var i=n.defaultIndex||+this.defaultIndex;e.push({values:n.children.map(function(e){return e[t.valueKey]}),className:n.className,defaultIndex:i}),n=n.children[i]}this.formattedColumns=e},emit:function(t){"text"===this.dataType?this.$emit(t,this.getColumnValue(0),this.getColumnIndex(0)):this.$emit(t,this.getValues(),this.getIndexes())},onCascadeChange:function(t){for(var e=this,n={children:this.columns},i=this.getIndexes(),s=0;s<=t;s++)n=n.children[i[s]];for(;n.children;)t++,this.setColumnValues(t,n.children.map(function(t){return t[e.valueKey]})),n=n.children[n.defaultIndex||0]},onChange:function(t){"cascade"===this.dataType&&this.onCascadeChange(t),"text"===this.dataType?this.$emit("change",this,this.getColumnValue(0),this.getColumnIndex(0)):this.$emit("change",this,this.getValues(),t)},getColumn:function(t){return this.children[t]},getColumnValue:function(t){var e=this.getColumn(t);return e&&e.getValue()},setColumnValue:function(t,e){var n=this.getColumn(t);n&&n.setValue(e)},getColumnIndex:function(t){return(this.getColumn(t)||{}).currentIndex},setColumnIndex:function(t,e){var n=this.getColumn(t);n&&n.setIndex(e)},getColumnValues:function(t){return(this.children[t]||{}).options},setColumnValues:function(t,e){var n=this.children[t];n&&n.setOptions(e)},getValues:function(){return this.children.map(function(t){return t.getValue()})},setValues:function(t){var e=this;t.forEach(function(t,n){e.setColumnValue(n,t)})},getIndexes:function(){return this.children.map(function(t){return t.currentIndex})},setIndexes:function(t){var e=this;t.forEach(function(t,n){e.setColumnIndex(n,t)})},confirm:function(){this.children.forEach(function(t){return t.stopMomentum()}),this.emit("confirm")},cancel:function(){this.emit("cancel")},genTitle:function(){var t=this.$createElement,e=this.slots("title");return e||(this.title?t("div",{class:["van-ellipsis",H("title")]},[this.title]):void 0)},genToolbar:function(){var t=this.$createElement;if(this.showToolbar)return t("div",{class:[f.a,H("toolbar")]},[this.slots()||[t("button",{attrs:{type:"button"},class:H("cancel"),on:{click:this.cancel}},[this.cancelButtonText||N("cancel")]),this.genTitle(),t("button",{attrs:{type:"button"},class:H("confirm"),on:{click:this.confirm}},[this.confirmButtonText||N("confirm")])]])},genColumns:function(){var t=this,e=this.$createElement;return this.formattedColumns.map(function(n,i){return e(k,{attrs:{valueKey:t.valueKey,allowHtml:t.allowHtml,className:n.className,itemHeight:t.itemHeight,defaultIndex:n.defaultIndex||+t.defaultIndex,swipeDuration:t.swipeDuration,visibleItemCount:t.visibleItemCount,initialOptions:n.values},on:{change:function(){t.onChange(i)}}})})}},render:function(t){var e=+this.itemHeight,n=e*this.visibleItemCount,i={height:e+"px"},s={height:n+"px"},o={backgroundSize:"100% "+(n-e)/2+"px"};return t("div",{class:H()},["top"===this.toolbarPosition?this.genToolbar():t(),this.loading?t(p.a,{class:H("loading")}):t(),this.slots("columns-top"),t("div",{class:H("columns"),style:s,on:{touchmove:d.c}},[this.genColumns(),t("div",{class:H("mask"),style:o}),t("div",{class:[f.b,H("frame")],style:i})]),this.slots("columns-bottom"),"bottom"===this.toolbarPosition?this.genToolbar():t()])}}),B=(n("mMXg"),n("qYlo")),$={name:"",data:function(){return{app_id:"",type:"",product_type:"",active:0,threeList:"",threeDetail:"",accountInput:"",buyNum:"1",showPicker:!1,columns:["1","2","3","4","5","6","7","8","9","10"]}},components:(w={},c()(w,B.a.name,B.a),c()(w,j.name,j),w),mounted:function(){this.app_id=this.$route.query.app_id,this.type=this.$route.query.type,this.getFuluShreeClassify()},computed:{},methods:{getFuluShreeClassify:function(){var t=this;return r()(s.a.mark(function e(){var n,i,a;return s.a.wrap(function(e){for(;;)switch(e.prev=e.next){case 0:return(n=t).bus.$emit("loading",!0),i='{"app_id":"'+n.app_id+'", "type":"'+n.type+'"}',e.next=5,n.ajaxPost("mini_get_fulu_goods_three_classify",i,{});case 5:200==(a=e.sent).data.code?(n.threeList=a.data.data,n.threeDetail=a.data.data[0],n.product_type=a.data.data[0].product_type):Object(o.a)(a.data.msg),n.bus.$emit("loading",!1);case 8:case"end":return e.stop()}},e,t)}))()},choiceLabel:function(t){this.active=t,this.threeDetail=this.threeList[t]},onConfirm:function(t){this.buyNum=t,this.showPicker=!1},submitBuy:function(){var t={app_id:this.app_id,product_id:this.threeDetail.product_id,buyNum:this.buyNum,accountInput:this.accountInput};if("卡密"!=this.threeDetail.product_type){if(""==this.accountInput)return Object(o.a)("请输入您的充值账号！"),!1;this.$router.push({path:"OrderDetail",query:t})}else this.$router.push({path:"OrderDetail",query:t})}}},P={render:function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("div",{staticClass:"wrap"},[n("article",{staticClass:"main goods_detail_box"},[n("div",{staticClass:"detail_top"},[n("div",{staticClass:"price"},[n("span",[n("i",[t._v("￥")]),t._v(t._s(t._f("addZero")(t.threeDetail.purchase_price)))]),t._v(" "),n("em",[t._v("￥"+t._s(t._f("addZero")(t.threeDetail.face_value)))])]),t._v(" "),n("div",{staticClass:"name"},[t._v(t._s(t.threeDetail.product_name))])]),t._v(" "),n("div",{staticClass:"detail_cont"},t._l(t.threeList,function(e,i){return n("div",{key:i,staticClass:"line",class:{active:t.active==i},on:{click:function(e){return t.choiceLabel(i)}}},[n("div",{staticClass:"num"},[e.purchase_price/e.face_value*10<10?n("span",[t._v(t._s((e.purchase_price/e.face_value*10).toFixed(1))+"折")]):t._e(),t._v(" "),n("h3",{staticClass:"overflowTextTwo"},[t._v(t._s(e.product_name))]),t._v(" "),n("p",[t._v(t._s(t._f("addZero")(e.purchase_price))),n("i",[t._v("元")])])])])}),0),t._v(" "),"直充"==t.threeDetail.product_type?n("div",{staticClass:"input_account"},[n("span",[t._v("请输入充值账号")]),t._v(" "),n("input",{directives:[{name:"model",rawName:"v-model",value:t.accountInput,expression:"accountInput"}],attrs:{type:"text",placeholder:"请输入充值账号"},domProps:{value:t.accountInput},on:{input:function(e){e.target.composing||(t.accountInput=e.target.value)}}})]):t._e(),t._v(" "),n("div",{staticClass:"input_account"},[n("span",[t._v("请选择充值数量")]),t._v(" "),n("div",{staticClass:"select",on:{click:function(e){t.showPicker=!0}}},[t._v(t._s(t.buyNum))])]),t._v(" "),n("div",{staticClass:"text_cont"},[t.threeDetail.details?n("div",[n("pre",{domProps:{innerHTML:t._s(t.threeDetail.details)}})]):n("div",[n("h3",[t._v("暂无详情说明...")])])])]),t._v(" "),n("van-popup",{attrs:{round:"",position:"bottom"},model:{value:t.showPicker,callback:function(e){t.showPicker=e},expression:"showPicker"}},[n("van-picker",{attrs:{"show-toolbar":"",columns:t.columns},on:{cancel:function(e){t.showPicker=!1},confirm:t.onConfirm}})],1),t._v(" "),n("footer",[n("div",{staticClass:"foot_btn",on:{click:t.submitBuy}},[t._v("立即购买")])])],1)},staticRenderFns:[]};var V=n("VU/8")($,P,!1,function(t){n("3sdn")},"data-v-38155b1c",null);e.default=V.exports},"V+2B":function(t,e,n){"use strict";n.d(e,"a",function(){return s}),n.d(e,"b",function(){return o});var i="van-hairline",s=i+"--top-bottom",o=i+"-unset--top-bottom"},mMXg:function(t,e,n){"use strict";var i=n("nsZj"),s=(n.n(i),n("02pT")),o=(n.n(s),n("T2s0")),a=(n.n(o),n("1H7Z")),r=(n.n(a),n("hW8u"));n.n(r)},qYlo:function(t,e,n){"use strict";var i=n("o69Z"),s=n("CsZI"),o=n("+2ln"),a=Object(i.b)("popup"),r=a[0],u=a[1];e.a=r({mixins:[Object(s.a)()],props:{round:Boolean,duration:[Number,String],closeable:Boolean,transition:String,safeAreaInsetBottom:Boolean,closeIcon:{type:String,default:"cross"},closeIconPosition:{type:String,default:"top-right"},position:{type:String,default:"center"},overlay:{type:Boolean,default:!0},closeOnClickOverlay:{type:Boolean,default:!0}},beforeCreate:function(){var t=this,e=function(e){return function(n){return t.$emit(e,n)}};this.onClick=e("click"),this.onOpened=e("opened"),this.onClosed=e("closed")},render:function(){var t,e=arguments[0];if(this.shouldRender){var n=this.round,s=this.position,a=this.duration,r="center"===s,c=this.transition||(r?"van-fade":"van-popup-slide-"+s),l={};if(Object(i.d)(a))l[r?"animationDuration":"transitionDuration"]=a+"s";return e("transition",{attrs:{name:c},on:{afterEnter:this.onOpened,afterLeave:this.onClosed}},[e("div",{directives:[{name:"show",value:this.value}],style:l,class:u((t={round:n},t[s]=s,t["safe-area-inset-bottom"]=this.safeAreaInsetBottom,t)),on:{click:this.onClick}},[this.slots(),this.closeable&&e(o.a,{attrs:{role:"button",tabindex:"0",name:this.closeIcon},class:u("close-icon",this.closeIconPosition),on:{click:this.close}})])])}}})}});