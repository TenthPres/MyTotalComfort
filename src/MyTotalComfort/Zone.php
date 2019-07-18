<?php


namespace Tenth\MyTotalComfort;

use Tenth\MyTotalComfort;


class Zone
{

    protected $context;
    protected $id;
    protected $page;
    protected $locationId;

    protected $name;

    protected $gatewayIsLost;
    protected $dispTempAvailable;
    protected $dispUnits;
    protected $dispTemp;
    protected $indoorHumiAvailable;
    protected $indoorHumi;
    protected $gatewayUpgrading;
    protected $alerts = [];
    protected $runStatus = 0;
    protected $fanStatus;


    public function getLocationId() {
        return $this->locationId;
    }


    public function __construct(MyTotalComfort $tccObject, $id, $data = []) {

        //$this->context = $tccObject;
        $this->id = $id;

        foreach ($data as $k => $v) {
            if (property_exists($this, $k) && $k !== 'id') {
                $this->$k = $v;
            }
        }
    }

}