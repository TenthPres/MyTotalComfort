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
     * ZoneTests constructor.  Loads up a session to be used by other tests.
     * @param null $name
     * @param array $data
     * @param string $dataName
     * @throws GuzzleException
     * @throws MyTotalComfort\Exception
     */
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        if ($this->session === null) {
            $this->session = MyTotalComfortTests::getSession();
            $this->zones = $this->session->getLocation()->getZones();
        }

        parent::__construct($name, $data, $dataName);
    }

    /**
     * @return MyTotalComfort\Zone
     * @throws MyTotalComfort\Exception
     * @throws GuzzleException
     */
    public function testGetZone()
    {
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


    /**
     * @depends testGetZone
     * @param MyTotalComfort\Zone $zone
     * @throws MyTotalComfort\Exception
     */
    public function testWriting(MyTotalComfort\Zone $zone)
    {
        $zone->hold = !$zone->hold;
        $this->assertSame(true, $zone->submitChanges());
        sleep(15);
        $zone->hold = false;
    }

}
