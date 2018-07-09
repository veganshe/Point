<?php

namespace App\Tool;

use Image;

class ImageUpload {

	protected $allowed_ext = ['png','jpg','gif','jpeg'];

	public function avatar($file, $folder, $file_prefix, $max_width = false) {
		// 建立文件夹目标
		$folder_name = "uploads/static/$folder/".date("Ym/d", time());
		$folder_temp_name = "$folder/".date("Ym/d", time());

		// 具体路径
		$upload_path = public_path().'/'.$folder_name;
		// 获取后缀名
		$extension = strtolower($file->getClientOriginalExtension()) ?: 'png';
		// 拼接名字
		$filename = $file_prefix . '_' .time().'_' . str_random(10) . '.' . $extension;
		if(! in_array($extension, $this->allowed_ext)) {
			return false;
		}
		$file->move($upload_path, $filename);
		return ['path' => "$folder_temp_name/$filename"];
	}
}

?>