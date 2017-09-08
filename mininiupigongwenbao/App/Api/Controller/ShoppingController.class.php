<?php
// 本类由系统自动生成，仅供测试用途
namespace Api\Controller;
use Think\Controller;
class ShoppingController extends PublicController {

	//***************************
	//  会员获取购物车列表接口
	//***************************
	public function index(){
		$qz=C('DB_PREFIX');
        $shopping=M("shopping_char");
        $shangchang=M("shangchang");
        $product=M("product");
		$user_id = intval($_REQUEST['user_id']);
		if (!$user_id) {
			echo json_encode(array('status'=>0));
			exit();
		}

		$cart = $shopping->where('uid='.intval($user_id))->field('id,uid,pid,price,num')->select();
		$pid = M('attr_spec_price_store')->where('id="'.$cart[0]['pid'].'"')->getField('pid');
        foreach ($cart as $k => $v) {
        	$pro_info = $product->where('id='.intval($pid))->field('name,photo_x')->find();
        	$cart[$k]['pro_name']=mb_substr($pro_info['name'], 0 , 20 ,"utf-8")."...";
        	$cart[$k]['photo_x']=__DATAURL__.$pro_info['photo_x'];
        	$attr_value_id = M('attr_spec_price_store')->where('id="'.$v['pid'].'"')->getField('attr_value_id');
        	$spec_value_id = M('attr_spec_price_store')->where('id="'.$v['pid'].'"')->getField('spec_value_id');
        	$cart[$k]['attr_value'] = M('attr_value')->where('id="'.$attr_value_id.'"')->getField('name');
        	$attr_id = M('attr_value')->where('id="'.$attr_value_id.'"')->getField('attr_id');
        	$cart[$k]['attr_name'] = M('attribute')->where('id="'.$attr_id.'"')->getField('attr_name');
        	$cart[$k]['spec_value'] = M('spec_value')->where('id="'.$spec_value_id.'"')->getField('spec_value');
        	$spec_id = M('spec_value')->where('id="'.$spec_value_id.'"')->getField('spec_id');
        	$cart[$k]['spec_name'] = M('spec')->where('id="'.$spec_id.'"')->getField('spec_name');
        }

		echo json_encode(array('status'=>1,'cart'=>$cart));
		exit();
    }

	//购物车商品删除
	public function delete(){
		$shopping=M("shopping_char");
		$cart_id=intval($_REQUEST['cart_id']);
		$check_id = $shopping->where('id='.intval($cart_id))->getField('id');
		if (!$check_id) {
			echo json_encode(array('status'=>1));
			exit();
		}

	    $res = $shopping->where('id ='.intval($cart_id))->delete(); // 删除
		if($res){
			echo json_encode(array('status'=>1));
			exit();
		}else{
			echo json_encode(array('status'=>0));
			exit();
		}
	}

	//***************************
	//  会员修改购物车数量接口
	//***************************
	public function up_cart(){
		$shopping=M("shopping_char");
		$uid = intval($_REQUEST['user_id']);
		$cart_id = intval($_REQUEST['cart_id']);
		$num=intval($_REQUEST['num']);

		if (!$uid || !$cart_id || !$num) {
			echo json_encode(array('status'=>0,'err'=>'网络异常.'.__LINE__));
			exit();
		}

		$check = $shopping->where('id='.intval($cart_id))->find();
		if (!$check) {
			echo json_encode(array('status'=>0,'err'=>'购物车信息错误！'));
			exit();
		}

		//检测库存
		$pro_num = M('attr_spec_price_store')->where('id='.intval($check['pid']))->getField('store');
		if($num>intval($pro_num)){
			echo json_encode(array('status'=>0,'err'=>'库存不足！'));
			exit();
		}
		
		$data=array();
		$data['num']=$num;

		$res = $shopping->where('id ='.intval($cart_id).' AND uid='.intval($uid))->save($data);
		if ($res) {
			echo json_encode(array('status'=>1,'succ'=>'操作成功!'));
			exit();
		}else{
			echo json_encode(array('status'=>0,'err'=>'操作失败.'));
			exit();
		}
		
	}

