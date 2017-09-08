<?php
namespace Api\Controller;
use Think\Controller;
class VoucherController extends PublicController {
	//***************************
	//  所有单页数据接口
	//***************************
    public function index(){
    	$condition = array();
        $condition['del'] = 0;
        $condition['start_time'] = array('lt',time());
        $condition['end_time'] = array('gt',time());

        $vou = M('voucher')->where($condition)->order('addtime desc')->select();
        foreach ($vou as $k => $v) {
            $vou[$k]['start_time'] = date("Y.m.d",intval($v['start_time']));
            $vou[$k]['end_time'] = date("Y.m.d",intval($v['end_time']));
            $vou[$k]['amount'] = floatval($v['amount']);
            $vou[$k]['full_money'] = floatval($v['full_money']);
            if ($v['proid']=='all' || empty($v['proid'])) {
                $vou[$k]['desc'] = '店内通用';
            }else{
                $vou[$k]['desc'] = '限定商品';
            }
        }
        echo json_encode(array('status'=>1,'vou'=>$vou));
        exit();
    }

    //***************************
    //  用户领取优惠券
    //***************************
    public function get_voucher(){
        $vid = intval($_REQUEST['vid']);
        $uid = intval($_REQUEST['uid']);
        $check_user = M('user')->where('id="'.$uid.'" AND del=0')->find();
        if (!$check_user) {
            echo json_encode(array('status'=>0,'err'=>'登录状态异常！'.__LINE__));
            exit();
        }

        $check_vou = M('voucher')->where('id='.intval($vid).' AND del=0')->find();
        if (!$check_vou) {
            echo json_encode(array('status'=>0,'err'=>'优惠券信息错误！'.__LINE__));
            exit();
        }

        //判断是否已领取过
        $check = M('user_voucher')->where('uid='.intval($uid).' AND vid='.intval($vid))->getField('id');
        if ($check) {
            echo json_encode(array('status'=>0,'err'=>'您已经领取过了！'));
            exit();
        }

        if (intval($check_vou['point'])!=0 && intval($check_vou['point'])>intval($check_user['jifen'])) {
            echo json_encode(array('status'=>0,'err'=>'积分余额不足！'));
            exit();
        }

        if ($check_vou['start_time']>time()) {
            echo json_encode(array('status'=>0,'err'=>'优惠券还未生效！'));
            exit();
        }

        if ($check_vou['end_time']<time()) {
            echo json_encode(array('status'=>0,'err'=>'优惠券已失效！'));
            exit();
        }

        if (intval($check_vou['count'])<=intval($check_vou['receive_num'])) {
            echo json_encode(array('status'=>0,'err'=>'优惠券已被领取完了！'));
            exit();
        }

        $data = array();
        $data['uid'] = $uid;
        $data['vid'] = $vid;
        $data['shop_id'] = intval($check_vou['shop_id']);
        $data['full_money'] = floatval($check_vou['full_money']);
        $data['amount'] = floatval($check_vou['amount']);
        $data['start_time'] = $check_vou['start_time'];
        $data['end_time'] = $check_vou['end_time'];
        $data['addtime'] = time();
        $res = M('user_voucher')->add($data);
        if ($res) {
            //修改会员积分
            if (intval($check_vou['point'])!=0) {
                $arr = array();
                $arr['jifen'] = intval($check_user['jifen'])-intval($check_vou['point']);
                $up = M('user')->where('id='.intval($uid))->save($arr);
            }

            //修改领取数量
            $arrs = array();
            $arrs['receive_num'] = intval($check_vou['receive_num'])+1;
            $ups = M('voucher')->where('id='.intval($vid))->save($arrs);
            
            echo json_encode(array('status'=>1));
            exit();
        }else{
            echo json_encode(array('status'=>0,'err'=>'领取失败！'));
            exit();
        }
    }

    //根据分类获取有效优惠券
    public function getQuan(){
        $cate_id =  $_REQUEST['cart_id'];
        $quan = M('user_voucher')->where('start_time<"'.time().'" AND end_time>"'.time().'"')->select();
        echo json_encode(array('status'=>1,'quan'=>$quan2));
    }

    //根据分类获取用户的有效优惠券
    public function getQuan2(){
        $cartId = $_REQUEST['cartId'];
        $cartId = trim($cartId,',');
        $cartId = explode(',',$cartId);
        $uid = $_REQUEST['user_id'];
        $quan = M('user_voucher')->where('start_time<"'.time().'" AND end_time>"'.time().'" AND uid="'.$uid.'" AND status=1')->select(); 
        $pid = array();
        $total = 0;
        foreach($cartId as $k => $v){
            $pid[] = M('shopping_char')->where('id="'.intval($v).'"')->getField('pid');
            $num = M('shopping_char')->where('id="'.intval($v).'"')->getField('num');
            $price = M('shopping_char')->where('id="'.intval($v).'"')->getField('price');
            $total += $num * $price;
        }
        $cate_id = array();
        foreach($pid as $k=> $v){
            $cate_id[] =  M('product')->where('id="'.intval($v).'" AND del=0 AND is_down=0')->getField('cid');
        }
        $cate_id = array_unique($cate_id);
        $quan2 = array();
        foreach($cate_id as $k => $v){
            foreach($quan as $k2 => $v2){
                $temp = M('voucher')->where('id="'.intval($v2['vid']).'" AND cate_id="'.intval($v).'" AND full_money<"'.$total.'"')->select();
                $quan2 = array_merge($quan2,$temp);
            }
            
        }
        $qtemp = M('voucher')->where('end_time>"'.time().'" AND cate_id=0 AND full_money<"'.$total.'"')->select();
        $quan2 = array_merge($quan2,$qtemp);
        if($quan2){
            foreach($quan as $k => $v){
               $quan2[$k]['start_time'] = date("Y-m-d",$v['start_time']);
               $quan2[$k]['end_time'] = date("Y-m-d",$v['end_time']);
            }
        }
        echo json_encode(array('status'=>1,'quan'=>$quan2));
    }
}
