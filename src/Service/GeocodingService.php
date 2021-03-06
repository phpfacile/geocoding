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
    const ADDRESS_TYPE_PLACE    = 'place';
    const ADDRESS_TYPE_LOCATION = 'location';

    /**
     * Array of instance of geocoder (ex: one for nomatim, onr for geonames, etc)
     *
     * @var $geocoders
     */
    protected $geocoders = [];

    /**
     * Retrieved from https://github.com/rupinder1133/country-codes-to-timezones-mapping/blob/master/cc%20to%20tz%20PHP.txt (no license defined and content validity not checked)
     *
     * @var $timeZoneByCountryCode
     */
    protected static $timeZoneByCountryCode = [
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
        'ZW' => 'Africa/Harare',
    ];

    /**
     * Constructor
     *
     * @param array      $providersCfg Configuration for different providers
     * @param string     $cacheDir     Full path to dir used at cache (must contain one dir per geocoder used)
     * @param string     $locale       Default locale to be used
     * @param HttpClient $httpClient   HttpClient to use
     *
     * @return GeocodingService
     */
    public function __construct($providersCfg, $cacheDir, $locale = 'fr', HttpClient $httpClient = null)
    {
        if (null === $httpClient) {
            $httpClient = new Guzzle6HttpClient();
        }

        $ttl = (30 * 24 * 3600);

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

            $storage = StorageFactory::factory(
                [
                    'adapter' => [
                        'name'    => 'filesystem',
                        'options' => [
                            'cache_dir' => $cacheDir.'/'.$providerName,
                            'ttl'       => $ttl,
                        ],
                    ],
                    'plugins' => [
                        'serializer',
                        'exception_handler' => ['throw_exceptions' => true],
                    ],
                ]
            );

            $cache = new SimpleCacheDecorator($storage);

            // If storage doesn't support per item TTL then setting an item TTL
            // will prevent any cache use :-/ according to SimpleCacheDecorator implementation
            $perItemTTL   = null;
            $capabilities = $storage->getCapabilities();
            if ((true === $capabilities->getStaticTtl()) && (0 < $capabilities->getMinTtl())) {
                $perItemTTL = $ttl;
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
     * @param string $addressFull Full text address (ex: "Paris, France" or "Avenue des champs Elysées, Paris, France")
     * @param string $locale      Locale (ex: 'fr', 'fr-FR' but not used)
     * @param string $limit       Max nb of results to be returned
     *
     * @return StdClass[]
     */
    public function getLocationsByAddress($addressFull, $locale = 'fr', $limit = 10)
    {
        $preferredProvider = 'nominatim';

        // For "addresses" location "nominatim" seems to be the best choice
        if (false === array_key_exists($preferredProvider, $this->geocoders)) {
            throw new \Exception('No '.$preferredProvider.' provider configured');
        }

        $query = GeocodeQuery::create($addressFull);
        $query = $query->withLocale($locale);
        $query = $query->withLimit($limit);

        $addresses = $this->geocoders[$preferredProvider]->geocodeQuery($query);

        return self::getGeocodedAdressesAsStdClassArray($addresses);
    }

    /**
     * Returns location of places found by geocoding as an array of array of properties
     * common to all tested providers
     *
     * @param string  $countryName  Name of the country
     * @param string  $placeName    Name of the place
     * @param string  $postalCode   Postal code (optionnal)
     * @param string  $locale       Locale (ex: 'fr', 'fr-FR' but not used)
     * @param string  $limit        Max nb of results to be returned
     * @param boolean $filterPlaces Whether or not to attempt to remove unexpected results (in case of places)
     *
     * @return StdClass[]
     */
    public function getPlacesByCountryAndPlaceName($countryName, $placeName, $postalCode = null, $locale = 'fr', $limit = 10, $filterPlaces = true)
    {
        // $preferedProvider = 'geonames';
        $preferredProvider = 'nominatim';

        $addressFull = trim($placeName).', '.trim($countryName);
        if (strlen($postalCode) > 0) {
            $addressFull = trim($postalCode).' '.$addressFull;
        }

        if (false === array_key_exists($preferredProvider, $this->geocoders)) {
            throw new \Exception('No '.$preferredProvider.' provider configured');
        }

        $query = GeocodeQuery::create($addressFull);
        $query = $query->withLocale($locale);
        $query = $query->withLimit($limit);

        $addresses = $this->geocoders[$preferredProvider]->geocodeQuery($query);

        return self::getGeocodedPlacesAsStdClassArray($addresses, $filterPlaces);
    }

    /**
     * Converts an array of geocoded addresses of places retrieved from a geocoder and convert it into
     * an array of "normalized" StdClass
     *
     * @param Geocoder\Model\Address[] $addresses    Addresses of places
     * @param boolean                  $filterPlaces Whether to try to remove unexpected addresses or not
     *
     * @return StdClass[]
     */
    protected static function getGeocodedPlacesAsStdClassArray($addresses, $filterPlaces = true)
    {
        return self::getGeocodedAdressesAsStdClassArray($addresses, self::ADDRESS_TYPE_PLACE, $filterPlaces);
    }

    /**
     * Converts an array of geocoded addresses retrieved from a geocoder and convert it into
     * an array of "normalized" StdClass
     *
     * @param Geocoder\Model\Address[] $addresses    Array of addresses
     * @param string                   $addressType  Whether it is a "generic" self::ADDRESS_TYPE_LOCATION or a self::ADDRESS_TYPE_PLACE
     * @param boolean                  $filterPlaces Whether to try to remove unexpected addresses or not
     *
     * @return StdClass[]
     */
    protected static function getGeocodedAdressesAsStdClassArray($addresses, $addressType = self::ADDRESS_TYPE_LOCATION, $filterPlaces = true)
    {
        $locations = [];

        // Here we assume webserver and database clocks are synchronized (usually it's OK as they are on the same server)
        $now = new \DateTime('now', new \DateTimeZone('UTC'));

        $previousAddress = null;

        foreach ($addresses as $address) {
            $location           = new \StdClass();
            $location->provider = $address->getProvidedBy();
            $location->geocodingDateTimeUTC = $now->format('Y-m-d H:i:s');

            switch ($address->getProvidedBy()) {
                case 'geonames':
                    $location->idProvider = $address->getGeonameId();
                    $location->name       = $address->getName();
                    // $address->getFCode(); // "PPLA2", "AIRP"
                    // $address->getFCLName(); // "city, village,...", "spot, building, farm"
                    break;
                case 'nominatim':
                    // Do we have to ignore this address? Tricky question
                    // Ex: With 'La Rochelle', 'France'
                    // The 2 first results are:
                    // * La Rochelle, Charente-Maritime, Nouvelle-Aquitaine, France métropolitaine, 17000, France
                    // * La Rochelle, Charente-Maritime, Nouvelle-Aquitaine, France métropolitaine, France
                    // Here we have to ignore the second one... but we can't ignore all addresses without postal code
                    // for instance, in case of 'Paris', 'France' the 2 first results have no postal code
                    // and as long as we are not interested in "arrondissements", the 1st result is the one
                    // we expect (dispite there is no postal code)
                    $ignoreAddress = false;
                    if ((true === $filterPlaces) && (self::ADDRESS_TYPE_PLACE === $addressType)) {
                        if (true === in_array($address->getType(), ['land_area'])) {
                            $ignoreAddress = true;
                        } else if (null !== $previousAddress) {
                            // Is it the same place as the previous one ?
                            // Unfortunately this test can not be regarded as fully reliable
                            $samePlace = true;
                            if (count($address->getAdminLevels()) === count($previousAddress->getAdminLevels())) {
                                $previousAdminLevels = $previousAddress->getAdminLevels();
                                foreach ($address->getAdminLevels() as $idx => $adminLevel) {
                                    if ($adminLevel->getLevel() !== $previousAdminLevels->get($idx)->getLevel()) {
                                        $samePlace = false;
                                        break;
                                    }

                                    // Unfortunately adminLevel code is empty so we have to rely on name
                                    if ($adminLevel->getName() !== $previousAdminLevels->get($idx)->getName()) {
                                        $samePlace = false;
                                        break;
                                    }

                                    // If ever, adminLevel code is in fact set
                                    if ($adminLevel->getCode() !== $previousAdminLevels->get($idx)->getCode()) {
                                        $samePlace = false;
                                        break;
                                    }
                                }
                            } else {
                                $samePlace = false;
                            }

                            // If they are the same we only keep the previous one
                            if (true === $samePlace) {
                                $ignoreAddress = true;
                            }
                        }
                    }

                    if (false === $ignoreAddress) {
                        $location->idProvider = $address->getOSMId();
                        $location->name       = $address->getDisplayName();

                        $location->custom = new \StdClass();
                        $location->custom->attribution = $address->getAttribution();
                        $location->custom->class       = $address->getClass();
                        $location->custom->osmType     = $address->getOSMType();
                        $location->custom->type        = $address->getType();
                    }
                    break;
                default:
                    throw new \Exception('Unsupported provider ['.$address->getProvidedBy().']');
            }

            if (false === $ignoreAddress) {
                /*
                    +              +----------+-----------+
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

                if (self::ADDRESS_TYPE_PLACE !== $addressType) {
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

                $adminLevels = $address->getAdminLevels();

                $location->adminLevels = [];
                foreach ($adminLevels as $adminLevel) {
                    $adminLevelStdClass        = new \StdClass();
                    $adminLevelStdClass->level = $adminLevel->getLevel();
                    $adminLevelStdClass->name  = $adminLevel->getName();
                    $adminLevelStdClass->code  = $adminLevel->getCode();
                    $location->adminLevels[]   = $adminLevelStdClass;
                }

                $locations[] = $location;

                $previousAddress = $address;
            }
        }

        return $locations;
    }

    /**
     * Does the provider returns any timezone?
     *
     * @param string $providerName Name (identifier) of the geocoder provider
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
        if (true === array_key_exists($country->isoCode, self::$timeZoneByCountryCode)) {
            return self::$timeZoneByCountryCode[$country->isoCode];
        }

        // TODO Manage countries with several timezones
        throw new \Exception('Unmanaged country isoCode ['.$country->isoCode.'] (either an invalid code or a country with several timezones)');
    }

    /**
     * Checks whether required fields (such as timezone) is defined
     * and complete them (if possible) if needed
     *
     * @param StdClass $locationStdClass Location as StdClass
     * @param array    $requiredFields   List of required fields
     *
     * @return void
     */
    public function completeWithMissingRequiredFields($locationStdClass, $requiredFields)
    {
        if (true === in_array('timezone', $requiredFields)) {
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
