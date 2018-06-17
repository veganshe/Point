<?php

use Illuminate\Http\Request;
use Illuminate\Contracts\Routing\Registrar as RouteContract;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
*/

// 测试接口访问
//Route::get('hello',function(Request $request) {
//	echo $request->ip();
//});

Route::group(['prefix' => 'v2'], function(RouteContract $api) {

    // Demo
    $api->get('ip', function (Request $request) {
        echo $request->ip();
    });

    // 用户组
    $api->group(['prefix' => 'user'], function(RouteContract $api) {
        // 用户页面
        $api->get('/{id}', 'UserController@profile');
        // 用户简介
        $api->get('/{id}/profile', 'UserController@profile');

        $api->get('/{id}/abc',function ($id) {
            echo '我的id:'.$id;
        });
    });

});










