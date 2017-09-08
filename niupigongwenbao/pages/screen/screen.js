// pages/screen/screen.js
var app = getApp();
Page({

   /**
    * 页面的初始数据
    */
   data: {
      hasLocation: false,
      locationAddress: '选择定位',
      his: true,
      indexs: '0',
      line:'2',
      searchValue:'',
      housetype_list: [
         {
            name: '综合排序',
            id: '0',
         },

         {
            name: '热度',
            id: '1',
         },
         {
            name: '价格最高',
            id: '2',
         },
         {
            name: '价格最低',
            id: '3',
         },
      ],
      housetype_ar: [
         {
            name: '分类',
            // id: '0',
         },

        //  {
        //     name: '热度',
        //     id: '1',
        //  },
        //  {
        //     name: '价格最高',
        //     id: '2',
        //  },
        //  {
        //     name: '价格最低',
        //     id: '3',
        //  },
      ],
    //   array: [
    //      { message: '你好', id: '0', }, { message: '你da', id: '1', }, { message: '你we好', id: '2', },
    //   ],
      array:'',
      house_type: 0,//附近
      // house_style: 0,//全部
      house_area: 0,//智能排行
      tabTxt: [
         {
            name: '综合排序',
            png: '../../images/arrow.png',
            img: 'sr'
         },

         
      ],

      tabTxts: [
         {
            name: '筛选',  
         },
      ],

      //tab文案
      tab: [true, true, true],

      
   },
   searchValueInput: function (e) {
       var value = e.detail.value;
       this.setData({
           searchValue: value,
       });
   },
   doSearch2: function () {
       var searchKey = this.data.searchValue;
    //    console.log(searchKey);
       wx.navigateTo({
           url: '../screen/screen?key=' + searchKey,
       })

      
   },
   /**
    * 生命周期函数--监听页面加载
    */
   onLoad: function (options) {
    //    console.log(options);
      
       var that = this;
       wx.request({
           url: app.d.ceshiUrl + '/Api/Category/getProduct',
           method: 'post',
           data: {
               cate_id: options.cat_id,
               key:options.key,
           },
           header: {
             'Content-Type':'application/x-www-form-urlencoded'
           },

           success: function (res) {
               //--init data 
            //    console.log(res.data.pro);
               var status = res.data.status;
               if (status == 1) {
                    that.setData({
                        pro: res.data.pro,
                        key: res.data.key,
                        catid: res.data.catid,
                        array: res.data.category,
                    });
                   
               }else {
                   that.setData({
                       array: res.data.category,
                   });
                   wx.showToast({
                       title: res.data.err,
                       duration: 2000,
                   });
               }
           },
           error: function (e) {
               wx.showToast({
                   title: '网络异常！',
                   duration: 2000,
               });
           },

       });

   },
   ping:function(e){
      wx.navigateTo({
          url: '../product/product?productId=' + e.currentTarget.dataset.pro_id,
         success: function(res) {},
         fail: function(res) {},
         complete: function(res) {},
      })
   },
   // 选项卡
   filterTab: function (e) {
      console.log(e)
      var data = [true, true, true],
         index = e.currentTarget.dataset.index;
      data[index] = !this.data.tab[index];
      console.log(index)
      if (index == 0) {
         this.setData({
            tab: data,
            his: false,
            indexs: 11,

         })
      } else {
         this.setData({
            tab: data,
            his: true,
            indexs: 0,
         })
      }
   },

   // 选项卡3
   filterTabs: function (e) {
      console.log(e)
      var data = [true, true, true],
         list = e.currentTarget.dataset.list;
      data[list] = !this.data.tab[list];
      console.log(list)
      if (list == 2) {
         this.setData({
            tab: data,
            his: false,
            line: 11,

         })
      } else {
         this.setData({
            tab: data,
            his: true,
            line: 2,
         })
      }
   },
   // 筛选3——第三级分类
   threes:function(e){
   
       console.log(e);
     var that=this,
     
     tabTxts = this.data.tabTxts,
     id = e.currentTarget.dataset.id,
      txt = e.currentTarget.dataset.txt,
     index = e.currentTarget.dataset.index;
     console.log(index);
      switch (index) {
         case index:
           tabTxts[0].name = txt;
           that.setData({
              tab: [true, true, true],
              tabTxts: tabTxts,
              his: true,
              line: 2,
           });
           break;
         }
      wx.request({
          url: app.d.ceshiUrl + '/Api/Category/getProduct',
          method: 'post',
          data: {
              cate_id: e.currentTarget.dataset.id,
          },
          header: {
              'Content-Type': 'application/x-www-form-urlencoded'
          },

          success: function (res) {
              //--init data 
              //    console.log(res.data.pro);
              var status = res.data.status;
              if (status == 1) {
                  that.setData({
                      pro: res.data.pro,
                      catid: res.data.catid,
                      array: res.data.category,
                  });

              } else {
                  that.setData({
                      array: res.data.category,
                      pro: res.data.pro,
                  });
                  wx.showToast({
                      title: res.data.err,
                      duration: 2000,
                  });
              }
          },
          error: function (e) {
              wx.showToast({
                  title: '网络异常！',
                  duration: 2000,
              });
          },

      });
   },

   // 遮盖层
   choseCondition: function () {
      var data = [true, true, true],
         index = 55;
      data[index] = !this.data.tab[index];
      this.setData({
         tab: data,
         his: true,
      })
   },
   // 地图
   chooseLocation: function (res) {
      console.log(res)
      var that = this
      wx.chooseLocation({
         success: function (res) {
            console.log(res)
            that.setData({
               hasLocation: true,
               locationAddress: res.name
            })
         }
      })
   },
   // 获取筛选项
   getFilter: function () {
      var self = this;
      wx.request({
         url: app.api.condition,
         data: {
            type: 'housetype-style-area'
         },
         header: {
            'Content-Type': 'application/json'
         },
         success: function (res) {
            console.log(res);
            self.getData();
            self.setData({
               filterList: res.data.data
            });
         },
         fail: function () {
            console.log('error!!!!!!!!!!!!!!')
         }
      })
   },
   //筛选项点击操作
   filter: function (e) {
       var that = this;
      console.log(e)
      var self = this,
         id = e.currentTarget.dataset.id,
         txt = e.currentTarget.dataset.txt,
         tabTxt = this.data.tabTxt,
         tabTxts = this.data.tabTxts;
      console.log(e.currentTarget.dataset.index)
      
      
      switch (e.currentTarget.dataset.index) {
         case '0':
            tabTxt[0].name = txt;
            self.setData({
               page: 1,
               data: [],
               tab: [true, true, true],
               tabTxt: tabTxt,
               house_type: id,
               his: true,
               indexs:0,
            });
            console.log(1);
            break;
         case '1':
            tabTxt[1].name = txt;

            self.setData({
               page: 1,
               data: [],
               tab: [true, true, true],
               tabTxt: tabTxt,
               house_style: id,
               his: true,
            });
            break;
         case '2':
            tabTxts[0].name = txt;
            self.setData({
               // tab: [true, true, true],
               tabTxts: tabTxts,
               house_area: id,
               // his: true,
               line: 2,
            });
            break;
      }
      
      //数据筛选
    //   self.loadShopData();
      switch (e.currentTarget.dataset.id){
        case '0':
            if(this.data.key=='undefined'){
                this.setData({
                    key:'',
                });
            }
            if (this.data.catid == 'undefined') {
                this.setData({
                    catid: '',
                });
            }
            wx.request({
                url: app.d.ceshiUrl + '/Api/Category/getProduct',
                data: {
                    num: '0.1',
                    key: this.data.key,
                    catid: this.data.catid,
                },
                header: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                success: function (res) {
                    var status = res.data.status;
                    if (status == 1) {
                        that.setData({
                            pro: res.data.pro,
                            key: res.data.key,
                            catid: res.data.catid,
                        });

                    } else {
                        wx.showToast({
                            title: res.data.err,
                            duration: 2000,
                        });
                    }
                }
            });
            break;
        case '1':
            if (this.data.key == 'undefined') {
                this.setData({
                    key: '',
                });
            }
            if (this.data.catid == 'undefined') {
                this.setData({
                    catid: '',
                });
            }
            wx.request({
                url: app.d.ceshiUrl + '/Api/Category/getProduct',
                data: {
                    num: '1',
                    key: this.data.key,
                    catid: this.data.catid,
                },
                header: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                success: function (res) {
                    var status = res.data.status;
                    if (status == 1) {
                        that.setData({
                            pro: res.data.pro,
                            key: res.data.key,
                            catid: res.data.catid,
                        });

                    } else {
                        wx.showToast({
                            title: res.data.err,
                            duration: 2000,
                        });
                    }
                }
            });
            break;
        case '2':
            if (this.data.key == 'undefined') {
                this.setData({
                    key: '',
                });
            }
            if (this.data.catid == 'undefined') {
                this.setData({
                    catid: '',
                });
            }
            wx.request({
                url: app.d.ceshiUrl + '/Api/Category/getProduct',
                data: {
                    num: '2',
                    key: this.data.key,
                    catid: this.data.catid,
                },
                header: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                success: function (res) {
                    var status = res.data.status;
                    if (status == 1) {
                        that.setData({
                            pro: res.data.pro,
                            key: res.data.key,
                            catid: res.data.catid,
                        });

                    } else {
                        wx.showToast({
                            title: res.data.err,
                            duration: 2000,
                        });
                    }
                }
            });
            break;
        case '3':
            if (this.data.key == 'undefined') {
                this.setData({
                    key: '',
                });
            }
            if (this.data.catid == 'undefined') {
                this.setData({
                    catid: '',
                });
            }
            wx.request({
                url: app.d.ceshiUrl + '/Api/Category/getProduct',
                data: {
                    num: '3',
                    key: this.data.key,
                    catid: this.data.catid,
                },
                header: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                success: function (res) {
                    var status = res.data.status;
                    if (status == 1) {
                        that.setData({
                            pro: res.data.pro,
                            key: res.data.key,
                            catid: res.data.catid,
                        });

                    } else {
                        wx.showToast({
                            title: res.data.err,
                            duration: 2000,
                        });
                    }
                }
            });
            break;
    }
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
   onShareAppMessage: function () {

   }
})