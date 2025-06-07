<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\JsonResponse;

class ForecastController extends Controller
{
    public function getForecast(Request $request): JsonResponse
    {
        $city = $request->query('city');

        if (!$city) {
            return response()->json(['error' => 'City is required'], 400);
        }

        $apiKey = env('WEATHERBIT_API_KEY');

        if (!$apiKey) {
            return response()->json(['error' => 'Weather API key is not configured'], 500);
        }

        try {
            $response = Http::timeout(5)->get('https://api.weatherbit.io/v2.0/forecast/daily', [
                'city' => $city,
                'country' => 'AU',
                'key' => $apiKey,
            ]);

            $data = $response->json();

            // Handle HTTP or API-level errors
            if (
                !$response->ok() ||
                !isset($data['data']) ||
                !is_array($data['data']) ||
                count($data['data']) < 1 ||
                (isset($data['error']) && $data['error']) // Weatherbit sometimes includes error msg even with 200 OK
            ) {
                return response()->json([
                    'error' => 'City not found or no forecast data available',
                    'raw' => $data,
                ], 404);
            }

            $forecast = collect($data['data'])->take(5)->map(function ($day) {
                return [
                    'date' => $day['datetime'],
                    'avg'  => round(($day['max_temp'] + $day['min_temp']) / 2),
                    'max'  => round($day['max_temp']),
                    'low'  => round($day['min_temp']),
                ];
            });

            return response()->json(['forecast' => $forecast], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'An unexpected error occurred while fetching the forecast.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
