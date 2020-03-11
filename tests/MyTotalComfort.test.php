<?php

namespace Tenth\MyTotalComfort\Tests;

use PHPUnit\Framework\TestCase;
use Tenth\MyTotalComfort;

class MyTotalComfortTests extends TestCase {

    protected $email = null;
    protected $password = null;
    /** @var MyTotalComfort  */
    protected $activeSession = null;

    private function getEmail()
    {
        if ($this->email === null) {
            $this->loadCredentials();
        }
        return $this->email;
    }

    private function getPassword()
    {
        if ($this->password === null) {
            $this->loadCredentials();
        }
        return $this->password;
    }

    private function loadCredentials()
    {
        if (file_exists('tests/credentials.json')) {
            $creds = json_decode(file_get_contents('tests/credentials.json'));
            $this->email = $creds->email;
            $this->password = $creds->password;
        } else {
            $this->email = getenv("TCC_EMAIL");
            $this->password = getenv("TCC_PASSWORD");
        }
    }

    public function test_invalidEmailThrowsException()
    {
        $this->expectException(MyTotalComfort\Exception::class);
        new MyTotalComfort('invalid', 'invalid');
    }

    public function test_invalidCredentialThrowsException()
    {
        $this->expectException(MyTotalComfort\Exception::class);
        (new MyTotalComfort('invalid@tenth.org', 'badPassword'))->getLocations();
    }

    public function test_constructor() {
        $this->assertSame(MyTotalComfort::class,
            get_class(new MyTotalComfort($this->getEmail(), $this->getPassword())));
    }

    public function test_login() {
        $session = new MyTotalComfort($this->getEmail(), $this->getPassword());
        $this->assertSame("array", gettype($session->getLocations()));
        return $session;
    }

    /**
     * @depends test_login
     * @param MyTotalComfort $session
     * @return MyTotalComfort\Location
     */
    public function test_getLocation(MyTotalComfort $session)
    {
        $loc = $session->getLocation();
        $this->assertSame(MyTotalComfort\Location::class, get_class($loc));
        return $loc;
    }


    /**
     * @depends test_login
     * @param MyTotalComfort $session
     * @return null
     * @throws MyTotalComfort\Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function test_getZonesByLocationDefault(MyTotalComfort $session)
    {
        $this->assertSame(MyTotalComfort\Zone::class, get_class($session->getZonesByLocation()[0]));
        return null;
    }

    /**
     * @depends test_login
     * @depends test_getLocation
     * @param MyTotalComfort $session
     * @param MyTotalComfort\Location $location
     * @throws MyTotalComfort\Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function test_getZonesByLocationProvidedId(MyTotalComfort $session, MyTotalComfort\Location $location)
    {
        $locs = $session->getLocations();
        $this->assertSame(MyTotalComfort\Zone::class, get_class($session->getZonesByLocation($location->getId())[0]));
    }


    /**
     * @depends test_login
     * @depends test_getLocation
     * @param MyTotalComfort $session
     * @param MyTotalComfort\Location $location
     * @throws MyTotalComfort\Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function test_getZonesByLocationProvidedObj(MyTotalComfort $session, MyTotalComfort\Location $location)
    {
        $this->assertSame(MyTotalComfort\Zone::class, get_class($session->getZonesByLocation($location)[0]));
    }

    /**
     * @depends test_login
     * @depends test_getLocation
     * @param MyTotalComfort $session
     * @param MyTotalComfort\Location $location
     * @throws MyTotalComfort\Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function test_getZonesByLocationNoReload(MyTotalComfort $session, MyTotalComfort\Location $location)
    {
        $locs = $session->getZonesByLocation($location, false);
        $this->assertSame(MyTotalComfort\Zone::class, get_class(array_pop($locs)));
    }

}
