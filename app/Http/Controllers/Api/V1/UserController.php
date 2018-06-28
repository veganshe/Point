<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\UserProfile as UserProfile;

class UserController extends BaseController
{
    public function index() {
    	$user = UserProfile::find(1)->bb->get();
    	return response()->json($user);
    }
}
