<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PostController extends BaseController
{
	// 发布文章
    public function publish(Request $request) {
    	$title = $request->input('title');
    	$post_type = $request->input('type',0);
    	$subject = $request->input('subject');
    	$content = $request->input('content');
    	$comment = $request->input('comment',0);
    	$bigpic = $request->input('bigpic');
    	$user_id = 1;

    	if($post_type == 1) {
    		// 获取文本框中的第一张图片

    		// 进行图片裁剪
    		$storage = '';

    	} elseif($post_type == 2) {
    		// $bigpic = $request->input('bigpic');

    		// 进行图片裁剪
    		$storage = '';
	   	} else { 

	   	}

	   	$data = [
	   		'title' => $title,
	   		'storage' => $storage,
	   		'bigpic' => $bigpic,
	   		'post_type' => $post_type,
	   		'subject' => $subject,
	   		'content' => $content,
	   		'user_id' => $user_id,
	   		'comment' => $comment,
	   		'publish_time' => time(),
	   	];

	   	$post_id = DB::table('post')->insertGetId($data);

	   	if($post_id > 0) {
	   		// 更新我的文章数
	   		DB::table('user_extras')->where('user_id',$user_id)->increment('posts_count',1);

	   		return response()->json(['message'=>'success','status_code' => 200]);
	   	} else {
	   		return response()->json(['message'=>'Publish post fail','status_code' => 500]);
	   	}
    }
}
