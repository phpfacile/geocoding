PHPFacile! Geocoding
==================

This is mainly aimed at retrieving coordinates (latitude, longitude) of a location (address, place).

It is based on geocoder-php (https://github.com/geocoder-php/Geocoder) and tries to use the provider matching our requirements:
* The provider must allow geocoding output storage
* The provider usage must be free (for few queries)

REM: A cache is used so as to meet as far as possible usage limitation defined by the providers

Installation
-----
At the root of your project type
```
composer require phpfacile/geocoding
```
Or add "phpfacile/geocoding": "^1.0" to the "require" part of your composer.json file
```composer
"require": {
    "phpfacile/geocoding": "^1.0"
}
```

Providers comparison
-----
| Providers | Storage allowed? | Restrictions         |
|-----------|------------------|----------------------|
| Geonames  | Yes              | No more than 1 req/s |
| GoogleMap | for 30 days max  |                      |
| MapBox    | No               |                      |
| Nominatim | Yes              | No more than 1 req/s |

Usage
-----
    $geocodingService = new GeocodingService($cfg, $cacheDir);

    $cfg = [
        'geonames' => [
            'username' => '<replace with geonames username>',
        ],
        'nominatim' => [
            'rootUrl' => 'https://nominatim.openstreetmap.org',
            'userAgent' => '<replace with a user-agent>',
        ]
    ];
$cacheDir.'/nominatim' must exist (ex: if $cacheDir='/cache' then '/cache/nominatim' folder must exist and be writable)

Actually, in the current implementation, in any case __nominatim__ is the preferred provider.

    $locations = $this->geocodingService->getLocationsByAddress('Etretat, France');
    foreach ($locations as $location) {
        var_dump($location);
    }

    $locations = $this->geocodingService->getPlacesByCountryAndPlaceName('France', 'Etretat');
    foreach ($locations as $location) {
        var_dump($location);
    }

Disclaimer
-----
Nominatim may stop its service at any moment

Troubleshooting
-----
If ever you modify the code so as to use geonames, you have to know that the following error message is silently ignored by the geonames provider (https://github.com/geocoder-php/geonames-provider)

    {"status":{"message":"user account not enabled to use the free webservice. Please enable it on your account page: http://www.geonames.org/manageaccount ","value":10}}

Todo
-----
* Manage localisation
* Remove unexpected (from a human point of view) results (for example, with nominatim, a query for "Paris, France" currently returns an entry with type="city" and an other entry almost identical but with type="administrative")
