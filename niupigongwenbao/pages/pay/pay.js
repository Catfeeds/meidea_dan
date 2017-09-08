var app = getApp();
// pages/order/downline.js
Page({
  data:{
    itemData:{},
    productData:[],
    supplierId:0,
    vid:0,
    productId:0,//proId
    buyCount:0,
    paytype:0,//0线下1微信
    remark:'',
    cartId:0,
    addrId:122,//收货地址//测试--
    btnDisabled:false,
    hui:false,
    one:0
  },
  hui:function(e){
     var i=1;
     if(this.data.one==0){
        this.setData({
           hui: true,
           one:i,
        });
     }
     else{
        this.setData({
           hui: false,
           one: 0,
        });
     }
     this.setData({
         total: this.data.total2,
     });

  },

    onLoad: function (options){
        var cart_id = options.cartId;
        this.setData({
            cartId: cart_id,
        });
    },

  onShow:function(){
      var that = this;
    wx.request({
        url: app.d.ceshiUrl + '/Api/Payment/buy_cart', 
        data: {
            uid: app.d.userId,
            cart_id: that.data.cartId,
        },
        header: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        success: function (res) {
            var adds = res.data.adds;
            if(adds){
              var address_id = adds.id;
            }else{
              var address_id = 0;
            }
            that.setData({
                productData: res.data.pro,
                address: adds,
                address_id: address_id,
                total: res.data.price,
                total2: res.data.price,
            });
            wx.request({
                url: app.d.ceshiUrl + '/Api/Voucher/getQuan2',
                data: {
                    user_id: app.d.userId,
                    cartId: that.data.cartId,
                },
                header: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                success: function (res) {
                    console.log(res.data);
                    that.setData({
                        quan: res.data.quan,
                    });
                }
            });
        }
    });
    
    // this.loadProductDetail();
  },
  loadProductDetail:function(){
    var that = this;
    wx.request({
      url: app.d.ceshiUrl + '/Api/Product/make_order',
      method:'post',
      data: {
        pro_id: that.data.productId,
        uid: app.globalData.userInfo.id,
      },
      header: {
        'Content-Type':  'application/x-www-form-urlencoded'
      },
      success: function (res) {
        console.log(JSON.stringify(res.data));
        //that.initProductData(res.data);
        that.setData({
          productData:res.data.pro,
          address:res.data.address,
          supplierId: res.data.SupplierID,
          total:res.data.pro.price_yh*that.data.buyCount,
        });
        //endInitData
      },
    });
  },
  initProductData: function(data){
    data["LunBoProductImageUrl"] = [];

    var imgs = data.LunBoProductImage.split(';');
    for(let url of imgs){
      url && data["LunBoProductImageUrl"].push(app.d.hostImg + url);
    }

    data.Price = data.Price/100;
    data.VedioImagePath = app.d.hostImg + '/' +data.VedioImagePath;
    data.videoPath = app.d.hostImg + '/' +data.videoPath;
  },
  remarkInput:function(e){
    this.setData({
      remark: e.detail.value,
    })
  },
  createProductOrderByWX:function(e){
    this.setData({
      paytype: 'weixin',
    });

    this.createProductOrder();
  },
  createProductOrderByXX:function(e){
    this.setData({
      paytype: 'cash',
    });

    this.createProductOrder();
  },
  createProductOrder:function(){
    this.setData({
      btnDisabled:false,
    })
    //创建订单
    var that = this;
    console.log(this.data);
    if (!that.data.address){
      wx.showToast({
        title: '请选择收货地址!',
        duration: 2000,
      })
    }
    wx.request({
      url: app.d.ceshiUrl + '/Api/Payment/payment',
      method:'post',
      data: {
        //uid:uid,pid:pro_id,aid:addr_id,sid:shop_id,buff:buff,num:num,price_yh:price_yh,p_price:p_price,price:z_price,type:pay_type,yunfei:yun_id,cart_id:cart_id,remark:ly
        uid:app.d.userId,
        // pid:that.data.productId,//proId
        // sid:that.data.productData.shop_id,//店铺ID
        cart_id:that.data.cartId,
        vid:that.data.vid,
        // num:that.data.buyCount,//购买数量
        paytype:that.data.paytype,//0线下1微信
        //yunfei运费
        aid: that.data.address_id,//地址的id
    
        remark: that.data.remark,//用户备注
        price: that.data.total,//总价
      },
      header: {
        'Content-Type':  'application/x-www-form-urlencoded'
      },
      success: function (res) {
        //--init data        
        var data = res.data;
        console.log(data);
        
        if(data.status == 1){
          //创建订单成功
          if(data.arr.pay_type == 'cash'){
              wx.showToast({
                 title:"请自行联系商家进行发货!",
              })
          }
          if(data.arr.pay_type == 'weixin'){
            //微信支付
            that.wxpay(data.arr);
          }
          //跳转到订单详情//不能跳转此处，因为没有orderID，只能跳转到待支付
          // wx.navigateTo({
          //   url: '/pages/order/detail?orderId='+data.OrderID,
          // });
          // wx.navigateTo({
          //   url: '/pages/user/dingdan?currentTab=0',
          // });
        }else{
          wx.showToast({
              title: res.data.err,
          })
        }
      },
    });

  },
  wxpay: function(order){
      wx.request({
        url: app.d.ceshiUrl + '/Api/Pay/dowxpay',
        data: {
          order_id:order.order_id,
          uid:app.d.userId,
        },
        method: 'POST', // OPTIONS, GET, HEAD, POST, PUT, DELETE, TRACE, CONNECT
        header: {
          'Content-Type':  'application/x-www-form-urlencoded'
        }, // 设置请求的 header
        success: function(res){
          if(res.data.status==1){
            var order=res.data.success;
            console.log(order); 
            wx.requestPayment({
              timeStamp: order.timeStamp,
              nonceStr: order.nonceStr,
              package: order.package,
              signType: 'MD5',
              paySign: order.paySign,
              success: function(res){
                wx.showToast({
                  title:"支付成功!",
                  duration: 2000,
                })
                setTimeout(function(){
                  wx.navigateTo({
                    url: '../user/dingdan?currentTab=1&otype=deliver',
                  })
                },2500);
              },
              fail: function() { 
                wx.showToast({
                  title:"支付失败!",
                })
              }
            })
          }
        },
        fail: function() {
          // fail
        },
        complete: function() {
          // complete
        }
      })
  },
  bindBtnPay:function(){

  },

  radioChange: function (e) {
      console.log(e);
      var amount = e.currentTarget.dataset.value;
        var vid = e.currentTarget.dataset.vid;
        this.setData({
            vid:vid,
        });
        // console.log(amount);
        if(!amount){
            this.setData({
                total: total2,
            });
        }else{
            this.setData({
                total: this.data.total2 - amount,
            });
        }
        
    }

});