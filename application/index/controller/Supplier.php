<?php
namespace app\index\controller;
use think\Controller;

class Supplier extends Controller {
	public function dtl() {
		if (request()->isGet()) {
			$products = db('product')
				->order('id', 'asc')
				->select();
			$this->assign('products', $products);
		}

		return $this->fetch('detail');
	}
}
