<?php


namespace Tenth\MyTotalComfort;

use Tenth\MyTotalComfort;


class Zone
{

    /** @var MyTotalComfort  */
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

    protected $loadedValues = [];


    public function getLocationId() {
        return $this->locationId;
    }


    /**
     * Zone constructor.
     *
     * @param MyTotalComfort $tccObject Provide the user context through which this information is gleaned.
     * @param int $id  The location ID number
     * @param array $data  Data to be inserted into the Location at construction.
     */
    /** @noinspection PhpMissingParentConstructorInspection */
    public function __construct(MyTotalComfort $tccObject, $id, $data = []) {

        $this->context = $tccObject;
        $this->id = $id;

        $this->setMultiple($data);
    }


    public function setMultiple(array $dataArray) {
        foreach ($dataArray as $k => $v) {
            $k = strtolower($k[0]) . substr($k,1);

//            if (property_exists($this, $k) && $k !== 'id') {  TODO expand properties and remove comments.
                $this->loadedValues[$k] = true;
                $this->$k = $v;
//            }
        }
    }

    /**
     * Getter.
     *
     * @param string $what The parameter to get
     * @return mixed
     * @throws Exception
     */
    public function __get($what) {
        if (property_exists($this, $what))
            return $this->$what;

        throw new Exception("No such thing");
    }


    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getDetails() { // TODO make protected, probably.
        $r = $this->context->request("get", "/portal/Device/CheckDataSession/" . $this->id, [
            'headers' => [
                'X-Requested-With' => 'XMLHttpRequest'
            ]
        ]);

        $data = json_decode($r->getBody());

        if (!$data->success)
            return false;

        $this->setMultiple((array)$data->latestData->uiData);
        $this->setMultiple((array)$data->latestData->fanData);
        $this->setMultiple([
            'hasFan' => $data->latestData->hasFan,
            'canControlHumidification' => $data->latestData->canControlHumidification
        ]);

        // TODO parse alerts.

        return true;

    }

}