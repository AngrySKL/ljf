<?php
namespace app\index\controller;
use think\Controller;

class Product extends Controller {

	private function findIndex($id, $products) {
		if (!isset($products) || empty($products)) {
			return false;
		}

		foreach ($products as $index => $product) {
			if ($product['id'] == $id) {
				return $index;
			}
		}

		return false;
	}

	private function getValidUrls($urls) {
		$urlList = explode(';', $urls);
		$validUrls = array();
		foreach ($urlList as $url) {
			if (!empty($url)) {
				array_push($validUrls, $url);
			}
		}

		return $validUrls;
	}

	public function dtl() {
		if (request()->isGet()) {
			$id = input('id');
			$products = db('product')
				->order('id', 'asc')
				->select();

			$index = $this->findIndex($id, $products);
			$currentProduct = $products[$index];
			$smallUrls = $this->getValidUrls($currentProduct['smallUrls']);
			$bigUrls = $this->getValidUrls($currentProduct['bigUrls']);

			$urls = array();
			$count = count($smallUrls);
			for ($i = 0; $i < $count; $i++) {
				array_push($urls, array('smallUrl' => $smallUrls[$i], 'bigUrl' => $bigUrls[$i]));
			}

			$this->assign('products', $products);
			$this->assign('index', $index);
			$this->assign('urls', $urls);
		}

		return $this->fetch('detail');
	}
}
