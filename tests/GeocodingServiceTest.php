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
                                $this->assertEquals('boundary', $location->custom->class);
                                $this->assertEquals('relation', $location->custom->osmType);
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
                                    $this->assertEquals('place', $location->custom->class);
                                    $this->assertEquals('relation', $location->custom->osmType);
                                    $this->assertEquals('city', $location->custom->type);
                                    $this->assertEquals(7444, $location->idProvider);
                                    $this->assertEquals('Paris', $location->locality);
                                    $this->assertEquals(48.856610099999997, $location->coordinates->latitude);
                                    $this->assertEquals(2.3514992000000001, $location->coordinates->longitude);
                                } else {
                                    $this->assertEquals('boundary', $location->custom->class);
                                    $this->assertEquals('relation', $location->custom->osmType);
                                    $this->assertEquals('administrative', $location->custom->type);
                                    $this->assertEquals(71525, $location->idProvider);
                                    $this->assertNull($location->locality);
                                    $this->assertEquals(48.85881005, $location->coordinates->latitude);
                                    $this->assertEquals(2.32003101155031, $location->coordinates->longitude);
                                }
                                //$this->assertEquals(1403916, $location->idProvider);
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
                                break;
                            default:
                                throw new \Exception('Not supported');
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

        $locations = $this->geocodingService->getLocationsByAddress('La Rochelle, France');
        $this->assertEquals(5, count($locations));

        $location = $locations[0];
        self::checkProviderOutput($preferredProvider, 'La Rochelle, France', $location, false);

        // TAKE CARE No more than 1 req per sec.
        sleep(2);

        $locations = $this->geocodingService->getLocationsByAddress('Paris, France');
        $this->assertEquals(5, count($locations));

        $location = $locations[0];
        self::checkProviderOutput($preferredProvider, 'Paris, France', $location, false);

        // TAKE CARE No more than 1 req per sec.
        sleep(2);

        $locations = $this->geocodingService->getLocationsByAddress('Place de l\'hôtel de ville, Paris, France');
        $this->assertEquals(3, count($locations));

        // TAKE CARE No more than 1 req per sec.
        sleep(2);

        $locations = $this->geocodingService->getLocationsByAddress('Place de la république, Paris, France');
        $this->assertEquals(5, count($locations));
    }

    public function testGetPlacesByCountryAndPlaceName()
    {
        $preferredProvider = 'nominatim';

        // TAKE CARE No more than 1 req per sec.
        sleep(2);

        $locations = $this->geocodingService->getPlacesByCountryAndPlaceName('France', 'La Rochelle');
        $this->assertEquals(5, count($locations));

        // Here the 2 1st responses are almost the same but
        // even same class, osmType and type
        // but the 1st one as a postal code, not the second one
        $location = $locations[0];
        self::checkProviderOutput($preferredProvider, 'La Rochelle, France', $location, true, 0);

        $location = $locations[1];
        self::checkProviderOutput($preferredProvider, 'La Rochelle, France', $location, true, 1);

        // Make sure the returned locations are in the selected country
        foreach ($locations as $location) {
            $this->assertEquals('FR', $location->country->isoCode);
        }

        // TAKE CARE No more than 1 req per sec.
        sleep(2);

        $locations = $this->geocodingService->getPlacesByCountryAndPlaceName('France', 'Paris');
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
