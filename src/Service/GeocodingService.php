<?php
namespace PHPFacile\Geocoding\Service;

use Geocoder\Provider\Cache\ProviderCache;
use Geocoder\Provider\Provider;
use Geocoder\Query\GeocodeQuery;
use Geocoder\Query\ReverseQuery;
use Geocoder\StatefulGeocoder;

use Geocoder\Provider\Nominatim\Nominatim as NominatimProvider;
use Geocoder\Provider\Geonames\Geonames as GeonamesProvider;

use Http\Adapter\Guzzle6\Client as Guzzle6HttpClient;
use Http\Client\HttpClient;

use Zend\Cache\StorageFactory;
use Zend\Cache\Psr\SimpleCache\SimpleCacheDecorator;

class GeocodingService
{
    protected $geocoders = [];

    // Retrieved from https://github.com/rupinder1133/country-codes-to-timezones-mapping/blob/master/cc%20to%20tz%20PHP.txt (no license defined and content validity not checked)
    static $timeZoneByCountryCode = [
        'AD' => 'Europe/Andorra',
        'AE' => 'Asia/Dubai',
        'AF' => 'Asia/Kabul',
        'AG' => 'America/Antigua',
        'AI' => 'America/Anguilla',
        'AL' => 'Europe/Tirane',
        'AM' => 'Asia/Yerevan',
        'AN' => 'America/Curacao',
        'AO' => 'Africa/Luanda',
        'AS' => 'Pacific/Pago_Pago',
        'AT' => 'Europe/Vienna',
        'AW' => 'America/Aruba',
        'AX' => 'Europe/Mariehamn',
        'AZ' => 'Asia/Baku',
        'BA' => 'Europe/Sarajevo',
        'BB' => 'America/Barbados',
        'BD' => 'Asia/Dhaka',
        'BE' => 'Europe/Brussels',
        'BF' => 'Africa/Ouagadougou',
        'BG' => 'Europe/Sofia',
        'BH' => 'Asia/Bahrain',
        'BI' => 'Africa/Bujumbura',
        'BJ' => 'Africa/Porto-Novo',
        'BL' => 'America/St_Barthelemy',
        'BM' => 'Atlantic/Bermuda',
        'BN' => 'Asia/Brunei',
        'BO' => 'America/La_Paz',
        'BS' => 'America/Nassau',
        'BT' => 'Asia/Thimphu',
        'BW' => 'Africa/Gaborone',
        'BY' => 'Europe/Minsk',
        'BZ' => 'America/Belize',
        'CC' => 'Indian/Cocos',
        'CF' => 'Africa/Bangui',
        'CG' => 'Africa/Brazzaville',
        'CH' => 'Europe/Zurich',
        'CI' => 'Africa/Abidjan',
        'CK' => 'Pacific/Rarotonga',
        'CM' => 'Africa/Douala',
        'CO' => 'America/Bogota',
        'CR' => 'America/Costa_Rica',
        'CU' => 'America/Havana',
        'CV' => 'Atlantic/Cape_Verde',
        'CX' => 'Indian/Christmas',
        'CY' => 'Asia/Nicosia',
        'CZ' => 'Europe/Prague',
        'DE' => 'Europe/Berlin',
        'DJ' => 'Africa/Djibouti',
        'DK' => 'Europe/Copenhagen',
        'DM' => 'America/Dominica',
        'DO' => 'America/Santo_Domingo',
        'DZ' => 'Africa/Algiers',
        'EE' => 'Europe/Tallinn',
        'EG' => 'Africa/Cairo',
        'EH' => 'Africa/El_Aaiun',
        'ER' => 'Africa/Asmara',
        'ET' => 'Africa/Addis_Ababa',
        'FI' => 'Europe/Helsinki',
        'FJ' => 'Pacific/Fiji',
        'FK' => 'Atlantic/Stanley',
        'FO' => 'Atlantic/Faroe',
        'FR' => 'Europe/Paris',
        'GA' => 'Africa/Libreville',
        'GB' => 'Europe/London',
        'GD' => 'America/Grenada',
        'GE' => 'Asia/Tbilisi',
        'GF' => 'America/Cayenne',
        'GG' => 'Europe/Guernsey',
        'GH' => 'Africa/Accra',
        'GI' => 'Europe/Gibraltar',
        'GM' => 'Africa/Banjul',
        'GN' => 'Africa/Conakry',
        'GP' => 'America/Guadeloupe',
        'GQ' => 'Africa/Malabo',
        'GR' => 'Europe/Athens',
        'GS' => 'Atlantic/South_Georgia',
        'GT' => 'America/Guatemala',
        'GU' => 'Pacific/Guam',
        'GW' => 'Africa/Bissau',
        'GY' => 'America/Guyana',
        'HK' => 'Asia/Hong_Kong',
        'HN' => 'America/Tegucigalpa',
        'HR' => 'Europe/Zagreb',
        'HT' => 'America/Port-au-Prince',
        'HU' => 'Europe/Budapest',
        'IE' => 'Europe/Dublin',
        'IL' => 'Asia/Jerusalem',
        'IM' => 'Europe/Isle_of_Man',
        'IN' => 'Asia/Kolkata',
        'IO' => 'Indian/Chagos',
        'IQ' => 'Asia/Baghdad',
        'IR' => 'Asia/Tehran',
        'IS' => 'Atlantic/Reykjavik',
        'IT' => 'Europe/Rome',
        'JE' => 'Europe/Jersey',
        'JM' => 'America/Jamaica',
        'JO' => 'Asia/Amman',
        'JP' => 'Asia/Tokyo',
        'KE' => 'Africa/Nairobi',
        'KG' => 'Asia/Bishkek',
        'KH' => 'Asia/Phnom_Penh',
        'KM' => 'Indian/Comoro',
        'KN' => 'America/St_Kitts',
        'KP' => 'Asia/Pyongyang',
        'KR' => 'Asia/Seoul',
        'KW' => 'Asia/Kuwait',
        'KY' => 'America/Cayman',
        'LA' => 'Asia/Vientiane',
        'LB' => 'Asia/Beirut',
        'LC' => 'America/St_Lucia',
        'LI' => 'Europe/Vaduz',
        'LK' => 'Asia/Colombo',
        'LR' => 'Africa/Monrovia',
        'LS' => 'Africa/Maseru',
        'LT' => 'Europe/Vilnius',
        'LU' => 'Europe/Luxembourg',
        'LV' => 'Europe/Riga',
        'LY' => 'Africa/Tripoli',
        'MA' => 'Africa/Casablanca',
        'MC' => 'Europe/Monaco',
        'MD' => 'Europe/Chisinau',
        'ME' => 'Europe/Podgorica',
        'MF' => 'America/Marigot',
        'MG' => 'Indian/Antananarivo',
        'MK' => 'Europe/Skopje',
        'ML' => 'Africa/Bamako',
        'MM' => 'Asia/Rangoon',
        'MO' => 'Asia/Macau',
        'MP' => 'Pacific/Saipan',
        'MQ' => 'America/Martinique',
        'MR' => 'Africa/Nouakchott',
        'MS' => 'America/Montserrat',
        'MT' => 'Europe/Malta',
        'MU' => 'Indian/Mauritius',
        'MV' => 'Indian/Maldives',
        'MW' => 'Africa/Blantyre',
        'MZ' => 'Africa/Maputo',
        'NA' => 'Africa/Windhoek',
        'NC' => 'Pacific/Noumea',
        'NE' => 'Africa/Niamey',
        'NF' => 'Pacific/Norfolk',
        'NG' => 'Africa/Lagos',
        'NI' => 'America/Managua',
        'NL' => 'Europe/Amsterdam',
        'NO' => 'Europe/Oslo',
        'NP' => 'Asia/Kathmandu',
        'NR' => 'Pacific/Nauru',
        'NU' => 'Pacific/Niue',
        'OM' => 'Asia/Muscat',
        'PA' => 'America/Panama',
        'PE' => 'America/Lima',
        'PG' => 'Pacific/Port_Moresby',
        'PH' => 'Asia/Manila',
        'PK' => 'Asia/Karachi',
        'PL' => 'Europe/Warsaw',
        'PM' => 'America/Miquelon',
        'PN' => 'Pacific/Pitcairn',
        'PR' => 'America/Puerto_Rico',
        'PS' => 'Asia/Gaza',
        'PW' => 'Pacific/Palau',
        'PY' => 'America/Asuncion',
        'QA' => 'Asia/Qatar',
        'RE' => 'Indian/Reunion',
        'RO' => 'Europe/Bucharest',
        'RS' => 'Europe/Belgrade',
        'RW' => 'Africa/Kigali',
        'SA' => 'Asia/Riyadh',
        'SB' => 'Pacific/Guadalcanal',
        'SC' => 'Indian/Mahe',
        'SD' => 'Africa/Khartoum',
        'SE' => 'Europe/Stockholm',
        'SG' => 'Asia/Singapore',
        'SH' => 'Atlantic/St_Helena',
        'SI' => 'Europe/Ljubljana',
        'SJ' => 'Arctic/Longyearbyen',
        'SK' => 'Europe/Bratislava',
        'SL' => 'Africa/Freetown',
        'SM' => 'Europe/San_Marino',
        'SN' => 'Africa/Dakar',
        'SO' => 'Africa/Mogadishu',
        'SR' => 'America/Paramaribo',
        'ST' => 'Africa/Sao_Tome',
        'SV' => 'America/El_Salvador',
        'SY' => 'Asia/Damascus',
        'SZ' => 'Africa/Mbabane',
        'TC' => 'America/Grand_Turk',
        'TD' => 'Africa/Ndjamena',
        'TF' => 'Indian/Kerguelen',
        'TG' => 'Africa/Lome',
        'TH' => 'Asia/Bangkok',
        'TJ' => 'Asia/Dushanbe',
        'TK' => 'Pacific/Fakaofo',
        'TL' => 'Asia/Dili',
        'TM' => 'Asia/Ashgabat',
        'TN' => 'Africa/Tunis',
        'TO' => 'Pacific/Tongatapu',
        'TR' => 'Europe/Istanbul',
        'TT' => 'America/Port_of_Spain',
        'TV' => 'Pacific/Funafuti',
        'TW' => 'Asia/Taipei',
        'TZ' => 'Africa/Dar_es_Salaam',
        'UG' => 'Africa/Kampala',
        'UK' => 'Europe/London',
        'UY' => 'America/Montevideo',
        'VA' => 'Europe/Vatican',
        'VC' => 'America/St_Vincent',
        'VE' => 'America/Caracas',
        'VG' => 'America/Tortola',
        'VI' => 'America/St_Thomas',
        'VN' => 'Asia/Ho_Chi_Minh',
        'VU' => 'Pacific/Efate',
        'WF' => 'Pacific/Wallis',
        'WS' => 'Pacific/Apia',
        'YE' => 'Asia/Aden',
        'YT' => 'Indian/Mayotte',
        'ZA' => 'Africa/Johannesburg',
        'ZM' => 'Africa/Lusaka',
        'ZW' => 'Africa/Harare'
    ];

