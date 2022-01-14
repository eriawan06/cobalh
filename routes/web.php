<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'api'], function () use ($router) {
    $router->group(['prefix' => 'authentication'], function () use ($router) {
        $router->post('/registration', 'AuthController@register');
        $router->post('/login', 'AuthController@login');
    });

    $router->group(['middleware' => ['auth']], function () use ($router) {
        // $router->get('/tes', ['middleware' => 'role:admin,user', function () {
        //     return 'TES';
        // }]);
        $router->group(['prefix' => 'users'], function () use ($router) {
            $router->get('/', ['middleware' => 'role:superadmin,admin', 'uses' => 'UserController@index']);
            $router->get('/{id}', ['middleware' => 'role:superadmin,admin,user', 'uses' => 'UserController@show']);
            $router->post('/create', ['middleware' => 'role:superadmin,admin,user', 'uses' => 'UserController@create']);
            $router->put('/{id}', ['middleware' => 'role:superadmin,admin,user', 'uses' => 'UserController@update']);
            $router->delete('/{id}', ['middleware' => 'role:superadmin,admin', 'uses' => 'UserController@destroy']);
        });
    });
});
