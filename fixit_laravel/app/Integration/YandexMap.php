<?php

namespace App\Integration;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class YandexMap
{
    public const BASE_URL = 'https://geocode-maps.yandex.ru/1.x';
    public const SUGGEST_URL = 'https://suggest-maps.yandex.ru/v1/suggest';

    public string $key;

    public function __construct()
    {
        $this->key = config('app.yandex_map_api_key');
    }

    public function getDataFromAddressComponent(array $addressComponents, string $searchFor): ?string
    {
        foreach ($addressComponents as $component) {
            if ($component['kind'] === $searchFor) {
                return $component['name'];
            }
        }

        return null;
    }

    /**
     * Get address suggestions (autocomplete)
     */
    public function addressId(string $address)
    {
        try {
            $client = new Client();
            $response = $client->request('get', self::SUGGEST_URL, [
                'query' => [
                    'apikey' => $this->key,
                    'text' => $address,
                    'type' => 'geo',
                    'results' => 5,
                ]
            ]);
            $responseArray = json_decode($response->getBody()->getContents(), true);

            $results = [];
            if (isset($responseArray['results'])) {
                foreach ($responseArray['results'] as $item) {
                    if (isset($item['title']['text'])) {
                        $results[] = [
                            'id' => $item['uri'] ?? $item['title']['text'],
                            'label' => $item['title']['text'],
                        ];
                    }
                }
            }

            return response()->json($results);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get address details by place URI
     */
    public function addressBasedOnPlaceId(string $placeId)
    {
        try {
            $response = Http::timeout(3)
                ->retry(2, 100)
                ->get(self::BASE_URL, [
                    'apikey' => $this->key,
                    'geocode' => $placeId,
                    'format' => 'json',
                    'results' => 1,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $geoObjects = $data['response']['GeoObjectCollection']['featureMember'] ?? [];

                if (empty($geoObjects)) {
                    return ['error' => 'No results found'];
                }

                $geoObject = $geoObjects[0]['GeoObject'];
                $metaData = $geoObject['metaDataProperty']['GeocoderMetaData'] ?? [];
                $components = $metaData['Address']['Components'] ?? [];
                $point = $geoObject['Point']['pos'] ?? '0 0';
                list($lng, $lat) = explode(' ', $point);

                return [
                    'streetNumber' => $this->getDataFromAddressComponent($components, 'house'),
                    'streetName' => $this->getDataFromAddressComponent($components, 'street'),
                    'locality' => $this->getDataFromAddressComponent($components, 'locality'),
                    'state' => $this->getDataFromAddressComponent($components, 'province'),
                    'area' => $this->getDataFromAddressComponent($components, 'area'),
                    'country' => $this->getDataFromAddressComponent($components, 'country'),
                    'postal_code' => $this->getDataFromAddressComponent($components, 'postal_code'),
                    'location' => [
                        'lat' => (float) $lat,
                        'lng' => (float) $lng,
                    ]
                ];
            }

            return ['error' => 'Request failed'];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage(), 'exception' => get_class($e)];
        }
    }

    /**
     * Get autocomplete locations (used for frontend autocomplete)
     */
    public function getAutocompleteLocations(string $input)
    {
        try {
            $response = Http::timeout(3)
                ->retry(2, 100)
                ->get(self::SUGGEST_URL, [
                    'apikey' => $this->key,
                    'text' => $input,
                    'type' => 'geo',
                    'results' => 5,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $predictions = [];

                if (isset($data['results'])) {
                    foreach ($data['results'] as $item) {
                        if (isset($item['title']['text'])) {
                            $predictions[] = [
                                'description' => $item['title']['text'],
                                'place_id' => $item['uri'] ?? $item['title']['text'],
                            ];
                        }
                    }
                }

                if (!empty($predictions)) {
                    return $predictions;
                }

                return ['error' => 'No addresses found for the input.'];
            }

            return ['error' => 'An unexpected error occurred.'];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage(), 'exception' => get_class($e)];
        }
    }

    /**
     * Get coordinates from place URI (used for geocoding)
     */
    public function getCoordinates($placeId)
    {
        try {
            $response = Http::timeout(3)
                ->retry(2, 100)
                ->get(self::BASE_URL, [
                    'apikey' => $this->key,
                    'geocode' => $placeId,
                    'format' => 'json',
                    'results' => 1,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $geoObjects = $data['response']['GeoObjectCollection']['featureMember'] ?? [];

                if (empty($geoObjects)) {
                    return ['error' => 'No results found or unexpected response structure.'];
                }

                $geoObject = $geoObjects[0]['GeoObject'];
                $point = $geoObject['Point']['pos'] ?? '0 0';
                list($lng, $lat) = explode(' ', $point);

                return [
                    'status' => 'OK',
                    'result' => [
                        'formatted_address' => $geoObject['metaDataProperty']['GeocoderMetaData']['text'] ?? ($geoObject['name'] ?? ''),
                        'geometry' => [
                            'location' => [
                                'lat' => (float) $lat,
                                'lng' => (float) $lng,
                            ]
                        ]
                    ]
                ];
            }

            return [
                'error' => 'No results found or unexpected response structure.',
                'response' => $response->json()
            ];
        } catch (\Exception $e) {
            return [
                'error' => 'Request failed.',
                'message' => $e->getMessage(),
                'exception' => get_class($e)
            ];
        }
    }
}