    /**
     *
     * @param array $providersCfg Configuration for different providers
     */
    public function __construct($providersCfg, $cacheDir, $locale = 'fr', HttpClient $httpClient = null)
    {
        if (null === $httpClient) {
            $httpClient = new Guzzle6HttpClient();
        }

        foreach ($providersCfg as $providerName => $providerCfg) {
            $provider = null;
            switch ($providerName) {
                case 'geonames':
                    // TODO Check userName is actually defined
                    $provider = new GeonamesProvider($httpClient, $providerCfg['userName']);
                    break;
                case 'nominatim':
                    // TODO Check rootUrl and userAgent are actually defined
                    $provider = new NominatimProvider($httpClient, $providerCfg['rootUrl'], $providerCfg['userAgent']);
                    break;
                default:
                    throw new \Exception('Unmanaged provider ['.$providerName.']');

            }
            $storage = StorageFactory::factory([
                'adapter' => [
                    'name' => 'filesystem',
                    'options' => [
                        'cache_dir' => $cacheDir.'/'.$providerName,
                        'ttl' => 3*24*3600
                    ]
                ],
                'plugins' => [
                    'serializer',
                    'exception_handler' => ['throw_exceptions' => true],
                ]
            ]);

            // TODO Raise an exception (or automatically create dir if possible)
            // if $cacheDir.'_'.$providerName doesn't exist or is not writable

            // FIXME Nothing can be found in cache dir after test => Cache is not working
            $cache = new SimpleCacheDecorator($storage);

            // If storage doesn't support per item TTL then setting an item TTL
            // will prevent any cache use :-/
            // according to SimpleCacheDecorator implementation
            $perItemTTL = null;
            $capabilities = $storage->getCapabilities();
            if ($capabilities->getStaticTtl() && (0 < $capabilities->getMinTtl())) {
                $perItemTTL = 3*24*3600;
            }

            $provideCache = new ProviderCache($provider, $cache, $perItemTTL);
            // FIXME Why should we set a locale here? How it is used?
            $this->geocoders[$providerName] = new StatefulGeocoder($provideCache, $locale);
        }
    }

