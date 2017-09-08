<?php
namespace Ht\Controller;
use Think\Controller;
class ProSpecController extends PublicController{

	/*
	*
	* 构造函数，用于导入外部文件和公共方法
	*/
	public function _initialize(){
		$this->spec = M('spec');
		$this->category = M('category');
		// 获取所有分类，进行关系划分
		$list = $this->category->where('tid>1')->select();
		foreach ($list as $k1 => $v1) {
			$list[$k1]['list2'] = $this->category->where('tid='.intval($v1['id']))->field('id,tid,name,bz_2')->select();
			foreach ($list[$k1]['list2'] as $k2 => $v2) {
				$list[$k1]['list2'][$k2]['list3'] = $this->category->where('tid='.intval($v2['id']))->field('id,tid,name,bz_2')->select();
			}
		}
		$this->assign('cateList',$list);
	}

	/*
	*
	* 获取、查询产品属性表数据
	*/
	public function index(){
		//搜索，根据产品属性名称搜索
		$spec_name = trim($_GET['spec_name']);
		$condition = array();
		if ($spec_name) {
			$condition['spec_name'] = array('LIKE','%'.$spec_name.'%');
			$this->assign('spec_name',$spec_name);
		}


		//分页
		$count   = $this->spec->where($condition)->count();// 查询满足要求的总记录数
		$Page    = new \Think\Page($count,25);// 实例化分页类 传入总记录数和每页显示的记录数(25)

		//分页跳转的时候保证查询条件
		foreach($condition as $key=>$val) {
		    $Page->parameter[$key]  =  urlencode($val);
		}

		//头部描述信息，默认值 “共 %TOTAL_ROW% 条记录”
		$Page->setConfig('header', '<li class="rows">共<b>%TOTAL_ROW%</b>条&nbsp;第<b>%NOW_PAGE%</b>页/共<b>%TOTAL_PAGE%</b>页</li>');
		//上一页描述信息
	    $Page->setConfig('prev', '上一页');
	    //下一页描述信息
	    $Page->setConfig('next', '下一页');
	    //首页描述信息
	    $Page->setConfig('first', '首页');
	    //末页描述信息
	    $Page->setConfig('last', '末页');
	    $Page->setConfig('theme', '%FIRST%%UP_PAGE%%LINK_PAGE%%DOWN_PAGE%%END%%HEADER%');

		$show  = $Page->show();// 分页显示输出

		$spec_list = $this->spec->where($condition)->order('sort desc,id desc')->limit($Page->firstRow.','.$Page->listRows)->select();	
		// foreach($attr_list as $k =>$v){
		// 	$attr_list[$k]['cate'] = M('category')->where('id="'.$v['cate_id'].'"')->getField('name');
		// }	
		$this->assign('spec_list',$spec_list);
		$this->assign('page',$show);
		$this->display(); // 输出模板

	}


	/*
	*
	* 跳转添加或修改产品属性数据页面
	*/
	public function add(){
		//如果是修改，则查询对应分类信息
		if (intval($_GET['spec_id'])) {
			$spec_id = intval($_GET['spec_id']);
		
			$spec_info = $this->spec->where('id='.intval($spec_id))->find();
			if (!$spec_info) {
				$this->error('没有找到相关信息.');
			}
			$this->assign('spec_info',$spec_info);
		}
		$this->display();
	}


	
	/*
	*
	* 产品属性删除
	*/
	public function del(){
		//以后删除还要加权限登录判断
		$id = intval($_GET['did']);
		if (!$id) {
			$this->error('非法操作.');
		}
		//判断该分类下是否还有子分类
		$check_id = $this->spec->where('id='.intval($id))->find();
		if (!$check_id) {
			$this->error('系统繁忙，请时候再试！');
		}

		//判断该产品属性用户是否正在使用
		// $check_spec = M('Guige')->where('spec_id='.intval($id))->getField('id');
		// if ($check_spec) {
		// 	$this->error('该产品属性商家正在使用中，不能删除！');
		// }

		//判断是否有产品正在用该属性
		$spec_value = M('spec_value')->where('spec_id="'.$id.'"')->select();
		$tmp = '';
		foreach($spec_value as $k => $v){
			$tmp = M('attr_spec_price_store')->where('spec_value_id="'.$v['id'].'"')->select();
			if(!empty($tmp)){
				$this->error('该属性下还有产品，不能删除！');
				exit;
			}
		}
		$res = $this->spec->where('id='.intval($id))->delete();
		if ($res) {
			M('spec_value')->where('spec_id="'.intval($id).'"')->delete();
			$this->success('操作成功.');
		}else{
			$this->error('操作失败.');
		}
	}

	//添加或者修改属性
	public function save(){
		$data = $_POST;
		$spec = M('spec');
		$spec_value = M('spec_value');
		if($data['spec_id'] == ''){
			$tmp['spec_name'] = $data['spec_name'];
			// $tmp['cate_id'] = $data['cate'];
			$bool = M('spec')->where('spec_name="'.$data['spec_name'].'"')->select();
			if(!empty($bool)){
				$this->error('该分类下已经存在该属性！');
				exit;
			}
			$tmp['sort'] = $data['sort'];
			$tmp['spec_value'] = $data['spec_value'];
			$tmp['addtime'] = time();
			$num = $spec->add($tmp);
			if($num){
				$data['spec_value'] = explode(',',$data['spec_value']);
				foreach($data['spec_value'] as $k => $v){
					$tmp2['spec_id'] = M('spec')->where('spec_name="'.$data['spec_name'].'"')->getField('id');
					$tmp2['spec_value'] = $v;
					$spec_value->add($tmp2);
					unset($tmp2);
				}
				$this->success('添加成功！','index');
			}else{
				$this->error('添加失败！');
			}
		}else{
			$tmp['spec_name'] = $data['spec_name'];
			$tmp['sort'] = $data['sort'];
			// $tmp['cate_id'] = $data['cate'];
			$tmp['spec_value'] = $data['spec_value'];
			// $tmp['addtime'] = time();
			$num = $spec->where('id="'.$data['spec_id'].'"')->save($tmp);
			if($num){
				$spec_value->where('spec_id="'.$data['spec_id'].'"')->delete();
				$data['spec_value'] = explode(',',$data['spec_value']);
				foreach($data['spec_value'] as $k => $v){
					$tmp3['spec_id'] = $data['spec_id'];
					$tmp3['spec_value'] = $v;
					$spec_value->add($tmp3);
					unset($tmp3);
				}
				$this->success('修改成功！','index');
			}else{
				$this->error('修改失败！');
			}
		}
		
	}

}
