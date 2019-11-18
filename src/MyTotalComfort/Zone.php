<?php


namespace Tenth\MyTotalComfort;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Tenth\MyTotalComfort;


class Zone
{

    const WRITABLE_ATTRIBUTES = ['coolSetpoint', 'heatSetpoint', 'coolNextPeriod', 'heatNextPeriod', 'hold'];

    protected $_dirty = false;

    /** @var MyTotalComfort  */
    protected $context;

    protected $id;
    protected $page;
    protected $locationId;


    /* CONNECTIVITY AND IDENTIFICATION */
    /** @property-read string The name of the zone.  */
    protected $name;

    /** @property-read bool Whether the connection to the gateway has been lost.  */
    protected $gatewayIsLost;



    /* DISPLAY AND INDOOR FEATURES */
    /** @property-read bool Whether the indoor temperature is available. */
    protected $dispTemperatureAvailable;

    /** @property-read string The units used for temperature.  Values are "F" or "C"  */
    protected $displayUnits;

    /** @property-read int The indoor temperature.  */
    protected $dispTemperature;

    /** @property-read bool Whether an indoor humidity sensor is present and available. */
    protected $indoorHumiditySensorAvailable;

    /** @property-read bool Whether an indoor humidity sensor is working properly. */
    protected $indoorHumiditySensorNotFault;

    /** @property-read int Indoor relative humidity. */
    protected $indoorHumidity;

    /** @property-read int UNKNOWN */
    protected $indoorHumidStatus;

    /** @property-read int UNKNOWN */
    protected $equipmentOutputStatus;



    /* OUTDOOR */
    /** @property-read int UNKNOWN */
    protected $outdoorHumidStatus;

    /** @property-read int Outdoor relative humidity */
    protected $outdoorHumidity;

    /** @property-read bool Whether outdoor humidity information is available */
    protected $outdoorHumidityAvailable;

    /** @property-read int True if the outdoor humidity sensor is not in a fault state */
    protected $outdoorSensorNotFault;

    /** @property-read int UNKNOWN */
    protected $outdoorTempStatus;

    /** @property-read int Outdoor temperature */
    protected $outdoorTemperature;

    /** @property-read int Whether the outdoor temperature is available */
    protected $outdoorTemperatureAvailable;

    /** @property-read int True of the outdoor temperature sensor is not in a fault state  */
    protected $outdoorTemperatureSensorNotFault;



    /* COOLING */
    /** @property int Cooling Setpoint */
    protected $coolSetpoint;

    /** @property-read int Cool Lower Setpoint Limit. */
    protected $coolLowerSetptLimit;

    /** @property-read int Cool Upper Setpoint Limit.  It is not clear if this can be changed by the owner. */
    protected $coolUpperSetptLimit;

    /** @property-read int Cool setpoint, according to the current period in the schedule. */
    protected $scheduleCoolSp;

    /** @property-read int UNKNOWN */
    protected $statusCool;



    /* HEATING */
    /** @property int Heat Setpoint */
    protected $heatSetpoint;

    /** @property-read int Heat Lower Setpoint Limit. It is not clear if this can be changed by the owner. */
    protected $heatLowerSetptLimit;

    /** @property-read int Heat Upper Setpoint Limit. */
    protected $heatUpperSetptLimit;

    /** @property-read int Heat setpoint, according to the current period in the schedule. */
    protected $scheduleHeatSp;

    /** @property-read int UNKNOWN */
    protected $statusHeat;



    /* OTHER SETPOINTS */
    /** @property-read int UNKNOWN */
    protected $currentSetpointStatus;

    /** @property-read int The minimum difference between the heat and cool setpoints. */
    protected $deadband;

    /** @property-read bool UNKNOWN */
    protected $dualSetpointStatus;

    /** @property-read bool Whether the setpoint can be changed.  It is unclear if this is ever false. */
    protected $setpointChangeAllowed;



    /* HOLDS */
    /** @property int|null When the hold if there is one, should end for cooling.  Counted as 15-minute blocks from midnight.  e.g. 8:15am = 8 * 4 + 1 = 33  */
    protected $coolNextPeriod;

    /** @property int|null When the hold if there is one, should end for heating.  Counted as 15-minute blocks from midnight.  e.g. 8:15am = 8 * 4 + 1 = 33  */
    protected $heatNextPeriod;

    /** @property bool Whether a temporary hold is in place on the zone. */
    protected $hold = false;

    /** @property-read bool Whether the thermostat is capable of being set to hold until a particular time. */
    protected $holdUntilCapable = false;

    /** @property-read bool Whether the thermostat is in a vacation hold. */
    protected $isInVacationHoldMode;

    /** @property-read int UNKNOWN */
    protected $temporaryHoldUntilTime;

    /** @property-read int UNKNOWN */
    protected $vacationHold;

    /** @property-read bool UNKNOWN */
    protected $vacationHoldCancelable;

    /** @property-read int UNKNOWN */
    protected $vacationHoldUntilTime;



    /* APPLICATION */
    /** @property-read boolean Whether the application is commercial.  */
    protected $commercial;



    /* SCHEDULE */
    /** @property-read boolean Whether the thermostat is capable of running a schedule. */
    protected $scheduleCapable;



    /* SWITCHES AND MODES */
    /** @property-read bool Whether the system can be turned to Auto mode. */
    protected $switchAutoAllowed;

