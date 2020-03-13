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
    public function getZone()
    {
        if ($this->session === null) {
            $this->session = MyTotalComfortTests::getSession();
            $this->zones = $this->session->getLocation()->getZones();
        }
        return reset($this->zones);
    }

    /**
     *
     */
    public function testDestructor()
    {
        $z = clone $this->getZone();
        unset($z);
        $this->assertTrue(true);
    }

    /**
     * @throws MyTotalComfort\Exception
     * @throws GuzzleException
     */
    public function testGetterId()
    {
        $this->assertTrue(is_numeric($this->getZone()->id));
    }

    /**
     * @throws MyTotalComfort\Exception
     * @throws GuzzleException
     */
    public function testGetterInvalidItem()
    {
        $this->expectException(MyTotalComfort\Exception::class);
        $this->getZone()->nonsense;
    }


    /**
     * @throws MyTotalComfort\Exception
     * @throws GuzzleException
     */
    public function testGetterProbablyAlreadyLoadedItem()
    {
        $this->assertTrue(is_numeric($this->getZone()->dispTemperature));
    }


    /**
     * @throws MyTotalComfort\Exception
     * @throws GuzzleException
     */
    public function testGetterProbablyNotAlreadyLoadedItem()
    {
        $this->assertSame("integer", gettype($this->getZone()->systemSwitchPosition));
    }


}
