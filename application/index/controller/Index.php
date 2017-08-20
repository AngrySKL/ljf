<?php
namespace app\index\controller;
use app\index\model\Product as ProductModel;
use think\Controller;

class Index extends Controller {
	public function index() {
		$list = ProductModel::order('id', 'asc')->select();
		$this->assign('list', $list);

		return $this->fetch();
	}
}
