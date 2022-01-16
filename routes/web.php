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

    $router->group(['prefix' => 'campaigns'], function () use ($router) {
        $router->get('/', 'CampaignController@index');
        $router->get('/{id}', 'CampaignController@show');
    });

    $router->group(['prefix' => 'public'], function () use ($router) {
        $router->post('/donations', 'DonationController@store');
        $router->post('/donations/{donationId}/confirmation', 'DonationController@confirm');
    });

    $router->group(['middleware' => ['auth']], function () use ($router) {
        $router->group(['prefix' => 'users'], function () use ($router) {
            $router->get('/', ['middleware' => 'role:superadmin,admin', 'uses' => 'UserController@index']);
            $router->get('/{id}', ['middleware' => 'role:superadmin,admin,user', 'uses' => 'UserController@show']);
            $router->put('/{id}', ['middleware' => 'role:superadmin,admin,user', 'uses' => 'UserController@update']);
            $router->delete('/{id}', ['middleware' => 'role:superadmin,admin', 'uses' => 'UserController@destroy']);
            $router->get('/{userId}/campaigns', ['middleware' => 'role:user', 'uses' => 'CampaignController@get_my_campaigns']);
            $router->get('/{userId}/donations', ['middleware' => 'role:user', 'uses' => 'DonationController@get_my_donations']);
        });

        $router->group(['prefix' => 'campaigns'], function () use ($router) {
            $router->post('/', ['middleware' => 'role:user', 'uses' => 'CampaignController@store']);
            $router->put('/{id}', ['middleware' => 'role:user', 'uses' => 'CampaignController@update']);
            $router->delete('/{id}', ['middleware' => 'role:superadmin,admin,user', 'uses' => 'CampaignController@destroy']);
        });

        $router->group(['prefix' => 'donations'], function () use ($router) {
            $router->get('/', ['middleware' => 'role:superadmin,admin', 'uses' => 'DonationController@index']);
            $router->get('/{id}', ['middleware' => 'role:superadmin,admin', 'uses' => 'DonationController@show']);
            $router->post('/', ['middleware' => 'role:user', 'uses' => 'DonationController@store']);
            $router->post('/{donationId}/confirmation', ['middleware' => 'role:user', 'uses' => 'DonationController@confirm']);
        });
    });
});
