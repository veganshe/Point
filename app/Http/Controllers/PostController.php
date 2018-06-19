<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{
    public function like(Request $request) {
    	$user_id = $request->input('uid');
    	$post_id = $request->input('postid');

        $like_id = DB::table('post_like')->where(['user_id'=> $user_id,'post_id'=>$post_id])->value('id');

        if($like_id == null) {
           $data = [
            'user_id' => $user_id,
            'post_id' => $post_id,
            'create_time' =>time()
        ];

        // 插入主表记录
        $like_id = DB::table('post_like')->insertGetId($data);

            if($like_id > 0) {
                // 更新文章表喜欢数
                DB::table('post')->where('id',$post_id)->increment('like_count',1);
                echo $like_id;
            } 
        }
    }

    public function unlike(Request $request) {
    	
    	$user_id = $request->input('uid');
    	$post_id = $request->input('postid');

    	$like_id = DB::table('post_like')->where(['user_id'=> $user_id,'post_id'=>$post_id])->value('id');

    	if($like_id > 0) {
    		DB::table('post_like')->delete($like_id);
    		DB::table('post')->where('id',$post_id)->decrement('like_count',1);
    	} else {
    		echo "like不存在";
    	}  
    }

    public function collection(Request $request) {
    	$user_id = $request->input('uid');
        $post_id = $request->input('postid');

        $like_id = DB::table('post_collections')->where(['user_id'=> $user_id,'post_id'=>$post_id])->value('id');

        if($like_id == null) {
           $data = [
            'user_id' => $user_id,
            'post_id' => $post_id,
            'create_time' =>time()
        ];

        // 插入主表记录
        $collection_id = DB::table('post_collections')->insertGetId($data);

            if($collection_id > 0) {
                // 更新文章表喜欢数
                DB::table('post')->where('id',$post_id)->increment('collection_count',1);
                echo $collection_id;
            } 
        }
    }

    public function uncollection(Request $request) {
    	$user_id = $request->input('uid');
        $post_id = $request->input('postid');
        $type = $request->input('type');

        if($type == 1) {
            // 普通单个取消收藏
            $collection_id = DB::table('post_collections')->where(['user_id'=> $user_id,'post_id'=>$post_id])->value('id');

            if($collection_id > 0) {
                DB::table('post_collections')->delete($collection_id);
                DB::table('post')->where('id',$post_id)->decrement('collection_count',1);
            } else {
                echo "collection不存在";
            } 
        } elseif ($type == 2) {
            // 获取收藏文章ID
            $postidArr = DB::table('post_collections')->where('user_id',$user_id)->pluck('post_id');
            // 减少文章收藏数
            DB::table('post')->whereIn('id',$postidArr)->decrement('collection_count',1);
            // 删除文章和收藏对应数据表
            DB::table('post_collections')->where('user_id',$user_id)->delete();
        } else {

        }
    }
}