    /**
     * Returns location found by geocoding as an array of array of properties
     * common to all tested providers
     *
     * @param string $address Full text address
     *
     * @return StdClass[]
     */
    public function getLocationsByAddress($addressFull)
    {
        // For "addresses" location "nominatim" seems to be the best choice
        if (false === array_key_exists('nominatim', $this->geocoders)) {
            throw new \Exception('No nominatim provider configured');
        }
        $addresses = $this->geocoders['nominatim']->geocodeQuery(GeocodeQuery::create($addressFull));

        return self::getGeocodedAdressesAsStdClassArray($addresses);
    }

    /**
     * Returns location of places found by geocoding as an array of array of properties
     * common to all tested providers
     *
     * @param string $countryName Name of the country
     * @param string $placeName   Name of the place
     * @param string $postalCode  Postal code (optionnal)
     * @param string $locale      Locale (ex: 'fr', 'fr-FR' but not used)
     *
     * @return StdClass[]
     */
    public function getPlacesByCountryAndPlaceName($countryName, $placeName, $postalCode = '', $locale = '')
    {
        $addressFull = trim($placeName).', '.trim($countryName);
        if (strlen($postalCode) > 0) {
            $addressFull = trim($postalCode).' '.$addressFull;
        }

        // $preferedProvider = 'geonames';
        $preferredProvider = 'nominatim';

        if (false === array_key_exists($preferredProvider, $this->geocoders)) {
            throw new \Exception('No '.$preferredProvider.' provider configured');
        }
        $addresses = $this->geocoders[$preferredProvider]->geocodeQuery(GeocodeQuery::create($addressFull));

        return self::getGeocodedPlacesAsStdClassArray($addresses);
    }

