<?php

namespace Tenth\MyTotalComfort\Tests;

use GuzzleHttp\Cookie\FileCookieJar;
use PHPUnit\Framework\TestCase;
use Tenth\MyTotalComfort;

class MyTotalComfortTests extends TestCase
{
    protected static $email = null;
    protected static $password = null;
    protected static $session = null;

    public static function getEmail()
    {
        if (self::$email === null) {
            self::loadCredentials();
        }
        return self::$email;
    }

    public static function getPassword()
    {
        if (self::$password === null) {
            self::loadCredentials();
        }
        return self::$password;
    }

    public static function getSession()
    {
        if (self::$session === null) {
            self::$session = new MyTotalComfort(self::getEmail(), self::getPassword());
        }
        return self::$session;
    }

    private static function loadCredentials()
    {
        if (file_exists('tests/credentials.json')) {
            $creds = json_decode(file_get_contents('tests/credentials.json'));
            self::$email = $creds->email;
            self::$password = $creds->password;
        } else {
            self::$email = getenv("TCC_EMAIL");
            self::$password = getenv("TCC_PASSWORD");
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
            new MyTotalComfort(self::getEmail(), self::getPassword())
        );
    }

    public function testLogin()
    {
        $session = self::getSession();
        // assertSame is being used here as assertIsArray
        $this->assertSame("array", gettype($session->getLocations()));
        return $session;
    }

    public function testLoginWithAltCookieJar()
    {
        $file = "tests/reports/cookiejar.json";
        $session = new MyTotalComfort(self::getEmail(), self::getPassword(), new FileCookieJar($file));
        // assertSame is being used here as assertIsArray
        $this->assertSame("array", gettype($session->getLocations()));
        $this->assertFileExists($file);
    }

    /**
     * @depends testLogin
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
     * @depends testLogin
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
     * @depends testLogin
     * @depends testGetLocation
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
     * @depends testLogin
     * @depends testGetLocation
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
     * @depends testLogin
     * @depends testGetLocation
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
