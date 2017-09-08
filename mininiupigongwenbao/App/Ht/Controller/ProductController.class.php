<?php
namespace Ht\Controller;
use Think\Controller;
class ProductController extends PublicController{
	//***********************************************
    public static $Array;//这个给检查产品的字段用 
    public static $PRO_FENLEI; //这个给产品分类打勾用
	//**************************************************
	//**********************************************
	//说明：产品列表管理 推荐 修改 删除 列表 搜索
	//**********************************************
	public function index(){
		$aaa_pts_qx=1;
		$id=(int)$_GET['id'];
		$shop_id=(int)$_GET['shop_id'];

		//搜索变量
		$type=$this->htmlentities_u8($_GET['type']);
		$tuijian=$this->htmlentities_u8($_GET['tuijian']);
		$name=$this->htmlentities_u8($_GET['name']);
		//===============================
		// 产品列表信息 搜索
		//===============================
		//搜索
		$where="1=1 AND pro_type=1 AND del<1";
		$tuijian!=='' ? $where.=" AND type=$tuijian" : null;
		$shop_id>0 ? $where.=" AND shop_id=$shop_id" : null;
		$name!='' ? $where.=" AND name like '%$name%'" : null;
		define('rows',20);
		$count=M('product')->where($where)->count();
		$rows=ceil($count/rows);
		$page=(int)$_GET['page'];
		$page<0?$page=0:'';
		$limit=$page*rows;
		$page_index=$this->page_index($count,$rows,$page);
		$productlist=M('product')->where($where)->order('addtime desc')->limit($limit,rows)->select();
		foreach ($productlist as $k => $v) {
			$productlist[$k]['shangchang'] = M('shangchang')->where('id='.intval($v['shop_id']))->getField('name');
			$productlist[$k]['cname'] = M('category')->where('id='.intval($v['cid']))->getField('name');
		}
		//搜索商品属性
		foreach($productlist as $k => $v){
			$attr_value_id = M('attr_spec_price_store')->where('pid="'.$v['id'].'"')->getField('attr_value_id');
			$productlist[$k]['attr'] = M('attr_value')->where('id="'.$attr_value_id.'"')->getField('name');
			$aid = M('attr_value')->where('name="'.$productlist[$k]['attr'].'"')->getField('attr_id');
			$productlist[$k]['attr_name'] = M('attribute')->where('id="'.$aid.'"')->getField('attr_name');
		}

		//搜索商品规格
		foreach($productlist as $k => $v){
			$spec_value_id = M('attr_spec_price_store')->where('pid="'.$v['id'].'"')->getField('spec_value_id');
			$productlist[$k]['spec'] = M('spec_value')->where('id="'.$spec_value_id.'"')->getField('spec_value');
			$sid = M('spec_value')->where('spec_value="'.$productlist[$k]['spec'].'"')->getField('spec_id');
			$productlist[$k]['spec_name'] = M('spec')->where('id="'.$sid.'"')->getField('spec_name');
		}

		//搜索商品小图
		foreach($productlist as $k => $v){
			$productlist[$k]['photo_x'] = M('attr_spec_price_store')->where('pid="'.$v['id'].'"')->getField('photo_x');
		}

		//搜索商品价格
		foreach($productlist as $k => $v){
			$productlist[$k]['price_yh'] = M('attr_spec_price_store')->where('pid="'.$v['id'].'"')->getField('price_yh');
		}

		//==========================
		// 将GET到的数据再输出
		//==========================
		$this->assign('id',$id);
		$this->assign('tuijian',$tuijian);
		$this->assign('name',$name);
		$this->assign('type',$type);
		$this->assign('shop_id',$shop_id);
		$this->assign('page',$page);
		//=============
		// 将变量输出
		//=============	
		$this->assign('productlist',$productlist);
		$this->assign('page_index',$page_index);
		$this->display();
	}
	//**********************************************
	//说明：产品 添加修改
	//注意：cid 分类id  shop_id店铺id
	//**********************************************
	public function add(){	
		$id=(int)$_GET['id'];
		$page=(int)$_GET['page'];
		$name=$_GET['name'];
		$type=$_GET['type'];

		if($_POST['submit']==true){
		try{
			//如果不是管理员则查询商家会员的店铺ID
			$id = intval($_POST['pro_id']);
			$array=array(
				'name'=>$_POST['name'] ,
				'intro'=>$_POST['intro'] ,
				'shop_id'=> intval($_POST['shop_id']) ,//所属店铺
				'cid'=> intval($_POST['cid']) ,			//产品分类ID
				'brand_id'=> intval($_POST['brand_id']) ,//产品品牌ID
				'pro_number'=>$_POST['pro_number'] ,	//产品编号
				'sort'=>(int)$_POST['sort'] , 
				'price_jf'=>(float)$_POST['price_jf'] ,//赠送积分
				'updatetime'=>time(),
				'content'=>$_POST['content'],
				'param' =>$_POST['param'], 
				'company'=>$_POST['company'],  //产品单位
				'pro_type'=>1,
				'renqi' =>intval($_POST['renqi'])
			);
			//判断产品详情页图片是否有设置宽度，去掉重复的100%
			if(strpos($array['content'],'width="100%"')){
				$array['content']=str_replace(' width="100%"','',$array['content']);
			}
			//为img标签添加一个width
			$array['content']=str_replace('alt=""','alt="" width="100%"',$array['content']);
		  
			//上传产品小图
			if (!empty($_FILES["photo_x"]["tmp_name"])) {
					//文件上传
					$info = $this->upload_images($_FILES["photo_x"],array('jpg','png','jpeg'),"product/".date(Ymd));
				    if(!is_array($info)) {// 上传错误提示错误信息
				        $this->error($info);
				        exit();
				    }else{// 上传成功 获取上传文件信息
					    $temp2['photo_x'] = 'UploadFiles/'.$info['savepath'].$info['savename'];
					    $array['photo_x'] = 'UploadFiles/'.$info['savepath'].$info['savename'];
					    $xt = M('attr_spec_price_store')->where('pid='.intval($id))->field('photo_x')->find();
					    if ($id && $xt['photo_x']) {
					    	$img_url = "Data/".$xt['photo_x'];
							if(file_exists($img_url)) {
								@unlink($img_url);
							}
					    }
				    }
			}

			//上传产品大图
			if (!empty($_FILES["photo_d"]["tmp_name"])) {
					//文件上传
					$info = $this->upload_images($_FILES["photo_d"],array('jpg','png','jpeg'),"product/".date(Ymd));
				    if(!is_array($info)) {// 上传错误提示错误信息
				        $this->error($info);
				        exit();
				    }else{// 上传成功 获取上传文件信息
					    $temp2['photo_d'] = 'UploadFiles/'.$info['savepath'].$info['savename'];
					    $dt = M('attr_spec_price_store')->where('pid='.intval($id))->field('photo_d')->find();
					    if ($id && $dt['photo_d']) {
					    	$img_url2 = "Data/".$dt['photo_d'];
							if(file_exists($img_url2)) {
								@unlink($img_url2);
							}
					    }
				    }
			}

			//多张商品轮播图上传
		  	$up_arr = array();
			if (!empty($_FILES["files"]["tmp_name"])) {
					foreach ($_FILES["files"]['name'] as $k => $val) {
						$up_arr[$k]['name'] = $val;
					}

					foreach ($_FILES["files"]['type'] as $k => $val) {
						$up_arr[$k]['type'] = $val;
					}

					foreach ($_FILES["files"]['tmp_name'] as $k => $val) {
						$up_arr[$k]['tmp_name'] = $val;
					}

					foreach ($_FILES["files"]['error'] as $k => $val) {
						$up_arr[$k]['error'] = $val;
					}

					foreach ($_FILES["files"]['size'] as $k => $val) {
						$up_arr[$k]['size'] = $val;
					}
			}
			if ($up_arr) {
					$res=array();
					$adv_str = '';
					foreach ($up_arr as $key => $value) {
						$res = $this->upload_images($value,array('jpg','png','jpeg'),"product/".date(Ymd));
					    if(is_array($res)) {
					    	// 上传成功 获取上传文件信息保存数据库
					    	$adv_str .= ','.'UploadFiles/'.$res['savepath'].$res['savename'];
					    }
					}
					$temp2['photo_string'] = $adv_str;
			}
			
			//判断是否需要操作产品标签
			if($_POST['tag']){
				$temp2['tag_id'] = $_POST['tag'];
			}

			//执行添加
			if(intval($id)>0){
				// $imgs = M('product')->where('id='.intval($id))->getField('photo_string');
				$imgs = M('attr_spec_price_store')->where('pid='.intval($id))->getField('photo_string');
				if ($imgs && $array['photo_string']) {
					$array['photo_string'] = $imgs.$array['photo_string'];
				}

				//将空数据排除掉，防止将原有数据空置
				foreach ($array as $k => $v) {
					if(empty($v)){
					  	unset($v);
					}
				}
				$tempId = M('attr_spec_price_store')->where('pid="'.intval($id).'"')->select();
				$sql = M('product')->where('id='.intval($id))->save($array);
				
				if($sql){
					
					if(!empty($temp2['photo_x'])){
						$temp['photo_x'] = $temp2['photo_x'];
					}else{
						$temp_x = M('attr_spec_price_store')->where('pid="'.intval($id).'"')->select();
						foreach($temp_x as $k => $v){
							$temp['photo_x'] = $v['photo_x'];
						}
						
					}
					if(!empty($temp2['photo_d'])){
						$temp['photo_d'] = $temp2['photo_d'];
					}else{
						$temp_d = M('attr_spec_price_store')->where('pid="'.intval($id).'"')->select();
						foreach($temp_d as $k => $v){
							$temp['photo_d'] = $v['photo_d'];
						}
					}
					if(!empty($temp2['photo_string'])){
						$temp['photo_string'] = $temp2['photo_string']; 
					}else{
						$temp_s = M('attr_spec_price_store')->where('pid="'.intval($id).'"')->select();
						foreach($temp_s as $k => $v){
							$temp['photo_string'] = $v['photo_string'];
						}
					}
					if(!empty($temp2['tag_id'])){
						$temp['tag_id'] = $temp2['tag_id']; 
					}else{
						$temp_t = M('attr_spec_price_store')->where('pid="'.intval($id).'"')->select();
						foreach($temp_t as $k => $v){
							$temp['tag_id'] = $v['tag_id'];
						}
					}
					if(!empty($_POST['attr_value'])){
						M('attr_spec_price_store')->where('pid="'.intval($id).'"')->delete();
						foreach($_POST['price'] as $k => $v){
							$temp['price'] = (float)$_POST['price'][$k];
							$temp['price_yh'] = (float)$_POST['price_yh'][$k];
							$temp['store'] = (float)$_POST['num'][$k];
							$temp['attr_value_id'] = $_POST['attr_value'][$k];
							$temp['spec_value_id'] = $_POST['spec_value'][$k];
							$temp['pid'] = $id;
							M('attr_spec_price_store')->add($temp);
						}

					}else{
						foreach($tempId as $k => $v){
							M('attr_spec_price_store')->where('id="'.intval($v['id']).'"')->save($temp);
						}
						
					}
					
				}
			}else{
				$array['addtime']=time();
				$sql = M('product')->add($array);
				if($sql){
					$temp2['pid'] = $sql;
					foreach($_POST['price'] as $k => $v){
						$temp2['price'] = (float)$_POST['price'][$k];
						$temp2['price_yh'] = (float)$_POST['price_yh'][$k];
						$temp2['store'] = (float)$_POST['num'][$k];
						$temp2['attr_value_id'] = $_POST['attr_value'][$k];
						$temp2['spec_value_id'] = $_POST['spec_value'][$k];
						M('attr_spec_price_store')->add($temp2);
					}
					
				}
				$id=$sql;
			}

			//规格操作
			if($sql){//name="guige_name[]
				$this->success('操作成功!','index');
				exit();
			}else{
				throw new \Exception('操作失败!');
			}
			  
			}catch(\Exception $e){
				echo "<script>alert('".$e->getMessage()."');location='{:U('index')}?shop_id=".$shop_id."';</script>";
			}
		}

		//=========================
		// 查询所有一级产品分类
		//=========================
		$cate_list = M('category')->where('tid=1')->field('id,name')->select();
		$this->assign('cate_list',$cate_list);

		//=========================
		// 查询产品信息
		//=========================
		$pro_allinfo= $id>0 ? M('product')->where('id='.$id)->find() : "";
		$pro_allinfo2= $id>0 ? M('attr_spec_price_store')->where('pid='.intval($id))->select() : array();
		//商场信息
		$shangchang= $pro_allinfo ? M('shangchang')->where('id='.intval($pro_allinfo['shop_id']))->find() : array();
		//产品分类
		$tid = M('category')->where('id='.intval($pro_allinfo['cid']))->getField('tid');
		$pro_allinfo['tid'] = intval($tid);
		if ($tid) {
			$catetwo = M('category')->where('tid='.intval($tid))->field('id,name')->select();
			$this->assign('catetwo',$catetwo);
		}

		//获取所有商品轮播图
		if($pro_allinfo2){
			if ($pro_allinfo2[0]['photo_string']) {
				$img_str = explode(',', trim($pro_allinfo2[0]['photo_string'],','));
				$this->assign('img_str',$img_str);
			}
		}
		
		//获取商品分类
		$c_id = M('product')->where('id="'.intval($id).'"')->getField('cid');
		$t_id = M('category')->where('id="'.$c_id.'"')->getField('tid');
		$this->assign('c_id',$c_id);
		$this->assign('t_id',$t_id);

		//获取所有商品属性
		if ($pro_allinfo2) {
			foreach($pro_allinfo2 as $k => $v){
					$pro_allinfo2[$k]['attr_value'] = M('attr_value')->where('id="'.intval($v['attr_value_id']).'"')->getField('name');
					$pro_allinfo2[$k]['attr_id'] = M('attr_value')->where('id="'.intval($v['attr_value_id']).'"')->getField('attr_id');
				}
		}

		//获取所有商品规格
		if ($pro_allinfo2) {
			foreach($pro_allinfo2 as $k => $v){
					$pro_allinfo2[$k]['spec_value'] = M('spec_value')->where('id="'.intval($v['spec_value_id']).'"')->getField('spec_value');
					$pro_allinfo2[$k]['spec_id'] = M('spec_value')->where('id="'.intval($v['spec_value_id']).'"')->getField('spec_id');
				}
		}

		//=========================
		// 查询所有品牌
		//=========================
		$brand_list = M('brand')->where('1=1')->field('id,name')->select();
		$this->assign('brand_list',$brand_list);

		//=========================
		// 查询所有属性
		//=========================
		$attr_list = M('attribute')->select();
		$this->assign('attr_list',$attr_list);

		//=========================
		// 查询所有规格
		//=========================
		$spec_list = M('spec')->select();
		$this->assign('spec_list',$spec_list);

		//=========================
		// 查询所有标签
		//=========================
		$tag_list = M('tag')->select();
		$this->assign('tag_list',$tag_list);

		//==========================
		// 将GET到的数据再输出
		//==========================
		$this->assign('id',$id);
		$this->assign('name',$name);
		$this->assign('type',$type);
		$this->assign('shop_id',$shop_id);
		$this->assign('page',$page);
		//=============
		// 将变量输出
		//=============	
		$this->assign('pro_allinfo',$pro_allinfo);
		$this->assign('pro_allinfo2',$pro_allinfo2);
		$tag_id = intval($pro_allinfo2[0]['tag_id']);
		$this->assign('tag_id',$tag_id);
		$this->assign('shangchang',$shangchang);

		$this->display();

	}

