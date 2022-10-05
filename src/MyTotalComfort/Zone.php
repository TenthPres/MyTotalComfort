<?php

namespace Tenth\MyTotalComfort;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Tenth\MyTotalComfort;

/**
 * Class Zone Represents a single Zone, which generally means a single thermostat.
 *
 * @package Tenth\MyTotalComfort
 *
 * @property-read int $id The id number of the zone
 * @property-read string $name The name of the zone.
 * @property-read bool $gatewayIsLost Whether the connection to the gateway has been lost.
 * @property-read bool $dispTemperatureAvailable Whether the indoor temperature is available.
 * @property-read string $displayUnits The units used for temperature.  Values are "F" or "C"
 * @property-read int $dispTemperature The indoor temperature.
 * @property-read bool $indoorHumiditySensorAvailable Whether an indoor humidity sensor is present and available.
 * @property-read bool $indoorHumiditySensorNotFault Whether an indoor humidity sensor is working properly.
 * @property-read int $indoorHumidity Indoor relative humidity.
 * @property-read int $indoorHumidStatus UNKNOWN
 * @property-read int $equipmentOutputStatus Heat: 1, Cool: 2.  Other values unknown. 
 * @property-read int $outdoorHumidStatus UNKNOWN
 * @property-read int $outdoorHumidity Outdoor relative humidity
 * @property-read bool $outdoorHumidityAvailable Whether outdoor humidity information is available
 * @property-read int $outdoorSensorNotFault True if the outdoor humidity sensor is not in a fault state
 * @property-read int $outdoorTempStatus UNKNOWN
 * @property-read int $outdoorTemperature Outdoor temperature
 * @property-read int $outdoorTemperatureAvailable Whether the outdoor temperature is available
 * @property-read int $outdoorTemperatureSensorNotFault True of the outdoor temperature sensor is not in a fault state
 * @property int $coolSetpoint Cooling Setpoint
 * @property-read int $coolLowerSetptLimit Cool Lower Setpoint Limit.
 * @property-read int $coolUpperSetptLimit Cool Upper Setpoint Limit.  It is not clear if this can be changed.
 * @property-read int $scheduleCoolSp Cool setpoint, according to the current period in the schedule.
 * @property-read int $statusCool UNKNOWN
 * @property int $heatSetpoint Heat Setpoint
 * @property-read int $heatLowerSetptLimit Heat Lower Setpoint Limit. It is not clear if this can be changed.
 * @property-read int $heatUpperSetptLimit Heat Upper Setpoint Limit.
 * @property-read int $scheduleHeatSp Heat setpoint, according to the current period in the schedule.
 * @property-read int $statusHeat UNKNOWN
 * @property-read int $currentSetpointStatus Scheduled: 0, Temporary: 1, Hold: 2, Vacation Hold: 3
 * @property-read int $deadband The minimum difference between the heat and cool setpoints.
 * @property-read bool $dualSetpointStatus UNKNOWN
 * @property-read bool $setpointChangeAllowed Whether the setpoint can be changed.  It is unclear if this is ever false.
 * @property int|null $coolNextPeriod When the hold if there is one, should end for cooling.  Counted as 15-minute
 *                  blocks from midnight.  e.g. 8:15am = 8 * 4 + 1 = 33
 * @property int|null $heatNextPeriod When the hold if there is one, should end for heating.  Counted as 15-minute
 *                  blocks from midnight.  e.g. 8:15am = 8 * 4 + 1 = 33
 * @property bool $hold Whether a temporary hold is in place on the zone.
 * @property-read bool $holdUntilCapable Whether the thermostat is capable of being set to hold until a particular time.
 * @property-read bool $isInVacationHoldMode Whether the thermostat is in a vacation hold.
 * @property-read int $temporaryHoldUntilTime UNKNOWN
 * @property-read int $vacationHold UNKNOWN
 * @property-read bool $vacationHoldCancelable UNKNOWN
 * @property-read int $vacationHoldUntilTime UNKNOWN
 * @property-read boolean $commercial Whether the application is commercial.
 * @property-read boolean $scheduleCapable Whether the thermostat is capable of running a schedule.
 * @property-read bool $switchAutoAllowed Whether the system can be turned to Auto mode.
 * @property-read bool $switchCoolAllowed Whether the system can be turned to Cool mode.
 * @property-read bool $switchHeatAllowed Whether the system can be turned to Heat mode.
 * @property-read bool $switchEmergencyHeatAllowed Whether the system can be turned to Emergency Heat mode. (Heat Pumps)
 * @property-read bool $switchOffAllowed Whether the system can be turned to Off mode.  Possibly always true.
 * @property-read int $systemSwitchPosition Current position of the system switch.  Values not entirely known.
 * @property-read int $fanStatus Auto: 0, On: 1, Circulate: 2, FollowSchedule: 3, Unknown: 4
 * @property-read bool $fanIsRunning self-expanatory
 * @property-read Alert[] $alerts An array of alert objects that may be present in the Zone.
 * @property-read bool $hasAlerts Whether there are active alerts
 */
