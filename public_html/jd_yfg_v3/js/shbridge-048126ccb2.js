"Use Strict";
(function( global, factory ) {
	/*让js文件，可以符合cmd,amd模式，能被node引入 end*/

	/*创建jQuery式的无new 实例化结构 初始化webview*/
	shbridge = function (el) {

		return new shbridge.prototype.init(el);

	};
	shbridge.prototype.init=!function(selector) {
		// console.log("初始化");
	}();


	shbridge.prototype.init.prototype = shbridge.prototype;
	/*创建jQuery式的无new 实例化结构 end*/

	/*调用WebView*/
	try{
		function connectWebViewJavascriptBridge(callback) {
			if (window.WebViewJavascriptBridge) {
				callback(WebViewJavascriptBridge);
			} else {
				document.addEventListener('WebViewJavascriptBridgeReady', function() {
					callback(WebViewJavascriptBridge);
				}, false);
			}
		}

		connectWebViewJavascriptBridge(function(bridge) {
			bridge.init(function(message, responseCallback) {
				// log('JS got a message', message);
				var data = { 'Javascript Responds':'Wee!' };
				// log('JS responding with', data);
				responseCallback(data);
			});
			bridge.registerHandler('testJavascriptHandler', function(data, responseCallback) {
				// log('ObjC called testJavascriptHandler with', data);
				var responseData = { 'Javascript Says':'Right back atcha!' };
				// log('JS responding with', responseData);
				responseCallback(responseData);
			});
			shbridge.bridge = bridge;
		});
	}catch(e){}

	/*调用WebView end*/
	/*公共的变量*/
	var UA = navigator.userAgent;
	var isxiaoshijie = /xiaoshijie/gi.test(UA);
	var u = {
		isAndroid : /android|adr/gi.test ( UA ) ,
		isIOS : /iphone|ipod|ipad/gi.test ( UA ) && ! this.isAndroid ,
		isBlackBerry : /BlackBerry/i.test ( UA ) ,
		isWindowPhone : /IEMobile/i.test ( UA ) ,
		isMobile : this.isAndroid || this.isIOS || this.isBlackBerry || this.isWindowPhone,
		isWeixin: /MicroMessenger/gi.test ( UA ),
		isQQ:/QQ/gi.test ( UA )
	};

	shbridge.sqb = function(api){
		trycatch('sqb',api);
	};

	/*公共的变量 end*/
	/*公共的方法*/
	function trycatch(name,data,fn){
		if(u.isAndroid){
			try{
				shbridge.bridge.callHandler(name, data , fn);
			}catch(e){
				console.group("异常：");
				console.log(e);
				console.groupEnd("异常：");
			}
		}

		if(u.isIOS){
			try{
				window.webkit.messageHandlers.sqb.postMessage(data);
			}catch (e){
				console.group("异常：");
				console.log(e);
				console.groupEnd("异常：");
			}
		}

	}
	/*公共的方法 end*/

	if ( typeof noGlobal === typeof undefined ) {
		window.shbridge = shbridge;
	}
	return shbridge;
})(window,document);