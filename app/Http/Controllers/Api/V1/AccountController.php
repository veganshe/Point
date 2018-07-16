<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\VerificationCode;
use Illuminate\Support\Facades\DB;
use App\User;
use JWTAuth;

class AccountController extends Controller
{
    public function updateToken() {
    	try {  
            $old_token = JWTAuth::getToken();
            $token = JWTAuth::refresh($old_token);  
            JWTAuth::invalidate($old_token);  
            $cacheKey = 'token';
            // Cache::forever($cacheKey,$token);
        } catch (TokenExpiredException $e) {  
            throw new AuthException(  
            trans('errors.refresh_token_expired'), $e);  
        } catch (JWTException $e) {  
            throw new AuthException(  
            trans('errors.token_invalid'), $e);  
        }  
        return response()->json(compact('token'));
    }

    public function query(Request $request) 
    {
    	$phone = $request->input('phone');

    	$user = DB::table('users')->where('phone',$phone)->first();

    	if($user) {
    		return response()->json(['phone' => $phone, 'status' => 1],200);
    	} else {
    		return response()->json(['phone' => $phone, 'status' => 0],200);
    	}
    }

    public function register(Request $request) 
    {
    	$phone = $request->input('phone');
    	$password = $request->input('password'); 
    	$code = $request->input('code');

    	$mobile = User::where('phone',$phone)->first();

    	if($mobile) {
    		return response()->json(['message' => 'Phone already exist','status_code'=> 500]);
    	}

    	if(!$this->checkCode($phone, $code)) {
    		return response()->json(['message' => 'Verification code is error','status_code'=> 500]);
    	}

        $user = new User();

        $user->name = 'Point';
        $user->phone = $phone;
        $user->createPassword($password);

        if(!$user->save()) {
        	return response()->json(['message' => 'Register is fail','status_code' => 500]);
        }

        DB::table('user_extras')->insert(['user_id' => $user->id]);
        DB::table('user_profile')->insert(['uid' => $user->id,'username'=>$user->name]);

        return response()->json([
            'token' => JWTAuth::fromUser($user),
            'token_type' => 'bearer',
            'expires_in' => config('jwt.ttl') * 60,
            'refresh_ttl' => config('jwt.refresh_ttl'),
        ])->setStatusCode(200);
    }

    public function restpwd(Request $request) 
    {
    	$phone = $request->input('phone');
    	$password = $request->input('pwd1');
    	$password2 = $request->input('pwd2');
    	$code = $request->input('code');

    	if($password != $password2) {
    		return response()->json(['message' => 'The passwords do not match','status_code'=> 500]);
    	}

    	$user = User::where('phone',$phone)->first();

    	if(!$user) {
    		return response()->json(['message' => 'Phone not exist','status_code'=> 500]);
    	}

    	if(!$this->checkCode($phone, $code)) {
    		return response()->json(['message' => 'Verification code is error','status_code'=> 500]);
    	}

        $user->createPassword($password);

        if(!$user->save()) {
        	return response()->json(['message' => 'fail','status_code' => 500]);
        }
        return response()->json(['message' => 'success','status_code' => 500]);
    }


    protected function checkCode(String $phone, String $code) {
        $now = time();

        $verify = VerificationCode::where('account', $phone)
            ->where('channel', 'sms')
            ->where('code', $code)
            ->orderby('id', 'desc')
            ->first();

        if(!$verify) {
            return false;
        }

        $vailTime = strtotime($verify->created_at);
        $validMaxTime = $vailTime + 300;

        if($now > $vailTime && $now < $validMaxTime) {
            $verify->delete();
            return true;
        } else {
            return false;
        }
    }
}
