<?php

use Illuminate\Http\Request;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::get('/getEpisode', 'EpisodeController@getEpisode');

Route::get('/getEpisode/{id}', 'EpisodeController@getOneEpisode');

Route::get('/getProgram', 'ProgramController@getProgram');

Route::get('/getProductList', 'ProductController@show_all');

Route::get('/getProductList', 'ProductController@show_all');


Route::group(['middleware' => ['web']], function(){

    Route::post('/contribute', 'ContributeController@store');

    Route::get('/getContribution', 'ContributeController@show');


    Route::get('/logout', function(){

        session()->flush();

        return response()->json(array(
            'success'   =>  true,
            'message'   =>  'Logout successfully'));
    });


    Route::post('/login/facebook', 'LoginController@LoginWitFB');

    Route::post('/login', 'LoginController@Login');

    Route::get('/getProductList/{id}', 'ProductController@show');

    Route::get('/getMemberPoint', 'MemberController@show');

    Route::post('/register', 'LoginController@register');

    Route::post('/mycard/getAuthCode', 'MyCardController@getAuthCode');


});