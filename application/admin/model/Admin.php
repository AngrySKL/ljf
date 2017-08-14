<?php
namespace app\admin\model;
use think\Model;

class Admin extends Model {
	public function login($data) {
		$user = db('admin')->where('name', '=', $data['name'])->find();
		if ($user) {
			if ($user['password'] == sha1($data['password'])) {
				session('username', $user['name']);
				return true;
			}
		}

		return false;
	}
}