class Zone
{

    /** @var string[] The names of Zone properties which are writable.  */
    const WRITABLE_ATTRIBUTES = ['coolSetpoint', 'heatSetpoint', 'coolNextPeriod', 'heatNextPeriod', 'hold'];

    /** @var bool $isDirty When true, indicates that there are changes to be saved back to the server. */
    protected $isDirty = false;

    /** @var MyTotalComfort */
    public $context;

    /** @var int */
    protected $id;

    /** @var int */
    protected $locationId;



    /* CONNECTIVITY AND IDENTIFICATION */
    /** @var string The name of the zone.  */
    protected $name;

    /** @var bool Whether the connection to the gateway has been lost.  */
    protected $gatewayIsLost;



    /* DISPLAY AND INDOOR FEATURES */
    /** @var bool Whether the indoor temperature is available. */
    protected $dispTemperatureAvailable;

    /** @var string The units used for temperature.  Values are "F" or "C"  */
    protected $displayUnits;

    /** @var int The indoor temperature.  */
    protected $dispTemperature;

    /** @var bool Whether an indoor humidity sensor is present and available. */
    protected $indoorHumiditySensorAvailable;

    /** @var bool Whether an indoor humidity sensor is working properly. */
    protected $indoorHumiditySensorNotFault;

    /** @var int Indoor relative humidity. */
    protected $indoorHumidity;

    /** @var int UNKNOWN */
    protected $indoorHumidStatus;

    /** @var int Heat: 1, Cool: 2.  Other values unknown.  */
    protected $equipmentOutputStatus;



    /* OUTDOOR */
    /** @var int UNKNOWN */
    protected $outdoorHumidStatus;

    /** @var int Outdoor relative humidity */
    protected $outdoorHumidity;

    /** @var bool Whether outdoor humidity information is available */
    protected $outdoorHumidityAvailable;

    /** @var int True if the outdoor humidity sensor is not in a fault state */
    protected $outdoorSensorNotFault;

    /** @var int UNKNOWN */
    protected $outdoorTempStatus;

    /** @var int Outdoor temperature */
    protected $outdoorTemperature;

    /** @var int Whether the outdoor temperature is available */
    protected $outdoorTemperatureAvailable;

    /** @var int True of the outdoor temperature sensor is not in a fault state  */
    protected $outdoorTemperatureSensorNotFault;



    /* COOLING */
    /** @var int Cooling Setpoint */
    protected $coolSetpoint;

    /** @var int Cool Lower Setpoint Limit. */
    protected $coolLowerSetptLimit;

    /** @var int Cool Upper Setpoint Limit.  It is not clear if this can be changed by the owner. */
    protected $coolUpperSetptLimit;

    /** @var int Cool setpoint, according to the current period in the schedule. */
    protected $scheduleCoolSp;

    /** @var int UNKNOWN */
    protected $statusCool;



    /* HEATING */
    /** @var int Heat Setpoint */
    protected $heatSetpoint;

