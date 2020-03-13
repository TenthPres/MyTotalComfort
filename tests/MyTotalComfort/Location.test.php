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
    public function getLocation()
    {
        if ($this->session === null) {
            $this->session = MyTotalComfortTests::getSession();
            $this->location = $this->session->getLocations();
            $this->location = array_pop($this->location);
        }
        return $this->location;
    }

    /**
     * @throws MyTotalComfort\Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testToString()
    {
        $this->assertSame("string", gettype($this->getLocation()->__toString()));
    }

    /**
     * @throws MyTotalComfort\Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testGetId()
    {
        $this->assertSame("integer", gettype($this->getLocation()->getId()));
    }

    /**
     * @throws MyTotalComfort\Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testGetZones()
    {
        $this->assertContainsOnlyInstancesOf(MyTotalComfort\Zone::class, $this->getLocation()->getZones());
    }
}