	/*
	* 商品获取二级分类
	*/
	public function getcid(){
		$cateid = intval($_REQUEST['cateid']);
		$catelist = M('category')->where('tid='.intval($cateid))->field('id,name')->select();
		echo json_encode(array('catelist'=>$catelist));
		exit();
	}

	/*
	* 商品单张图片删除
	*/
	public function img_del(){
		$img_url = trim($_REQUEST['img_url']);
		$pro_id = intval($_REQUEST['pro_id']);
		$check_info = M('attr_spec_price_store')->where('pid="'.intval($pro_id).'"')->find();
		if (!$check_info) {
			echo json_encode(array('status'=>0,'err'=>'产品不存在或已下架删除！'));
			exit();
		}

		$arr = explode(',', trim($check_info['photo_string'],','));
		if (in_array($img_url, $arr)) {
			foreach ($arr as $k => $v) {
				if ($img_url===$v) {
					unset($arr[$k]);
				}
			}
			$data = array();
			$data['photo_string'] = implode(',', $arr);
			$res = M('attr_spec_price_store')->where('pid='.intval($pro_id))->save($data);
			if (!$res) {
				echo json_encode(array('status'=>0,'err'=>'操作失败！'.__LINE__));
				exit();
			}
			//删除服务器上传文件
			$url = "Data/".$img_url;
			if (file_exists($url)) {
				@unlink($url);
			}

			echo json_encode(array('status'=>1));
			exit();
		}else{
			echo json_encode(array('status'=>0,'err'=>'操作失败！'.__LINE__));
			exit();
		}
	}

