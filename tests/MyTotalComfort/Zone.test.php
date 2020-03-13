<?php

namespace Tenth\MyTotalComfort\Tests\MyTotalComfort;

use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\TestCase;
use Tenth\MyTotalComfort;
use Tenth\MyTotalComfort\Tests\MyTotalComfortTests;

class ZoneTests extends TestCase
{
    /** @var MyTotalComfort  */
    private $session = null;

    /** @var MyTotalComfort\Zone[] */
    private $zones;

    /**
     * @return MyTotalComfort\Zone
     * @throws MyTotalComfort\Exception
     * @throws GuzzleException
     */
    public function testGetZone()
    {
        if ($this->session === null) {
            $this->session = MyTotalComfortTests::getSession();
            $this->zones = $this->session->getLocation()->getZones();
        }
        $this->assertContainsOnlyInstancesOf(MyTotalComfort\Zone::class, $this->zones);
        return $this->zones[array_rand($this->zones)];
    }

    /**
     * @depends testGetZone
     * @param MyTotalComfort\Zone $zone
     */
    public function testDestructor(MyTotalComfort\Zone $zone)
    {
        $z = clone $zone;
        unset($z);
        $this->assertTrue(true);
    }

    /**
     * @depends testGetZone
     * @param MyTotalComfort\Zone $zone
     */
    public function testGetterId(MyTotalComfort\Zone $zone)
    {
        $this->assertTrue(is_numeric($zone->id));
    }

    /**
     * @depends testGetZone
     * @param MyTotalComfort\Zone $zone
     */
    public function testGetterInvalidItem(MyTotalComfort\Zone $zone)
    {
        $this->expectException(MyTotalComfort\Exception::class);
        $zone->nonsense;
    }


    /**
     * @depends testGetZone
     * @param MyTotalComfort\Zone $zone
     */
    public function testGetterProbablyAlreadyLoadedItem(MyTotalComfort\Zone $zone)
    {
        $this->assertTrue(is_numeric($zone->dispTemperature));
    }


    /**
     * @depends testGetZone
     * @param MyTotalComfort\Zone $zone
     */
    public function testGetterProbablyNotAlreadyLoadedItem(MyTotalComfort\Zone $zone)
    {
        $this->assertSame("integer", gettype($zone->systemSwitchPosition));
    }


}
