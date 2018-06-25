<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\User;
use JWTAuth;

class RegisterController extends BaseController
{
    public function test() {
    	echo  'ok';
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6' //|confirmed' 
        ]);
    }

    protected function create(array $data)
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);
    }

  public function register(Request $request)
  {
    // $this->validator($request->all())->validate();

    $user = $this->create($request->all());
    $token = JWTAuth::fromUser($user);
    return ["token" => $token];
  }
}
