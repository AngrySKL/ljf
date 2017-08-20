<?php
namespace app\index\controller;
use think\Controller;

class Supplier extends Controller {

	private function findType($id, $types) {
		if (!isset($types) || empty($types)) {
			return false;
		}

		foreach ($types as $index => $type) {
			if ($type['id'] == $id) {
				return $index;
			}
		}

		return false;
	}

	public function dtl() {
		if (request()->isGet()) {
			$products = db('product')
				->order('id', 'asc')
				->select();

			$types = db('suppliertype')
				->order('id', 'asc')
				->select();

			$results = db('supplier')
				->order('id', 'asc')
				->select();

			$suppliers = array();
			foreach ($results as $item) {
				array_push($suppliers,
					array('id' => $item['id'],
						'type' => $types[$this->findType($item['typeId'], $types)]['type'],
						'typeDescription' => $types[$this->findType($item['typeId'], $types)]['description'],
						'name' => $item['name'],
						'imageUrl' => $item['imageUrl'],
						'homeUrl' => $item['homeUrl']));
			}

			$this->assign('products', $products);
			$this->assign('types', $types);
			$this->assign('suppliers', $suppliers);
		}

		return $this->fetch('detail');
	}
}