	//***************************
	//说明：产品 设置推荐
	//***************************
	public function set_tj(){
		$pro_id = intval($_REQUEST['pro_id']);
		$tj_update=M('product')->field('shop_id,type')->where('id='.intval($pro_id).' AND del=0')->find();
		if (!$tj_update) {
			$this->error('产品不存在或已下架删除！');
			exit();
		}

		// $shopinfo = M('shangchang')->where('id='.intval($tj_update['shop_id']))->find();
		//查status,不符合条件不给通过
		// if(intval($shopinfo['status']) != 1) { 
		//     $this->error('商家未通过审核，产品不能设置推荐.');
		//     exit;
		// }

		//查推荐type
		//dump($tj_update);
		$data = array();
		$data['type'] = $tj_update['type']==1 ? 0 : 1;
		$up = M('product')->where('id='.intval($pro_id))->save($data);
		if ($up) {
			$this->redirect('index',array('page'=>intval($_REQUEST['page'])));
			exit();
		}else{
		    $this->error('操作失败！');
			exit();
		}
	}

	//***************************
	//说明：产品 设置新品
	//***************************
	public function set_new(){
		$pro_id = intval($_REQUEST['pro_id']);
		$tj_update=M('product')->field('shop_id,is_show')->where('id='.intval($pro_id).' AND del=0')->find();
		if (!$tj_update) {
			$this->error('产品不存在或已下架删除！');
			exit();
		}

		//查推荐type
		$data = array();
		$data['is_show'] = $tj_update['is_show']==1 ? 0 : 1;
		$up = M('product')->where('id='.intval($pro_id))->save($data);
		if ($up) {
			$this->redirect('index',array('page'=>intval($_REQUEST['page'])));
			exit();
		}else{
		    $this->error('操作失败！');
			exit();
		}
	}