    protected static function getGeocodedPlacesAsStdClassArray($addresses)
    {
        return self::getGeocodedAdressesAsStdClassArray($addresses, 'place');
    }

    protected static function getGeocodedAdressesAsStdClassArray($addresses, $addressType = 'location')
    {
        $locations = [];

        foreach ($addresses as $address) {
            $location = new \StdClass();
            $location->provider = $address->getProvidedBy();
            switch ($address->getProvidedBy()) {
                case 'geonames':
                    $location->idProvider = $address->getGeonameId();
                    $location->name       = $address->getName();
                    // $address->getFCode(); // "PPLA2", "AIRP"
                    // $address->getFCLName(); // "city, village,...", "spot, building, farm"
                    break;
                case 'nominatim':
                    $location->idProvider = $address->getOSMId();
                    $location->name       = $address->getDisplayName();

                    $location->custom              = new \StdClass();
                    $location->custom->attribution = $address->getAttribution();
                    $location->custom->class       = $address->getClass();
                    $location->custom->osmType     = $address->getOSMType();
                    $location->custom->type        = $address->getType();
                    break;
                default:
                    throw new \Exception('Unsupported provider ['.$address->getProvidedBy().']');
            }

            // $adminLevels = $address->getAdminLevels();
            // var_dump($location->name);
            // foreach ($adminLevels as $adminLevel) var_dump($adminLevel);

            /*
                               +----------+-----------+
                               | geonames | nominatim |
                +--------------+----------+-----------+
                | streetNumber |    ?     |     ?     |
                | streetName   | not set  |    set    |
                | postalCode   | not set  |    set    |
                | locality     | not set  |    set    |
                | subLocality  | not set  |    set    |
                | latitude     |    set   |    set    |
                | longitude    |    set   |    set    |
                | timezone     |    set   |  not set  |
                | country      |    set   |    set    |
                +--------------+----------+-----------+
            */
            if ('place' !== $addressType) {
                $location->streetNumber = $address->getStreetNumber();
                $location->streetName   = $address->getStreetName();
                $location->subLocality  = $address->getSubLocality();
            }

            $location->postalCode       = $address->getPostalCode();
            $location->locality         = $address->getLocality();
            $location->timezone         = $address->getTimezone();
            $location->country          = new \StdClass();
            $location->country->isoCode = $address->getCountry()->getCode();

            $location->coordinates            = new \StdClass();
            $location->coordinates->latitude  = $address->getCoordinates()->getLatitude();
            $location->coordinates->longitude = $address->getCoordinates()->getLongitude();

            $locations[] = $location;
        }
        return $locations;
    }

