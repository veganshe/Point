<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use JWTAuth;

class TestController extends Controller
{
    public function getUser() {
    	$id = JWTAuth::parseToken()->authenticate()->id;
    	echo "我的id：".$id;
    }
}
