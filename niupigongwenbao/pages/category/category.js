// import ApiList from  '../../config/api';
// import request from '../../utils/request.js';
//获取应用实例  
var app = getApp();
Page({
    data: {
        // types: null,
        typeTree: {}, // 数据缓存
        currType: 0 ,
        // 当前类型
        "types": [
        ],
        typeTree: [],
        ping:[{},{},{}],
    },
        
    onLoad: function (option){
       console.log(option)
        var that = this;
        wx.request({
            url: app.d.ceshiUrl + '/Api/Category/index',
            method:'post',
            data: {},
            header: {
                'Content-Type':  'application/x-www-form-urlencoded'
            },
            success: function (res) {
                console.log(res.data.hot)
                //--init data 
                var status = res.data.status;
                if(status==1) { 
                    var list = res.data.list;
                    var catList = res.data.catList;
                    that.setData({
                        types:list,
                        typeTree:catList,
                        hot:res.data.hot,
                    });
                } else {
                    wx.showToast({
                        title:res.data.err,
                        duration:2000,
                    });
                }
            that.setData({
                     currType: 16
               });    
      console.log(list)
      console.log(catList)
            },
            error:function(e){
                wx.showToast({
                    title:'网络异常！',
                    duration:2000,
                });
            },

        });
    },    
 
    // 点击商品详情事件
    ping: function (e) {
       wx.navigateTo({
           url: '../product/product?productId='+ e.currentTarget.dataset.pro_id,
          success: function (res) { },
          fail: function (res) { },
          complete: function (res) { },
       })
    },

// 产品加载
    tapType: function (e){
        var that = this;
        const currType = e.currentTarget.dataset.typeId;

        that.setData({
            currType: currType
        });
        console.log(currType);
        wx.request({
            url: app.d.ceshiUrl + '/Api/Category/getcat',
            method:'post',
            data: {cat_id:currType},
            header: {
                'Content-Type':  'application/x-www-form-urlencoded'
            },
            success: function (res) {
               console.log(res)
                var status = res.data.status;
                if(status==1) { 
                    var catList = res.data.catList;
                    that.setData({
                        typeTree:catList,
                        hot:res.data.hot,
                    });
                } else {
                    wx.showToast({
                        title:res.data.err,
                        duration:2000,
                    });
                }
            },
            error:function(e){
                wx.showToast({
                    title:'网络异常！',
                    duration:2000,
                });
            }
        });
    },
})