<?php
// 本类由系统自动生成，仅供测试用途
namespace Api\Controller;
use Think\Controller;
class CategoryController extends PublicController {
	//***************************
	// 产品分类
	//***************************
    public function index(){
    	$list = M('category')->where('tid=1')->field('id,tid,name,bz_1')->select();
        foreach($list as $k => $v){
            $list[$k]['bz_1'] =  __DATAURL__.$v['bz_1'];
        }
        // $catList = M('category')->where('tid='.intval($list[0]['id']))->field('id,name,bz_1')->select(); 
        if($_REQUEST['cate_id']){
            $tid = M('category')->where('id="'.$_REQUEST['cate_id'].'"')->getField('tid');
            $catList = M('category')->where('tid="'.$tid.'"')->field('id,name,bz_1')->select();
           
        }else{
             $catList = M('category')->where('tid>1 AND bz_2=1')->order('id desc')->field('id,name,bz_1')->select();
        }
        
         foreach ($catList as $k => $v) {
                $catList[$k]['bz_1'] = __DATAURL__.$v['bz_1'];
            }

        //搜索热门商品
        if($_REQUEST['cate_id']){
            foreach($catList as $k => $v){
                $hot = M('product')->where('del=0 AND pro_type=1 AND is_down=0 AND type=1 AND cid="'.$_REQUEST['cate_id'].'"')->select();
            }
        }else{
            $hot = array();
            foreach($catList as $k => $v){
                $tmp = M('product')->where('del=0 AND pro_type=1 AND is_down=0 AND type=1 AND cid="'.$v['id'].'"')->select();
                $hot = array_merge($hot,$tmp);
            }
        }
        

        foreach($hot as $k => $v){
            $hot[$k]['photo_x'] =  __DATAURL__.$v['photo_x'];
            $tag_id = M('attr_spec_price_store')->where('pid="'.$v['id'].'"')->getField('tag_id');
            $hot[$k]['tag'] = M('tag')->where('id="'.$tag_id.'"')->getField('name');
            $hot[$k]['price'] = M('attr_spec_price_store')->where('pid="'.$v['id'].'"')->getField('price_yh');
        }

    	//json加密输出
		//dump($json);
		echo json_encode(array('status'=>1,'list'=>$list,'catList'=>$catList,'hot'=>$hot));
        exit();
    }

    //***************************
    // 产品分类
    //***************************
    public function getcat(){
        $catid = intval($_REQUEST['cat_id']);
        if (!$catid) {
            echo json_encode(array('status'=>0,'err'=>'没有找到产品数据.'));
            exit();
        }

        $catList = M('category')->where('tid='.intval($catid))->field('id,name,bz_1')->select();
        foreach ($catList as $k => $v) {
            $catList[$k]['bz_1'] = __DATAURL__.$v['bz_1'];
        }

         //搜索热门商品
         $hot = array();
        foreach($catList as $k => $v){
            $temp = M('product')->where('del=0 AND pro_type=1 AND is_down=0 AND type=1 AND cid="'.$v['id'].'"')->select();
            $hot = array_merge($hot,$temp);
        }

        foreach($hot as $k => $v){
            $hot[$k]['photo_x'] =  __DATAURL__.$v['photo_x'];
             $tag_id = M('attr_spec_price_store')->where('pid="'.$v['id'].'"')->getField('tag_id');
             $hot[$k]['tag'] = M('tag')->where('id="'.$tag_id.'"')->getField('name');
             $hot[$k]['price'] = M('attr_spec_price_store')->where('pid="'.$v['id'].'"')->getField('price_yh');
        }

        //json加密输出
        //dump($json);
        echo json_encode(array('status'=>1,'catList'=>$catList,'hot'=>$hot));
        exit();
    }
    public function brand(){
        $brand=M("brand");
        $page=I("request.page");
        if(!$page){
            $page=1;
        }
        $limit=($page*40)-40;

        $list=$brand->limit($limit,40)->select();
        if (!$list) {
            echo json_encode(array('status'=>0,'err'=>'没有更多的数据了.'));
            exit();
        }
        foreach ($list as $k => $v) {
            $list[$k]['digest']=mb_substr($v['digest'],0,39,"utf-8");
            $list[$k]['photo']=__DATAURL__.$v['photo'];
        }
        echo json_encode(array('status'=>1,'list'=>$list));
        exit();   
    }

