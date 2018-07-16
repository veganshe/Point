<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\VerificationCode;
use Illuminate\Support\Facades\DB;
use JWTAuth;

class VerifyCodeController extends Controller
{
    public function send(Request $request) 
    {
    	$phone = $request->input('phone');

    	$map = [
    		'channel' => 'sms',
    		'user_id' => 0,
    		'account' => $phone,
    		'code' => rand(0,999999),
    	];

    	if(!VerificationCode::create($map)) {
    		return response()->json(['message' => 'fail','status_code' => 500]);
    	} else {
    		return response()->json(['message' => 'success','status_code' => 200]);
    	}
    }

    public function check(Request $request) {
        $phone = $request->input('phone');
        $code = $request->input('code');
        return response()->json($this->checkCode($phone,$code));
    }

    protected function checkCode(String $phone, String $code) {
        $now = time();

        $verify = VerificationCode::where('account', $phone)
            ->where('channel', 'sms')
            ->where('code', $code)
            ->orderby('id', 'desc')
            ->first();

        if(!$verify) {
            return ['message' => 'Verification code is null','status_code' => 500];
        }

        $vailTime = strtotime($verify->created_at);
        $validMaxTime = $vailTime + 300;

        if($now > $vailTime && $now < $validMaxTime) {
            return ['message' => 'success','status_code' => 200];
        } else {
            return ['message' => 'Verification code is error','status_code' => 500];
        }
    }
}
