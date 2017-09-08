// pages/user/user.js

var app = getApp();
Page({
   /**
    * 页面的初始数据
    */
   data: {
      userInfo: {},
      groom:'',
   },
  
   /**
    * 生命周期函数--监听页面加载
    */
   onLoad: function () {
      var that = this
      //调用应用实例的方法获取全局数据
      app.getUserInfo(function (userInfo) {
         //更新数据
         that.setData({
            userInfo: userInfo,
            loadingHidden: true
         })
      });
      wx.request({
          url: app.d.ceshiUrl + '/Api/Index/index',
          data: {},
          header: {
              'Content-Type': 'application/x-www-form-urlencoded'
          },
          success: function (res) {
              that.setData({
                  groom: res.data.renqi,
              });
             
          }
      });
      wx.request({
          url: app.d.ceshiUrl + '/Api/User/userinfo',
          data: {
              uid:app.d.userId,
          },
          header: {
              'Content-Type': 'application/x-www-form-urlencoded'
          },
          success: function (res) {
              console.log(res.data.userinfo);
              that.setData({
                  user: res.data.userinfo,
              });

          }
      })
      // this.loadOrderStatus();
   },
   bin: function () {
      wx.navigateTo({
         url: '../plan/plan',
         success: function (res) { },
         fail: function (res) { },
         complete: function (res) { },
      })
   },
   /**
    * 生命周期函数--监听页面初次渲染完成
    */
   onReady: function () {

   },

   /**
    * 生命周期函数--监听页面显示
    */
   onShow: function () {

   },

   /**
    * 生命周期函数--监听页面隐藏
    */
   onHide: function () {

   },

   /**
    * 生命周期函数--监听页面卸载
    */
   onUnload: function () {

   },

   /**
    * 页面相关事件处理函数--监听用户下拉动作
    */
   onPullDownRefresh: function () {

   },

   /**
    * 页面上拉触底事件的处理函数
    */
   onReachBottom: function () {

   },

   /**
    * 用户点击右上角分享
    */
  //  onShareAppMessage: function () {

  //  }
})