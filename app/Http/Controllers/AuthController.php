<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\HQAPIService;
use App\Services\IvaoLoginService;
use App\Services\JwtService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{

    private $hqApi;

    public function __construct(HQAPIService $hqApi)
    {
        $this->hqApi = $hqApi;
    }

    public function auth(Request $request) {
        $this->validate($request, [
            'ivao-token' => 'required'
        ]);

        try {
            $authResponse = IvaoLoginService::getAuthData($request->get('ivao-token'));

            // get access_token as jwt and extract field sub from payload

            if(!isset($authResponse['access_token']))
                return response()->json(['error' => 'auth.invalidToken'], 403);
            $tokenParts = explode('.', $authResponse['access_token']);
            if(count($tokenParts) != 3)
                return response()->json(['error' => 'auth.invalidToken'], 403);
            $payload = json_decode(base64_decode($tokenParts[1]), true);
            if(!isset($payload['sub']))
                return response()->json(['error' => 'auth.invalidToken'], 403);

            $ivaoUser = $this->hqApi->getUserInfo($payload['sub']);

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

            $user->refresh();

            if(!$user->admin&&env('APP_ENV')=='stage')
                return response()->json(['error' => 'admin.noAdmin'], 401);

            return response()->json([
                'jwt' => JwtService::encode([
                    'vid' => $ivaoUser['vid'],
                    'id' => $user['id']
                ])
            ]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['error' => 'auth.error'], 403);
        }
    }

    public static function canAccessAdmin($ivaoUser) {

        if(!$ivaoUser['staff']) return 0;

        $division   =   env('IVAO_DIVISION');

        $positions = explode(',', env('AUTHORIZED_STAFF_POSITIONS'));
        $regex = "/";

        foreach($positions as $index => $position){
            $position = str_replace('0', "[0-9]", $position);
            $position = $division . '-' . $position;
            $regex .= $position;

            if($index < count($positions) - 1) {
                $regex .= "|";
            }
        }

        $regex .= "/";


        if(preg_match($regex, $ivaoUser['staff'])) return 1;

        return 0;
    }
}