	//多个购物车商品删除
	public function qdelete(){
		$uid = intval($_REQUEST['uid']);
		if (!$uid) {
			echo json_encode(array('status'=>0,'err'=>'网络异常，请稍后再试.'));
			exit();
		}
		$shopping=M("shopping_char");
		$cart_id=trim($_REQUEST['cart_id'],',');
		if (!$cart_id) {
			echo json_encode(array('status'=>0,'err'=>'网络错误，请稍后再试.'));
			exit();
		}

	    $res = $shopping->where('id in ('.$cart_id.') AND uid='.intval($uid))->delete(); // 删除
		if($res){
			echo json_encode(array('status'=>1));
			exit();
		}else{
			echo json_encode(array('status'=>0,'err'=>'操作失败.'));
			exit();
		}
	}


	//添加购物车
	public function add(){
		$uid = intval($_REQUEST['uid']);
		if (!$uid) {
			echo json_encode(array('status'=>0,'err'=>'系统错误.'));
			exit();
		}
		//加入购物车
		$check = $this->check_cart(intval($_POST['pid']));
		if ($check['status']==0) {
			echo json_encode(array('status'=>0,'err'=>$check['err']));
			exit;
		}

		$check_info = M('product')->where('id='.intval($_POST['pid']).' AND del=0 AND is_down=0')->find();
		$shpp=M("shopping_char");
		$shu=trim($_POST['val'],',');

		//判断购物车内是否已经存在该商品
		$data = array();
		$cart_info = $shpp->where('pid='.intval($_POST['ppid']).' AND uid='.intval($_POST['uid']))->field('id,num')->find();
		if ($cart_info) {
			$data['num'] = intval($cart_info['num'])+intval($_POST['num']);
			$shpp->where('id='.intval($cart_info['id']))->save($data);
			$res=$cart_info['id'];
		}else{
			$data['pid']=intval($_POST['ppid']);
			$data['pro_id']=intval($_REQUEST['pid']);
			$data['num']=intval($_POST['num']);
			$data['buff']=$shu;
			$data['addtime']=time();
			$data['uid']=intval($_POST['uid']);
			$data['shop_id']=intval($check_info['shop_id']);
			$data['type']=2;
			//如果产品有属性，则存入属性设置的价格;否则存产品表的价格
			if ($check_info['pro_buff']) {
				$data['price'] = $_POST['yh_price'];
			}else{
				$data['price'] = M('attr_spec_price_store')->where('id="'.$_REQUEST['ppid'].'"')->getField('price_yh');
			}

			$res=$shpp->add($data);
		}

		if($res){
			echo json_encode(array('status'=>1,'cart_id'=>$res)); //该商品已成功加入您的购物车
			exit;
		}else{
			echo json_encode(array('status'=>0,'err'=>'加入失败.'));
			exit;
		}
	}

	//***************************
	//  会员立即购买下单接口
	//***************************
	public function check_shop(){
		$cart_id = trim($_REQUEST['cart_id'],',');
		$id=explode(',',$cart_id);
		if (!$cart_id) {
			echo json_encode(array('status'=>0));
			exit();
		}

		foreach ($id as $k=>$v){
			$shoop[$k]=M("shopping_char")->where('id ='.intval($v))->field('shop_id,pid')->find();
        }

		foreach($shoop as $key => $value){
			$result[$key] = M("product")->where('id='.intval($value['pid']))->field('id,price,price_yh')->select();
			$price[] = i_array_column($result[$key], 'price_yh');
		}
		//dump($price);exit;
		foreach($price as $keys => $va){
			$str .= implode(",", $va).",";
		}
		$str = trim($str, ",");
		$parr = explode(",", $str);
		if(array_sum($parr) && in_array("0", $parr)){
			echo json_encode(array('status'=>0));
			exit();
		}
		
		$names = i_array_column($shoop, 'shop_id');
		
		$arr=array_unique($names);
		$val= sizeof($arr);
		if($val=='1'){
			echo json_encode(array('status'=>1));
			exit();
		}else{
			echo json_encode(array('status'=>2));
			exit();
		}	 
	}

