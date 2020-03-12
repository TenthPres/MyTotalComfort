<?php

namespace Tenth\MyTotalComfort\Tests;

use GuzzleHttp\Cookie\FileCookieJar;
use PHPUnit\Framework\TestCase;
use Tenth\MyTotalComfort;

class MyTotalComfortTests extends TestCase
{
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

    public function testInvalidEmailThrowsException()
    {
        $this->expectException(MyTotalComfort\Exception::class);
        new MyTotalComfort('invalid', 'invalid');
    }

    public function testInvalidCredentialThrowsException()
    {
        $this->expectException(MyTotalComfort\Exception::class);
        (new MyTotalComfort('invalid@tenth.org', 'badPassword'))->getLocations();
    }

    public function testConstructor()
    {
        $this->assertInstanceOf(
            MyTotalComfort::class,
            new MyTotalComfort($this->getEmail(), $this->getPassword())
        );
    }

    public function testLogin()
    {
        $session = new MyTotalComfort($this->getEmail(), $this->getPassword());
        // assertSame is being used here as assertIsArray
        $this->assertSame("array", gettype($session->getLocations()));
        return $session;
    }

    public function testLoginWithAltCookieJar()
    {
        $file = "tests/reports/cookiejar.json";
        $session = new MyTotalComfort($this->getEmail(), $this->getPassword(), new FileCookieJar($file));
        // assertSame is being used here as assertIsArray
        $this->assertSame("array", gettype($session->getLocations()));
        $this->assertFileExists($file);
    }

    /**
     * @depends test_login
     * @param MyTotalComfort $session
     * @return MyTotalComfort\Location
     */
    public function testGetLocation(MyTotalComfort $session)
    {
        $loc = $session->getLocation();
        $this->assertInstanceOf(MyTotalComfort\Location::class, $loc);
        return $loc;
    }


    /**
     * @depends test_login
     * @param MyTotalComfort $session
     * @return null
     * @throws MyTotalComfort\Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testGetZonesByLocationDefault(MyTotalComfort $session)
    {
        $this->assertContainsOnlyInstancesOf(MyTotalComfort\Zone::class, $session->getZonesByLocation());
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
    public function testGetZonesByLocationProvidedId(MyTotalComfort $session, MyTotalComfort\Location $location)
    {
        $this->assertContainsOnlyInstancesOf(
            MyTotalComfort\Zone::class,
            $session->getZonesByLocation($location->getId())
        );
    }


    /**
     * @depends test_login
     * @depends test_getLocation
     * @param MyTotalComfort $session
     * @param MyTotalComfort\Location $location
     * @throws MyTotalComfort\Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testGetZonesByLocationProvidedObj(MyTotalComfort $session, MyTotalComfort\Location $location)
    {
        // assertSame is being used here as assertInstanceOf
        $this->assertContainsOnlyInstancesOf(
            MyTotalComfort\Zone::class,
            $session->getZonesByLocation($location)
        );
    }

    /**
     * @depends test_login
     * @depends test_getLocation
     * @param MyTotalComfort $session
     * @param MyTotalComfort\Location $location
     * @throws MyTotalComfort\Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testGetZonesByLocationNoReload(MyTotalComfort $session, MyTotalComfort\Location $location)
    {
        $locs = $session->getZonesByLocation($location, false);
        $this->assertContainsOnlyInstancesOf(MyTotalComfort\Zone::class, $locs);
    }
}
