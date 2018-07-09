<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use JWTAuth;

class CommentController extends BaseController
{
    public function publish(Request $request) 
    {
        $user_id = JWTAuth::parseToken()->authenticate()->id;
        $post_id = $request->input('postid');
        $content = $request->input('body');

        $data = [
            'post_id' => $post_id,
            'user_id' => $user_id,
            'body' => $content,
            'publish_time' => time()
        ];

        $comment_id = DB::table('post_comments')->insertGetId($data);

        if($comment_id) {

            DB::table('post')->where('id',$post_id)->increment('comment_count',1);

            return response()->json(['message'=>'success','status_code' => 200]);
        } else {
            return response()->json(['message'=>'Published commment fail','status_code' => 500]);
        }
    }


    public function masking(Request $request, $id)
    {
        $user_id = JWTAuth::parseToken()->authenticate()->id;
        $comment_id = $id;

        $comment = DB::table('post_comments')
                       ->select('post_id','user_id')
                       ->where(['id' => $comment_id, 'masking' => 0])
                       ->first();
        if (!$comment) {
            return response()->json(['message'=>'Comment is empty','status_code' => 500]);
        }               

        $author_id = DB::table('post')->where('id',$comment->post_id)->value('user_id');

        if($user_id == $comment->user_id || $user_id == $author_id) {
            // 隐藏评论
            DB::table('post_comments')->where('id',$comment_id)->update(['masking' => 1]);
            // 评论数 - 1
            DB::table('post')->where('id',$comment->post_id)->decrement('comment_count',1);

            return response()->json(['message'=>'success','status_code' => 200]);

        } else {
            return response()->json(['message'=>'Deleted comment fail','status_code' => 500]);
        }
    }

    public function like(Request $request, $id) 
    {
    	$user_id = JWTAuth::parseToken()->authenticate()->id;
    	$post_id = $request->input('postid');
    	$comment_id = $id;

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
	            // 更新评论表喜欢数
	            DB::table('post_comments')->where('id',$comment_id)->increment('like_count',1);

	            return response()->json(['message'=>'success','status_code' => 200]);
	        } 
	    } else {
	    	return response()->json(['message'=>'comment already liked','status_code' => 500]);
	    }
    }

    public function unlike(Request $request, $id) 
    {
    	$user_id = JWTAuth::parseToken()->authenticate()->id;
    	$comment_id = $id;

    	$comment_like = DB::table('post_comment_like')
    						->select('id','post_id')
    						->where(['user_id'=> $user_id,'comment_id'=>$comment_id])
    						->first();
        // dd($comment_like);
    	if($comment_like) {
    		DB::table('post_comment_like')->delete($comment_like->id);
    		DB::table('post_comments')->where('id',$comment_id)->decrement('like_count',1);

    		return response()->json(['message'=>'success','status_code' => 200]);
    	} else {
    		return response()->json(['message'=>'like already cancel','status_code' => 500]);
    	}  
    }
}