    /** @var int Heat Lower Setpoint Limit. It is not clear if this can be changed by the owner. */
    protected $heatLowerSetptLimit;

    /** @var int Heat Upper Setpoint Limit. */
    protected $heatUpperSetptLimit;

    /** @var int Heat setpoint, according to the current period in the schedule. */
    protected $scheduleHeatSp;

    /** @var int UNKNOWN */
    protected $statusHeat;



    /* OTHER SETPOINTS */
    /** @var int UNKNOWN */
    protected $currentSetpointStatus;

    /** @var int The minimum difference between the heat and cool setpoints. */
    protected $deadband;

    /** @var bool UNKNOWN */
    protected $dualSetpointStatus;

    /** @var bool Whether the setpoint can be changed.  It is unclear if this is ever false. */
    protected $setpointChangeAllowed;



    /* HOLDS */
    /** @var int|null When the hold if there is one, should end for cooling.  Counted as 15-minute blocks from midnight.  e.g. 8:15am = 8 * 4 + 1 = 33  */
    protected $coolNextPeriod;

    /** @var int|null When the hold if there is one, should end for heating.  Counted as 15-minute blocks from midnight.  e.g. 8:15am = 8 * 4 + 1 = 33  */
    protected $heatNextPeriod;

    /** @var bool Whether a temporary hold is in place on the zone. */
    protected $hold = false;

    /** @var bool Whether the thermostat is capable of being set to hold until a particular time. */
    protected $holdUntilCapable = false;

    /** @var bool Whether the thermostat is in a vacation hold. */
    protected $isInVacationHoldMode;

    /** @var int UNKNOWN */
    protected $temporaryHoldUntilTime;

    /** @var int UNKNOWN */
    protected $vacationHold;

    /** @var bool UNKNOWN */
    protected $vacationHoldCancelable;

    /** @var int UNKNOWN */
    protected $vacationHoldUntilTime;



    /* APPLICATION */
    /** @var boolean Whether the application is commercial.  */
    protected $commercial;



    /* SCHEDULE */
    /** @var boolean Whether the thermostat is capable of running a schedule. */
    protected $scheduleCapable;



    /* SWITCHES AND MODES */
    /** @var bool Whether the system can be turned to Auto mode. */
    protected $switchAutoAllowed;

    /** @var bool Whether the system can be turned to Cool mode. */
    protected $switchCoolAllowed;

    /** @var bool Whether the system can be turned to Heat mode. */
    protected $switchHeatAllowed;

    /** @var bool Whether the system can be turned to Emergency Heat mode. (For Heat Pumps, mostly.) */
    protected $switchEmergencyHeatAllowed;

    /** @var bool Whether the system can be turned to Off mode.  It is not clear if this is ever false. */
    protected $switchOffAllowed;

    /** @var int Current position of the system switch.  Values not known.  */
    protected $systemSwitchPosition;



    /* ALERTS */
    /** @var Alert[] */
    protected $alerts = [];

    /** @var bool Whether the zone has current alerts. */
    protected $hasAlerts = false;


    /* STATUS */
    /** @var int Current system running mode */
    protected $runStatus = 0;

    /** @var int Current fan running mode  Auto: 0, On: 1, Circulate: 2, FollowSchedule: 3, Unknown: 4 */
    protected $fanStatus;
    
    /** @var bool fan is running */
    protected $fanIsRunning;


    /* CACHE CONTROL */
    /** @var string[]  */
    protected $loadedValues = [];


    /**
     * Gets the Location ID that contains the Zone.
     *
     * @return int Location ID
     */
    public function getLocationId()
    {
        return $this->locationId;
    }

    /**
     * Zone constructor.
     *
     * @param MyTotalComfort $tccObject Provide the user context through which this information is gleaned.
     * @param int $id  The location ID number
     * @param array $data  Data to be inserted into the Location at construction.
     * @return void
     */
    public function __construct(MyTotalComfort $tccObject, $id, $data = [])
    {
        $this->context = $tccObject;
        $this->id = $id;

        $this->setMultiple($data);
    }