	//购物车添加。删除检测公共方法
	public function check_cart($pid){
		//检查产品是否存在或删除
		$check_info = M('product')->where('id='.intval($pid).' AND del=0 AND is_down=0')->find();
		if (!$check_info) {
			return array('status'=>0,'err'=>'商品不存在或已下架.');
		}

		return array('status'=>1);
	}   

    /*
       去除HTNL标签
    */
    public function html_entity($array){
    	foreach ($array as $key => $value) {
        	$array[$key]['content'] = strip_tags(html_entity_decode($value['content']));
        }
        return $array;
    }

    /*
       查看立即购买商品的信息
    */
   public function getProduct(){
   		$pro_id = intval($_REQUEST['pro_id']);
		$pro = M('product')->where('id="'.$pro_id.'" AND del=0 AND is_down=0')->find();
		//dump($pro);exit;
		if(!$pro){
			echo json_encode(array('status'=>0,'err'=>'商品不存在或已下架！'));
			exit();
		}
		$renqi =  M('product')->where('id="'.$pro_id.'" AND del=0 AND is_down=0')->getField('renqi');
		$data['renqi'] = intval($renqi) + 1;
		M('product')->where('id="'.$pro_id.'"')->save($data);
        $pro['photo_x'] =  __DATAURL__.$pro['photo_x'];
        $lun = trim(M('attr_spec_price_store')->where('pid="'.$pro_id.'"')->getField('photo_string'),',');
        $lun = explode(',',$lun);
        foreach($lun as $k => $v){
        	 $lun[$k] =  __DATAURL__.$v;
        }
	    $tag_id = M('attr_spec_price_store')->where("pid=".$pro_id)->getField('tag_id');
	    if($tag_id){
	    	$pro['tag'] = M('tag')->where("id=".$tag_id)->getField('name');
	    }
	    if($pro['param']){
	    	$param = array();
	    	$pro['param'] = explode(',',$pro['param']);
	    	foreach ($pro['param'] as $k => $v) {
	    		$temp = explode(':',$v);
	    		$temp2[$temp[0]] = $temp[1];
	    		$param = array_merge($param,$temp2);
	    	}
	    }
	    
	    $pro['price_yh'] = M('attr_spec_price_store')->where("pid=".$pro_id)->getField('price_yh');
	    $pro['price'] = M('attr_spec_price_store')->where("pid=".$pro_id)->getField('price');
	    $pro['store'] = M('attr_spec_price_store')->where("pid=".$pro_id)->getField('store');
	    //搜索属性
	    $attr_value_id =  M('attr_spec_price_store')->where("pid=".$pro_id)->getField('attr_value_id');
	    if($attr_value_id){
	    	$attr_id =  M('attr_value')->where('id="'.$attr_value_id.'"')->getField('attr_id');
	    	$pro['attr'] = M('attribute')->where('id="'.$attr_id.'"')->getField('attr_name');
	    	$pro['attr_value'] = M('attr_value')->where('id="'.$attr_value_id.'"')->getField('name');
	    }
	    //搜索规格
	    $spec_value_id =  M('attr_spec_price_store')->where("pid=".$pro_id)->getField('spec_value_id');
	    if($spec_value_id){
	    	$spec_id =  M('spec_value')->where('id="'.$spec_value_id.'"')->getField('spec_id');
	    	$pro['spec'] = M('spec')->where('id="'.$spec_id.'"')->getField('spec_name');
	    	$pro['spec_value'] = M('spec_value')->where('id="'.$spec_value_id.'"')->getField('spec_value');
	    }
		//$content = preg_replace("/width:.+?[\d]+px;/",'',$pro['content']);
		$content = str_replace('/minipg/Data/', __DATAURL__, $pro['content']);
		$pro['content']=html_entity_decode($content, ENT_QUOTES ,'utf-8');

		//搜索该分类下的优惠券
		$time = time();
		$cid = M('product')->where('id="'.$pro_id.'" AND del=0 AND is_down=0')->getField('cid');
		$quan = M('voucher')->where('cate_id="'.$cid.'"  AND end_time>"'.$time.'"')->select();
		echo json_encode(array('status'=>1,'content'=>$content,'pro'=>$pro,'lun'=>$lun,'quan'=>$quan,'param'=>$param));
	
   }


}
