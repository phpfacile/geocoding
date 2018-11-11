<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use PHPFacile\Geocoding\Service\GeocodingService;

final class GeocodingServiceTest extends TestCase
{
    protected $geocodingService;

    protected function setUp()
    {
        $cfg = include(__DIR__.'/config/local.php');
        $this->geocodingService = new GeocodingService($cfg['geocoding']['geocoders'], $cfg['geocoding']['cache']['cache_dir']);
    }

    protected function checkProviderOutput($provider, $query, $location, $isPlace, $idx = 0)
    {
        switch ($provider)
        {
            case 'nominatim':
                switch ($query)
                {
                    case 'La Rochelle, France':
                        switch ($idx) {
                            case 0:
                            case 1:
                                $this->assertEquals('nominatim', $location->provider);
                                $this->assertEquals('relation', $location->custom->osmType);
                                $this->assertEquals('boundary', $location->custom->class);
                                $this->assertEquals('administrative', $location->custom->type);
                                if (0 === $idx) {
                                    $this->assertEquals(117858, $location->idProvider);
                                    $this->assertEquals('La Rochelle, Charente-Maritime, Nouvelle-Aquitaine, France métropolitaine, 17000, France', $location->name);
                                    $this->assertEquals('17000', $location->postalCode);
                                    $this->assertEquals('La Rochelle', $location->locality);
                                    $this->assertEquals(46.1591126, $location->coordinates->latitude);
                                    $this->assertEquals(-1.1520434, $location->coordinates->longitude);
                                } else {
                                    $this->assertEquals(1661547, $location->idProvider);
                                    $this->assertEquals('La Rochelle, Charente-Maritime, Nouvelle-Aquitaine, France métropolitaine, France', $location->name);
                                    $this->assertNull($location->postalCode);
                                    $this->assertNull($location->locality);
                                    $this->assertEquals(46.1876796, $location->coordinates->latitude);
                                    $this->assertEquals(-1.0642807350468, $location->coordinates->longitude);
                                }
                                if ($isPlace) {
                                    $this->assertFalse(property_exists($location, 'streetNumber'));
                                    $this->assertFalse(property_exists($location, 'streetName'));
                                } else {
                                    $this->assertNull($location->streetNumber);
                                    $this->assertNull($location->streetName);
                                }
                                if ($isPlace) {
                                    $this->assertFalse(property_exists($location, 'subLocality'));
                                } else {
                                    $this->assertNull($location->subLocality);
                                }
                                $this->assertNull($location->timezone);
                                $this->assertEquals('FR', $location->country->isoCode);
                                $this->assertEquals(2, count($location->adminLevels));
                                $this->assertEquals(1, $location->adminLevels[0]->level);
                                $this->assertEquals('Nouvelle-Aquitaine', $location->adminLevels[0]->name);
                                $this->assertEquals('', $location->adminLevels[0]->code);
                                $this->assertEquals(2, $location->adminLevels[1]->level);
                                $this->assertEquals('La Rochelle', $location->adminLevels[1]->name);
                                $this->assertEquals('', $location->adminLevels[1]->code);
                                break;
                            case 2:
                                $this->assertEquals('node', $location->custom->osmType);
                                $this->assertEquals('nominatim', $location->provider);
                                $this->assertEquals('place', $location->custom->class);
                                $this->assertEquals('hamlet', $location->custom->type);
                                $this->assertEquals(2781941597, $location->idProvider);
                                $this->assertEquals('La Rochelle, Charolles, Saône-et-Loire, Bourgogne-Franche-Comté, France métropolitaine, France', $location->name);
                                $this->assertNull($location->postalCode);
                                $this->assertEquals('La Rochelle', $location->locality);
                                $this->assertEquals(46.2942431, $location->coordinates->latitude);
                                $this->assertEquals(4.3845907, $location->coordinates->longitude);
                                if ($isPlace) {
                                    $this->assertFalse(property_exists($location, 'streetNumber'));
                                    $this->assertFalse(property_exists($location, 'streetName'));
                                } else {
                                    $this->assertNull($location->streetNumber);
                                    $this->assertNull($location->streetName);
                                }
                                if ($isPlace) {
                                    $this->assertFalse(property_exists($location, 'subLocality'));
                                } else {
                                    $this->assertNull($location->subLocality);
                                }
                                $this->assertNull($location->timezone);
                                $this->assertEquals('FR', $location->country->isoCode);
                                $this->assertEquals(2, count($location->adminLevels));
                                $this->assertEquals(1, $location->adminLevels[0]->level);
                                $this->assertEquals('Bourgogne-Franche-Comté', $location->adminLevels[0]->name);
                                $this->assertEquals('', $location->adminLevels[0]->code);
                                $this->assertEquals(2, $location->adminLevels[1]->level);
                                $this->assertEquals('Charolles', $location->adminLevels[1]->name);
                                $this->assertEquals('', $location->adminLevels[1]->code);
                                break;
                            default:
                                throw new \Exception('Not supported');
                        }
                        break;
                    case 'Paris, France':
                        switch ($idx) {
                            case 0:
                            case 1:
                                $this->assertEquals('nominatim', $location->provider);
                                if (0 === $idx) {
                                    $this->assertEquals('relation', $location->custom->osmType);
                                    $this->assertEquals('place', $location->custom->class);
                                    $this->assertEquals('city', $location->custom->type);
                                    $this->assertEquals(7444, $location->idProvider);
                                    $this->assertEquals('Paris', $location->locality);
                                    $this->assertEquals(48.856610099999997, $location->coordinates->latitude);
                                    $this->assertEquals(2.3514992000000001, $location->coordinates->longitude);
                                } else {
                                    $this->assertEquals('relation', $location->custom->osmType);
                                    $this->assertEquals('boundary', $location->custom->class);
                                    $this->assertEquals('administrative', $location->custom->type);
                                    $this->assertEquals(71525, $location->idProvider);
                                    $this->assertNull($location->locality);
                                    $this->assertEquals(48.85881005, $location->coordinates->latitude);
                                    $this->assertEquals(2.32003101155031, $location->coordinates->longitude);
                                }
                                $this->assertEquals('Paris, Île-de-France, France métropolitaine, France', $location->name);
                                if ($isPlace) {
                                    $this->assertFalse(property_exists($location, 'streetNumber'));
                                    $this->assertFalse(property_exists($location, 'streetName'));
                                } else {
                                    $this->assertNull($location->streetNumber);
                                    $this->assertNull($location->streetName);
                                }
                                $this->assertNull($location->postalCode);
                                if ($isPlace) {
                                    $this->assertFalse(property_exists($location, 'subLocality'));
                                } else {
                                    $this->assertNull($location->subLocality);
                                }
                                $this->assertNull($location->timezone);
                                $this->assertEquals('FR', $location->country->isoCode);
                                $this->assertEquals(2, count($location->adminLevels));
                                $this->assertEquals(1, $location->adminLevels[0]->level);
                                $this->assertEquals('Île-de-France', $location->adminLevels[0]->name);
                                $this->assertEquals('', $location->adminLevels[0]->code);
                                $this->assertEquals(2, $location->adminLevels[1]->level);
                                $this->assertEquals('Paris', $location->adminLevels[1]->name);
                                $this->assertEquals('', $location->adminLevels[1]->code);
                                break;
                            case 2:
                                $this->assertEquals('nominatim', $location->provider);
                                $this->assertEquals('node', $location->custom->osmType);
                                $this->assertEquals('place', $location->custom->class);
                                $this->assertEquals('hamlet', $location->custom->type);
                                $this->assertEquals(5204281064, $location->idProvider);
                                $this->assertEquals('Paris', $location->locality);
                                $this->assertEquals(43.8287799, $location->coordinates->latitude);
                                $this->assertEquals(5.4270482, $location->coordinates->longitude);
                                $this->assertEquals('Paris, Apt, Vaucluse, Provence-Alpes-Côte d\'Azur, France métropolitaine, 84499, France', $location->name);
                                if ($isPlace) {
                                    $this->assertFalse(property_exists($location, 'streetNumber'));
                                    $this->assertFalse(property_exists($location, 'streetName'));
                                } else {
                                    $this->assertNull($location->streetNumber);
                                    $this->assertNull($location->streetName);
                                }
                                $this->assertEquals(84499, $location->postalCode);
                                if ($isPlace) {
                                    $this->assertFalse(property_exists($location, 'subLocality'));
                                } else {
                                    $this->assertNull($location->subLocality);
                                }
                                $this->assertNull($location->timezone);
                                $this->assertEquals('FR', $location->country->isoCode);
                                $this->assertEquals(2, count($location->adminLevels));
                                $this->assertEquals(1, $location->adminLevels[0]->level);
                                $this->assertEquals('Provence-Alpes-Côte d\'Azur', $location->adminLevels[0]->name);
                                $this->assertEquals('', $location->adminLevels[0]->code);
                                $this->assertEquals(2, $location->adminLevels[1]->level);
                                $this->assertEquals('Apt', $location->adminLevels[1]->name);
                                $this->assertEquals('', $location->adminLevels[1]->code);
                                break;
                            default:
                                throw new \Exception('Not supported');
                        }
                        break;
                    case 'Saint-Martin, France':
                        switch ($idx) {
                            case 0:
                            case 1:
                                $this->assertEquals('nominatim', $location->provider);
                                if (0 === $idx) {
                                    $this->assertEquals('relation', $location->custom->osmType);
                                    $this->assertEquals('boundary', $location->custom->class);
                                    $this->assertEquals('administrative', $location->custom->type);
                                    $this->assertEquals(1891583, $location->idProvider);
                                    $this->assertNull($location->locality);
                                    $this->assertEquals(18.0814066, $location->coordinates->latitude);
                                    $this->assertEquals(-63.0467131, $location->coordinates->longitude);
                                    $this->assertEquals(1, count($location->adminLevels));
                                    $this->assertEquals(1, $location->adminLevels[0]->level);
                                    $this->assertEquals('Saint-Martin', $location->adminLevels[0]->name);
                                    $this->assertEquals('', $location->adminLevels[0]->code);
                                } else {
                                    $this->assertEquals('relation', $location->custom->osmType);
                                    $this->assertEquals('boundary', $location->custom->class);
                                    $this->assertEquals('land_area', $location->custom->type);
                                    $this->assertEquals(299354, $location->idProvider);
                                    $this->assertNull($location->locality);
                                    $this->assertEquals(18.0814066, $location->coordinates->latitude);
                                    $this->assertEquals(-63.0467131, $location->coordinates->longitude);
                                    $this->assertEquals(0, count($location->adminLevels));
                                }
                                $this->assertEquals('Saint-Martin, 97150, France', $location->name);
                                if ($isPlace) {
                                    $this->assertFalse(property_exists($location, 'streetNumber'));
                                    $this->assertFalse(property_exists($location, 'streetName'));
                                } else {
                                    $this->assertNull($location->streetNumber);
                                    $this->assertNull($location->streetName);
                                }
                                $this->assertEquals('97150', $location->postalCode);
                                if ($isPlace) {
                                    $this->assertFalse(property_exists($location, 'subLocality'));
                                } else {
                                    $this->assertNull($location->subLocality);
                                }
                                $this->assertNull($location->timezone);
                                $this->assertEquals('FR', $location->country->isoCode);
                                break;
                            case 2:
                                $this->assertEquals('nominatim', $location->provider);
                                $this->assertEquals('relation', $location->custom->osmType);
                                $this->assertEquals('boundary', $location->custom->class);
                                $this->assertEquals('administrative', $location->custom->type);
                                $this->assertEquals(146202, $location->idProvider);
                                $this->assertEquals('Saint-Martin-sur-Oust', $location->locality);
                                $this->assertEquals(47.74511, $location->coordinates->latitude);
                                $this->assertEquals(-2.25487, $location->coordinates->longitude);
                                //$this->assertEquals(1403916, $location->idProvider);
                                $this->assertEquals('Saint-Martin-sur-Oust, Vannes, Morbihan, Bretagne, France métropolitaine, 56200, France', $location->name);
                                if ($isPlace) {
                                    $this->assertFalse(property_exists($location, 'streetNumber'));
                                    $this->assertFalse(property_exists($location, 'streetName'));
                                } else {
                                    $this->assertNull($location->streetNumber);
                                    $this->assertNull($location->streetName);
                                }
                                $this->assertEquals(56200, $location->postalCode);
                                if ($isPlace) {
                                    $this->assertFalse(property_exists($location, 'subLocality'));
                                } else {
                                    $this->assertNull($location->subLocality);
                                }
                                $this->assertNull($location->timezone);
                                $this->assertEquals('FR', $location->country->isoCode);
                                $this->assertEquals(2, count($location->adminLevels));
                                $this->assertEquals(1, $location->adminLevels[0]->level);
                                $this->assertEquals('Bretagne', $location->adminLevels[0]->name);
                                $this->assertEquals('', $location->adminLevels[0]->code);
                                $this->assertEquals(2, $location->adminLevels[1]->level);
                                $this->assertEquals('Vannes', $location->adminLevels[1]->name);
                                $this->assertEquals('', $location->adminLevels[1]->code);
                                break;
                            default:
                                $this->assertTrue(in_array($location->custom->type, ['administrative']));
                                //throw new \Exception('Not supported');
                        }
                        break;
                    case 'Nueva York, Estados Unidos':
                        switch ($idx) {
                            case 0:
                            case 1:
                                $this->assertEquals('nominatim', $location->provider);
                                if (0 === $idx) {
                                    $this->assertEquals('relation', $location->custom->osmType);
                                    $this->assertEquals('place', $location->custom->class);
                                    $this->assertEquals('city', $location->custom->type);
                                    $this->assertEquals(175905, $location->idProvider);
                                    $this->assertNull($location->locality);
                                    $this->assertEquals(40.730861900000001, $location->coordinates->latitude);
                                    $this->assertEquals(-73.987155799999996, $location->coordinates->longitude);
                                    $this->assertEquals(1, count($location->adminLevels));
                                    $this->assertEquals(1, $location->adminLevels[0]->level);
                                    $this->assertEquals('Nueva York', $location->adminLevels[0]->name);
                                    $this->assertEquals('', $location->adminLevels[0]->code);
                                } else {
                                    $this->assertEquals('relation', $location->custom->osmType);
                                    $this->assertEquals('boundary', $location->custom->class);
                                    $this->assertEquals('administrative', $location->custom->type);
                                    $this->assertEquals(61320, $location->idProvider);
                                    $this->assertNull($location->locality);
                                    $this->assertEquals(43.156168100000002, $location->coordinates->latitude);
                                    $this->assertEquals(-75.844994600000007, $location->coordinates->longitude);
                                    $this->assertEquals(1, count($location->adminLevels));
                                    $this->assertEquals(1, $location->adminLevels[0]->level);
                                    $this->assertEquals('Nueva York', $location->adminLevels[0]->name);
                                    $this->assertEquals('', $location->adminLevels[0]->code);
                                }
                                $this->assertEquals('Nueva York, Estados Unidos de América', $location->name);
                                if ($isPlace) {
                                    $this->assertFalse(property_exists($location, 'streetNumber'));
                                    $this->assertFalse(property_exists($location, 'streetName'));
                                } else {
                                    $this->assertNull($location->streetNumber);
                                    $this->assertNull($location->streetName);
                                }
                                $this->assertNull($location->postalCode);
                                if ($isPlace) {
                                    $this->assertFalse(property_exists($location, 'subLocality'));
                                } else {
                                    $this->assertNull($location->subLocality);
                                }
                                $this->assertNull($location->timezone);
                                $this->assertEquals('US', $location->country->isoCode);
                                break;
                            default:
                                $this->assertTrue(in_array($location->custom->type, ['administrative']));
                                //throw new \Exception('Not supported');
                        }
                        break;
                    default:
                        throw new \Exception('Not supported');
                }
                break;
            case 'geonames':
                switch ($query)
                {
                    case 'La Rochelle, France':
                        $this->assertEquals('geonames', $location->provider);
                        $this->assertEquals(3006787, $location->idProvider);
                        $this->assertEquals('La Rochelle', $location->name);
                        $this->assertEquals(46.16667, $location->coordinates->latitude);
                        $this->assertEquals(-1.15, $location->coordinates->longitude);
                        $this->assertNull($location->streetNumber);
                        $this->assertNull($location->streetName);
                        $this->assertNull($location->subLocality);
                        $this->assertEquals('La Rochelle', $location->locality);
                        $this->assertNull($location->postalCode);
                        $this->assertEquals('Europe/Paris', $location->timezone);
                        $this->assertEquals('FR', $location->country->isoCode);
                        break;
                    default:
                        throw new \Exception('Not supported');
                }
                break;
            default:
                throw new \Exception('Not supported');
        }
    }

