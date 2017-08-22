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
	if (!is_dir($dir)) {
		return;
	}

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

// 1. $fileName和$saveName的数量一一对应
// * 如果fileName中只包含一个文件，那么只需要一个saveName
// * 如果fileName中包含多个文件，那么需要saveName以数组的形式存储每个文件对应的文件名
// 返回保存的文件的url地址或者包含所有保存文件的url地址的数组
function moveFile($fileName, $localPath, $urlPath, $saveName) {
	if (empty($fileName) ||
		empty($localPath) ||
		empty($urlPath)) {
		return false;
	}

	if (!is_array($saveName)) {
		$pic = request()->file($fileName);
		if (count($pic) > 1) {
			return false;
		}

		if ($pic[0]->move($localPath, $saveName)) {
			return $urlPath . $saveName;
		} else {
			return false;
		}

	} else {
		$pics = request()->file($fileName);
		if (!is_array($pics) ||
			count($pics) != count($saveName)) {
			return false;
		}

		$urls = array();
		foreach ($pics as $index => $pic) {
			if ($pic->move($localPath, $saveName[$index])) {
				array_push($urls, $urlPath . $saveName[$index]);
			} else {
				return false;
			}
		}

		return $urls;
	}
}

function getRandFileName($extension) {
	return getRandChar(10) . $extension;
}

function stringToArray($source, $separator) {
	$list = explode($separator, $source);
	$result = array();
	foreach ($list as $item) {
		if (!empty($item)) {
			array_push($result, $item);
		}
	}

	return $result;
}

function meargeArray($array1, $key1, $array2, $key2) {
	if (empty($array1) ||
		empty($key1) ||
		empty($array2) ||
		empty($key2)) {
		return false;
	}

	if (count($array1) != count($array2)) {
		return false;
	}

	$newArray = array();
	foreach ($array1 as $index => $value) {
		array_push($newArray, array($key1 => $array1[$index],
			$key2 => $array2[$index]));
	}

	return $newArray;
}