	//***************************
	//说明：产品 删除
	//***************************
	public function del()
	{
		$id = intval($_REQUEST['did']);
		$info = M('product')->where('id='.intval($id))->find();
		if (!$info) {
			$this->error('产品信息错误.'.__LINE__);
			exit();
		}

		if (intval($info['del'])==1) {
			$this->success('操作成功！.'.__LINE__);
			exit();
		}

		$data=array();
		$data['del'] = $info['del'] == '1' ?  0 : 1;
		$data['del_time'] = time();
		$up = M('product')->where('id='.intval($id))->save($data);
		if ($up) {
			$name = M('product')->where('id='.intval($id))->getField('name');
			M('product')->where('name="'.$name.'"')->save($data);
			M('attr_spec_price_store')->where('pid="'.$id.'"')->delete();
			$this->redirect('index',array('page'=>intval($_REQUEST['page'])));
			exit();
		}else{
			$this->error('操作失败.');
			exit();
		}
	}	

	//*************************************************************************
    // 说明：销售统计，发送过来的字段是产品的id  此处不同订单和店铺的销售统计
    //*************************************************************************
    public function access(){
    	$aaa_pts_qx=1;

		$id=(int)$_GET['id'];
		$where="id=".intval($id);
		//检查是否是管理员，然后再查询该产是否下架
		if($_SESSION['admininfo']['qx']!=4){
			$shop_id =(int)M('adminuser')->where('id='.$_SESSION['admininfo']['id'])->getField('shop_id');
			$where .= ' and shop_id='.$shop_id;
		}else{
		     $shop_id=(int)$_GET['shop_id'];
		}
		//检查产品是否已经被删除
		if(intval(M('product')->where($where)->count('id'))<1){
			echo '产品不存在,或已被删除！';
			return;
		}
		$type = $_GET['type'];
	    //for一下到今日为止，为数有多少天或者多少个月，然后canvas就绘画出多少天/月来
		for($i=0;$i<12;$i++){
		  //日期
		  if($type=='m'){
			 $day = strtotime(date('Y-m')) - 86400*30*(11-$i);
			 $dayend = $day+86400*30;
			 $day_String .= ',"'.date('Y/m',$day).'"';
		  }else{
			 $day = strtotime(date('Y-m-d')) - 86400*(11-$i);
			 $dayend = $day+86400; 
			 $day_String .= ',"'.date('m/d',$day).'"';
		  }
		  $ordernum=M('OrderProduct')->where("pid=$id and addtime>$day and addtime<$dayend")->count();
		  $data1.=',['.$i.','.$ordernum.']';
		}

		$today = strtotime(date('Y-m-d'));
		//dump($today);exit;
        //今日订单数
        $today = time(0,0,0);
        $where = "pid=$id and addtime>$today and addtime<".($today+86400);
		$order_today = M('OrderProduct')->where($where)->count();
        //昨日订单数
        $today+=86400;
        $where = "pid=$id and addtime>$today and addtime<".($today+86400);
		$order_yesterday = M('OrderProduct')->where($where)->count();
        //总订单数
		$order_total=M('OrderProduct')->where("pid=$id")->count();
		//==========================
		// 将GET到的数据再输出
		//==========================
		$this->assign('id',$id);
		$this->assign('type',$type);
		//=============
		// 将变量输出
		//=============	
		$this->assign('day_String',$day_String);
		$this->assign('data1',$data1);
		$this->assign('order_today',$order_today);
		$this->assign('order_yesterday',$order_yesterday);
		$this->assign('order_total',$order_total);
		$this->display();

    }

