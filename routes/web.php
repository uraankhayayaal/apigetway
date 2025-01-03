<?php

declare(strict_types=1);

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
    $router->get('/', 'ApiController@get');
    $router->get('/openapi', 'ApiController@openapi');
    $router->group(['prefix' => 'auth', 'as' => 'auth'], function () use ($router) {
        $router->post('/login', 'AuthController@login');
        $router->post('/register', 'AuthController@register');
        $router->get('/confirm', 'AuthController@confirm');
        $router->post('/forgot-password', 'AuthController@forgotPassword');
        $router->post('/reset-password', 'AuthController@resetPassword');
        $router->post('/user/{id}/comment/{message}', 'AuthController@get');
    });
});

$router->group(['prefix' => 'api', 'middleware' => 'auth'], function () use ($router) {
    $router->group(['prefix' => 'auth', 'as' => 'auth'], function () use ($router) {
        $router->get('/validate-token', 'AuthController@validateToken');
    });
});

$router->group(['prefix' => 'api'], function () use ($router) {
    $router->get('/{serviceName}/{servicePath:.*}', 'ServiceController@get');
    $router->post('/{serviceName}/{servicePath:.*}', 'ServiceController@post');
    $router->put('/{serviceName}/{servicePath:.*}', 'ServiceController@put');
    $router->patch('/{serviceName}/{servicePath:.*}', 'ServiceController@patch');
    $router->options('/{serviceName}/{servicePath:.*}', 'ServiceController@options');
    $router->head('/{serviceName}/{servicePath:.*}', 'ServiceController@head');
});