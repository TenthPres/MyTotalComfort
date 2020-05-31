<?php

namespace Tenth\MyTotalComfort\Tests\MyTotalComfort;

use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\TestCase;
use Tenth\MyTotalComfort;
use Tenth\MyTotalComfort\Tests\MyTotalComfortTests;

class AlertTests extends TestCase
{

    private $zones = null;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        if ($this->zones === null) {
            $session = MyTotalComfortTests::getSession();
            $this->zones = $session->getLocation()->getZones();
        }

        parent::__construct($name, $data, $dataName);
    }


    /**
     * @param bool $mustBeAcknowledgable
     * @return MyTotalComfort\Alert
     */
    public function findAnAlert($mustBeAcknowledgable = false)
    {
        foreach ($this->zones as $z) {
            if ($z->hasAlerts) {
                foreach ($z->alerts as $a) {
                    if (!$mustBeAcknowledgable || $a->acknowledgable) {
                        return $a;
                    }
                }
            }
        }
        return null;
    }

    /**
     *
     */
    public function testAlertAcknowledgeSync()
    {
        $a = $this->findAnAlert(true);
        if ($a !== null) {
            $a->acknowledgeSync();
        } else {
            $this->markTestSkipped("Could not perform this test because no acknowledgable alerts were available.");
        }
        $this->assertSame(true, true); // the real test is whether there's an exception.
    }


    /**
     *
     */
    public function testAlertAcknowledgeAsync()
    {
        $a = $this->findAnAlert(true);
        if ($a !== null) {
            $a->acknowledge();
        } else {
            $this->markTestSkipped("Could not perform this test because no acknowledgable alerts were available.");
        }
        $this->assertSame(true, true); // the real test is whether there's an exception.
    }

    /**
     * Finds any alert for the purposes of testing.
     *
     * @return MyTotalComfort\Alert
     */
    public function testGetAnyAlert()
    {
        $a = $this->findAnAlert();
        if ($a === null) {
            $this->markTestSkipped("No alerts currently available for these tests.");
        }
        $this->assertSame(1, 1);
        return $a;
    }

    /**
     * @depends testGetAnyAlert
     * @param MyTotalComfort\Alert $a
     */
    public function testToString(MyTotalComfort\Alert $a)
    {
        $this->assertSame("string", gettype($a->__toString()));
    }

    /**
     * @depends testGetAnyAlert
     * @param MyTotalComfort\Alert $a
     */
    public function testGetterInvalidItem(MyTotalComfort\Alert $a)
    {
        $this->expectException(MyTotalComfort\Exception::class);
        $a->nonsense;
    }
}
