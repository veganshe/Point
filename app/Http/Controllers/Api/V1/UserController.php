<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Model\UserProfile as UserProfile;
use JWTAuth;

class UserController extends BaseController
{
	// 用户首页
    public function index($id) 
    {
		$userinfo = DB::table('user_profile')
                        ->join('user_extras','user_profile.uid','=','user_extras.user_id')
                        ->where('user_profile.uid', $id)
                        ->first();
        if(!$userinfo) {
            return response()->json(['message'=>'user not exist','status_code' => 500]);
        }

        // 获取 Redis 我的关注人员id
        $followuids = [];

    	$user = [
    		'uid' => $userinfo->uid,
    		'name' => $userinfo->username,
    		'avatar' => $userinfo->avatar,
    		'present' => $userinfo->present,
    		'following' => in_array($userinfo->uid, $followuids) ? 1 : 0,
    		'followers_count' => $userinfo->followers_count,
    		'followings_count' => $userinfo->followings_count,
    		'tags_count' => $userinfo->tags_count,
    		'posts_count' => $userinfo->posts_count
    	];
        return response()->json($user);
    }

    // 关注用户
    public function follow(Request $request, $id) {
    	$user_id = $id;
    	$follower_id = JWTAuth::parseToken()->authenticate()->id;

    	$followid = DB::table('user_follow')->where(['user_id' => $user_id,'follower_id' => $follower_id])->first();

    	if($followid == null) {
    		$data = [
    			'user_id' => $user_id,
    			'follower_id' => $follower_id
    		];
    		$followid = DB::table('user_follow')->insertGetId($data);

    		if($followid > 0) {
    			// 我的关注者人数 + 1
    			DB::table('user_extras')->where('user_id',$follower_id)->increment('followings_count',1);
    			// 关注者的粉丝人数 + 1
    			DB::table('user_extras')->where('user_id',$user_id)->increment('followers_count',1);

    			// 更新 Redis 关注用户缓存（未完成）


    			return response()->json(['message'=>'success','status_code' => 200]);
    		} else {
    			return response()->json(['message'=>'Following is fail','status_code' => 500]);
    		}

    	} else {
    		return response()->json(['message'=>'User already followed','status_code' => 500]);
    	}
    }

    // 取消用户关注
    public function unfollow(Request $request, $id) {
    	$user_id = $id;
    	$follower_id = JWTAuth::parseToken()->authenticate()->id;

    	$followid = DB::table('user_follow')->where(['user_id' => $user_id,'follower_id' => $follower_id])->value('id');

    	if($followid > 0) {
    		// 删除关注对应记录
    		DB::table('user_follow')->where('id',$followid)->delete();
    		// 我的关注者人数 - 1
    		DB::table('user_extras')->where('user_id',$follower_id)->decrement('followings_count',1);
    		// 关注者的粉丝人数 - 1
    		DB::table('user_extras')->where('user_id',$user_id)->decrement('followers_count',1);

    		// 更新 Redis 关注用户缓存（未完成）


    		return response()->json(['message'=>'success','status_code' => 500]);
    	} else {
    		return response()->json(['message'=>'User already unfollowed','status_code' => 500]);
    	}
    }

    // 我正在关注的用户的用户列表
    public function following(Request $request, $id) { 
    	$user_id = $id;
    	$page = $request->input('page',1);

    	// 获取关注者总数
    	// $followerCount = DB::table('user_follow')->where('follower_id',$user_id)->count();
    	// 获取关注者id集合
    	$ids = DB::table('user_follow')->where('follower_id',$user_id)->pluck('user_id');
    	// 以后更改为 Redis 获取我关注人的id
    	$myids = [];

    	$pageNum = 20;

    	$followers = DB::table('user_profile')
    					->select('uid','username','avatar','followings_count','followers_count')
    					->join('user_extras','user_profile.uid','=','user_extras.user_id')
    					->whereIn('user_profile.uid',$ids)
    					->forPage($page,$pageNum)
    					->get();
    	foreach($followers as $follower) {
    		$follower->following = in_array($follower->uid, $myids) ? 1 : 0;
    	}

    	return response()->json($followers)->setStatusCode(200);
    }

    // 我粉丝列表
    public function followers(Request $request, $id) {
    	$user_id = $id;
    	$page = $request->input('page',1);

    	// 获取粉丝总数
    	$followerCount = DB::table('user_follow')->where('user_id',$user_id)->count();
    	// 获取粉丝id集合
    	$followerids = DB::table('user_follow')->where('user_id',$user_id)->pluck('follower_id');
    	// 获取我关注人id集合 (更改为 Redis)
    	$followingcollect = DB::table('user_follow')->where('follower_id',$user_id)->pluck('user_id');
    	$followingids = $followingcollect->toArray();

    	$pageNum = 20;
    	$totalPage = ceil($followerCount/$pageNum);
    	// DB::connection()->enableQueryLog();
    	$followers = DB::table('user_profile')
    					->select('uid','username','avatar','followings_count','followers_count')
    					->join('user_extras','user_profile.uid','=','user_extras.user_id')
    					->whereIn('user_profile.uid',$followerids)
    					->forPage($page,$pageNum)
    					->get();
    	// print_r(DB::getQueryLog());
    	// dd($followingids);			
    	// 循环判断是否关注
    	foreach ($followers as $follower) {
    		$follower->following = in_array($follower->uid, $followingids) ? 1 : 0;
    	}

    	return response()->json($followers);
    }

    // 我关注的标签列表
    public function tag(Request $request) {
        $user_id = JWTAuth::parseToken()->authenticate()->id;
        $page = $request->input('page',1);

        // 获取我标签关注总数
        $pageNum = 20;

        // 获取我关注的所有标签id(以后更改为 Redis)
        $Ids = DB::table('user_tag_follow')->where('user_id',$user_id)->pluck('tag_id');

        //获取标签数组
        $tags = DB::table('tags')->forPage($page,$pageNum)->whereIn('id',$Ids)->get();

        return response()->json($tags);
    }

    // 我的文章
    public function post(Request $request, $id) {
        $user_id = $id;
        $page = $request->input('page',1);

        // 获取我标签关注总数
        $pageNum = 20;

        // 获取我关注的所有标签id(以后更改为 Redis)
        $posts = DB::table('post')->where('user_id',$user_id)->orderby('id','desc')->forPage($page,$pageNum)->get();

        return response()->json($posts);
    }

    public function collection() {
        $user_id = 1;
        $page = $request->input('page',1);

        $pageNum = 20;

        $collections = DB::table('post')
                ->leftjoin('post_collections','post.id','=','post_collections.post_id')
                ->where('post_collections.user_id',$user_id)
                ->where('post.audit_status',0)
                ->orderby('create_time.id','desc')
                ->forPage($page,$pageNum)
                ->get();

        return response()->json($collections);
    }

    public function comment() {
        $user_id = 1;
        $page = $request->input('page',1);

        $pageNum = 20;

        $comments = DB::table('post_comments')
                    ->leftjoin('post','posts_comments.post_id','=','post.id')
                    ->where('posts_comments.user_id',$user_id)
                    ->where('post.audit_status',0)
                    ->orderby('post_comments.id','desc')
                    ->forPage($page,$pageNum)
                    ->get();

        return response()->json($comments);
    }

}
