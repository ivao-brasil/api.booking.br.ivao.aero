<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\IvaoLoginService;
use App\Services\JwtService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function auth(Request $request) {
        $this->validate($request, [
            'ivao-token' => 'required'
        ]);

        try {
            $ivaoUser = IvaoLoginService::getAuthData($request->get('ivao-token'));

            $user = User::updateOrCreate(['vid' => $ivaoUser['vid']], [
                'vid' => $ivaoUser['vid'],
                'firstName' => $ivaoUser['firstname'],
                'lastName' => $ivaoUser['lastname'],
                'atcRating' => $ivaoUser['ratingatc'],
                'pilotRating' => $ivaoUser['ratingpilot'],
                'division' => $ivaoUser['division'],
                'country'=> $ivaoUser['country']
            ]);


            User::where('vid', $ivaoUser['vid'])
                    ->update(['admin' => AuthController::canAccessAdmin($ivaoUser)]);


            return response()->json([
                'jwt' => JwtService::encode([
                    'vid' => $ivaoUser['vid'],
                    'id' => $user['id']
                ])
            ]);
        } catch (Exception $e) {
            Log::error("Error to authenticate user");
            return response()->json(['error' => 'error to authenticate user'], 403);
        }
    }

    public static function canAccessAdmin($ivaoUser) {

        if(!$ivaoUser['staff']) return 0;

        $regex = "/[A-Z]{2}-EC|[A-Z]{2}-EAC|[A-Z]{2}-EA[0-9]|[A-Z]{2}-DIR|[A-Z]{2}-ADIR|[A-Z]{2}-WM|[A-Z]{2}-AWM|[A-Z]{2}-WMA[0-9]/";

        if(preg_match($regex, $ivaoUser['staff'])) return 1;

        return 0;
    }
}
