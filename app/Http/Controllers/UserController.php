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

    public function following(Request $request) { }

    public function follower(Request $request) { }


}
