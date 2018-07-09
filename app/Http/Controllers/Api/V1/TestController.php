<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use JWTAuth;
use App\Tool\ImageUpload;

class TestController extends Controller
{
    public function getUser() {
    	$id = JWTAuth::parseToken()->authenticate()->id;
    	echo "我的id：".$id;
    }

    public function up(Request $request, ImageUpload $upload) {
    	$data = $request->file('photo');

    	$result = $upload->save($data,'avatar',1);

    	if($result) {
    		echo $result['path'];
    	}
    }
}
