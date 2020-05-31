<?php

namespace Tenth\MyTotalComfort\Tests;

use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Cookie\FileCookieJar;
use GuzzleHttp\Cookie\SetCookie;
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

    /**
     * @return MyTotalComfort
     */
    public function testConstructor()
    {
        $session = self::getSession();
        $this->assertInstanceOf(
            MyTotalComfort::class,
            $session
        );
        return $session;
    }

    /**
     * @depends testConstructor
     * @param MyTotalComfort $session
     * @return MyTotalComfort|null
     * @throws MyTotalComfort\Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testLogin(MyTotalComfort $session)
    {
        // assertSame is being used here as assertIsArray
        $this->assertSame("array", gettype($session->getLocations()));
        return $session;
    }

    /**
     * @throws MyTotalComfort\Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testLoginWithAltCookieJar()
    {
        $this->markTestSkipped("Skipping to reduce run count..."); // todo remove this line
        $jar = new CookieJar();

        try {
            $session = new MyTotalComfort(self::getEmail(), self::getPassword(), $jar);
            $session->getLocations(); // used to force login
        } catch (MyTotalComfort\Exception $e) {
            if ($e->getMessage() === "Too many login attempts.") {
                $this->fail($e->getMessage());
            } else {
                throw $e;
            }
        }
        $this->assertSame(SetCookie::class, get_class($jar->getCookieByName("ASP.NET_SessionId")));
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
