/**
 * Created by xiaohong on 2019/7/26
 */
Vue.http.options.emulateJSON = true;
Vue.http.options.timeout = 10000;

new Vue({
  el: '#app',
  data: {
    isWx: Number(isWx),
    isAndroid: mobileUtil.isAndroid,
//    items: '',
    sign: '',
    appId: '',
  },
  created() {
    var url = window.location.href;
    var objUrl = utils.getUrlParams(url);
    this.sign = objUrl.sign || '';
    this.appId = objUrl.appId || '';
  },
  mounted() {},
  methods: {
    handleJdItem: function (itemId) {
      this.$http.post('/h5/jd/getAc726ItemUrl', {
        sign: this.sign,
        appId: this.appId,
        itemId: itemId,
      }).then(function (response) {
        var data = response.body;
        if (data.status.code == 1001) {
          var result = data.result;

          if (isWx) {
            window.location.href = result.url;
          } else {
            var data= {
              type: 'openJDApp',
              static: {
                url: result.url,
              },
              target: 1,
            };
            shbridge.sqb(data);
          }
        } else {
          utils.toast(data.status.msg);
        }
      }).catch(function () {
        utils.toast('服务器出错了');
      });
    },
    handleShare: function () {
      this.$http.post('/h5/jd/ac726Share', {
        sign: this.sign,
        appId: this.appId,
      }).then(function (response) {
        var data = response.body;
        if (data.status.code == 1001) {
          var result = data.result;

          var data= {
            type: 'share',
            static: {
              src: result.shareImg,
            },
          };
          shbridge.sqb(data);
        } else {
          utils.toast(data.status.msg);
        }
      }).catch(function () {
        utils.toast('服务器出错了');
      });
    },
  },
});