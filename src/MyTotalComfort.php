<?php

namespace Tenth {

    use GuzzleHttp\Client;
    use GuzzleHttp\Cookie\SetCookie;
    use GuzzleHttp\Exception\GuzzleException;
    use GuzzleHttp\RequestOptions;
    use Tenth\MyTotalComfort\Zone;
    use Tenth\MyTotalComfort\Location;

    /**
     * Class MyTotalComfort
     *
     * The Scope of one object of this class is defined as the bounds of what can be seen by a single user using the web
     * interface.
     *
     * @package Tenth
     */
    class MyTotalComfort {
        protected $cookieJar = null;
        protected $client = null;

        protected $defaultLocationId = null;

        protected $locations = [];
        protected $zones = [];

        protected $cache;

        private $email;
        private $password;


        /**
         * MyTotalComfort constructor.  Pass login arguments.
         * @param string $email Login email address.
         * @param string $password Login password.
         * @param \GuzzleHttp\Cookie\CookieJarInterface $cookieJar Optional. Cookie jar to be used if a specific one is
         * desired.  Useful for allowing logins to persist between script runs.
         * @param array|object $cache Entity used for caching system state and configuration info.  Can help reduce server calls.
         *
         */
        public function __construct($email, $password, $cookieJar = null, &$cache = [])
        {

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo "A valid email address was not provided for login."; // TODO change to exception
                return;
            }

            if ($cookieJar === null)
                $this->cookieJar = new \GuzzleHttp\Cookie\CookieJar();
            else
                $this->cookieJar = $cookieJar;

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
         * @param array $options Guzzle Client options.
         *
         * @return mixed|\Psr\Http\Message\ResponseInterface
         *
         * @throws \GuzzleHttp\Exception\GuzzleException
         */
        protected function request($method, $uri, $options = []) {
            $options['synchronous'] = true;

            $resp = $this->client->request($method, $uri, $options);

            if (strpos($resp->getBody(), "Login Form") !== false) {
                $this->login();
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
         * @throws \GuzzleHttp\Exception\GuzzleException
         */
        public function getLocations($reload = true) {

            if ($reload) {
                $r = $this->request('get', 'https://www.mytotalconnectcomfort.com/portal/Locations');
                $body = $r->getBody();

                preg_match_all('/data-id=\"([0-9]+)\"/', $body, $locIdMatches);
                preg_match_all('/<div class=\"location-name\">[\s]+([^<\n]+)[\s]+<\/div>/', $body, $locNameMatches);

                $locIdMatches = $locIdMatches[1];
                $locNameMatches = $locNameMatches[1];

                if (count($locIdMatches) !== count($locNameMatches))
                    throw new \Exception("Could not parse locations.");

                foreach ($locIdMatches as $i => $id) {
                    $this->locations[$id] = $this->getLocation($id, ['name' => $locNameMatches[$i]]);
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
        public function getLocation($id = null, $dataFromCaller = []) {
            if ($id === null)
                $id = $this->defaultLocationId;

            $id = intval($id);

            if (!isset($this->locations[$id])) {
                $this->locations[$id] = new Location($this, $id, $dataFromCaller);
            }

            return $this->locations[$id];
        }


        /**
         * Gets a particular zone within the user context.
         *
         * @param int|null $id The ID number of the zone desired.  Required.
         * @param mixed[] $dataFromCaller Data to be included in the Zone gleaned from the caller.
         * @return Zone
         */
        public function getZone($id, $dataFromCaller) {
            $id = intval($id);

            if (!isset($this->zones[$id])) {
                $this->zones[$id] = new Zone($this, $id, $dataFromCaller);
            }

            return $this->zones[$id];
        }


        /**
         * Gets all of the zones in a single location.
         *
         * @param int|Location $location A Location object or the id of a location.
         * @param bool $reload When true, the list is loaded fresh from the server.
         * @return Zone[]
         * @throws GuzzleException
         */
        public function getZonesByLocation($location = null, $reload = true)
        {
            if ($location === null) {
                $location = $this->defaultLocationId;
            } elseif (get_class($location) === "Location") {
                $location = $location->getId();
            }

            $zil = [];

            if ($reload) {
                $zil = $this->loadZonesInLocation($location);
            } else {
                foreach ($this->zones as $id => $z) {
                    if ($z->getLocationId() === $location)
                        $zil[$id] = &$z;
                }
            }

            return $zil;

        }


        /**
         * The login function.  Essentially wraps the web client's request to https://www.mytotalconnectcomfort.com/portal/?timeout=True
         *
         * @return boolean Whether login was accepted.  Read the error with the $loginError property.
         *
         * @throws \GuzzleHttp\Exception\GuzzleException
         */
        protected function login() {
            /* Execute Login */
            $this->client->request('POST', 'https://www.mytotalconnectcomfort.com/portal/', [
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
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.157 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3',
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

            return true;

        }


        /**
         * @param $locationId
         * @return array
         * @throws GuzzleException
         */
        protected function loadZonesInLocation($locationId) {

            if ($locationId !== null) {
                $url = 'https://www.mytotalconnectcomfort.com/portal/' . $locationId . '/Zones';
                $opts = [];
            } else {
                $url = 'https://www.mytotalconnectcomfort.com/portal/';
                $opts = [
                    'on_stats' => function (\GuzzleHttp\TransferStats $stats) {
                        if (preg_match('/([0-9]+)/', $stats->getEffectiveUri(), $matches) === 1) {
                            $this->defaultLocationId = $matches[1];
                        } else {
                            $this->defaultLocationId = false;
                        }
                    }
                ];
            }

            $resp = $this->request('GET', $url, $opts);
            $zil = $this->addZonesFromHtml($resp->getBody(), 1, $locationId);

            if (preg_match_all("/'pageNumber'><a href='(\/portal\/[0-9]+\/Zones\/page([0-9]+))'>/", $resp->getBody(), $pageMatches, PREG_SET_ORDER) > 0) {
                foreach ($pageMatches as $page) {
                    $r = $this->request('GET', 'https://www.mytotalconnectcomfort.com' . $page[1]);
                    $zil = array_merge($zil, $this->addZonesFromHtml($r->getBody(), $page[2], $locationId));
                }
            }

            return $zil;

        }

        protected function addZonesFromHtml($html, $pageNumber, $locationId) {
            preg_match_all("/data-id=\"([\d]+)\"[\s\S\R]+<div class=\"location-name\">([^<]+)<[\s\S\R]+([\d\-]{1,3})&deg[\s\S\R]+([\d\-]{1,3})%<\/div[\s\S\R]+\"alert\">([\s\S\R]+)<\/td>/mU", $html, $matches, PREG_SET_ORDER);

            if ($locationId === null)
                $locationId = $this->defaultLocationId;

            $pageNumber = intval($pageNumber);
            $locationId = intval($locationId);

            $zones = [];

            foreach ($matches as $therm) {

                preg_match("/(\w{4,7})Icon\" style=\"\"/", $therm[0],$status);

                if ($status)
                    $status = $status[1];
                else
                    $status = null;

                $zones[] = $this->getZone($therm[1],[
                    'zoneId' => $therm[1],
                    'page' => $pageNumber,
                    'location' => $locationId,
                    'name' => $therm[2],
                    'runStatus' => $status, // TODO parse into int?
                    'dispTemp' => intval($therm[3]),
                    'indoorHumi' => intval($therm[4]),
                    'errors' => $therm[5] // TODO parse into string[].
                ]);
            }

            return $zones;
        }


    }



}
