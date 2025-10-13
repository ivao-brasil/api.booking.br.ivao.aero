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
            $authResponse = IvaoLoginService::getAccessTokenFromAuthCode($request->get('ivao-token'));

            // get access_token as jwt and extract field sub from payload

            if(!isset($authResponse['access_token']))
                return response()->json(['error' => 'auth.invalidToken1', 'sub' => 1], 403);
            $tokenParts = explode('.', $authResponse['access_token']);
            if(count($tokenParts) != 3)
                return response()->json(['error' => 'auth.invalidToken2', 'sub' => 2], 403);
            $payload = json_decode(base64_decode($tokenParts[1]), true);
            if(!isset($payload['sub']))
                return response()->json(['error' => 'auth.invalidToken3', 'sub' => 3], 403);

            $ivaoUser = $this->hqApi->getUserInfo($payload['sub']);

            $user = User::updateOrCreate(['vid' => $ivaoUser['id']], [
                'vid' => $ivaoUser['id'],
                'firstName' => $ivaoUser['firstName'],
                'lastName' => $ivaoUser['lastName'],
                'atcRating' => $ivaoUser['rating']['atcRating']['id'],
                'pilotRating' => $ivaoUser['rating']['pilotRating']['id'],
                'division' => $ivaoUser['divisionId'],
                'country'=> $ivaoUser['countryId']
            ]);

            User::where('vid', $ivaoUser['id'])
                    ->update(['admin' => AuthController::canAccessAdmin($ivaoUser)]);

            $user->refresh();

            if(!$user->admin&&env('APP_ENV')=='stage')
                return response()->json(['error' => 'admin.noAdmin'], 401);

            return response()->json([
                'jwt' => JwtService::encode([
                    'vid' => $ivaoUser['id'],
                    'id' => $user['id']
                ])
            ]);
        } catch (\Exception $e) {
            Log::error($e);
            return response()->json(['error' => 'auth.error'], 403);
        }
    }

    public static function canAccessAdmin($ivaoUser) {

        if(!$ivaoUser['userStaffPositions']) return 0;

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

        foreach($ivaoUser['userStaffPositions'] as $staffPosition) {
            if(preg_match($regex, $staffPosition['id'])) {
                return 1;
            }
        }

        return 0;
    }
}
