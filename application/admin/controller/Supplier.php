<?php

namespace app\admin\controller;
use app\admin\controller\Base;

require_once 'Helper.php';

class Supplier extends Base {

	private $listRows = 10;

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
			$localPath = getUploadLocalPath() . 'supplier/' . $name . '/';
			createDir($localPath);

			$urlPath = getUploadUrlPath() . 'supplier/' . $name . '/';
			$url = moveFile('img', $localPath, $urlPath, getRandFileName('.jpg'));
			if (!$url) {
				delFile($localPath);
				$this->error('上传文件失败，请重试！');
			}
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
		$name = db('supplier')->find($id)['name'];
		if (db('supplier')->delete($id)) {
			$localPath = './upload/supplier/' . $name . '/';
			delDir($localPath);
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
			$localPath = getUploadLocalPath() . 'supplier/' . $name . '/';
			createDir($localPath);

			$url = '';
			$editImg = input('editImg');

			// editImg 只能可能有3个值
			// 0 代表删除旧图
			// 1 代表修改旧图
			// -1 代表未做修改
			if ($editImg == 0) {
				// 0 删除图片
				delFile($localPath);
				$url = null;
			} elseif ($editImg == 1) {
				// 1 修改图片
				// 图片有修改时，先删除原先的旧图，再上传新图
				delFile($localPath);
				$urlPath = getUploadUrlPath() . 'supplier/' . $name . '/';
				$url = moveFile('img', $localPath, $urlPath, getRandFileName('.jpg'));
				if (!$url) {
					delFile($localPath);
					$this->error('上传文件失败，请重试！');
				}
			} else {
				// -1 未做任何修改
				$url = $supplier['imageUrl'];
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