	//**************************************
  	// 说明：产品字段检测
  	//**************************************
	public function check(){
		try 
		{
			
			if(self::$Array['name']==''){
				throw new \Exception('产品名字不能为空！');
			}
			
			if(self::$Array['shop_id']==''){
				throw new \Exception('请选择所属店铺！');
			}
			
			
			//验证开始时间
			/*if(self::$Array['start_time']==''){
				throw new \Exception('开始日期不能为空！');
			}else
			{
			    $start_time=@strtotime(self::$Array['start_time']);
				if(!$start_time){
					throw new \Exception('开始日期格式不正确！');
				}else{
					self::$Array['start_time'] = $start_time;
				}
			}
			
			//验证结束时间
			if(self::$Array['end_time']==''){
				throw new \Exception('结束日期不能为空！');
			}else
			{
			    $end_time=@strtotime(self::$Array['end_time']);
				if($end_time){
					$end_time+=86399;
					if($end_time<$start_time)
					{
					   throw new \Exception('结束日期不能大于开始日期！');
					}
					self::$Array['end_time'] = $end_time;
				}else{
					throw new \Exception('结束日期格式不正确！');
				}
			}*/
			
			return 1;
			
		}
		catch(\Exception $e)
		{
			$this->error_message=$e->getMessage();
			return false;
		}
    }

