<?php

namespace Tenth\MyTotalComfort\Tests\MyTotalComfort;

use PHPUnit\Framework\TestCase;
use Tenth\MyTotalComfort;
use Tenth\MyTotalComfort\Tests\MyTotalComfortTests;

class LocationTests extends TestCase
{
    /** @var MyTotalComfort  */
    private $session = null;
    /** @var MyTotalComfort\Location */
    private $location;

    /**
     * @return MyTotalComfort\Location
     * @throws MyTotalComfort\Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testGetLocation()
    {
        if ($this->session === null) {
            $this->session = MyTotalComfortTests::getSession();
            $this->location = $this->session->getLocations();
            $this->location = array_pop($this->location);
        }
        $this->assertSame(MyTotalComfort\Location::class, get_class($this->location));
        return $this->location;
    }

    /**
     * @depends testGetLocation
     */
    public function testToString(MyTotalComfort\Location $loc)
    {
        $this->assertSame("string", gettype($loc->__toString()));
    }

    /**
     * @depends testGetLocation
     */
    public function testGetId(MyTotalComfort\Location $loc)
    {
        $this->assertSame("integer", gettype($loc->getId()));
    }

    /**
     * @depends testGetLocation
     */
    public function testGetZones(MyTotalComfort\Location $loc)
    {
        $this->assertContainsOnlyInstancesOf(MyTotalComfort\Zone::class, $loc->getZones());
    }
}
