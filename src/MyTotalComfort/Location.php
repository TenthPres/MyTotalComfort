<?php

namespace Tenth\MyTotalComfort;


use Tenth\MyTotalComfort;


/**
 * Class Location
 *
 * Locations appear through the Total Comfort Control interface at https://mytotalcomfortcontrol.com/portal/Locations
 *
 * These typically refer to physical addresses, but could be used to distinguish between buildings in larger campuses.
 * Locations generally have distinct street addresses.
 *
 * @package Tenth\MyTotalComfort
 */
class Location
{
    protected $context;
    protected $id;

    protected $name;


    /**
     * Location constructor.
     *
     * @param MyTotalComfort $tccObject Provide the user context through which this information is gleaned.
     * @param int $id  The location ID number
     * @param array $data  Data to be inserted into the Location at construction.
     */
    public function __construct(MyTotalComfort $tccObject, $id, $data = []) {

        $this->context = $tccObject;
        $this->id = $id;

        foreach ($data as $k => $v) {
            if (property_exists($this, $k) && $k !== 'id') {
                $this->$k = $v;
            }
        }
    }


    /**
     * Gets the name of the location.
     *
     * @return string The name of the location.
     */
    public function __toString() {
        return $this->name;
    }


    /**
     * Gets the ID number of the location.
     *
     * @return int The ID of the Location
     */
    public function getId() {
        return $this->id;
    }


    /**
     * Get the Zones in this Location.
     *
     * @return Zone[] THe Zones contained in the Location.
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws Exception
     */
    public function getZones() {
        return $this->context->getZonesByLocation($this->id);
    }
}