    //**********************************************
	//说明：商品商品规格
	//**********************************************
	public function pro_guige(){
		$pro_id = intval($_REQUEST['pid']);
		$check_info = M('product')->where('id='.intval($pro_id).' AND del=0')->field('id,pro_buff')->find();
		if (!$check_info) {
			$this->error('产品不存在或已下架删除！');
			exit();
		}


		$guige_list = M('guige')->where('pid='.intval($pro_id))->order('attr_id asc')->select();
		foreach ($guige_list as $k => $v) {
			$guige_list[$k]['attr_name'] = M('attribute')->where('id='.intval($v['attr_id']))->getField('attr_name');
		}

		$this->assign('pro_id',$pro_id);
		$this->assign('pro_info',$check_info);
		$this->assign('guige_list',$guige_list);
		$this->display();
	}

	//**********************************************
	//说明：设置单产品不同规格不同价格不同库存
	//**********************************************
	public function set_attr(){
		$pro_id = intval($_REQUEST['pid']);
		$check_info = M('product')->where('id='.intval($pro_id).' AND del=0')->field('id,name,price_yh,num,company,pro_buff')->find();
		if (!$check_info) {
			$this->error('产品不存在或已下架删除！');
			exit();
		}

		//已有属性
		$guige = M('guige')->where('pid='.intval($pro_id))->select();
		foreach ($guige as $k => $v) {
			$guige[$k]['attr_name'] = M('attribute')->where('id='.intval($v['attr_id']))->getField('attr_name');
		}

		//获取已有规格
		if ($check_info['pro_buff']!='') {
			$pro_buff = explode(',', trim($check_info['pro_buff'],','));
			$buff = array();$buffs = array();
			foreach ($pro_buff as $k => $v) {
				$guige_list = array();
				$buff['attr_id'] = $v;
				$buff['attr_name'] = M('attribute')->where('id='.intval($v))->getField('attr_name');
				$guige_list = M('guige')->where('attr_id='.intval($v).' AND pid='.intval($pro_id))->field('id,name')->select();
				$guige = array();$guige2 = array();
				foreach ($guige_list as $key => $val) {
					$guige['id'] = $val['id'];
					$guige['name'] = $val['name'];
					$guige2[] = $guige;
				}
				$buff['guige_list'] = $guige2;
				$buffs[] = $buff;
			}
		}

		//产品属性管理
		$attr_list = M('attribute')->select();
		$this->assign('pro_id',$pro_id);
		$this->assign('pro_info',$check_info);
		$this->assign('guige',$guige);
		$this->assign('attr_list',$attr_list);
		$this->assign('guige_list',$buffs);
		$this->display();
	}

