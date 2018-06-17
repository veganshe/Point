<?php

namespace App\Http\Controllers;

//use App\Test;
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

    
    public function profile($id) {
        $abc = user::find(1);
        return response()->json($abc);
    }


}
