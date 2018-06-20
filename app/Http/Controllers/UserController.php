<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Model\User as user;


class UserController extends Controller
{
    /**
     * 用户详细页
     * @param Request $request
     */
    public function index(Request $request, $id) {
    	
    	$userinfo = DB::table('user_profile')->where('uid',$id)->first();
    	$user_extras = DB::table('user_extras')->where('user_id',$id)->first();

    	$user = [
    		'uid' => $userinfo->uid,
    		'name' => $userinfo->username,
    		'avatar' => $userinfo->avatar,
    		'present' => $userinfo->present,
    		'followers_count' => $user_extras->followers_count,
    		'followings_count' => $user_extras->followings_count,
    		'tags_count' => $user_extras->tags_count,
    		'posts_count' => $user_extras->posts_count
    	];
        return response()->json($user);
    }

    /**
     * User Profile
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile(Request $request, $id) {

        $user = DB::table('user_profile')->where('uid',$id)->first();

        return response()->json($user);
    }

    public function profilea(Request $request, $id) {

        $user = DB::select("select * from user_profile as a,user_extras as b where a.uid = b.user_id and a.uid = ?",[1]);


//        echo $request->route('id2');
//        $id = $request->input('id');
//        $abc = user::find();
//        echo "我的ID：".$request->input('page');
//        echo "我的ID：".$abc->tags_count;
        return response()->json($user);
    }

    public function follow(Request $request) {
    	$user_id = $request->input('uid', 0);
    	$follower_id = $request->input('followerid', 0);

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
    		}
    	}
    }

    public function unfollow(Request $request) {
    	$user_id = $request->input('uid', 0);
    	$follower_id = $request->input('followerid', 0);

    	$followid = DB::table('user_follow')->where(['user_id' => $user_id,'follower_id' => $follower_id])->value('id');

    	if($followid > 0) {
    		// 删除关注对应记录
    		DB::table('user_follow')->where('id',$followid)->delete();
    		// 我的关注者人数 - 1
    		DB::table('user_extras')->where('user_id',$follower_id)->decrement('followings_count',1);
    		// 关注者的粉丝人数 - 1
    		DB::table('user_extras')->where('user_id',$user_id)->decrement('followers_count',1);
    	}
    }

    public function following(Request $request, $id) { 
    	$user_id = $id;
    	$page = $request->input('p',1);

    	// 获取关注者总数
    	$followerCount = DB::table('user_follow')->where('follower_id',$user_id)->count();
    	// 获取关注者id集合
    	$ids = DB::table('user_follow')->where('follower_id',$user_id)->pluck('user_id');

    	$pageNum = 20;
    	$totalPage = ceil($followerCount/$pageNum);

    	$followers = DB::table('user_profile')
    					->select('uid','username','avatar','followings_count','followers_count')
    					->join('user_extras','user_profile.uid','=','user_extras.user_id')
    					->whereIn('user_profile.uid',$ids)
    					->forPage($page,$pageNum)
    					->get();
    	$data = [
    		'count' => $followerCount,
    		'totalpage' => $totalPage,
    		'followings' => $followers
    	];

    	return response()->json($data)->setStatusCode(200);
    }

    public function follower(Request $request, $id) {
    	$user_id = $id;
    	$page = $request->input('p',1);

    	// 获取粉丝总数
    	$followerCount = DB::table('user_follow')->where('user_id',$user_id)->count();
    	// 获取粉丝id集合
    	$followerids = DB::table('user_follow')->where('user_id',$user_id)->pluck('follower_id');
    	// 获取我关注人id集合
    	$followingcollect = DB::table('user_follow')->where('follower_id',$user_id)->pluck('user_id');
    	$followingids = $followingcollect->toArray();

    	$pageNum = 20;
    	$totalPage = ceil($followerCount/$pageNum);
    	// DB::connection()->enableQueryLog();
    	$followers = DB::table('user_profile')
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

    	$data = [
    		'count' => $followerCount,
    		'totalpage' => $totalPage,
    		'followers' => $followers
    	];

    	return response()->json($data);
    }
}
