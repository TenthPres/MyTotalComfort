<?php

namespace Tenth\MyTotalComfort;


use Tenth\MyTotalComfort;

class Location
{
    protected $context;
    protected $id;

    protected $name;

    public function __construct(MyTotalComfort $tccObject, $id, $data = []) {

        $this->context = $tccObject;
        $this->id = $id;

        foreach ($data as $k => $v) {
            if (property_exists($this, $k) && $k !== 'id') {
                $this->$k = $v;
            }
        }
    }


    public function __toString() {
        return $this->name;
    }


    public function getId() {
        return $this->id;
    }


    public function getZones() {
        $this->context->getZonesByLocation($this->id);
    }
}