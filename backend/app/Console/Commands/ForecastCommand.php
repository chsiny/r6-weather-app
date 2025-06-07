<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ForecastCommand extends Command
{
    protected $signature = 'forecast {cities?*}';
    protected $description = 'Display 5-day weather forecast for given cities';

    public function handle()
    {
        $cities = $this->argument('cities');

        // If no cities were passed, ask interactively
        if (empty($cities)) {
            $cityInput = $this->ask('Enter one or more cities separated by commas');
            $cities = array_map('trim', explode(',', $cityInput));
        }

        $apiKey = env('WEATHERBIT_API_KEY');

        foreach ($cities as $city) {
            $this->line("\n🌤  Forecast for <info>$city</info>:\n");

            try {
                $response = Http::timeout(5)->get('https://api.weatherbit.io/v2.0/forecast/daily', [
                    'city' => $city,
                    'country' => 'AU',
                    'key' => $apiKey,
                ]);

                if (!$response->ok()) {
                    return response()->json([
                        'error' => 'Failed to fetch forecast from Weatherbit',
                        'status' => $response->status(),
                        'message' => optional($response->json())['error'] ?? 'Unknown error'
                    ], $response->status());
                }
                
                $data = $response->json();
                
                if (
                    !isset($data['data']) || 
                    count($data['data']) < 1 ||
                    (isset($data['error']) && $data['error']) // Weatherbit sometimes returns `error` field
                ) {
                    return response()->json([
                        'error' => 'City not found or no forecast data available',
                        'status' => 404
                    ], 404);
                }

                $data = $response->json();

                if (!isset($data['data']) || count($data['data']) < 1) {
                    $this->warn("⚠️ No forecast data available for $city.");
                    continue;
                }

                $rows = collect($data['data'])->take(5)->map(function ($day) {
                    return [
                        'Date'      => $day['datetime'],
                        'Avg (°C)'  => round(($day['max_temp'] + $day['min_temp']) / 2),
                        'Max (°C)'  => round($day['max_temp']),
                        'Low (°C)'  => round($day['min_temp']),
                    ];
                });

                $this->table(['Date', 'Avg (°C)', 'Max (°C)', 'Low (°C)'], $rows);

            } catch (\Exception $e) {
                $this->error("⚠️  Error fetching forecast for $city: " . $e->getMessage());
            }
        }
    }
}
