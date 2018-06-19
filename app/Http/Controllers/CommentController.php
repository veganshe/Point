<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommentController extends Controller
{
     public function like(Request $request) {
    	$user_id = $request->input('uid');
    	$post_id = $request->input('postid');
    	$comment_id = $request->input('commentid');

        $comment_like_id = DB::table('post_comment_like')
        				->where(['user_id'=> $user_id,'post_id'=>$post_id,'comment_id'=>$comment_id])
        				->value('id');

        if($comment_like_id == null) {
           $data = [
	            'user_id' => $user_id,
	            'post_id' => $post_id,
	            'comment_id'=> $comment_id,
	            'create_time' =>time()
	        ];

	        // 插入主表记录
	        $comment_like_id = DB::table('post_comment_like')->insertGetId($data);

	            if($comment_like_id > 0) {
	                // 更新文章表喜欢数
	                DB::table('post')->where('id',$post_id)->increment('comment_count',1);
	                echo $comment_like_id;
	            } 
	    }
    }

    public function unlike(Request $request) {
    	$user_id = $request->input('uid');
    	$comment_id = $request->input('commentid');

    	$comment_like = DB::table('post_comment_like')
    						->select('id','post_id')
    						->where(['user_id'=> $user_id,'comment_id'=>$comment_id])
    						->first();

    	if($comment_like->id > 0) {
    		DB::table('post_comment_like')->delete($comment_like->id);
    		DB::table('post')->where('id',$comment_like->post_id)->decrement('comment_count',1);
    	} else {
    		echo "comment_like_id不存在";
    	}  
    }
}
