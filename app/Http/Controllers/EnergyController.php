<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\EnergyData;

class EnergyController extends Controller
{
    public function index(Request $request)
    {
        // Default to Delhi's coordinates
        $latitude = $request->input('latitude', 28.6139);
        $longitude = $request->input('longitude', 77.2090);
        $locationName = $request->input('location_name', 'Delhi');
        
        try {
            // Fetch current and forecast data with error handling
            $response = Http::withOptions([
                'verify' => false, // Disable SSL verification temporarily
                'timeout' => 30,
                'connect_timeout' => 30,
            ])->get('https://api.open-meteo.com/v1/forecast', [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'hourly' => 'temperature_2m,apparent_temperature,shortwave_radiation,windspeed_10m,winddirection_10m',
                'daily' => 'temperature_2m_max,temperature_2m_min,shortwave_radiation_sum,windspeed_10m_max,winddirection_10m_dominant',
                'timezone' => 'auto'
            ]);

            if (!$response->successful()) {
                throw new \Exception('API request failed: ' . $response->status());
            }

            $data = $response->json();
        } catch (\Exception $e) {
            // Log the error
            \Log::error('Weather API Error: ' . $e->getMessage());
            
            // Generate sample data for development
            $data = $this->generateSampleData();
        }

        // Process daily data for forecast
        $dailyData = [];
        if (isset($data['daily'])) {
            foreach ($data['daily']['time'] as $index => $date) {
                $dailyData[] = [
                    'date' => $date,
                    'max_temp' => $data['daily']['temperature_2m_max'][$index],
                    'min_temp' => $data['daily']['temperature_2m_min'][$index],
                    'radiation' => $data['daily']['shortwave_radiation_sum'][$index],
                    'wind_speed' => $data['daily']['windspeed_10m_max'][$index],
                    'wind_direction' => $data['daily']['winddirection_10m_dominant'][$index]
                ];
            }
        }

        // Generate historical data (last 30 days) - using a simple model
        $historicalData = [];
        $currentDate = new \DateTime();
        for ($i = 30; $i >= 0; $i--) {
            $date = clone $currentDate;
            $date->modify("-{$i} days");
            $historicalData[] = [
                'date' => $date->format('Y-m-d'),
                'temperature' => rand(15, 30), // Example data
                'radiation' => rand(2000, 8000), // Example data
                'wind_speed' => rand(5, 25), // Example wind speed data
                'wind_direction' => rand(0, 360) // Example wind direction data
            ];
        }
        
        return view('energy.index', compact('data', 'locationName', 'dailyData', 'historicalData'));
    }

    /**
     * Generate sample data for development/testing
     */
    private function generateSampleData()
    {
        $today = now();
        $dailyData = [];
        $hourlyData = [];

        // Generate 7 days of data
        for ($i = 0; $i < 7; $i++) {
            $date = $today->copy()->addDays($i);
            $dailyData[] = [
                'time' => $date->format('Y-m-d'),
                'temperature_2m_max' => rand(25, 35),
                'temperature_2m_min' => rand(15, 25),
                'shortwave_radiation_sum' => rand(500, 1000),
                'windspeed_10m_max' => rand(5, 15),
                'winddirection_10m_dominant' => rand(0, 360)
            ];
        }

        // Generate 24 hours of data
        for ($i = 0; $i < 24; $i++) {
            $hourlyData[] = [
                'time' => $today->copy()->addHours($i)->format('Y-m-d\TH:i'),
                'temperature_2m' => rand(20, 30),
                'apparent_temperature' => rand(20, 30),
                'shortwave_radiation' => rand(0, 1000),
                'windspeed_10m' => rand(5, 15),
                'winddirection_10m' => rand(0, 360)
            ];
        }

        return [
            'daily' => [
                'time' => array_column($dailyData, 'time'),
                'temperature_2m_max' => array_column($dailyData, 'temperature_2m_max'),
                'temperature_2m_min' => array_column($dailyData, 'temperature_2m_min'),
                'shortwave_radiation_sum' => array_column($dailyData, 'shortwave_radiation_sum'),
                'windspeed_10m_max' => array_column($dailyData, 'windspeed_10m_max'),
                'winddirection_10m_dominant' => array_column($dailyData, 'winddirection_10m_dominant')
            ],
            'hourly' => [
                'time' => array_column($hourlyData, 'time'),
                'temperature_2m' => array_column($hourlyData, 'temperature_2m'),
                'apparent_temperature' => array_column($hourlyData, 'apparent_temperature'),
                'shortwave_radiation' => array_column($hourlyData, 'shortwave_radiation'),
                'windspeed_10m' => array_column($hourlyData, 'windspeed_10m'),
                'winddirection_10m' => array_column($hourlyData, 'winddirection_10m')
            ]
        ];
    }

    public function updateLocation(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'locationName' => 'required|string'
        ]);

        // Store location in session
        session([
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'locationName' => $request->locationName
        ]);

        return response()->json(['success' => true]);
    }

    private function getWeatherData($latitude, $longitude)
    {
        // Fetch current and forecast data
        $response = Http::get('https://api.open-meteo.com/v1/forecast', [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'hourly' => 'temperature_2m,apparent_temperature,shortwave_radiation,windspeed_10m,winddirection_10m',
            'daily' => 'temperature_2m_max,temperature_2m_min,shortwave_radiation_sum,windspeed_10m_max,winddirection_10m_dominant',
            'timezone' => 'auto'
        ]);

        return $response->json();
    }

    private function getDailyData($data)
    {
        // Process daily data for forecast
        $dailyData = [];
        if (isset($data['daily'])) {
            foreach ($data['daily']['time'] as $index => $date) {
                $dailyData[] = [
                    'date' => $date,
                    'max_temp' => $data['daily']['temperature_2m_max'][$index],
                    'min_temp' => $data['daily']['temperature_2m_min'][$index],
                    'radiation' => $data['daily']['shortwave_radiation_sum'][$index],
                    'wind_speed' => $data['daily']['windspeed_10m_max'][$index],
                    'wind_direction' => $data['daily']['winddirection_10m_dominant'][$index]
                ];
            }
        }
        return $dailyData;
    }

    private function getHistoricalData($latitude, $longitude)
    {
        // Generate historical data (last 30 days) - using a simple model
        $historicalData = [];
        $currentDate = new \DateTime();
        for ($i = 30; $i >= 0; $i--) {
            $date = clone $currentDate;
            $date->modify("-{$i} days");
            $historicalData[] = [
                'date' => $date->format('Y-m-d'),
                'temperature' => rand(15, 30), // Example data
                'radiation' => rand(2000, 8000), // Example data
                'wind_speed' => rand(5, 25), // Example wind speed data
                'wind_direction' => rand(0, 360) // Example wind direction data
            ];
        }
        return $historicalData;
    }
}