<?php

namespace Tenth {

    use GuzzleHttp\Client;
    use GuzzleHttp\Exception\GuzzleException;
    use GuzzleHttp\RequestOptions;
    use Tenth\MyTotalComfort\Exception;
    use Tenth\MyTotalComfort\Zone;
    use Tenth\MyTotalComfort\Location;

    /**
     * Class MyTotalComfort
     *
     * The Scope of one object of this class is defined as the bounds of what can be seen by a single user using the TCC
     * web interface.
     *
     * @package Tenth
     */
    class MyTotalComfort
    {

        /** @var \GuzzleHttp\Cookie\CookieJarInterface */
        protected $cookieJar = null;

        /** @var Client  */
        protected $client = null;

        /** @var int|null */
        protected $defaultLocationId = null;

        /** @var Location[] */
        protected $locations = [];

        /** @var Zone[] */
        protected $zones = [];

        /** @var string */
        private $email;

        /** @var string */
        private $password;


        /**
         * MyTotalComfort constructor.  Pass login arguments.
         *
         * @param string $email Login email address.
         * @param string $password Login password.
         * @param \GuzzleHttp\Cookie\CookieJarInterface $cookieJar Optional. Cookie jar to be used if desired.  Useful
         * for allowing TCC logins to persist between script runs.  If not provided, one will be created.
         * @return void
         *
         * @throws Exception Thrown when credentials are invalid.
         */
        public function __construct($email, $password, $cookieJar = null)
        {

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("A valid email address was not provided for login.");
            }

            if ($cookieJar === null) {
                $this->cookieJar = new \GuzzleHttp\Cookie\CookieJar();
            } else {
                $this->cookieJar = $cookieJar;
            }

            $this->email = $email;
            $this->password = $password;

            $this->client = new Client([
                RequestOptions::COOKIES => $this->cookieJar,
                RequestOptions::ALLOW_REDIRECTS => [
                    'max'             => 10,        // allow at most 10 redirects.
                    'strict'          => false,      // use "strict" RFC compliant redirects.
                    'referer'         => true,      // add a Referer header
                    'track_redirects' => true,
                ]
            ]);
        }


        /**
         * Wrapper for the Guzzle request method.  Detects when the client is not logged in, and executes the login.
         *
         * @param string $method 'POST', 'GET', etc.
         * @param string $uri The URI to which the request is to be sent.
         * @param mixed[] $options Guzzle Client options.
         *
         * @return \Psr\Http\Message\ResponseInterface
         *
         * @throws GuzzleException
         * @throws Exception
         */
        public function request($method, $uri, $options = [])
        {
            $resp = $this->client->request($method, "https://www.mytotalconnectcomfort.com" . $uri, $options);

            if (strpos($resp->getBody(), "Forgot Password?") !== false) {
                if ($this->login()) {
                    return $this->request($method, $uri, $options);
                }
            }

            return $resp;
        }


        /**
         * Gets the list of locations available in the current context.
         *
         * @param bool $reload When true, sends a request (or multiple requests) to the server.  Otherwise, simply
         * returns the list stored in the cache.
         *
         * @return Location[]
         *
         * @throws GuzzleException
         * @throws Exception
         */
        public function getLocations($reload = true)
        {
            if ($reload || count($this->locations) === 0) {
                $r = $this->request('get', '/portal/Locations');
                $body = $r->getBody();

                preg_match_all('/data-id=\"([0-9]+)\"/', $body, $locIdMatches);
                preg_match_all('/<div class=\"location-name\">[\s]+([^<\n]+)[\s]+<\/div>/', $body, $locNameMatches);

                $locIdMatches = $locIdMatches[1];
                $locNameMatches = $locNameMatches[1];

                if (count($locIdMatches) !== count($locNameMatches)) {
                    throw new Exception("Could not parse locations.");
                }

                foreach ($locIdMatches as $i => $id) {
                    $this->locations[$id] = $this->getLocation($id, ['name' => $locNameMatches[$i]]);
                }

                if (count($locIdMatches) < 1) {
                    throw new Exception("No Locations Found.  Locations must be created using the web interface.");
                }

                $this->defaultLocationId = $locIdMatches[0];
            }

            return $this->locations;
        }


        /**
         * Gets a particular location within the user context.
         *
         * @param int|null $id The ID number of the location desired, or null for the default location.
         * @param mixed[] $dataFromCaller Data to be included in the Location gleaned from the caller.
         * @return Location
         */
        public function getLocation($id = null, $dataFromCaller = [])
        {
            if ($id === null) {
                $id = $this->defaultLocationId;
            }

            $id = intval($id);

            if (!isset($this->locations[$id])) {
                $this->locations[$id] = new Location($this, $id, $dataFromCaller);
            }

            return $this->locations[$id];
        }


        /**
         * Gets a particular zone within the user context.
         *
         * @param int $id The ID number of the zone desired.  Required.
         * @param mixed[] $dataFromCaller Data to be included in the Zone gleaned from the caller.
         * @return Zone
         */
        public function getZone($id, $dataFromCaller = [])
        {
            $id = intval($id);

            if (!isset($this->zones[$id])) {
                $this->zones[$id] = new Zone($this, $id, $dataFromCaller);
            } else {
                $this->zones[$id]->setMultiple($dataFromCaller);
            }

            return $this->zones[$id];
        }


