<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use JWTAuth;


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
    	$tagids = $request->input('tags');
    	$user_id = 1;
    	$tagArr = [];

    	// 判断标签是否为空 标签去重复未完成
    	if($tagids != null) {
    		$tag_str = '';
    		$tagArr = explode(',', $tagids);
    		$tags = DB::table('tags')->select('id','tag_name')
    								->whereIn('id',$tagArr)
    								->get();

    		foreach($tags as $tag) {
    			$tag_str .=  $tag->id.':'. $tag->tag_name .';';
    		}

    		$tag_str = mb_substr($tag_str, 0, -1);
    	}

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
	   		// 增加标签文章数
	   		DB::table('tags')->increment('post_count',1);
	   		//增加文章标签对应关系
	   		foreach($tags as $tag) {
	   			DB::table('user_post_tags')->insert([
	   				'user_id' => 1,
	   				'post_id' => $post_id,
	   				'tag_id' => $tag->id
	   			]);
	   		}

	   		return response()->json(['message'=>'success','status_code' => 200]);
	   	} else {
	   		return response()->json(['message'=>'Publish post fail','status_code' => 500]);
	   	}
    }

    // 喜欢文章
    public function like(Request $request, $id) {
    	$user_id = JWTAuth::parseToken()->authenticate()->id;
    	$post_id = $id;

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

                // 发送短消息


                return response()->json(['message'=>'success','status_code' => 200]);
            } else {
            	return response()->json(['message'=>'fail','status_code' => 500]);
            }
        } else {
        	return response()->json(['message'=>'Post already like','status_code' => 500]);
        }
    }

    // 取消喜欢的文章
    public function unlike(Request $request, $id) {
    	
    	$user_id = JWTAuth::parseToken()->authenticate()->id;
    	$post_id = $id;

    	$like_id = DB::table('post_like')->where(['user_id'=> $user_id,'post_id'=>$post_id])->value('id');

    	if($like_id > 0) {
    		DB::table('post_like')->delete($like_id);
    		DB::table('post')->where('id',$post_id)->decrement('like_count',1);

    		// 发送短消息

    		return response()->json(['message'=>'success','status_code' => 200]);
    	} else {
    		return response()->json(['message'=>'Post already unlike','status_code' => 500]);
    	}  
    }

    public function collection(Request $request, $id) {
    	$user_id = JWTAuth::parseToken()->authenticate()->id;
        $post_id = $id;

        $collection_id = DB::table('post_collections')->where(['user_id'=> $user_id,'post_id'=>$post_id])->value('id');

        if($collection_id == null) {
           $data = [
	           'user_id' => $user_id,
	           'post_id' => $post_id,
	           'create_time' =>time()
        	];

        	// 插入主表记录
        	$id = DB::table('post_collections')->insertGetId($data);

            if($id > 0) {
                // 更新文章表喜欢数
                DB::table('post')->where('id',$post_id)->increment('collection_count',1);

                return response()->json(['message'=>'success','status_code' => 200]);
            } else {
            	return response()->json(['message'=>'fail','status_code' => 500]);
            }
        } else {
        	return response()->json(['message'=>'Post already collection','status_code' => 500]);
        }
    }

    public function uncollection(Request $request, $id) {
    	$user_id = $request->input('uid');
        $post_id = $id;
        $type = $request->input('type');

        if($type == 1) {
            // 普通单个取消收藏
            $collection_id = DB::table('post_collections')->where(['user_id'=> $user_id,'post_id'=>$post_id])->value('id');

            if($collection_id > 0) {
                DB::table('post_collections')->delete($collection_id);
                DB::table('post')->where('id',$post_id)->decrement('collection_count',1);

                return response()->json(['message'=>'success','status_code' => 200]);
            } else {
                return response()->json(['message'=>'Post already uncollection','status_code' => 500]);
            } 
        } elseif ($type == 2) {
            // 获取收藏文章ID
            $postidArr = DB::table('post_collections')->where('user_id',$user_id)->pluck('post_id');
            // 减少文章收藏数
            DB::table('post')->whereIn('id',$postidArr)->decrement('collection_count',1);
            // 删除文章和收藏对应数据表
            DB::table('post_collections')->where('user_id',$user_id)->delete();

            return response()->json(['message'=>'success','status_code' => 200]);
        } else {

        }
    }
}
