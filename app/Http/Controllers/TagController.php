<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TagController extends Controller
{

    public function index(Request $request) {
        $user_id = $request->input('uid',0);

        // 获取所有标签大类
        $tags = DB::table('tag_categories')->orderBy('weight','desc')->get();

        // 获取我关注的标签
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
            if($user_id > 0) {
                foreach ($tag as $key) {
                    $key->following = in_array($key->id, $ftids) ? 1 : 0;
                }
            }
            $tag_categories->tags = $tag;
        }
        return response()->json($tags);
    }

    public function follow(Request $request) {
    	$user_id = $request->input('uid', 0);
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
    		}
    	}
    }

    public function unfollow(Request $request) {
    	$user_id = $request->input('uid', 0);
    	$tag_id = $request->input('tagid', 0);

    	$id = DB::table('user_tag_follow')->where(['user_id' => $user_id,'tag_id' => $tag_id])->value('id');

    	if($id > 0) {
    		// 删除关注对应记录
    		DB::table('user_tag_follow')->where('id',$id)->delete();
    		// 我关注的标签 - 1
    		DB::table('user_extras')->where('user_id',$user_id)->decrement('tags_count',1);
    		// 标签被关注数 - 1
    		DB::table('tags')->where('id',$tag_id)->decrement('follow_count',1);
    	}
    }

    public function following(Request $request) {
    	$user_id = $request->input('uid',0);
    	$page = $request->input('p',1);

    	// 获取我标签关注总数
    	$tagCount = DB::table('user_tag_follow')->where('user_id',$user_id)->count();
    	$pageNum = 20;
    	$totalPage = ceil($tagCount/$pageNum);

    	// 获取我关注的所有标签id
    	$myTagArr = DB::table('user_tag_follow')->where('user_id',$user_id)->pluck('tag_id');

    	//获取标签数组
    	$tags = DB::table('tags')->forPage($page,$pageNum)->whereIn('id',$myTagArr)->get();

    	$data = [
    		'count' => $tagCount,
    		'totalpage' => $totalPage,
    		'tags' => $tags
    	];

    	return response()->json($data);
    }
}
