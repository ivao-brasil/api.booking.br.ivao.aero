<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;

class HQAPIService
{
    const DEFAULT_ENDPOINT = 'https://api.ivao.aero/v2';

    public function getAirportDataByIcao($airportIcao) {
        
        try {
            $endpoint = $this->getApiEndpoint() . "/airports/$airportIcao";
            $response = Http::withHeaders($this->getAuthHeaders())->get($endpoint);
    
            $response->throw()->json();
        } catch (Exception $e) {
            return abort(418, "airport.notFound");
        }

    }

    private function getAuthHeaders() {
        return [
            'apiKey' => env('IVAO_API_KEY')
        ];
    }

    private function getApiEndpoint() {
        $result = env('IVAO_API_ENDPOINT');
        if (empty($result)) {
            $result = HQAPIService::DEFAULT_ENDPOINT;
        }

        return $result;
    }
}