	//********************************
	//说明：保存产品规格
	//********************************
	public function save_guide(){
		
		$pro_id = intval($_POST['pro_id']);
		$check_info = M('product')->where('id='.intval($pro_id).' AND del=0')->field('id,name,price_yh,num,company,pro_buff')->find();
		if (!$check_info) {
			$this->error('产品不存在或已下架删除！');
			exit();
		}

		if($_POST['attr_name']) {
			//产品规格
			$attribute = $_POST['attr_name'];
			$str = '';
			foreach ($attribute as $k => $v) {
				$checkId = M('attribute')->where('attr_name="'.$v.'" AND pro_id='.intval($pro_id))->getField('id');
				//先添加产品属性
				if (intval($checkId)) {
					$rs = intval($checkId);
				} else {
					$count = M('attribute')->where('pro_id='.intval($pro_id))->getField('COUNT(id)');
					if ($count>=4) {
						$this->error('每种产品只能添加4种属性规格！');
						exit();
					}
					$attr = array();
					$attr['attr_name'] = $v;
					$attr['addtime'] = time();
					$attr['pro_id'] = $pro_id;
					$rs = M('attribute')->add($attr);
				}
				
				$guige_name = $_POST['gg_name'][$k];
				if ($guige_name && $rs) {
					foreach ($guige_name as $key => $val) {
						$data=array();
						$data['pid'] = $pro_id;
						$data['attr_id'] = $rs;
						$data['name'] = $val;
						$data['price'] = $_POST['gg_price'][$k][$key];
						$data['stock'] = intval($_POST['gg_stock'][$k][$key]);
						$data['addtime'] = time();
						$res = M('guige')->add($data);
						if (!$res) {
							$this->error('部分规格添加失败，请核对过后再补充！',U('pro_guige',array('pid'=>$pro_id)));
							exit();
						}
					}
				}
			}

			$up_pro = M('product')->where('id='.intval($pro_id))->save(array('is_buff'=>1));
			$this->set_pro_attr($pro_id);

			$this->success('操作成功！',U('pro_guige',array('pid'=>$pro_id)));
			exit();
		}
		$this->error('没有获取到属性信息！');
		exit();
	}

	//********************************
	//说明：ajax修改价格库存
	//********************************、
	public function ajax_up(){
		$pro_id = intval($_POST['pro_id']);
		$id = intval($_POST['id']);
		$vals = trim($_POST['vals']);
		$type = trim($_POST['type']);
		$check = M('guige')->where('id='.intval($id).' AND pid='.intval($pro_id))->find();
		if (!$check) {
			echo json_encode(array('status'=>0,'err'=>'系统错误.'.__LINE__));
			exit();
		}

		$data = array();
		if ($type=='pro_price') {
			if ($check['price']==$vals) {
				echo json_encode(array('status'=>1));
				exit();
			}
			$data['price'] = floatval(sprintf("%.2f",$vals));
		}elseif ($type=='pro_stock') {
			if ($check['stock']==$vals) {
				echo json_encode(array('status'=>1));
				exit();
			}
			$data['stock'] = intval($vals);
		}

		if ($data) {
			$res = M('guige')->where('id='.intval($id).' AND pid='.intval($pro_id))->save($data);
			if ($res) {
				echo json_encode(array('status'=>1));
				exit();
			}else{
				echo json_encode(array('status'=>0,'err'=>'网络异常，请稍后再试.'.__LINE__));
				exit();
			}
		}else{
			echo json_encode(array('status'=>0,'err'=>'没有找到要修改的数据.'.__LINE__));
			exit();
		}

	}