    /** @property-read bool Whether the system can be turned to Cool mode. */
    protected $switchCoolAllowed;

    /** @property-read bool Whether the system can be turned to Heat mode. */
    protected $switchHeatAllowed;

    /** @property-read bool Whether the system can be turned to Emergency Heat mode. (For Heat Pumps, mostly.) */
    protected $switchEmergencyHeatAllowed;

    /** @property-read bool Whether the system can be turned to Off mode.  It is not clear if this is ever false. */
    protected $switchOffAllowed;

    /** @property-read int Current position of the system switch.  Values not entirely known.  */
    protected $systemSwitchPosition;




    protected $alerts = [];
    protected $runStatus = 0;

    protected $fanStatus;

    /** @var array  */
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
    /* @noinspection PhpMissingParentConstructorInspection */
    public function __construct(MyTotalComfort $tccObject, $id, $data = []) {

        $this->context = $tccObject;
        $this->id = $id;

        $this->setMultiple($data);
    }


    public function setMultiple(array $dataArray) {
        foreach ($dataArray as $k => $v) {
            $k = strtolower($k[0]) . substr($k,1);

            if (property_exists($this, $k) && $k !== 'id') {
                $this->loadedValues[$k] = true;
                $this->$k = $v;
            }
        }
    }


    protected function validateDetailValues() {
        if (!$this->dispTemperatureAvailable) {
            $this->dispTemperature = false;
            $this->displayUnits = '';
        }

        if (!$this->outdoorTemperatureAvailable || !$this->outdoorTemperatureSensorNotFault) {
            $this->outdoorTemperature = false;
        }

        if (!$this->indoorHumiditySensorAvailable || !$this->indoorHumiditySensorNotFault) {
            $this->indoorHumidity = false;
        }

        if (!$this->switchCoolAllowed) {
            $this->coolSetpoint = false;
        }

        if (!$this->switchHeatAllowed) {
            $this->heatSetpoint = false;
        }

    }

    /**
     * Getter.
     *
     * @param string $what The parameter to get
     * @return mixed
     * @throws Exception
     * @throws GuzzleException
     */
    public function __get($what) {
        if (!property_exists($this, $what))
            throw new Exception("No such thing as $what");

        if ($what === 'id')
            return $this->id;

        if (!isset($this->loadedValues[$what])) {
//            echo "Loading details for " . $what;

            $this->loadDetails();

        }

        return $this->$what;



    }


    public function submitChanges() {

        if (!$this->_dirty)
            return true;

        try {
            $r = $this->context->request('POST', '/portal/Device/SubmitControlScreenChanges', [
                \GuzzleHttp\RequestOptions::JSON => [
                    'CoolNextPeriod' => $this->coolNextPeriod,
                    'CoolSetpoint' => ($this->hold ? $this->coolSetpoint : null),
                    'DeviceID' => $this->id,
                    'FanMode' => null,
                    'HeatNextPeriod' => $this->heatNextPeriod,
                    'HeatSetpoint' => ($this->hold ? $this->heatSetpoint : null),
                    'StatusCool' => (int)$this->hold,
                    'StatusHeat' => (int)$this->hold,
                    'SystemSwitch' => null
                ],
                'headers' => [
                    'Origin' => 'https://www.mytotalconnectcomfort.com/',
                    'Referer' => 'https://www.mytotalconnectcomfort.com/portal/Device/Control/' . $this->id,
                    'Host' => 'www.mytotalconnectcomfort.com',
                    'Content-Type' => 'application/json; charset=UTF-8',
                    'Upgrade-Insecure-Requests' => 1,
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.157 Safari/537.36',
                    'Accept' => 'application/json, text/javascript, */*; q=0.01',
                    'Accept-Encoding' => 'gzip, deflate, br',
                    'Cache-Control' => 'no-cache',
                    'DNT' => '1',
                    'X-Requested-With' => 'XMLHttpRequest'
                ]
            ]);


            if ($r->getBody()->__toString() === "{\"success\":1}") {
                $this->_dirty = false;
                return true;
            }

        } catch (GuzzleException $e) {

        }
        return false;
    }


    public function __destruct() {
        $this->submitChanges();
    }


    /**
     * @param $what
     * @param $value
     * @throws Exception
     * @throws GuzzleException
     */
    public function __set($what, $value) {

//        echo "setting " . $what;

        if (!in_array($what, self::WRITABLE_ATTRIBUTES))
            return;

        if ($this->__get($what) == $value) {
//            echo "<br />{$what} not changed.<br />";
            return;
        }

        $this->_dirty = true;
        $this->$what = $value;

        if ($what === "heatSetpoint" || $what === "coolSetpoint") {
            $this->hold = true;
        }
    }


    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function loadDetails() { // TODO make protected, probably.
        $r = $this->context->request("get", "/portal/Device/CheckDataSession/" . $this->id, [
            'headers' => [
                'X-Requested-With' => 'XMLHttpRequest'
            ]
        ]);

        $data = json_decode($r->getBody());

//        var_dump($data);

        if (!$data->success)
            return false;

        $this->setMultiple((array)$data->latestData->uiData);
        $this->setMultiple((array)$data->latestData->fanData);
        $this->setMultiple([
            'hasFan' => $data->latestData->hasFan,
            'canControlHumidification' => $data->latestData->canControlHumidification
        ]);

        // TODO parse alerts.

        $this->validateDetailValues();

        return true;

    }

}