    public function testGetLocationsByAddress()
    {
        $preferredProvider = 'nominatim';

        // TAKE CARE No more than 1 req per sec.
        sleep(2);

        $locations = $this->geocodingService->getLocationsByAddress('La Rochelle, France', 'fr', 5);
        $this->assertEquals(5, count($locations));

        $location = $locations[0];
        self::checkProviderOutput($preferredProvider, 'La Rochelle, France', $location, false);

        // TAKE CARE No more than 1 req per sec.
        sleep(2);

        $locations = $this->geocodingService->getLocationsByAddress('Paris, France', 'fr', 5);
        $this->assertEquals(5, count($locations));

        $location = $locations[0];
        self::checkProviderOutput($preferredProvider, 'Paris, France', $location, false);

        // TAKE CARE No more than 1 req per sec.
        sleep(2);

        $locations = $this->geocodingService->getLocationsByAddress('Place de l\'hôtel de ville, Paris, France', 'fr', 5);
        $this->assertEquals(3, count($locations));

        // TAKE CARE No more than 1 req per sec.
        sleep(2);

        $locations = $this->geocodingService->getLocationsByAddress('Place de la république, Paris, France', 'fr', 5);
        $this->assertEquals(5, count($locations));
    }

    public function testGetPlacesByCountryAndPlaceName()
    {
        $preferredProvider = 'nominatim';

        // TAKE CARE No more than 1 req per sec.
        sleep(2);

        //--------------------------
        // La Rochelle, without any place filter
        //--------------------------
        $locations = $this->geocodingService->getPlacesByCountryAndPlaceName('France', 'La Rochelle', null, 'fr', 5, false);
        $this->assertEquals(5, count($locations));

        // Here (with no class filter) the 2 1st responses are almost the same but
        // even same class, osmType and type
        // but the 1st one as a postal code, not the second one
        $location = $locations[0];
        self::checkProviderOutput($preferredProvider, 'La Rochelle, France', $location, true, 0);

        $location = $locations[1];
        self::checkProviderOutput($preferredProvider, 'La Rochelle, France', $location, true, 1);

        $location = $locations[2];
        self::checkProviderOutput($preferredProvider, 'La Rochelle, France', $location, true, 2);

        // Make sure the returned locations are in the selected country
        foreach ($locations as $location) {
            $this->assertEquals('FR', $location->country->isoCode);
        }

        //--------------------------
        // La Rochelle, with place filter
        //--------------------------
        $locations = $this->geocodingService->getPlacesByCountryAndPlaceName('France', 'La Rochelle', null, 'fr', 5);
        $this->assertEquals(4, count($locations));

        $location = $locations[0];
        self::checkProviderOutput($preferredProvider, 'La Rochelle, France', $location, true, 0);

        // Here 2nd location is the 3rd result of the previous request
        $location = $locations[1];
        self::checkProviderOutput($preferredProvider, 'La Rochelle, France', $location, true, 2);

        // Make sure the returned locations are in the selected country
        foreach ($locations as $location) {
            $this->assertEquals('FR', $location->country->isoCode);
        }

        // TAKE CARE No more than 1 req per sec.
        sleep(2);

        //--------------------------
        // Paris, without any place filter
        //--------------------------
        $locations = $this->geocodingService->getPlacesByCountryAndPlaceName('France', 'Paris', null, 'fr', 5, false);
        $this->assertEquals(5, count($locations));

        // Here the 2 1st responses are almost the same but
        // the 1st one has class = "place", osmType = "relation", type = "city"
        // the 2nd one has class = "boundary", osmType = "relation", type = "administrative"

        $location = $locations[0];
        self::checkProviderOutput($preferredProvider, 'Paris, France', $location, true, 0);

        $location = $locations[1];
        self::checkProviderOutput($preferredProvider, 'Paris, France', $location, true, 1);

        // Make sure the returned locations are in the selected country
        foreach ($locations as $location) {
            $this->assertEquals('FR', $location->country->isoCode);
        }

        //--------------------------
        // Paris, with place filter
        //--------------------------
        $locations = $this->geocodingService->getPlacesByCountryAndPlaceName('France', 'Paris', null, 'fr', 5);
        $this->assertEquals(4, count($locations));

        $location = $locations[0];
        self::checkProviderOutput($preferredProvider, 'Paris, France', $location, true, 0);

        // Here 2nd location is the 3rd result of the previous request
        $location = $locations[1];
        self::checkProviderOutput($preferredProvider, 'Paris, France', $location, true, 2);

        // Make sure the returned locations are in the selected country
        foreach ($locations as $location) {
            $this->assertEquals('FR', $location->country->isoCode);
        }

        //--------------------------
        // Saint-Martin, most common place name in france (255 places) according to wikipedia (no place filter)
        //--------------------------
        $locations = $this->geocodingService->getPlacesByCountryAndPlaceName('France', 'Saint-Martin', null, 'fr', 30, false);
        $this->assertEquals(30, count($locations));

        $location = $locations[0];
        self::checkProviderOutput($preferredProvider, 'Saint-Martin, France', $location, true, 0);

        $location = $locations[1];
        self::checkProviderOutput($preferredProvider, 'Saint-Martin, France', $location, true, 1);

        $location = $locations[2];
        self::checkProviderOutput($preferredProvider, 'Saint-Martin, France', $location, true, 2);

        for ($i = 3; $i < count($locations); $i++) {
            self::checkProviderOutput($preferredProvider, 'Saint-Martin, France', $location, true, $i);
        }

        // Make sure the returned locations are in the selected country
        foreach ($locations as $location) {
            $this->assertEquals('FR', $location->country->isoCode);
        }

        //--------------------------
        // Saint-Martin, most common place name in france (255 places) according to wikipedia (with place filter)
        //--------------------------
        $locations = $this->geocodingService->getPlacesByCountryAndPlaceName('France', 'Saint-Martin', null, 'fr', 10);
        $this->assertEquals(9, count($locations));

        $location = $locations[0];
        self::checkProviderOutput($preferredProvider, 'Saint-Martin, France', $location, true, 0);

        // Here 2nd location is the 3rd result of the no filter query
        $location = $locations[1];
        self::checkProviderOutput($preferredProvider, 'Saint-Martin, France', $location, true, 2);


        // Make sure the returned locations are in the selected country
        foreach ($locations as $location) {
            $this->assertEquals('FR', $location->country->isoCode);
        }

        //---------------------------------------------
        // Query in a country with several timezones (no place filter)
        //---------------------------------------------
        $locations = $this->geocodingService->getPlacesByCountryAndPlaceName('United States Of America', 'San Francisco', null, 'en', 5, false);
        $this->assertEquals(5, count($locations));
    }

