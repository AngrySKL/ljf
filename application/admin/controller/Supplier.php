<?php
namespace app\admin\controller;
use app\admin\controller\Base;

class Supplier extends Base {

	private $listRows = 10;

	private function delFile($dirName, $self = false) {
		if (!file_exists($dirName)) {
			return false;
		}
		if (is_file($dirName) || is_link($dirName)) {
			return unlink($dirName);
		}
		$dir = dir($dirName);
		if ($dir) {
			while (false !== $entry = $dir->read()) {
				if ($entry == '.' || $entry == '..') {
					continue;
				}
				$this->delFile($dirName . '/' . $entry);
			}
		}
		$dir->close();
		$self && rmdir($dirName);
	}

	private function delDir($dir) {
		//先删除目录下的文件：
		$dh = opendir($dir);
		while ($file = readdir($dh)) {
			if ($file != "." && $file != "..") {
				$fullpath = $dir . "/" . $file;
				if (!is_dir($fullpath)) {
					unlink($fullpath);
				} else {
					$this->delDir($fullpath);
				}
			}
		}

		closedir($dh);
		//删除当前文件夹：
		if (rmdir($dir)) {
			return true;
		} else {
			return false;
		}
	}

	private function createDir($dir) {
		if (!is_dir($dir)) {
			mkdir($dir, 0777, true);
		}
	}

	private function getRandChar($length) {
		$str = null;
		$strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
		$max = strlen($strPol) - 1;

		for ($i = 0; $i < $length; $i++) {
			$str .= $strPol[rand(0, $max)];
		}

		return $str;
	}

	private function moveFile($fileName, $localPath, $urlPath, &$finalName, $addOrEdit) {
		$pic = request()->file($fileName);

		// 0 add 1 edit
		if ($addOrEdit == 1) {
			if (empty($pic)) {
				return;
			} else {
				$this->delFile($localPath);
			}
		}

		$saveName = $this->getRandChar(10) . '.jpg';
		if ($pic->move($localPath, $saveName)) {
			$finalName = $urlPath . $saveName;
			return true;
		} else {
			return false;
		}
	}

	public function lst() {
		$list = db('supplier')
			->order('id', 'asc')
			->paginate($this->listRows);
		$pageRender = $list->render();

		$this->assign('list', $list);
		$this->assign('pageRender', $pageRender);
		return $this->fetch();
	}

	public function add() {
		$types = db('suppliertype')
			->order('id', 'asc')
			->select();

		if (request()->isPost()) {
			$name = input('name');
			$localPath = './upload/supplier/' . $name . '/';
			$this->createDir($localPath);

			$urlPath = SITE_URL . '/public/upload/supplier/' . $name . '/';
			$url = '';
			$this->moveFile('img', $localPath, $urlPath, $url, 0);

			$data = ['typeId' => input('typeId'),
				'name' => $name,
				'imageUrl' => $url,
				'homeUrl' => input('homeUrl')];
			if (db('supplier')->insert($data)) {
				return $this->success('添加供应商成功！', 'lst');
			} else {
				return $this->error('添加供应商失败！');
			}
		}

		$this->assign('types', $types);
		return $this->fetch();
	}

	public function del() {
		$id = input('id');
		if (db('supplier')->delete($id)) {
			$this->success('删除供应商成功！', 'lst');
		} else {
			$this->error('删除供应商失败！');
		}
		return $this->fetch();
	}

	public function edit() {
		$types = db('suppliertype')
			->order('id', 'asc')
			->select();
		$id = input('id');
		$supplier = db('supplier')->find($id);

		if (request()->isPost()) {
			$name = input('name');
			$localPath = './upload/supplier/' . $name . '/';
			$this->createDir($localPath);

			$url = '';
			$removeImg = input('removeImg');
			// 0 删除文件
			if ($removeImg == 0) {
				$this->delFile($localPath);
				$url = null;
			} else {
				$urlPath = SITE_URL . '/public/upload/supplier/' . $name . '/';
				$this->moveFile('img', $localPath, $urlPath, $url, 1);
			}

			$data = ['id' => input('id'),
				'typeId' => input('typeId'),
				'name' => $name,
				'imageUrl' => $url,
				'homeUrl' => input('homeUrl')];

			$save = db('supplier')->update($data);
			if ($save !== false) {
				$this->success('修改供应商成功！', 'lst');
			} else {
				$this->success('修改供应商失败！');
			}
		}

		$this->assign('types', $types);
		$this->assign('supplier', $supplier);
		return $this->fetch();
	}

	public function typeLst() {
		$list = db('suppliertype')
			->order('id', 'asc')
			->paginate($this->listRows);
		$pageRender = $list->render();

		$this->assign('list', $list);
		$this->assign('pageRender', $pageRender);

		return $this->fetch();
	}

	public function typeAdd() {
		if (request()->isPost()) {
			$data = ['type' => input('type'),
				'description' => input('description')];

			if (db('suppliertype')->insert($data)) {
				return $this->success('添加供应商类别成功！', 'typeLst');
			} else {
				return $this->error('添加供应商类别失败！');
			}
		}
		return $this->fetch();
	}

	public function typeDel() {
		$id = input('id');
		if (db('suppliertype')->delete($id)) {
			$this->success('删除供应商类别成功！', 'typeLst');
		} else {
			$this->error('删除供应商类别失败！');
		}
		return $this->fetch();
	}

	public function typeEdit() {
		$id = input('id');
		$type = db('suppliertype')->find($id);
		if (request()->isPost()) {
			$data = [
				'id' => input('id'),
				'type' => input('type'),
				'description' => input('description'),
			];

			$save = db('suppliertype')->update($data);
			if ($save !== false) {
				$this->success('修改供应商类别成功！', 'typeLst');
			} else {
				$this->success('修改供应商类别失败！');
			}

			return;
		}
		$this->assign('type', $type);
		return $this->fetch();
	}
}