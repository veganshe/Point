<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use JWTAuth;

class CommentController extends BaseController
{
    public function like(Request $request) 
    {
    	$user_id = JWTAuth::parseToken()->authenticate()->id;
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

	            return response()->json(['message'=>'success','status_code' => 200]);
	        } 
	    } else {
	    	return response()->json(['message'=>'comment already liked','status_code' => 500]);
	    }
    }

    public function unlike(Request $request) 
    {
    	$user_id = JWTAuth::parseToken()->authenticate()->id;
    	$comment_id = $request->input('commentid');

    	$comment_like = DB::table('post_comment_like')
    						->select('id','post_id')
    						->where(['user_id'=> $user_id,'comment_id'=>$comment_id])
    						->first();

    	if($comment_like->id > 0) {
    		DB::table('post_comment_like')->delete($comment_like->id);
    		DB::table('post')->where('id',$comment_like->post_id)->decrement('comment_count',1);

    		return response()->json(['message'=>'success','status_code' => 200]);
    	} else {
    		return response()->json(['message'=>'like already cancel','status_code' => 500]);
    	}  
    }
}
