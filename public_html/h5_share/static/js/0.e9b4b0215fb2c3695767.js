webpackJsonp([0],{LkCz:function(t,e,i){"use strict";Object.defineProperty(e,"__esModule",{value:!0});var n=i("TQvf"),o=i.n(n),r={name:"",data:function(){return{title:"",app_id:"",itemid:"",article:"",tipsBox:!1,tkmoney:"",many_img:""}},components:{},mounted:function(){this.loadInfo()},methods:{loadInfo:function(){var t=this;t.app_id=this.$route.query.app_id,t.itemid=this.$route.query.itemid;var e={data:'{"app_id":'+t.app_id+', "itemid":'+t.itemid+"}"};t.app_id&&t.itemid&&t.post("share_commodity_part",e).then(function(e){if(200==e.data.code){var i=e.data.data;t.article=i,t.title=i.title,t.many_img=i.many_img[0],t.tkmoney=Math.abs(i.tkmoney_general)}else alert("该优惠商品仅限在葡萄浏览器APP内购买，赶紧注册并下载享受优惠吧！"),t.toRegister()})},toRegister:function(){window.location.href="http://api.36qq.com/api/xin_share_register?id="+this.app_id},copy:function(){var t=this,e=new o.a(".copy_btn");e.on("success",function(i){t.tipsBox=!0,e.destroy(),setTimeout(function(){t.tipsBox=!1},2e3)})}}},a={render:function(){var t=this,e=t.$createElement,i=t._self._c||e;return i("div",{directives:[{name:"wechat-title",rawName:"v-wechat-title",value:this.title,expression:"this.title"}],staticClass:"wrap"},[i("header",[i("div",{staticClass:"share_thead"},[t._m(0),t._v(" "),i("div",{staticClass:"right"},[i("div",{staticClass:"button",on:{click:t.toRegister}},[t._v("立即下载")])]),t._v(" "),i("div",{staticClass:"tip"},[t._v("下载APP后购买再省"+t._s(t.tkmoney)+"元")])])]),t._v(" "),i("article",{staticClass:"main whiteBg"},[i("div",{staticClass:"share_tbox"},[i("div",{staticClass:"img_box"},[i("img",{attrs:{src:t.many_img}}),t._v(" "),t._m(1)]),t._v(" "),i("div",{staticClass:"info_box"},[i("div",{staticClass:"title"},[t._v(t._s(t.article.title))]),t._v(" "),i("div",{staticClass:"bottom"},[i("div",{staticClass:"price"},[i("h4",[i("em",[t._v("券后价 ￥")]),t._v(t._s(t.article.coupon_price)+"\n            ")]),t._v(" "),i("span",[t._v("原价￥"+t._s(t.article.price))])]),t._v(" "),i("div",{staticClass:"coupon"},[i("em",[t._v("￥"+t._s(t.article.coupon))])])])]),t._v(" "),i("div",{staticClass:"copy_box"},[i("div",{staticClass:"button copy_btn",attrs:{"data-clipboard-text":t.article.command},on:{click:t.copy}},[t._v("一键复制")]),t._v(" "),i("div",{staticClass:"text"},[i("p",[t._v(t._s(t.article.command))]),t._v(" "),t._m(2)])])])]),t._v(" "),t.tipsBox?i("div",{staticClass:"tips_box"},[i("p",[t._v("复制成功")])]):t._e()])},staticRenderFns:[function(){var t=this.$createElement,e=this._self._c||t;return e("div",{staticClass:"left"},[e("div",{staticClass:"logo"},[e("img",{attrs:{src:i("Z2Ou")}})]),this._v(" "),e("div",{staticClass:"text"},[e("h3",[this._v("葡萄浏览器")]),this._v(" "),e("p",[this._v("自用省钱 分享赚钱")])])])},function(){var t=this.$createElement,e=this._self._c||t;return e("div",{staticClass:"loading"},[e("div",{staticClass:"icon"})])},function(){var t=this.$createElement,e=this._self._c||t;return e("p",[this._v("\n            点击一键复制，启动【\n            "),e("i",[this._v("淘宝APP")]),this._v("】即可领券购买\n          ")])}]};var c=i("VU/8")(r,a,!1,function(t){i("rxxL")},null,null);e.default=c.exports},TQvf:function(t,e,i){
/*!
 * clipboard.js v2.0.4
 * https://zenorocha.github.io/clipboard.js
 * 
 * Licensed MIT © Zeno Rocha
 */
var n;n=function(){return function(t){var e={};function i(n){if(e[n])return e[n].exports;var o=e[n]={i:n,l:!1,exports:{}};return t[n].call(o.exports,o,o.exports,i),o.l=!0,o.exports}return i.m=t,i.c=e,i.d=function(t,e,n){i.o(t,e)||Object.defineProperty(t,e,{enumerable:!0,get:n})},i.r=function(t){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})},i.t=function(t,e){if(1&e&&(t=i(t)),8&e)return t;if(4&e&&"object"==typeof t&&t&&t.__esModule)return t;var n=Object.create(null);if(i.r(n),Object.defineProperty(n,"default",{enumerable:!0,value:t}),2&e&&"string"!=typeof t)for(var o in t)i.d(n,o,function(e){return t[e]}.bind(null,o));return n},i.n=function(t){var e=t&&t.__esModule?function(){return t.default}:function(){return t};return i.d(e,"a",e),e},i.o=function(t,e){return Object.prototype.hasOwnProperty.call(t,e)},i.p="",i(i.s=0)}([function(t,e,i){"use strict";var n="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t},o=function(){function t(t,e){for(var i=0;i<e.length;i++){var n=e[i];n.enumerable=n.enumerable||!1,n.configurable=!0,"value"in n&&(n.writable=!0),Object.defineProperty(t,n.key,n)}}return function(e,i,n){return i&&t(e.prototype,i),n&&t(e,n),e}}(),r=s(i(1)),a=s(i(3)),c=s(i(4));function s(t){return t&&t.__esModule?t:{default:t}}var l=function(t){function e(t,i){!function(t,e){if(!(t instanceof e))throw new TypeError("Cannot call a class as a function")}(this,e);var n=function(t,e){if(!t)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return!e||"object"!=typeof e&&"function"!=typeof e?t:e}(this,(e.__proto__||Object.getPrototypeOf(e)).call(this));return n.resolveOptions(i),n.listenClick(t),n}return function(t,e){if("function"!=typeof e&&null!==e)throw new TypeError("Super expression must either be null or a function, not "+typeof e);t.prototype=Object.create(e&&e.prototype,{constructor:{value:t,enumerable:!1,writable:!0,configurable:!0}}),e&&(Object.setPrototypeOf?Object.setPrototypeOf(t,e):t.__proto__=e)}(e,a.default),o(e,[{key:"resolveOptions",value:function(){var t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:{};this.action="function"==typeof t.action?t.action:this.defaultAction,this.target="function"==typeof t.target?t.target:this.defaultTarget,this.text="function"==typeof t.text?t.text:this.defaultText,this.container="object"===n(t.container)?t.container:document.body}},{key:"listenClick",value:function(t){var e=this;this.listener=(0,c.default)(t,"click",function(t){return e.onClick(t)})}},{key:"onClick",value:function(t){var e=t.delegateTarget||t.currentTarget;this.clipboardAction&&(this.clipboardAction=null),this.clipboardAction=new r.default({action:this.action(e),target:this.target(e),text:this.text(e),container:this.container,trigger:e,emitter:this})}},{key:"defaultAction",value:function(t){return u("action",t)}},{key:"defaultTarget",value:function(t){var e=u("target",t);if(e)return document.querySelector(e)}},{key:"defaultText",value:function(t){return u("text",t)}},{key:"destroy",value:function(){this.listener.destroy(),this.clipboardAction&&(this.clipboardAction.destroy(),this.clipboardAction=null)}}],[{key:"isSupported",value:function(){var t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:["copy","cut"],e="string"==typeof t?[t]:t,i=!!document.queryCommandSupported;return e.forEach(function(t){i=i&&!!document.queryCommandSupported(t)}),i}}]),e}();function u(t,e){var i="data-clipboard-"+t;if(e.hasAttribute(i))return e.getAttribute(i)}t.exports=l},function(t,e,i){"use strict";var n,o="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t},r=function(){function t(t,e){for(var i=0;i<e.length;i++){var n=e[i];n.enumerable=n.enumerable||!1,n.configurable=!0,"value"in n&&(n.writable=!0),Object.defineProperty(t,n.key,n)}}return function(e,i,n){return i&&t(e.prototype,i),n&&t(e,n),e}}(),a=i(2),c=(n=a)&&n.__esModule?n:{default:n};var s=function(){function t(e){!function(t,e){if(!(t instanceof e))throw new TypeError("Cannot call a class as a function")}(this,t),this.resolveOptions(e),this.initSelection()}return r(t,[{key:"resolveOptions",value:function(){var t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:{};this.action=t.action,this.container=t.container,this.emitter=t.emitter,this.target=t.target,this.text=t.text,this.trigger=t.trigger,this.selectedText=""}},{key:"initSelection",value:function(){this.text?this.selectFake():this.target&&this.selectTarget()}},{key:"selectFake",value:function(){var t=this,e="rtl"==document.documentElement.getAttribute("dir");this.removeFake(),this.fakeHandlerCallback=function(){return t.removeFake()},this.fakeHandler=this.container.addEventListener("click",this.fakeHandlerCallback)||!0,this.fakeElem=document.createElement("textarea"),this.fakeElem.style.fontSize="12pt",this.fakeElem.style.border="0",this.fakeElem.style.padding="0",this.fakeElem.style.margin="0",this.fakeElem.style.position="absolute",this.fakeElem.style[e?"right":"left"]="-9999px";var i=window.pageYOffset||document.documentElement.scrollTop;this.fakeElem.style.top=i+"px",this.fakeElem.setAttribute("readonly",""),this.fakeElem.value=this.text,this.container.appendChild(this.fakeElem),this.selectedText=(0,c.default)(this.fakeElem),this.copyText()}},{key:"removeFake",value:function(){this.fakeHandler&&(this.container.removeEventListener("click",this.fakeHandlerCallback),this.fakeHandler=null,this.fakeHandlerCallback=null),this.fakeElem&&(this.container.removeChild(this.fakeElem),this.fakeElem=null)}},{key:"selectTarget",value:function(){this.selectedText=(0,c.default)(this.target),this.copyText()}},{key:"copyText",value:function(){var t=void 0;try{t=document.execCommand(this.action)}catch(e){t=!1}this.handleResult(t)}},{key:"handleResult",value:function(t){this.emitter.emit(t?"success":"error",{action:this.action,text:this.selectedText,trigger:this.trigger,clearSelection:this.clearSelection.bind(this)})}},{key:"clearSelection",value:function(){this.trigger&&this.trigger.focus(),window.getSelection().removeAllRanges()}},{key:"destroy",value:function(){this.removeFake()}},{key:"action",set:function(){var t=arguments.length>0&&void 0!==arguments[0]?arguments[0]:"copy";if(this._action=t,"copy"!==this._action&&"cut"!==this._action)throw new Error('Invalid "action" value, use either "copy" or "cut"')},get:function(){return this._action}},{key:"target",set:function(t){if(void 0!==t){if(!t||"object"!==(void 0===t?"undefined":o(t))||1!==t.nodeType)throw new Error('Invalid "target" value, use a valid Element');if("copy"===this.action&&t.hasAttribute("disabled"))throw new Error('Invalid "target" attribute. Please use "readonly" instead of "disabled" attribute');if("cut"===this.action&&(t.hasAttribute("readonly")||t.hasAttribute("disabled")))throw new Error('Invalid "target" attribute. You can\'t cut text from elements with "readonly" or "disabled" attributes');this._target=t}},get:function(){return this._target}}]),t}();t.exports=s},function(t,e){t.exports=function(t){var e;if("SELECT"===t.nodeName)t.focus(),e=t.value;else if("INPUT"===t.nodeName||"TEXTAREA"===t.nodeName){var i=t.hasAttribute("readonly");i||t.setAttribute("readonly",""),t.select(),t.setSelectionRange(0,t.value.length),i||t.removeAttribute("readonly"),e=t.value}else{t.hasAttribute("contenteditable")&&t.focus();var n=window.getSelection(),o=document.createRange();o.selectNodeContents(t),n.removeAllRanges(),n.addRange(o),e=n.toString()}return e}},function(t,e){function i(){}i.prototype={on:function(t,e,i){var n=this.e||(this.e={});return(n[t]||(n[t]=[])).push({fn:e,ctx:i}),this},once:function(t,e,i){var n=this;function o(){n.off(t,o),e.apply(i,arguments)}return o._=e,this.on(t,o,i)},emit:function(t){for(var e=[].slice.call(arguments,1),i=((this.e||(this.e={}))[t]||[]).slice(),n=0,o=i.length;n<o;n++)i[n].fn.apply(i[n].ctx,e);return this},off:function(t,e){var i=this.e||(this.e={}),n=i[t],o=[];if(n&&e)for(var r=0,a=n.length;r<a;r++)n[r].fn!==e&&n[r].fn._!==e&&o.push(n[r]);return o.length?i[t]=o:delete i[t],this}},t.exports=i},function(t,e,i){var n=i(5),o=i(6);t.exports=function(t,e,i){if(!t&&!e&&!i)throw new Error("Missing required arguments");if(!n.string(e))throw new TypeError("Second argument must be a String");if(!n.fn(i))throw new TypeError("Third argument must be a Function");if(n.node(t))return function(t,e,i){return t.addEventListener(e,i),{destroy:function(){t.removeEventListener(e,i)}}}(t,e,i);if(n.nodeList(t))return function(t,e,i){return Array.prototype.forEach.call(t,function(t){t.addEventListener(e,i)}),{destroy:function(){Array.prototype.forEach.call(t,function(t){t.removeEventListener(e,i)})}}}(t,e,i);if(n.string(t))return function(t,e,i){return o(document.body,t,e,i)}(t,e,i);throw new TypeError("First argument must be a String, HTMLElement, HTMLCollection, or NodeList")}},function(t,e){e.node=function(t){return void 0!==t&&t instanceof HTMLElement&&1===t.nodeType},e.nodeList=function(t){var i=Object.prototype.toString.call(t);return void 0!==t&&("[object NodeList]"===i||"[object HTMLCollection]"===i)&&"length"in t&&(0===t.length||e.node(t[0]))},e.string=function(t){return"string"==typeof t||t instanceof String},e.fn=function(t){return"[object Function]"===Object.prototype.toString.call(t)}},function(t,e,i){var n=i(7);function o(t,e,i,o,r){var a=function(t,e,i,o){return function(i){i.delegateTarget=n(i.target,e),i.delegateTarget&&o.call(t,i)}}.apply(this,arguments);return t.addEventListener(i,a,r),{destroy:function(){t.removeEventListener(i,a,r)}}}t.exports=function(t,e,i,n,r){return"function"==typeof t.addEventListener?o.apply(null,arguments):"function"==typeof i?o.bind(null,document).apply(null,arguments):("string"==typeof t&&(t=document.querySelectorAll(t)),Array.prototype.map.call(t,function(t){return o(t,e,i,n,r)}))}},function(t,e){var i=9;if("undefined"!=typeof Element&&!Element.prototype.matches){var n=Element.prototype;n.matches=n.matchesSelector||n.mozMatchesSelector||n.msMatchesSelector||n.oMatchesSelector||n.webkitMatchesSelector}t.exports=function(t,e){for(;t&&t.nodeType!==i;){if("function"==typeof t.matches&&t.matches(e))return t;t=t.parentNode}}}])},t.exports=n()},Z2Ou:function(t,e){t.exports="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAFAAAABQCAYAAACOEfKtAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyFpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNi1jMTQyIDc5LjE2MDkyNCwgMjAxNy8wNy8xMy0wMTowNjozOSAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIChXaW5kb3dzKSIgeG1wTU06SW5zdGFuY2VJRD0ieG1wLmlpZDpDNEFCQjNFMEJENjgxMUU5QTM1MEJFOUIyQkEwQjQ4RSIgeG1wTU06RG9jdW1lbnRJRD0ieG1wLmRpZDpDNEFCQjNFMUJENjgxMUU5QTM1MEJFOUIyQkEwQjQ4RSI+IDx4bXBNTTpEZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSJ4bXAuaWlkOkM0QUJCM0RFQkQ2ODExRTlBMzUwQkU5QjJCQTBCNDhFIiBzdFJlZjpkb2N1bWVudElEPSJ4bXAuZGlkOkM0QUJCM0RGQkQ2ODExRTlBMzUwQkU5QjJCQTBCNDhFIi8+IDwvcmRmOkRlc2NyaXB0aW9uPiA8L3JkZjpSREY+IDwveDp4bXBtZXRhPiA8P3hwYWNrZXQgZW5kPSJyIj8+wUdbfgAAGg1JREFUeNrUXQeYVEW6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"},rxxL:function(t,e){}});