    //***************************
    // 子类产品
    //***************************
    public function getProduct(){
        $catid = intval($_REQUEST['cate_id']);
        if($_REQUEST['key']){
            $key = $_REQUEST['key'];
        }else{
            $key = '';
        }
        
        // if ($catid == '' && $key == '') {
        //     echo json_encode(array('status'=>0,'err'=>'没有找到产品数据.'));
        //     exit();
        // }
        if($catid){
            $pro = M('product')->where('del=0 AND is_down=0 AND pro_type=1 AND cid="'.$catid.'"')->select();
        }else if($key){
            $pro = M('product')->where('del=0 AND pro_type=1 AND is_down=0 AND name like "%'.$key.'%"')->order('addtime desc')->select();
        }
       if(!empty($_REQUEST['num'])){
            switch($_REQUEST['num']){
                case '0.1':
                    if($_REQUEST['key']){
                        $pro = M('product')->where('del=0 AND pro_type=1 AND is_down=0 AND name like "%'.$_REQUEST['key'].'%"')->order('addtime desc')->select();
                    }else if(!empty($_REQUEST['catid'])){
                        $catid = $_REQUEST['catid'];
                        $pro = M('product')->where('del=0 AND is_down=0 AND pro_type=1 AND cid="'.$_REQUEST['catid'].'"')->order('addtime desc')->select();
                    }
                    break;
                case '1':
                   if($_REQUEST['key']){
                        $pro = M('product')->where('del=0 AND pro_type=1 AND is_down=0 AND name like "%'.$_REQUEST['key'].'%"')->order('renqi desc')->select();
                    }else if($_REQUEST['catid']){
                        $catid = $_REQUEST['catid'];
                        $pro = M('product')->where('del=0 AND is_down=0 AND pro_type=1 AND cid="'.$_REQUEST['catid'].'"')->order('renqi desc')->select();
                    }
                    break;
                case '2':
                    if($_REQUEST['key']){
                        $pro = M('product')->where('del=0 AND pro_type=1 AND is_down=0 AND name like "%'.$_REQUEST['key'].'%"')->order('addtime desc')->select();
                        foreach($pro as $k => $v){
                            $pro[$k]['price'] = M('attr_spec_price_store')->where('pid="'.$v['id'].'"')->getField('price_yh');
                        }
                        $sort = array(  
                            'direction' => 'SORT_DESC', //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序  
                            'field'     => 'price',       //排序字段  
                        );  
                        $arrSort = array();  
                        foreach($pro AS $uniqid => $row){  
                            foreach($row AS $key=>$value){  
                                $arrSort[$key][$uniqid] = $value;  
                            }  
                        }  
                        if($sort['direction']){  
                            array_multisort($arrSort[$sort['field']], constant($sort['direction']), $pro);  
                        }  
                    }else if($_REQUEST['catid']){
                        $catid = $_REQUEST['catid'];
                        $pro = M('product')->where('del=0 AND is_down=0 AND pro_type=1 AND cid="'.$_REQUEST['catid'].'"')->select();
                        foreach($pro as $k => $v){
                            $pro[$k]['price'] = M('attr_spec_price_store')->where('pid="'.$v['id'].'"')->getField('price_yh');
                        }
                        $sort = array(  
                            'direction' => 'SORT_DESC', //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序  
                            'field'     => 'price',       //排序字段  
                        );  
                        $arrSort = array();  
                        foreach($pro AS $uniqid => $row){  
                            foreach($row AS $key=>$value){  
                                $arrSort[$key][$uniqid] = $value;  
                            }  
                        }  
                        if($sort['direction']){  
                            array_multisort($arrSort[$sort['field']], constant($sort['direction']), $pro);  
                        }  
                    }
                    break;
                case '3':
                   if($_REQUEST['key']){
                        $pro = M('product')->where('del=0 AND pro_type=1 AND is_down=0 AND name like "%'.$_REQUEST['key'].'%"')->order('addtime desc')->select();
                        foreach($pro as $k => $v){
                            $pro[$k]['price'] = M('attr_spec_price_store')->where('pid="'.$v['id'].'"')->getField('price_yh');
                        }
                        $sort = array(  
                            'direction' => 'SORT_ASC', //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序  
                            'field'     => 'price',       //排序字段  
                        );  
                        $arrSort = array();  
                        foreach($pro AS $uniqid => $row){  
                            foreach($row AS $key=>$value){  
                                $arrSort[$key][$uniqid] = $value;  
                            }  
                        }  
                        if($sort['direction']){  
                            array_multisort($arrSort[$sort['field']], constant($sort['direction']), $pro);  
                        }  
                    }else if($_REQUEST['catid']){
                        $catid = $_REQUEST['catid'];
                        $pro = M('product')->where('del=0 AND is_down=0 AND pro_type=1 AND cid="'.$_REQUEST['catid'].'"')->select();
                        foreach($pro as $k => $v){
                            $pro[$k]['price'] = M('attr_spec_price_store')->where('pid="'.$v['id'].'"')->getField('price_yh');
                        }
                        $sort = array(  
                            'direction' => 'SORT_ASC', //排序顺序标志 SORT_DESC 降序；SORT_ASC 升序  
                            'field'     => 'price',       //排序字段  
                        );  
                        $arrSort = array();  
                        foreach($pro AS $uniqid => $row){  
                            foreach($row AS $key=>$value){  
                                $arrSort[$key][$uniqid] = $value;  
                            }  
                        }  
                        if($sort['direction']){  
                            array_multisort($arrSort[$sort['field']], constant($sort['direction']), $pro);  
                        }  
                    }
                    break;
            }
       }

        foreach($pro as $k => $v){
            $pro[$k]['photo_x'] =  __DATAURL__.$v['photo_x'];
            $tag_id = M('attr_spec_price_store')->where('pid="'.$v['id'].'"')->getField('tag_id');
            $pro[$k]['tag'] = M('tag')->where('id="'.$tag_id.'"')->getField('name');
            $pro[$k]['price'] = M('attr_spec_price_store')->where('pid="'.$v['id'].'"')->getField('price_yh');
            $pro[$k]['ping'] = M('product_dp')->where('pid="'.$v['id'].'"')->count();
        }

        //json加密输出
        //dump($json);
        $category = M('category')->where('bz_2=1 AND tid!=0')->order('bz_2 desc,sort desc')->field('id,name,bz_1')->select();
        if (empty($pro)) {
            echo json_encode(array('status'=>0,'err'=>'没有找到产品数据.','category'=>$category,'pro'=>$pro));
            exit();
        }
        $key = $_REQUEST['key'];

        echo json_encode(array('status'=>1,'pro'=>$pro,'catid'=>$catid,'key'=>$key,'category'=>$category));
        exit();
    }

}