	//********************************
	//说明：规格图片上传
	//********************************、
	public function guige_upload(){
		$id = intval($_POST['gg_id']);
		$check_info = M('guige')->where('id='.intval($id))->find();
		if (!$check_info) {
			$this->error('参数错误.'.__LINE__);
			exit();
		}
		$array = array();
		if (!empty($_FILES['file_'.$id]['tmp_name'])) {
			//文件上传
			$info = $this->upload_images($_FILES['file_'.$id],array('jpg','png','jpeg'),"attribute/".date(Ymd));
			if(!is_array($info)) {// 上传错误提示错误信息
				$this->error($info);
				exit();
			}else{// 上传成功 获取上传文件信息
				$array['img'] = 'UploadFiles/'.$info['savepath'].$info['savename'];
			}			
		}
		if ($array) {
			$res = M('guige')->where('id='.intval($id))->save($array);
			if (!$res) {
				$this->error('上传失败，请稍后再试.'.__LINE__);
				exit();
			}

			//删除之前的图片
			if ($check_info['img']) {
				$img_url = "Data/".$xt['img'];
				if(file_exists($img_url)) {
					@unlink($img_url);
				}
			}
		}

		$this->redirect('pro_guige',array('pid' => intval($check_info['pid'])));
	}

	//********************************
	//说明：产品单个规格删除
	//********************************
	public function del_guige(){
		$id = intval($_REQUEST['gg_id']);
		$check_info = M('guige')->where('id='.intval($id))->find();
		if (!$check_info) {
			$this->error('参数错误.'.__LINE__);
			exit();
		}

		$res = M('guige')->where('id='.intval($id))->delete();
		if ($res) {
			//删除之前的图片
			if ($check_info['img']) {
				$img_url = "Data/".$check_info['img'];
				if(file_exists($img_url)) {
					@unlink($img_url);
				}
			}
			$this->success('操作成功！');
			exit();
		}else{
			$this->error('删除失败.');
			exit();
		}

	}

	//**********************************************
	//说明：设置单产品不同规格不同价格不同库存 公共方法
	//**********************************************
	public function set_pro_attr($pro_id){
		//查询产品是否存在
		$pro_info = M('product')->where('id='.intval($pro_id))->find();
		if (!$pro_info) {
			return false;
		}

		//获取产品所有规格属性组合，没有就添加
		$proAttrid = M('pro_attr')->where('pid='.intval($pro_id))->getField('id');
		//遍历查询到的属性名称
		$d = array();$pro_buff=array();
		$buff = M('product')->where('id='.intval($pro_id))->getField('pro_buff');
		$pro_buff = explode(',', $buff);
		foreach ($pro_buff as $k => $v) {
			$a = M('guige')->where('pid='.intval($pro_id).' AND attr_id='.intval($v))->field('name')->select();
			foreach ($a as $key => $val) {
				$b[$k][] = $val['name'];
			}
		}

		//组合所有规格属性
		foreach ($b[0] as $k => $v) {
			if ($b[1]) {
				foreach ($b[1] as $k1 => $v1) {
					if ($b[2]) {
						foreach ($b[2] as $k2 => $v2) {
							if ($b[3]) {
								foreach ($b[3] as $k3 => $v3) {
									$d[] = $v.','.$v1.','.$v2.','.$v3;
								}
							}else{
								$d[] = $v.','.$v1.','.$v2;
							}
						}
					}else{
						$d[] = $v.','.$v1;
					}
				}
			}else{
				$d[] = $v;
			}
		}

		//把所有组合存入一个数组
		$arr=array();$arr1=array();
		foreach ($d as $k => $v) {
			$arr1['gg_name'] = $v;
			$arr1['price'] = $pro_info['price_yh'];//价格：默认为添加产品时的优惠价格
			$arr1['stock'] = $pro_info['num'];//库存：默认为添加产品时的数量
			$arr[] = $arr1;
		}

		//存入产品属性规格表
		$data = array();
		$data['pid'] = intval($pro_id);
		$data['name'] = serialize($arr);
		$data['addtime'] = time();
		if ($proAttrid) {
			$res = M('pro_attr')->where('id='.intval($proAttrid))->save($data);
		}else{
			$res = M('pro_attr')->add($data);
		}

		if ($res) {
			return true;
		}else{
			return false;
		}
	}

	//根据属性获取属性值
	public function getAttrValue(){
		$id = $_POST['attr_id'];
		$attr_value = M('attr_value')->where('attr_id="'.$id.'"')->select();
		$this->ajaxReturn($attr_value);
	}

	//根据规格获取规格值
	public function getSpec(){
		$id = $_POST['spec_id'];
		$spec_value = M('spec_value')->where('spec_id="'.$id.'"')->select();
		$this->ajaxReturn($spec_value);
	}
}
