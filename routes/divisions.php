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

use App\Http\Controllers\EventDataExporter;
use Illuminate\Support\Facades\Auth;

$router->group(['middleware' => 'auth', 'prefix' => 'divisions'], function () use ($router) {
    $router->get('/', 'DivisionsController@get');
    $router->get('/{divisionId}', 'DivisionsController@getSingle');
    $router->get('/{divisionId}/events', 'EventController@getFromDivision');
    $router->get('/{divisionId}/events/{eventId}', 'EventController@getSingleFromDivision');
    $router->post('/{divisionId}/events', 'EventController@create');
    $router->put('/{divisionId}/events/{eventId}', 'EventController@update');
    $router->delete('/{divisionId}/events/{eventId}', 'EventController@delete');
    // $router->get('/{divisionId}/export', 'EventDataExporterController@__invoke');
});