        /**
         * Gets all of the zones in a single location.
         *
         * @param int|Location $location A Location object or the id of a location.  Uses first location if null.
         * @param bool $reload When true, the list is loaded fresh from the server.
         * @return Zone[]
         * @throws GuzzleException
         * @throws Exception
         */
        public function getZonesByLocation($location = null, $reload = true)
        {
            if ($location === null) {
                $location = $this->defaultLocationId;
            } elseif (is_object($location) && get_class($location) === Location::class) {
                $location = $location->getId();
            }

            $zil = [];

            if ($reload) {
                $zil = $this->loadZonesInLocation($location);
            } else {
                foreach ($this->zones as $id => $z) {
                    if ($z->getLocationId() === $location) {
                        $zil[$id] = &$z;
                    }
                }
            }

            return $zil;
        }


        /**
         * The login function.  Wraps the client's request to https://www.mytotalconnectcomfort.com/portal/?timeout=True
         *
         * @param int $recurr Increments from 0 during recursion.
         * @return boolean Whether login was accepted.  Read the error with the $loginError property.
         *
         * @throws Exception For login failures
         * @throws GuzzleException
         */
        protected function login($recurr = 0)
        {
            /* Execute Login */
            $r = $this->client->request('POST', 'https://www.mytotalconnectcomfort.com/portal/', [
                'form_params' => [
                    'timeOffset' => 0, //240, // TODO use actual values, and adjust for DST
                    'UserName' => $this->email,
                    'Password' => $this->password,
                    'RememberMe' => 'false'
                ],
                'headers' => [
                    'Origin' => 'https://www.mytotalconnectcomfort.com/',
                    'Referer' => 'https://www.mytotalconnectcomfort.com/portal/',
                    'Host' => 'www.mytotalconnectcomfort.com',
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Upgrade-Insecure-Requests' => 1,
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko)' .
                        ' Chrome/74.0.3729.157 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;' .
                        'q=0.8,application/signed-exchange;v=b3',
                    'Accept-Encoding' => 'gzip, deflate, br',
                    'Cache-Control' => 'no-cache',
                    'DNT' => '1'
                ],
                'synchronous' => true,
                'on_stats' => function (\GuzzleHttp\TransferStats $stats) {
                    if (preg_match('/([0-9]+)/', $stats->getEffectiveUri(), $matches) === 1) {
                        $this->defaultLocationId = $matches[1];
                    } else {
                        $this->defaultLocationId = false;
                    }
                }
            ]);
            
            if (strpos($r->getBody(), "Login was unsuccessful.") > 0) {
                throw new Exception("Login failed.  System Error.");
            }

            if (strpos($r->getBody(), "Forgot Password?") !== false) {
                if ($recurr < 2) {
                    $this->login($recurr++);
                } else {
                    throw new Exception("Login failed.  Could not handle cookies.");
                }
            }

            return true;
        }


        /**
         * @param int $locationId
         * @return Zone[]
         * @throws GuzzleException
         * @throws Exception
         */
        protected function loadZonesInLocation($locationId)
        {
            $url = '/portal/' . $locationId . '/Zones';
            $resp = $this->request('GET', $url);
            $html = $resp->getBody();

            $zil = $this->addZonesFromHtml($html, 1, $locationId);

            $pagePattern = "/'pageNumber'><a href='(\/portal\/[0-9]+\/Zones\/page([0-9]+))'>/";

            if (preg_match_all($pagePattern, $resp->getBody(), $pageMatches, PREG_SET_ORDER) > 0) {
                foreach ($pageMatches as $page) {
                    $r = $this->request('GET', $page[1]);
                    $zil = array_merge($zil, $this->addZonesFromHtml($r->getBody(), $page[2], $locationId));
                }
            }

            return $zil;
        }

        /**
         * @param string $html
         * @param int $pageNumber
         * @param int $locationId
         * @return Zone[]
         */
        protected function addZonesFromHtml($html, $pageNumber, $locationId)
        {
            $zonePattern = "/data-id=\"([\d]+)\"[\s\S]+" .
                "<div class=\"location-name\">([^<]+)<[\s\S]+([\d\-]{1,3})&deg[\s\S]+([\d\-]{1,3})%" .
                "<\/div[\s\S]+\"alert\">([\s\S]+)<\/td>/mUX";

            preg_match_all($zonePattern, $html, $matches, PREG_SET_ORDER);

            $pageNumber = intval($pageNumber);
            $locationId = intval($locationId);

            $zones = [];

            foreach ($matches as $therm) {
                preg_match("/(\w{4,7})Icon\" style=\"\"/", $therm[0], $status);

                if ($status) {
                    $status = $status[1];
                } else {
                    $status = null;
                }

                $zones[] = $this->getZone($therm[1], [
                    'zoneId' => $therm[1],
                    'page' => $pageNumber,
                    'locationId' => $locationId,
                    'name' => $therm[2],
                    'runStatus' => $status, // TODO parse into int?
                    'dispTemperatureAvailable' => is_numeric($therm[3]),
                    'dispTemperature' => intval($therm[3]),
                    'indoorHumiditySensorAvailable' => is_numeric($therm[4]),
                    'indoorHumidity' => intval($therm[4]),
                    'errors' => $therm[5] // TODO parse into string[].
                ]);
            }

            return $zones;
        }
    }
}