    /**
     * Intended for pulling in data from other classes in this package.
     *
     * @param mixed[] $dataArray
     */
    public function setMultiple(array $dataArray)
    {
        foreach ($dataArray as $k => $v) {
            $k = strtolower($k[0]) . substr($k, 1);

            if ($k === "hasAlerts") {
                $this->hasAlerts = !!$v;
                $this->loadedValues['hasAlerts'] = true;
            } elseif ($k === "alerts") {
                $this->alerts = Alert::fromJsonString($v, $this);
                $this->hasAlerts = count($this->alerts) > 0;
                $this->loadedValues['hasAlerts'] = true;
                $this->loadedValues['alerts'] = true;
            } elseif (property_exists($this, $k) && $k !== 'id') {
                $this->loadedValues[$k] = true;
                $this->$k = $v;
            }
        }
    }

    /**
     * Cleans up data to keep API results consistent and logical.
     *
     * @return void
     */
    protected function validateDetailValues()
    {
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
     * Getter.  For the many, many readable parameters. The purpose of this function is to avoid API calls until needed.
     *
     * @param string $what The parameter to get
     * @return mixed The requested parameter
     * @throws Exception
     * @throws GuzzleException
     */
    public function __get($what)
    {
        if (!property_exists($this, $what)) {
            throw new Exception("No such thing as $what");
        }

        if ($what === 'id') {
            return $this->id;
        }

        if (!isset($this->loadedValues[$what])) {
            $this->loadDetails();
        }

        return $this->$what;
    }

    /**
     * Submits any changes that may be necessary.
     *
     * @return bool True on success, false on failure.  Will return true if there are no changes to submit.
     * @throws Exception
     */
    public function submitChanges()
    {

        if (!$this->isDirty) {
            return true;
        }

        try {
            $r = $this->context->request('POST', '/portal/Device/SubmitControlScreenChanges', [
                RequestOptions::JSON => [
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
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko)' .
                        ' Chrome/74.0.3729.157 Safari/537.36',
                    'Accept' => 'application/json, text/javascript, */*; q=0.01',
                    'Accept-Encoding' => 'gzip, deflate, br',
                    'Cache-Control' => 'no-cache',
                    'DNT' => '1',
                    'X-Requested-With' => 'XMLHttpRequest'
                ]
            ]);


            if ($r->getBody()->__toString() === "{\"success\":1}") {
                $this->isDirty = false;
                return true;
            }
        } catch (GuzzleException $e) {
        }
        return false;
    }

    /**
     *
     * Submits any pending changes before the object is destroyed.
     *
     * @return void
     *
     * @throws Exception
     */
    public function __destruct()
    {
        $this->submitChanges();
    }

    /**
     * Setter for the writable properties.
     *
     * @param string $what The parameter to set
     * @param mixed $value The value to set the parameter to
     * @throws Exception
     * @throws GuzzleException
     * @return void
     */
    public function __set($what, $value)
    {
        if (!in_array($what, self::WRITABLE_ATTRIBUTES)) {
            return;
        }

        if ($this->__get($what) == $value) {
            return;
        }

        $this->isDirty = true;
        $this->$what = $value;

        if ($what === "heatSetpoint" || $what === "coolSetpoint") {
            $this->hold = true;
        }
    }

    /**
     * Meant to be an internal function, this method loads detailed info from TCC.
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws Exception
     * @internal
     */
    protected function loadDetails()
    {
        $r = $this->context->request("get", "/portal/Device/CheckDataSession/" . $this->id, [
            'headers' => [
                'X-Requested-With' => 'XMLHttpRequest'
            ]
        ]);

        $data = json_decode($r->getBody());

        if (!$data->success) {
            return false;
        }

        $this->setMultiple((array)$data->latestData->uiData);
        $this->setMultiple((array)$data->latestData->fanData);
        $this->setMultiple([
            'hasFan' => $data->latestData->hasFan,
            'canControlHumidification' => $data->latestData->canControlHumidification
        ]);
        $this->setMultiple(['alerts' => $data->alerts]);

        $this->validateDetailValues();

        return true;
    }
}

