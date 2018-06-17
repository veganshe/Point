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
}
