<?php
namespace app\admin\controller;

function delFile($dirName, $self = false) {
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
			delFile($dirName . '/' . $entry);
		}
	}
	$dir->close();
	$self && rmdir($dirName);
}

function delDir($dir) {
	//先删除目录下的文件：
	$dh = opendir($dir);
	while ($file = readdir($dh)) {
		if ($file != "." && $file != "..") {
			$fullpath = $dir . "/" . $file;
			if (!is_dir($fullpath)) {
				unlink($fullpath);
			} else {
				delDir($fullpath);
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

function createDir($dir) {
	if (!is_dir($dir)) {
		mkdir($dir, 0777, true);
	}
}

function getRandChar($length) {
	$str = null;
	$strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
	$max = strlen($strPol) - 1;

	for ($i = 0; $i < $length; $i++) {
		$str .= $strPol[rand(0, $max)];
	}

	return $str;
}