    /**
     * Does the provider returns any timezone?
     *
     * @return boolean
     */
     public function doProvideTimezone($providerName = null)
     {
         if (null === $providerName) {
             $providerName = $this->geocoder->getProvider()->getName();
         }

         switch ($providerName) {
             case 'nominatim':
                return false;
            default:
                throw new \Exception('Unmanaged provider ['.$providerName.']');
         }
     }

     /**
      * Returns the time zone of the given location
      *
      * @param StdClass $country    StdClass with isoCode attribute
      * @param string   $postalCode Postal code
      * @param string   $location   Location name (ex; Paris)
      *
      * @return string  Timezone (ex: Europe/Paris)
      */
     public function getTimeZoneByCountryAndLocation($country, $postalCode, $location)
     {
         if (array_key_exists($country->isoCode, self::$timeZoneByCountryCode)) {
             return self::$timeZoneByCountryCode[$country->isoCode];
         }

         // TODO Manage countries with several timezones
         throw new \Exception('Unmanaged country isoCode ['.$country->isoCode.'] (either an invalid code or a country with several timezones)');
     }

     public function completeWithMissingRequiredFields($locationStdClass, $requiredFields)
     {
        if (in_array('timezone', $requiredFields)) {
            // Do our best to get the timezone (so as to be able to compute UTC datetime)
            if ((0 === strlen($locationStdClass->timezone))
                && (false === $this->doProvideTimezone($locationStdClass->provider))
            ) {
                // Try another provider
                $locationStdClass->timezone = $this->getTimeZoneByCountryAndLocation($locationStdClass->country, $locationStdClass->postalCode, $locationStdClass->locality);
                // FIXME Store zipcode provider ??
            }
        }
     }
}
