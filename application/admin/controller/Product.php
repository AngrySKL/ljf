<?php
namespace app\admin\controller;
use app\admin\controller\Base;

require_once 'Helper.php';

class Product extends Base {

	private $listRows = 10;

	private function checkImgs() {
		$smallPics = request()->file('smallImgs');
		if (empty($smallPics)) {
			$this->error('上传小图失败，请重新上传！');
		}

		$smallCount = is_array($smallPics) ? count($smallPics) : 1;

		$bigPics = request()->file('bigImgs');
		if (empty($bigPics)) {
			$this->error('上传大图失败，请重新上传！');
		}

		$bigCount = is_array($bigPics) ? count($bigPics) : 1;
		if ($smallCount != $bigCount) {
			$this->error('小图和大图必须一一对应，请重新上传！');
		}

		return $smallCount;
	}

	public function lst() {
		$results = db('product')
			->order('id', 'asc')
			->paginate($this->listRows);

		$list = array();
		foreach ($results as $product) {
			$urls = array();
			$smallUrls = stringToArray($product['smallUrls'], ';');
			$bigUrls = stringToArray($product['bigUrls'], ';');

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

			$imgCount = $this->checkImgs();

			$smallNames = null;
			$bigNames = null;
			if ($imgCount == 1) {
				$smallNames = getRandFileName('.jpg');
				$bigNames = str_replace('.', '@2x.', $smallNames);
			} else {
				$smallNames = array();
				$bigNames = array();
				for ($i = 0; $i < $imgCount; $i++) {
					$smallName = getRandFileName('.jpg');
					while (in_array($smallName, $smallNames)) {
						$smallName = getRandFileName('.jpg');
					}

					array_push($smallNames, $smallName);
					array_push($bigNames, str_replace('.', '@2x.', $smallName));
				}
			}

			$name = input('name');
			$localPath = './upload/product/' . $name . '/';
			createDir($localPath);

			$urlPath = SITE_URL . '/public/upload/product/' . $name . '/';

			$smallUrls = moveFile('smallImgs', $localPath, $urlPath, $smallNames);
			$bigUrls = moveFile('bigImgs', $localPath, $urlPath, $bigNames);

			if (!$smallUrls || !$bigUrls) {
				delFile($localPath);
				$this->error('上传图片失败，请重试！');
			}

			$data = ['name' => $name,
				'description' => input('description'),
				'smallUrls' => $imgCount == 1 ? $smallUrls : implode(';', $smallUrls),
				'bigUrls' => $imgCount == 1 ? $bigUrls : implode(';', $bigUrls)];
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
			// 小图和大图皆由用户上传，这里不做图片处理。
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
					// 小图和大图都修改了,先删除旧图，再上传新图
					delFile($localPath);

					$imgCount = $this->checkImgs();
					$smallNames = null;
					$bigNames = null;
					if ($imgCount == 1) {
						$smallNames = getRandFileName('.jpg');
						$bigNames = str_replace('.', '@2x.', $smallNames);
					} else {
						$smallNames = array();
						$bigNames = array();
						for ($i = 0; $i < $imgCount; $i++) {
							$smallName = getRandFileName('.jpg');
							while (in_array($smallName, $smallNames)) {
								$smallName = getRandFileName('.jpg');
							}

							array_push($smallNames, $smallName);
							array_push($bigNames, str_replace('.', '@2x.', $smallName));
						}
					}
					$smallUrls = moveFile('smallImgs', $localPath, $urlPath, $smallNames);
					$bigUrls = moveFile('bigImgs', $localPath, $urlPath, $bigNames);

					if (!$smallUrls || !$bigUrls) {
						delFile($localPath);
						$this->error('上传图片失败，请重试！');
					}

					$data['smallUrls'] = $imgCount == 1 ? $smallUrls : implode(';', $smallUrls);
					$data['bigUrls'] = $imgCount == 1 ? $bigUrls : implode(';', $bigUrls);
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