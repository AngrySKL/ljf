<?php
namespace app\admin\controller;
use app\admin\controller\Base;

require_once 'Helper.php';

class Product extends Base {
	private $listRows = 10;

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

	private function moveFile($smallName, $bigName, $localPath, $urlPath, &$finalSmallName, &$finalBigName, $addOrEdit) {
		$smallPics = request()->file($smallName);
		$bigPics = request()->file($bigName);

		// 0 add 1 edit
		if ($addOrEdit == 1) {
			if (empty($smallPics) || empty($bigPics)) {
				return;
			} else {
				delFile($localPath);
			}
		}

		$count = count($smallPics);
		for ($i = 0; $i < $count; $i++) {
			$prefix = getRandChar(10);

			$smallName = $prefix . '.jpg';
			$smallPic = $smallPics[$i];
			if ($smallPic->move($localPath, $smallName)) {
				$finalSmallName = $finalSmallName . $urlPath . $smallName . ';';
			} else {
				return false;
			}

			$bigName = $prefix . '@2x.jpg';
			$bigPic = $bigPics[$i];
			if ($bigPic->move($localPath, $bigName)) {
				$finalBigName = $finalBigName . $urlPath . $bigName . ';';
			} else {
				return false;
			}
		}
	}

	public function lst() {
		$results = db('product')
			->order('id', 'asc')
			->paginate($this->listRows);

		$list = array();
		foreach ($results as $product) {
			$urls = array();
			$smallUrls = $this->getValidUrls($product['smallUrls']);
			$bigUrls = $this->getValidUrls($product['bigUrls']);

			foreach ($smallUrls as $index => $url) {
				array_push($urls, array('smallUrl' => $smallUrls[$index],
					'bigUrl' => $bigUrls[$index]));
			}

			array_push($list, array('id' => $product['id'],
				'name' => $product['name'],
				'description' => $product['description'],
				'urls' => $urls));
		}

		$this->assign('list', $list);
		return $this->fetch();
	}

	public function add() {
		if (request()->isPost()) {
			$name = input('name');
			$localPath = './upload/product/' . $name . '/';
			createDir($localPath);

			$urlPath = SITE_URL . '/public/upload/product/' . $name . '/';
			$smallUrls = '';
			$bigUrls = '';
			$this->moveFile('smallImgs', 'bigImgs', $localPath, $urlPath, $smallUrls, $bigUrls, 0);

			$data = ['name' => $name,
				'description' => input('description'),
				'smallUrls' => $smallUrls,
				'bigUrls' => $bigUrls];
			if (db('product')->insert($data)) {
				return $this->success('添加商品成功！', 'lst');
			} else {
				return $this->error('添加商品失败！');
			}
		}
		return $this->fetch();
	}

	public function del() {
		$id = input('id');
		$name = db('product')->find($id)['name'];
		if (db('product')->delete($id)) {
			$localPath = './upload/product/' . $name . '/';
			delDir($localPath);
			$this->success('删除商品成功！', 'lst');
		} else {
			$this->error('删除商品失败！');
		}
		return $this->fetch();
	}

	public function edit() {
		$id = input('id');
		$product = db('product')->find($id);
		if (request()->isPost()) {
			// 这里强制约定，小图和大图的操作一一对应。
			// * 编辑的时候把小图删了，却没有删大图，那么，这里会把大图也删了。
			// * 编辑的时候如果只换了小图，却没有换大图，那么，编辑无效。
			$editSmallImgs = input('editSmallImgs');
			$editBigImgs = input('editBigImgs');

			$name = input('name');
			$localPath = './upload/product/' . $name . '/';
			createDir($localPath);

			$urlPath = SITE_URL . '/public/upload/product/' . $name . '/';

			$data = array();
			$data['id'] = $id;
			$data['name'] = input('name');
			$data['description'] = input('description');
			if ($editSmallImgs == -1) {
				if ($editBigImgs != -1) {
					// 小图没有变化而大图变了
					$this->error('修改商品失败，小图和大图必须同步修改！');
				}
			} else if ($editSmallImgs == 0) {
				if ($editBigImgs != 0) {
					// 小图删了而大图没删
					$this->error('修改商品失败，小图和大图必须同步修改！');
				} else {
					// 小图和大图都删了
					delFile($localPath);
					$data['smallUrls'] = null;
					$data['bigUrls'] = null;
				}
			} else if ($editSmallImgs == 1) {
				if ($editBigImgs != 1) {
					// 小图修改了而大图没修改
					$this->error('修改商品失败，小图和大图必须同步修改！');
				} else {
					// 小图和大图都修改了
					$smallUrls = '';
					$bigUrls = '';
					$this->moveFile('smallImgs', 'bigImgs', $localPath, $urlPath, $smallUrls, $bigUrls, 1);
					$data['smallUrls'] = $smallUrls;
					$data['bigUrls'] = $bigUrls;
				}
			}

			$save = db('product')->update($data);
			if ($save !== false) {
				$this->success('修改商品成功！', 'lst');
			} else {
				$this->success('修改商品失败！');
			}

		}

		$this->assign('product', $product);
		return $this->fetch();
	}
}