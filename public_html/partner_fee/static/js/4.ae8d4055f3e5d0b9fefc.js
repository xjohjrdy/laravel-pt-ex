webpackJsonp([4],{I7eg:function(t,e){},Ly8w:function(t,e){},gN7t:function(t,e,i){"use strict";Object.defineProperty(e,"__esModule",{value:!0});var s,a=i("Xxa5"),n=i.n(a),o=(i("eqfM"),i("/QYm")),r=i("exGp"),c=i.n(r),l=i("bOdI"),u=i.n(l),h=(i("yffH"),i("sdMh")),d=(i("jAcA"),i("86U2")),v=(i("yIEv"),i("OIh9")),g={name:"",data:function(){return{title:"",app_id:"",uType:"",active:3,isMore:!1,isLoading:!1,loadText:"",detailList:[],lastPage:1,page:1,navList:[]}},components:(s={},u()(s,v.a.name,v.a),u()(s,d.a.name,d.a),u()(s,h.a.name,h.a),s),created:function(){this.app_id=this.$route.query.app_id,this.active=Number(this.$route.query.type),this.uType=this.$route.query.uType,this.navList=this.$route.query.navList;var t=["爆款商城","淘宝","京东","拼多多","饿了么","办信用卡"];0==this.uType?(this.title="奖励明细",this.navList=t.slice(0,5)):(this.title="公司奖励明细",this.navList=t.slice(0,6)),this.getOutListDetail(this.active)},mounted:function(){window.addEventListener("scroll",this.handleScroll,!0)},beforeDestroy:function(){window.removeEventListener("scroll",this.handleScroll,!0)},methods:{getOutListDetail:function(t){var e=this;return c()(n.a.mark(function i(){var s,a,r,c,l,u;return n.a.wrap(function(i){for(;;)switch(i.prev=i.next){case 0:if((s=e).isMore=!1,s.bus.$emit("loading",!0),s.loadText="加载中...",s.active=t,a=s.detailList,t>=4&&t++,r='{"app_id": "'+s.app_id+'", "type": "'+(Number(t)+1)+'"}',0!=s.uType){i.next=14;break}return i.next=11,s.ajaxPost("mini_get_all_team_orders_data?page="+s.page,r,{});case 11:c=i.sent,i.next=17;break;case 14:return i.next=16,s.ajaxPost("mini_wu_out_list_detail?page="+s.page,r,{});case 16:c=i.sent;case 17:if(200==c.data.code){for(l=c.data.data,u=0;u<l.data.length;u++)a.push(l.data[u]);0==s.uType?(s.lastPage=l.lastPage,s.page=l.currentPage):(s.lastPage=l.last_page,s.page=l.current_page),s.isMore=!0}else s.isMore=!0,Object(o.a)(c.data.msg);s.bus.$emit("loading",!1),s.loadText="~已经到底咯~",s.isLoading&&(Object(o.a)("刷新成功"),s.isLoading=!1);case 21:case"end":return i.stop()}},i,e)}))()},changeTabs:function(t){this.detailList=[],this.lastPage=1,this.page=1,this.getOutListDetail(t)},onRefresh:function(){this.detailList=[],this.lastPage=1,this.page=1,this.getOutListDetail(this.active)},handleScroll:function(t){this.isMore&&t.target.scrollHeight<=t.target.scrollTop+t.target.clientHeight+10&&"list_content"==t.target.className&&(this.lastPage>this.page?(this.page++,this.getOutListDetail(this.active)):this.loadText="~已经到底咯~")}}},p={render:function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("div",{directives:[{name:"wechat-title",rawName:"v-wechat-title",value:t.title,expression:"title"}],staticClass:"wrap"},[i("article",{staticClass:"main"},[i("van-tabs",{attrs:{color:0==t.uType?"#ff3e3e":"#ff7400","title-inactive-color":"#666","title-active-color":0==t.uType?"#ff3e3e":"#ff7400","line-width":"0.7rem","line-height":".05rem",animated:"",swipeable:""},on:{change:t.changeTabs},model:{value:t.active,callback:function(e){t.active=e},expression:"active"}},t._l(t.navList,function(e,s){return i("van-tab",{key:s,attrs:{title:e,"title-style":0==t.uType?"padding:0 .12rem;margin:0 0 0 0.53333rem;":""}},[i("div",{staticClass:"list_content"},[i("van-pull-refresh",{on:{refresh:t.onRefresh},model:{value:t.isLoading,callback:function(e){t.isLoading=e},expression:"isLoading"}},[t.detailList.length>0?i("div",[i("div",{staticClass:"reward_box"},[i("div",{staticClass:"title"},[i("em",[t._v("日期")]),t._v(" "),0==t.uType?i("span",[t._v("预估奖励")]):i("span",[t._v("预估公司奖励")])]),t._v(" "),t._l(t.detailList,function(e,s){return i("div",{key:s,staticClass:"line"},[i("em",[t._v(t._s(e.created_at))]),t._v(" "),0==t.uType?i("div",[0==e.status?i("span",{staticClass:"not"},[i("i",[t._v("￥"+t._s(t._f("addZero")(0==t.active?e.money:e.tkmoney_vip)))]),t._v("(已失效)")]):i("span",{staticStyle:{color:"#ff3e3e"}},[t._v("￥"+t._s(t._f("addZero")(0==t.active?e.money:e.tkmoney_vip)))])]):i("div",[0==e.status?i("span",[t._v("￥"+t._s(t._f("addZero")(e.money)))]):i("span",{staticClass:"not"},[i("i",[t._v("￥"+t._s(t._f("addZero")(e.money)))]),t._v("(已失效)")])])])})],2),t._v(" "),i("div",{staticClass:"list_bottom"},[t._v(t._s(t.loadText))])]):i("div",[t.isMore?i("div",{staticClass:"data_null"},[i("p",[t._v("~暂无记录~")])]):t._e()])])],1)])}),1)],1)])},staticRenderFns:[]};var f=i("VU/8")(g,p,!1,function(t){i("Ly8w")},"data-v-1e98f1de",null);e.default=f.exports},sdMh:function(t,e,i){"use strict";var s=i("o69Z"),a=i("RP/J"),n=i("JOBW"),o=i("vwLT"),r=i("pIDD"),c=Object(s.b)("pull-refresh"),l=c[0],u=c[1],h=c[2],d=["pulling","loosing","success"];e.a=l({mixins:[o.a],props:{disabled:Boolean,successText:String,pullingText:String,loosingText:String,loadingText:String,value:{type:Boolean,required:!0},successDuration:{type:[Number,String],default:500},animationDuration:{type:[Number,String],default:300},headHeight:{type:[Number,String],default:50}},data:function(){return{status:"normal",distance:0,duration:0}},computed:{touchable:function(){return"loading"!==this.status&&"success"!==this.status&&!this.disabled},headStyle:function(){if(50!==this.headHeight)return{height:this.headHeight+"px"}}},watch:{value:function(t){this.duration=this.animationDuration,t?this.setStatus(+this.headHeight,!0):this.slots("success")||this.successText?this.showSuccessTip():this.setStatus(0,!1)}},mounted:function(){this.bindTouchEvent(this.$refs.track),this.scrollEl=Object(n.d)(this.$el)},methods:{checkPullStart:function(t){this.ceiling=0===Object(n.c)(this.scrollEl),this.ceiling&&(this.duration=0,this.touchStart(t))},onTouchStart:function(t){this.touchable&&this.checkPullStart(t)},onTouchMove:function(t){this.touchable&&(this.ceiling||this.checkPullStart(t),this.touchMove(t),this.ceiling&&this.deltaY>=0&&"vertical"===this.direction&&(Object(a.c)(t),this.setStatus(this.ease(this.deltaY))))},onTouchEnd:function(){var t=this;this.touchable&&this.ceiling&&this.deltaY&&(this.duration=this.animationDuration,"loosing"===this.status?(this.setStatus(+this.headHeight,!0),this.$emit("input",!0),this.$nextTick(function(){t.$emit("refresh")})):this.setStatus(0))},ease:function(t){var e=+this.headHeight;return t>e&&(t=t<2*e?e+(t-e)/2:1.5*e+(t-2*e)/4),Math.round(t)},setStatus:function(t,e){var i;i=e?"loading":0===t?"normal":t<this.headHeight?"pulling":"loosing",this.distance=t,i!==this.status&&(this.status=i)},genStatus:function(){var t=this.$createElement,e=this.status,i=this.distance,s=this.slots(e,{distance:i});if(s)return s;var a=[],n=this[e+"Text"]||h(e);return-1!==d.indexOf(e)&&a.push(t("div",{class:u("text")},[n])),"loading"===e&&a.push(t(r.a,{attrs:{size:"16"}},[n])),a},showSuccessTip:function(){var t=this;this.status="success",setTimeout(function(){t.setStatus(0)},this.successDuration)}},render:function(){var t=arguments[0],e={transitionDuration:this.duration+"ms",transform:this.distance?"translate3d(0,"+this.distance+"px, 0)":""};return t("div",{class:u()},[t("div",{ref:"track",class:u("track"),style:e},[t("div",{class:u("head"),style:this.headStyle},[this.genStatus()]),this.slots()])])}})},yffH:function(t,e,i){"use strict";var s=i("nsZj"),a=(i.n(s),i("WpgC")),n=(i.n(a),i("I7eg"));i.n(n)}});