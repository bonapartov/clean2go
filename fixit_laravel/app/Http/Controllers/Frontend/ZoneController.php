<?php

namespace App\Http\Controllers\Frontend;

use App\Models\Zone;
use App\Models\Address;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Integration\GoogleMap;
use App\Models\Role;
use Illuminate\Support\Facades\Http;

use MatanYadaev\EloquentSpatial\Objects\Point;

class ZoneController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    
	public function getAddress(Request $request)
    {
        if ($request->filled(['lat', 'lng'])) {
            $lat = (float) $request->lat;
            $lng = (float) $request->lng;
            $apiKey = config('app.yandex_map_api_key');

            try {
                $response = Http::timeout(3) // Set timeout in seconds
                ->retry(2, 100) // Retry 2 times with 100ms delay
                ->get("https://maps.googleapis.com/maps/api/geocode/json", [
                'latlng' => "{$lat},{$lng}",
                'key' => $apiKey
            ]);

            if ($response->successful() && isset($response['results'][0])) {
                $address = $response['results'][0]['formatted_address'] ?? null;
                $this->setZone($lat, $lng, $address);
                return response()->json(json_decode($response));
            }

            } catch (\Exception $e) {
                return response()->json(['error' => 'Request failed.', 'message' => $e->getMessage()], 500);
            }
        }

        return response()->json(['error' => 'Invalid coordinates.'], 422);
    }    


    public function getZoneIds($latitude, $longitude)
    {
        $point = new Point($latitude, $longitude);
        return Zone::whereContains('place_points', $point)->pluck('id')?->toArray();
    }

    public function checkZone(Request $request)
    {
        $zoneIds = session('zoneIds', []);
        return response()->json(['zoneSet' => !empty($zoneIds), 'location' => session('location', [])]);
    }

    public function autoComplete(Request $request)
    {
        $googleMap = new GoogleMap;
        $location = $request->location;
        $autoCompleteAddress = $googleMap->getAutocompleteLocations($location);
        return response()->json($autoCompleteAddress);
    }

    public function getCoordinates(Request $request)
    {
        $googleMap = new GoogleMap;
        $placeId = $request->place_id;
        $response = $googleMap->getCoordinates($placeId);

        if(isset($response['status'])) {
            if ($response['status'] == 'OK') {
                $address = $response['result']['formatted_address'];
                $lng = $response['result']['geometry']['location']['lng'];
                $lat = $response['result']['geometry']['location']['lat'];
                $zoneIds = $this->setZone($lat, $lng, $address);
                return response()->json(['status' => 'OK', 'zoneIds'=> $zoneIds]);
            }
        }

        return response()->json(['error' => $response]);
    }

    public function setZone($lat, $lng, $address = null, $source = 'manual')
    {
        session(['location' => $address]);
        $zoneIds = $this->getZoneIds($lat, $lng);
        session(['zoneIds' => $zoneIds]);

        // Only a guest's own manual address search is a candidate to offer as
        // their first saved address at registration (GPS is where they are
        // standing, not necessarily where they want deliveries).
        if ($source === 'manual' && !auth()->check()) {
            session(['guest_manual_address' => [
                'address' => $address,
                'latitude' => $lat,
                'longitude' => $lng,
            ]]);
        } elseif ($source !== 'manual' || auth()->check()) {
            session()->forget('guest_manual_address');
        }

        return $zoneIds;
    }

    public function setZoneFromAddress(Request $request, $address)
    {
        $address = Address::where('user_id', auth()->id())->findOrFail($address);

        if (!$address->latitude || !$address->longitude) {
            return response()->json(['error' => __('frontend::static.location.address_missing_coordinates')], 422);
        }

        $zoneIds = $this->setZone($address->latitude, $address->longitude, $address->address, 'saved');
        session(['selected_address_id' => $address->id]);

        return response()->json(['status' => 'OK', 'zoneIds' => $zoneIds]);
    }
}