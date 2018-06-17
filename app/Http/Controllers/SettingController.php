<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    /**
     * 修改简介
     * @param Request $request
     */
    public function present(Request $request) {
        $uid = $request->input('uid');
    	$data = [
    	    'present' => $request->input('present')
    	];
    	$id = DB::table('user_profile')->where('uid',$uid)->update($data);
    }

    /**
     * 修改性别
     * @param Request $request
     */
    public function sex (Request $request) {
        $uid = $request->input('uid');

        $data = [
            'sex' => $request->input('sex')
        ];

        $id = DB::table('user_profile')->where('uid',$uid)->update($data);
    }

    /**
     * 设置生日
     * @param Request $request
     */
    public function birthday (Request $request) {
        $uid = $request->input('uid');

        $data = [
            'birthday' => $request->input('birthday')
        ];

        $id = DB::table('user_profile')->where('uid',$uid)->update($data);
    }

    /**
     * 设置学校
     * @param Request $request
     */
    public function school (Request $request) {
        $uid = $request->input('uid');

        $data = [
            'school_id' => $request->input('school_id'),
            'school_name' => $request->input('school_name')
        ];

        $id = DB::table('user_profile')->where('uid',$uid)->update($data);
    }

    /**
     * 修改兴趣
     * @param Request $request
     */
    public function interest (Request $request) {
        $uid = $request->input('uid');

        $data = [
            'interest' => $request->input('interest')
        ];

        $id = DB::table('user_profile')->where('uid',$uid)->update($data);
    }
}
