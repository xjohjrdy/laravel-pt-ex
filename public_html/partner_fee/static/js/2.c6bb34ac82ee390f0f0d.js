webpackJsonp([2],{"/Cm2":function(t,e){},"3X7g":function(t,e,i){"use strict";(function(t){e.b=l,e.a=function(t){o.call(r,t)};var s=i("o69Z"),n=Date.now();var r=s.g?t:window,a=r.requestAnimationFrame||function(t){var e=Date.now(),i=Math.max(0,16-(e-n)),s=setTimeout(t,i);return n=e+i,s},o=r.cancelAnimationFrame||r.clearTimeout;function l(t){return a.call(r,t)}}).call(e,i("DuR2"))},"6gXq":function(t,e){},"86U2":function(t,e,i){"use strict";var s,n=i("o69Z"),r=i("3X7g"),a=i("JOBW");var o=i("n8HW");var l=i("RP/J"),c=i("V+2B"),h=i("1SJR"),u=i("5Fm4"),d=i("Pen3"),f=Object(n.b)("tab"),p=f[0],v=f[1],g=p({props:{dot:Boolean,type:String,info:[Number,String],color:String,title:String,isActive:Boolean,ellipsis:Boolean,disabled:Boolean,scrollable:Boolean,activeColor:String,inactiveColor:String,swipeThreshold:[Number,String]},computed:{style:function(){var t={},e=this.color,i=this.isActive,s="card"===this.type;e&&s&&(t.borderColor=e,this.disabled||(i?t.backgroundColor=e:t.color=e));var n=i?this.activeColor:this.inactiveColor;return n&&(t.color=n),this.scrollable&&this.ellipsis&&(t.flexBasis=88/this.swipeThreshold+"%"),t}},methods:{onClick:function(){this.$emit("click")}},render:function(){var t=arguments[0];return t("div",{attrs:{role:"tab","aria-selected":this.isActive},class:[v({active:this.isActive,disabled:this.disabled,complete:!this.ellipsis}),{"van-ellipsis":this.ellipsis}],style:this.style,on:{click:this.onClick}},[t("span",{class:v("text")},[this.slots()||this.title,t(d.a,{attrs:{dot:this.dot,info:this.info}})])])}}),b=Object(n.b)("sticky"),m=b[0],x=b[1],y=m({mixins:[Object(u.a)(function(t){this.scroller||(this.scroller=Object(a.d)(this.$el)),t(this.scroller,"scroll",this.onScroll,!0),this.onScroll()})],props:{zIndex:[Number,String],container:null,offsetTop:{type:[Number,String],default:0}},data:function(){return{fixed:!1,height:0,transform:0}},computed:{style:function(){if(this.fixed){var t={};return Object(n.d)(this.zIndex)&&(t.zIndex=this.zIndex),this.offsetTop&&this.fixed&&(t.top=this.offsetTop+"px"),this.transform&&(t.transform="translate3d(0, "+this.transform+"px, 0)"),t}}},methods:{onScroll:function(){var t=this;this.height=this.$el.offsetHeight;var e=this.container,i=+this.offsetTop,s=Object(a.c)(window),n=Object(a.a)(this.$el),r=function(){t.$emit("scroll",{scrollTop:s,isFixed:t.fixed})};if(e){var o=n+e.offsetHeight;if(s+i+this.height>o){var l=this.height+s-o;return l<this.height?(this.fixed=!0,this.transform=-(l+i)):this.fixed=!1,void r()}}s+i>n?(this.fixed=!0,this.transform=0):this.fixed=!1,r()}},render:function(){var t=arguments[0],e=this.fixed;return t("div",{style:{height:e?this.height+"px":null}},[t("div",{class:x({fixed:e}),style:this.style},[this.slots()])])}}),S=i("RfZZ"),_=i("vwLT"),T=Object(n.b)("tabs"),C=T[0],O=T[1],w=C({mixins:[_.a],props:{count:Number,duration:[Number,String],animated:Boolean,swipeable:Boolean,currentIndex:Number},computed:{style:function(){if(this.animated)return{transform:"translate3d("+-1*this.currentIndex*100+"%, 0, 0)",transitionDuration:this.duration+"s"}},listeners:function(){if(this.swipeable)return{touchstart:this.touchStart,touchmove:this.touchMove,touchend:this.onTouchEnd,touchcancel:this.onTouchEnd}}},methods:{onTouchEnd:function(){var t=this.direction,e=this.deltaX,i=this.currentIndex;"horizontal"===t&&this.offsetX>=50&&(e>0&&0!==i?this.$emit("change",i-1):e<0&&i!==this.count-1&&this.$emit("change",i+1))},genChildren:function(){var t=this.$createElement;return this.animated?t("div",{class:O("track"),style:this.style},[this.slots()]):this.slots()}},render:function(){return(0,arguments[0])("div",{class:O("content",{animated:this.animated}),on:Object(S.a)({},this.listeners)},[this.genChildren()])}}),I=Object(n.b)("tabs"),j=I[0],k=I[1];e.a=j({mixins:[Object(h.b)("vanTabs"),Object(u.a)(function(t){this.scroller||(this.scroller=Object(a.d)(this.$el)),t(window,"resize",this.resize,!0),this.scrollspy&&t(this.scroller,"scroll",this.onScroll,!0)})],model:{prop:"active"},props:{color:String,sticky:Boolean,animated:Boolean,swipeable:Boolean,scrollspy:Boolean,background:String,lineWidth:[Number,String],lineHeight:[Number,String],titleActiveColor:String,titleInactiveColor:String,type:{type:String,default:"line"},active:{type:[Number,String],default:0},border:{type:Boolean,default:!0},ellipsis:{type:Boolean,default:!0},duration:{type:[Number,String],default:.3},offsetTop:{type:[Number,String],default:0},lazyRender:{type:Boolean,default:!0},swipeThreshold:{type:[Number,String],default:4}},data:function(){return{position:"",currentIndex:null,lineStyle:{backgroundColor:this.color}}},computed:{scrollable:function(){return this.children.length>this.swipeThreshold||!this.ellipsis},navStyle:function(){return{borderColor:this.color,background:this.background}},currentName:function(){var t=this.children[this.currentIndex];if(t)return t.computedName},scrollOffset:function(){return this.sticky?+this.offsetTop+this.tabHeight:0}},watch:{color:"setLine",active:function(t){t!==this.currentName&&this.setCurrentIndexByName(t)},children:function(){var t=this;this.setCurrentIndexByName(this.currentName||this.active),this.setLine(),this.$nextTick(function(){t.scrollIntoView(!0)})},currentIndex:function(){this.scrollIntoView(),this.setLine(),this.stickyFixed&&!this.scrollspy&&Object(a.g)(Math.ceil(Object(a.a)(this.$el)-this.offsetTop))},scrollspy:function(t){t?Object(l.b)(this.scroller,"scroll",this.onScroll,!0):Object(l.a)(this.scroller,"scroll",this.onScroll)}},mounted:function(){this.onShow()},activated:function(){this.onShow(),this.setLine()},methods:{resize:function(){this.setLine()},onShow:function(){var t=this;this.$nextTick(function(){t.inited=!0,t.tabHeight=Object(a.e)(t.$refs.wrap),t.scrollIntoView(!0)})},setLine:function(){var t=this,e=this.inited;this.$nextTick(function(){var i=t.$refs.titles;if(i&&i[t.currentIndex]&&"line"===t.type&&(s=t.$el,r=window.getComputedStyle(s),a="none"===r.display,o=null===s.offsetParent&&"fixed"!==r.position,!a&&!o)){var s,r,a,o,l=i[t.currentIndex].$el,c=t.lineWidth,h=t.lineHeight,u=Object(n.d)(c)?c:l.offsetWidth/2,d=l.offsetLeft+l.offsetWidth/2,f={width:Object(n.a)(u),backgroundColor:t.color,transform:"translateX("+d+"px) translateX(-50%)"};if(e&&(f.transitionDuration=t.duration+"s"),Object(n.d)(h)){var p=Object(n.a)(h);f.height=p,f.borderRadius=p}t.lineStyle=f}})},setCurrentIndexByName:function(t){var e=this.children.filter(function(e){return e.computedName===t}),i=(this.children[0]||{}).index||0;this.setCurrentIndex(e.length?e[0].index:i)},setCurrentIndex:function(t){if(t=this.findAvailableTab(t),Object(n.d)(t)&&t!==this.currentIndex){var e=null!==this.currentIndex;this.currentIndex=t,this.$emit("input",this.currentName),e&&this.$emit("change",this.currentName,this.children[t].title)}},findAvailableTab:function(t){for(var e=t<this.currentIndex?-1:1;t>=0&&t<this.children.length;){if(!this.children[t].disabled)return t;t+=e}},onClick:function(t){var e=this.children[t],i=e.title,s=e.disabled,n=e.computedName;s?this.$emit("disabled",n,i):(this.setCurrentIndex(t),this.scrollToCurrentContent(),this.$emit("click",n,i))},scrollIntoView:function(t){var e=this.$refs.titles;if(this.scrollable&&e&&e[this.currentIndex]){var i=this.$refs.nav,n=e[this.currentIndex].$el;!function(t,e,i){Object(r.a)(s);var n=0,a=t.scrollLeft,o=0===i?1:Math.round(1e3*i/16);!function i(){t.scrollLeft+=(e-a)/o,++n<o&&(s=Object(r.b)(i))}()}(i,n.offsetLeft-(i.offsetWidth-n.offsetWidth)/2,t?0:+this.duration)}},onSticktScroll:function(t){this.stickyFixed=t.isFixed,this.$emit("scroll",t)},scrollToCurrentContent:function(){var t=this;if(this.scrollspy){this.clickedScroll=!0;var e=this.children[this.currentIndex],i=e&&e.$el;if(i)!function(t,e,i){var s=Object(a.b)(),n=s<t,o=0===e?1:Math.round(1e3*e/16),l=(t-s)/o;!function e(){s+=l,(n&&s>t||!n&&s<t)&&(s=t),Object(a.g)(s),n&&s<t||!n&&s>t?Object(r.b)(e):i&&i()}()}(Math.ceil(Object(a.a)(i))-this.scrollOffset,+this.duration,function(){t.clickedScroll=!1})}},onScroll:function(){if(this.scrollspy&&!this.clickedScroll){var t=this.getCurrentIndexOnScroll();this.setCurrentIndex(t)}},getCurrentIndexOnScroll:function(){for(var t=this.children,e=0;e<t.length;e++){if(Object(a.f)(t[e].$el)>this.scrollOffset)return 0===e?0:e-1}return t.length-1}},render:function(){var t,e=this,i=arguments[0],s=this.type,n=this.ellipsis,r=this.animated,a=this.scrollable,l=this.children.map(function(t,r){return i(g,{ref:"titles",refInFor:!0,attrs:{type:s,dot:t.dot,info:t.info,title:t.title,color:e.color,isActive:r===e.currentIndex,ellipsis:n,disabled:t.disabled,scrollable:a,activeColor:e.titleActiveColor,inactiveColor:e.titleInactiveColor,swipeThreshold:e.swipeThreshold},style:t.titleStyle,scopedSlots:{default:function(){return t.slots("title")}},on:{click:function(){e.onClick(r),Object(o.b)(t.$router,t)}}})}),h=i("div",{ref:"wrap",class:[k("wrap",{scrollable:a}),(t={},t[c.d]="line"===s&&this.border,t)]},[i("div",{ref:"nav",attrs:{role:"tablist"},class:k("nav",[s]),style:this.navStyle},[this.slots("nav-left"),l,"line"===s&&i("div",{class:k("line"),style:this.lineStyle}),this.slots("nav-right")])]);return i("div",{class:k([s])},[this.sticky?i(y,{attrs:{container:this.$el,offsetTop:this.offsetTop},on:{scroll:this.onSticktScroll}},[h]):h,i(w,{attrs:{count:this.children.length,animated:r,duration:this.duration,swipeable:this.swipeable,currentIndex:this.currentIndex},on:{change:this.setCurrentIndex}},[this.slots()])])}})},I7eg:function(t,e){},K974:function(t,e,i){"use strict";Object.defineProperty(e,"__esModule",{value:!0});var s,n=i("Xxa5"),r=i.n(n),a=i("exGp"),o=i.n(a),l=i("bOdI"),c=i.n(l),h=(i("eqfM"),i("/QYm")),u=(i("yffH"),i("sdMh")),d=(i("jAcA"),i("86U2")),f=(i("yIEv"),i("OIh9")),p={name:"",data:function(){return{app_id:"",urlType:"",tabTitle:"",active:0,isMore:!1,isLoading:!1,loadText:"",listA:[],listB:[],lastPageA:1,lastPageB:1,page:1}},components:(s={},c()(s,f.a.name,f.a),c()(s,d.a.name,d.a),c()(s,u.a.name,u.a),c()(s,h.a.name,h.a),s),created:function(){this.urlType=this.$route.query.urlType,this.tabTitle=1==this.urlType?"商城今日佣金预估":2==this.urlType?"商城本月佣金预估":"商城累计佣金预估"},mounted:function(){this.app_id=this.$route.query.app_id,this.getMemberCny(),window.addEventListener("scroll",this.handleScroll,!0)},beforeDestroy:function(){window.removeEventListener("scroll",this.handleScroll,!0)},methods:{getMemberCny:function(){var t=this;return o()(r.a.mark(function e(){var i,s,n,a,o;return r.a.wrap(function(e){for(;;)switch(e.prev=e.next){case 0:return(i=t).bus.$emit("loading",!0),i.isMore=!1,i.loadText="加载中...",s='{"app_id": "'+i.app_id+'", "type": "'+i.urlType+'"}',e.next=7,i.ajaxGet("mini_shop_member_cny?page="+i.page,s,{});case 7:if(200==(n=e.sent).data.code){if((a=n.data.data).log.data.length>0)for(o=0;o<a.log.data.length;o++)i.listA.push(a.log.data[o]);else i.loadText="~已经到底咯~";if(a.pretend_credit_log.data.length>0)for(o=0;o<a.pretend_credit_log.data.length;o++)i.listB.push(a.pretend_credit_log.data[o]);else i.loadText="~已经到底咯~";i.lastPageA=a.log.last_page,i.lastPageB=a.pretend_credit_log.last_page,i.lastPageA>i.lastPageB?i.page=a.log.current_page:i.page=a.pretend_credit_log.current_page,i.isMore=!0}else i.isMore=!0,Object(h.a)(n.data.msg);i.bus.$emit("loading",!1),i.loadText="~已经到底咯~",i.isLoading&&(Object(h.a)("刷新成功"),i.isLoading=!1);case 12:case"end":return e.stop()}},e,t)}))()},changeTabs:function(t){},onRefresh:function(){this.listA=[],this.listB=[],this.lastPageA=1,this.lastPageB=1,this.page=1,this.getMemberCny()},handleScroll:function(t){t.target.scrollTop+t.target.clientHeight==t.target.scrollHeight&&(0==this.active?this.lastPageA>this.page?(this.page++,this.getMemberCny()):this.loadText="~已经到底咯~":1==this.active&&(this.lastPageB>this.page?(this.page++,this.getMemberCny()):this.loadText="~已经到底咯~"))}}},v={render:function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("div",{staticClass:"wrap"},[i("article",{staticClass:"main"},[i("van-tabs",{attrs:{"title-inactive-color":"#666","title-active-color":"#ff3e3e",color:"#ff3e3e","line-width":"4.6rem","line-height":"1px",swipeable:"",animated:"","azy-render":""},on:{change:t.changeTabs},model:{value:t.active,callback:function(e){t.active=e},expression:"active"}},[i("van-tab",{attrs:{title:"已到账商城佣金"}},[i("div",{staticClass:"list_content"},[i("van-pull-refresh",{on:{refresh:t.onRefresh},model:{value:t.isLoading,callback:function(e){t.isLoading=e},expression:"isLoading"}},[t.listA.length>0?i("div",{staticClass:"list_item"},[i("ul",{staticClass:"one_list"},t._l(t.listA,function(e,s){return i("li",{key:s},[i("h3",[t._v("订单号："+t._s(e.order_id))]),t._v(" "),i("p",[t._v(t._s(e.created_at))]),t._v(" "),i("div",{staticClass:"price"},[t._v("+"+t._s(e.money)+"元")])])}),0),t._v(" "),i("div",{staticClass:"list_bottom"},[t._v(t._s(t.loadText))])]):i("div",[t.isMore?i("div",{staticClass:"data_null"},[i("p",[t._v("~暂无记录~")])]):t._e()])])],1)]),t._v(" "),i("van-tab",{attrs:{title:t.tabTitle}},[i("div",{staticClass:"list_content"},[i("van-pull-refresh",{on:{refresh:t.onRefresh},model:{value:t.isLoading,callback:function(e){t.isLoading=e},expression:"isLoading"}},[t.listB.length>0?i("div",{staticClass:"list_item"},[i("ul",{staticClass:"two_list"},t._l(t.listB,function(e,s){return i("li",{key:s},[i("h2",[t._v("来自"+t._s(e.from)+"用户")]),t._v(" "),i("h3",[t._v("订单号 "+t._s(e.order_id))]),t._v(" "),i("p",[t._v(t._s(e.created_at))]),t._v(" "),i("div",{staticClass:"price"},[t._v("+"+t._s(e.money)+"元")])])}),0),t._v(" "),i("div",{staticClass:"list_bottom"},[t._v(t._s(t.loadText))])]):i("div",[t.isMore?i("div",{staticClass:"data_null"},[i("p",[t._v("~暂无记录~")])]):t._e()])])],1)])],1)],1)])},staticRenderFns:[]};var g=i("VU/8")(p,v,!1,function(t){i("/Cm2")},"data-v-34eec67b",null);e.default=g.exports},OIh9:function(t,e,i){"use strict";var s=i("RfZZ"),n=i("o69Z"),r=i("1SJR"),a=i("n8HW"),o=Object(n.b)("tab"),l=o[0],c=o[1];e.a=l({mixins:[Object(r.a)("vanTabs")],props:Object(s.a)({},a.c,{dot:Boolean,info:[Number,String],name:[Number,String],title:String,titleStyle:null,disabled:Boolean}),data:function(){return{inited:!1}},computed:{computedName:function(){return Object(n.d)(this.name)?this.name:this.index},isActive:function(){return this.computedName===this.parent.currentName}},watch:{"parent.currentIndex":function(){this.inited=this.inited||this.isActive},title:function(){this.parent.setLine()},inited:function(t){var e=this;this.parent.lazyRender&&t&&this.$nextTick(function(){e.parent.$emit("rendered",e.computedName,e.title)})}},render:function(t){var e=this.slots,i=this.parent,s=this.isActive,n=this.inited||i.scrollspy||!i.lazyRender,r=i.scrollspy||s,a=n?e():t();return i.animated?t("div",{attrs:{role:"tabpanel","aria-hidden":!s},class:c("pane-wrapper",{inactive:!s})},[t("div",{class:c("pane")},[a])]):t("div",{directives:[{name:"show",value:r}],attrs:{role:"tabpanel"},class:c("pane")},[a])}})},S6Ip:function(t,e){},bFPQ:function(t,e){},jAcA:function(t,e,i){"use strict";var s=i("nsZj"),n=(i.n(s),i("T2s0")),r=(i.n(n),i("S6Ip")),a=(i.n(r),i("bFPQ"));i.n(a)},sdMh:function(t,e,i){"use strict";var s=i("o69Z"),n=i("RP/J"),r=i("JOBW"),a=i("vwLT"),o=i("pIDD"),l=Object(s.b)("pull-refresh"),c=l[0],h=l[1],u=l[2],d=["pulling","loosing","success"];e.a=c({mixins:[a.a],props:{disabled:Boolean,successText:String,pullingText:String,loosingText:String,loadingText:String,value:{type:Boolean,required:!0},successDuration:{type:[Number,String],default:500},animationDuration:{type:[Number,String],default:300},headHeight:{type:[Number,String],default:50}},data:function(){return{status:"normal",distance:0,duration:0}},computed:{touchable:function(){return"loading"!==this.status&&"success"!==this.status&&!this.disabled},headStyle:function(){if(50!==this.headHeight)return{height:this.headHeight+"px"}}},watch:{value:function(t){this.duration=this.animationDuration,t?this.setStatus(+this.headHeight,!0):this.slots("success")||this.successText?this.showSuccessTip():this.setStatus(0,!1)}},mounted:function(){this.bindTouchEvent(this.$refs.track),this.scrollEl=Object(r.d)(this.$el)},methods:{checkPullStart:function(t){this.ceiling=0===Object(r.c)(this.scrollEl),this.ceiling&&(this.duration=0,this.touchStart(t))},onTouchStart:function(t){this.touchable&&this.checkPullStart(t)},onTouchMove:function(t){this.touchable&&(this.ceiling||this.checkPullStart(t),this.touchMove(t),this.ceiling&&this.deltaY>=0&&"vertical"===this.direction&&(Object(n.c)(t),this.setStatus(this.ease(this.deltaY))))},onTouchEnd:function(){var t=this;this.touchable&&this.ceiling&&this.deltaY&&(this.duration=this.animationDuration,"loosing"===this.status?(this.setStatus(+this.headHeight,!0),this.$emit("input",!0),this.$nextTick(function(){t.$emit("refresh")})):this.setStatus(0))},ease:function(t){var e=+this.headHeight;return t>e&&(t=t<2*e?e+(t-e)/2:1.5*e+(t-2*e)/4),Math.round(t)},setStatus:function(t,e){var i;i=e?"loading":0===t?"normal":t<this.headHeight?"pulling":"loosing",this.distance=t,i!==this.status&&(this.status=i)},genStatus:function(){var t=this.$createElement,e=this.status,i=this.distance,s=this.slots(e,{distance:i});if(s)return s;var n=[],r=this[e+"Text"]||u(e);return-1!==d.indexOf(e)&&n.push(t("div",{class:h("text")},[r])),"loading"===e&&n.push(t(o.a,{attrs:{size:"16"}},[r])),n},showSuccessTip:function(){var t=this;this.status="success",setTimeout(function(){t.setStatus(0)},this.successDuration)}},render:function(){var t=arguments[0],e={transitionDuration:this.duration+"ms",transform:this.distance?"translate3d(0,"+this.distance+"px, 0)":""};return t("div",{class:h()},[t("div",{ref:"track",class:h("track"),style:e},[t("div",{class:h("head"),style:this.headStyle},[this.genStatus()]),this.slots()])])}})},yIEv:function(t,e,i){"use strict";var s=i("nsZj"),n=(i.n(s),i("6gXq"));i.n(n)},yffH:function(t,e,i){"use strict";var s=i("nsZj"),n=(i.n(s),i("WpgC")),r=(i.n(n),i("I7eg"));i.n(r)}});