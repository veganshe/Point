<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use JWTAuth;

class TagController extends BaseController
{
	// 标签首页
    public function index(Request $request) {
        $user_id = $JWTAuth::parseToken()->authenticate()->id;

        // 获取所有标签大类
        $tags = DB::table('tag_categories')->orderBy('weight','desc')->get();

        // 获取我关注的标签(以后改为 Redis 获取)
        if($user_id > 0) {
            $fti = DB::table('user_tag_follow')->where('user_id',$user_id)->pluck('tag_id');
            // 集合转换为数组
            $ftids = $fti->toArray();
        }

        foreach($tags as $tag_categories) {
            // 标签关系对应表
            $tagids = DB::table('tag_relationship')->where('tag_cateid',$tag_categories->id)->pluck('tag_id'); 
            $tag = DB::table('tags')
                      ->where('isshow',1)
                      ->whereIn('id',$tagids)
                      ->get();
            
            // 判断是否有关注
            foreach ($tag as $key) {
                $key->following = in_array($key->id, $ftids) ? 1 : 0;
            }
            
            $tag_categories->tags = $tag;
        }
        return response()->json($tags);
    }

    // 关注标签
    public function follow(Request $request) {
    	$user_id = $JWTAuth::parseToken()->authenticate()->id;
    	$tag_id = $request->input('tagid', 0);

    	$id = DB::table('user_tag_follow')->where(['user_id' => $user_id,'tag_id' => $tag_id])->first();

    	if($id == null) {
    		$data = [
    			'user_id' => $user_id,
    			'tag_id' => $tag_id
    		];
    		$id = DB::table('user_tag_follow')->insertGetId($data);

    		if($id > 0) {
    			// 我关注的标签 + 1
    			DB::table('user_extras')->where('user_id',$user_id)->increment('tags_count',1);
    			// 标签被关注数 + 1
    			DB::table('tags')->where('id',$tag_id)->increment('follow_count',1);

    			// 更新我的关注 Redis

    			return response()->json(['message'=>'success','status_code' => 200]);
    		} else {
    			return response()->json(['message'=>'Following tag is fail','status_code' => 500]);
    		}
    	} else {
    		return response()->json(['message'=>'Tag already followed','status_code' => 500]);
    	}
    }

    // 取消标签关注
    public function unfollow(Request $request) {
    	$user_id = $JWTAuth::parseToken()->authenticate()->id;
    	$tag_id = $request->input('tagid', 0);

    	$id = DB::table('user_tag_follow')->where(['user_id' => $user_id,'tag_id' => $tag_id])->value('id');

    	if($id > 0) {
    		// 删除关注对应记录
    		DB::table('user_tag_follow')->where('id',$id)->delete();
    		// 我关注的标签 - 1
    		DB::table('user_extras')->where('user_id',$user_id)->decrement('tags_count',1);
    		// 标签被关注数 - 1
    		DB::table('tags')->where('id',$tag_id)->decrement('follow_count',1);

    		// 更新 Redis 我关注的标签（未完成）


    		return response()->json(['message'=>'success','status_code' => 200]);

    	} else {
    		return response()->json(['message'=>'Tag already unfollowed','status_code' => 500]);
    	}
    }
}
