<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\UserProfile;
use App\Model\School;
use App\Tool\ImageUpload;
use JWTAuth;

class ProfileController extends BaseController
{
	// 用户中心首页
    public function index()
    {
    	$user_id = JWTAuth::parseToken()->authenticate()->id;
    	$user = userProfile::find($user_id);

    	return response()->json($user);
    }

    // 更新个人类别
    public function setting(Request $request, UserProfile $userProfile) 
    {
    	$action = $request->input('action');
    	$value = $request->input('value');

    	$user_id = JWTAuth::parseToken()->authenticate()->id;

    	$actions = ['present','sex','birthday','interest'];

    	if(! in_array($action, $actions)) {
    		return response()->json(['message' => 'action is not allowed','status_code' => 500]);
    	}

    	$data = [
    		$action => $value,
    	];

    	$userProfile->where('uid',$user_id)->update($data);

    	return response()->json(['message' => 'success','status_code' => 200]);
    }

    // 获取学校
    public function getschool(Request $request, School $school)
    {
    	$key = $request->input('key');

    	$schools = $school->where('school_name','like','%'.$key.'%')
    					  ->get();

    	return response()->json($schools);
    }

    // 设置学校
    public function setschool(Request $request, UserProfile $userProfile) {
    	$user_id = JWTAuth::parseToken()->authenticate()->id;
    	$school_id = $request->input('id');
    	$school_name = $request->input('name');

    	$data = [
    		'school_id' => $school_id,
    		'school_name' => $school_name
    	];

    	$userProfile->where('uid',$user_id)->update($data);

    	return response()->json(['message' => 'success','status_code' => 200]);
    }

    // 上传用户头像
    public function avatar(Request $request, ImageUpload $upload, UserProfile $userProfile) {
        $avatar = $request->file('avatar');
        $user_id = JWTAuth::parseToken()->authenticate()->id;

        if(! $request->hasFile('avatar')) {
            return response()->json(['message' => 'Avatar is empty','status_code' => 500]);
        }

        $result = $upload->avatar($avatar,'avatar',$user_id);

        if($result) {
            $avatar_url =  config('api.img_cdn').'/'.$result['path'];

            $data = [
                'avatar' => $avatar_url,
            ];

            $userProfile->where('uid',$user_id)->update($data);

            return response()->json(['message' => 'success','status_code' => 200]);
        }
    }
}
