<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class LocationSearchController extends Controller
{
    private $apiKey = 'AIzaSyA0_osRLuYb7ufKA-CoP6S1YjaqcMTXUiU';

    /**
     * Search for locations using Google Places Autocomplete
     */
    public function autocomplete(Request $request)
    {
        $query = $request->input('query');

        if (empty($query) || strlen($query) < 3) {
            return response()->json(['predictions' => []]);
        }

        try {
            $response = Http::get('https://maps.googleapis.com/maps/api/place/autocomplete/json', [
                'input' => $query,
                'key' => $this->apiKey,
                'components' => 'country:in', // Restrict to India
            ]);

            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch locations'], 500);
        }
    }

    /**
     * Get place details including coordinates
     */
    public function details(Request $request)
    {
        $placeId = $request->input('place_id');

        if (empty($placeId)) {
            return response()->json(['error' => 'Place ID required'], 400);
        }

        try {
            $response = Http::get('https://maps.googleapis.com/maps/api/place/details/json', [
                'place_id' => $placeId,
                'key' => $this->apiKey,
                'fields' => 'name,formatted_address,geometry',
            ]);

            return response()->json($response->json());
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch place details'], 500);
        }
    }
}
