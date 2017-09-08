<?php
namespace Ht\Controller;
use Think\Controller;
class TagController extends PublicController{

	/*
	*
	* 获取、查询所有标签数据
	*/
	public function index(){
		$condition = array();
		$where = '';
		if($_POST['tag_name']){
			$condition['name'] = $_POST['tag_name'];
			$where .= "name like '%".$_POST['tag_name']."%'";
		}
		
		//分页
		$count   = M('tag')->where($where)->count();// 查询满足要求的总记录数
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
	    /*
	    * 分页主题描述信息 
	    * %FIRST%  表示第一页的链接显示  
	    * %UP_PAGE%  表示上一页的链接显示   
	    * %LINK_PAGE%  表示分页的链接显示
	    * %DOWN_PAGE% 	表示下一页的链接显示
	    * %END%   表示最后一页的链接显示
	    */
	    $Page->setConfig('theme', '%FIRST%%UP_PAGE%%LINK_PAGE%%DOWN_PAGE%%END%%HEADER%');

		$show    = $Page->show();// 分页显示输出
		// 进行分页数据查询 注意limit方法的参数要使用Page类的属性
		$tag_list = M('tag')->where($where)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('tag_list',$tag_list);
		$this->display();
	}

	/*
	*
	* 添加标签
	*/
	public function add(){
		if($_GET['id']){
			$tag_info = M('tag')->where('id="'.$_GET['id'].'"')->select();
			$this->assign('tag_info',$tag_info);
		}
		$this->display();
	}

	/*
	*
	* 保存标签信息
	*/
	public function save(){
		$data['name'] = $_POST['name'];
		if($_POST['id']){
			$num = M('tag')->where('id="'.$_POST['id'].'"')->save($data);
		}else{
			$num = M('tag')->add($data);
		}
		if($num){
			$this->success('操作成功！','index');
		}else{
			$this->error('操作失败！');
		}
	}

	/*
	*
	* 保存标签信息
	*/
	public function del(){
		$id = $_GET['id'];
		$res = M('attr_spec_price_store')->where('tag_id="'.$id.'"')->select();
		if($res){
			$this->error('删除失败，该标签下还有商品！');
			exit;
		}
		$num = M('tag')->where('id="'.$id.'"')->delete();
		if($num){
			$this->success('删除成功！','index');
		}else{
			$this->error('删除失败！');
		}
	}
}
