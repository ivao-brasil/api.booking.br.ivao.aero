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

use Illuminate\Support\Facades\Auth;

$router->group(['middleware' => 'auth', 'prefix' => 'aircraft'], function() use($router) {
    $router->get('/', 'AircraftController@get');
    $router->get('/missing', 'AircraftController@getMissing');
    $router->post('/', 'AircraftController@create');
    $router->put('/{id}', 'AircraftController@update');
    $router->delete('/{id}', 'AircraftController@delete');
});
