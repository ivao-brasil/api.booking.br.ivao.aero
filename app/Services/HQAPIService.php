<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class HQAPIService
{
    const DEFAULT_ENDPOINT = 'https://api.ivao.aero/v2';

    public function getAirportDataByIcao($airportIcao)
    {
        $endpoint = $this->getApiEndpoint() . "/airports/$airportIcao";
        $response = Http::withHeaders($this->getAuthHeaders())->get($endpoint);
        $data = $response->json();

        Log::info('API response received', ['endpoint' => $endpoint, 'response' => $data, 'status' => $response->status()]);

        if ($response->status() === Response::HTTP_NOT_FOUND) {
            abort(404, "airport.notFound");
        }

        if ($response->status() !== Response::HTTP_OK) {
            abort(500, "airport.requestFailed");
        }

        return $data;
    }

    private function getApiEndpoint()
    {
        $result = env('IVAO_API_ENDPOINT');
        if (empty($result)) {
            $result = HQAPIService::DEFAULT_ENDPOINT;
        }
        return $result;
    }

    private function getAuthHeaders()
    {
        return [
            'apiKey' => env('IVAO_API_KEY')
        ];
    }
}