    public function testL10nInGetPlacesByCountryAndPlaceName()
    {
        $preferredProvider = 'nominatim';

        // TAKE CARE No more than 1 req per sec.
        sleep(2);

        //-------------------------------------------------------
        // Nueva York, Estados Unidos en 'es' (without any place filter)
        //--------------------------------------------------------
        $locations = $this->geocodingService->getPlacesByCountryAndPlaceName('Estados Unidos', 'Nueva York', null, 'es', 5, false);
        $this->assertEquals(2, count($locations));

        $location = $locations[0];
        self::checkProviderOutput($preferredProvider, 'Nueva York, Estados Unidos', $location, true, 0);

        $location = $locations[1];
        self::checkProviderOutput($preferredProvider, 'Nueva York, Estados Unidos', $location, true, 1);

        // Make sure the returned locations are in the selected country
        foreach ($locations as $location) {
            $this->assertEquals('US', $location->country->isoCode);
        }
    }

    public function testTimezoneCountryWithSingleTimezone()
    {
        $country = new \StdClass();
        $country->isoCode = 'FR';
        $postalCode = '';
        $location = '';
        $timeZone = $this->geocodingService->getTimeZoneByCountryAndLocation($country, $postalCode, $location);
        $this->assertEquals('Europe/Paris', $timeZone);
    }

    /**
     * @expectedException Exception
     */
    public function testTimezoneCountryWithSeveralTimezone()
    {
        $country = new \StdClass();
        $country->isoCode = 'US';
        $postalCode = '';
        $location = '';
        $timeZone = $this->geocodingService->getTimeZoneByCountryAndLocation($country, $postalCode, $location);
    }
}
