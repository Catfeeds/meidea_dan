//index.js
//获取应用实例
var app = getApp()
Page({
  data: {
     imgUrls:'',
     indicatorDots: true,
     autoplay: true,
     interval: 5000,
     duration: 1000,
     circular: true,
     classify:'',
     shop: '',
     renqi:'',
     
     },
   //   搜索
   suo:function(){
      wx.navigateTo({
         url: '../search/search',
         success: function(res) {},
         fail: function(res) {},
         complete: function(res) {},
      })
   },
  //分类跳转
  classify: function(e) {
      var ops= e.currentTarget.dataset.id;
    //var index = e.currentTarget.dataset.index;
    //   var text = this.data.classify[index].text
    wx.navigateTo({
       url: '../classify/classify?ops='+ops,
       success: function(res) {},
       fail: function(res) {},
       complete: function(res) {},
    })
  },
// 优惠劵

  jj: function (e) {
     var id = e.currentTarget.dataset.id;
     console.log(e);
     wx.request({
         url: app.d.ceshiUrl + '/Api/Voucher/get_voucher',
         data: {
            uid: app.d.userId,
            vid: id,
         },
         header: {
                 'Content-Type': 'application/x-www-form-urlencoded'
         },
         success: function (res) {
             var status = res.data.status;
             if(status == 1){
                 wx.showToast({
                     title: '已领取',
                     icon: '',
                     image: '',
                     duration: 0,
                     mask: true,
                     success: function (res) { },
                     fail: function (res) { },
                     complete: function (res) { },
                 })
             }else{
                 wx.showToast({
                     title: res.data.err,
                     duration: 2000,
                 });
             }
            
         }
     })
     


  },





  onLoad: function () {
    // console.log('onLoad')
    // console.log(app.d.userId);
    var that = this
    //获取首页信息
    wx.request({
        url: app.d.ceshiUrl + '/Api/Index/index',
        header: {
            'content-type': 'application/json'
        },
        success: function (res) {
            that.setData({
                imgUrls: res.data.ggtop,
                classify: res.data.procat,
                shop: res.data.shop,
                renqi: res.data.renqi,
                tubiao: res.data.tubiao,
            })
        }
    }),
    //调用应用实例的方法获取全局数据
    app.getUserInfo(function(userInfo){
      //更新数据
      that.setData({
        userInfo:userInfo
      })
    })
  },
  onShareAppMessage: function () {
      return {
          title: '牛皮公文包',
          path: '/pages/index/index',
          success: function (res) {
              // 分享成功
          },
          fail: function (res) {
              // 分享失败
          }
      }
  }
})
