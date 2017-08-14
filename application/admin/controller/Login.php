<?php
namespace app\admin\controller;
use app\admin\model\Admin;
use think\Controller;

class Login extends Controller {
	public function index() {
		if (request()->isPost()) {
			$admin = new Admin();
			$data = input('post.');
			if ($admin->login($data)) {
				$this->redirect('index/index');
			} else {
				$this->error('信息错误');
			}

		}

		return $this->fetch('login');
	}
}