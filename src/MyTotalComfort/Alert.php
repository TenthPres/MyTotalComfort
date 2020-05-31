<?php

namespace Tenth\MyTotalComfort;

use Tenth\MyTotalComfort;

/**
 * Class Alert
 * @package Tenth\MyTotalComfort
 *
 *
 */
class Alert
{
    protected $acknowledgable = false; // should be treated as immutable.
    protected $dateTime;
    protected $text;
    protected $id; // only present on acknowledgable alerts
    protected $zone;


    /**
     * @param string $jsonString
     * @param Zone $zone
     * @return Alert[]
     */
    public static function fromJsonString($jsonString, Zone $zone)
    {
        preg_match_all(
            '/<div class="alert-content">[\s\S]+<span>([\s\S]+(\d{1,2}\/\d{1,2}\/\d{4} ' .
                '\d{1,2}:\d{2}:\d{2} [AP]M)[\s\S]+)[\s\.]+<\/span>' .
                '[\s\S]+(?:AlertID" type="hidden" value="([\d]+)"[\s\S]+)?<div/Um',
            $jsonString,
            $matches,
            PREG_SET_ORDER
        );

        $alerts = [];
        foreach ($matches as $content) {
            $alerts[] = new Alert($content, $zone);
        }
        return $alerts;
    }

    public function __get($what)
    {
        if (!property_exists($this, $what)) {
            throw new Exception("No such thing as $what");
        }

        return $this->$what;
    }

    /**
     * Alert constructor.
     * @param string[] $attributes Matches from the JSON status response
     * @param Zone $zone The zone to which the alert belongs.
     */
    protected function __construct($attributes, Zone $zone)
    {
        $this->text = $attributes[1];
        $this->dateTime = \DateTime::createFromFormat("n/j/Y g:i:s A", $attributes[2], MyTotalComfort::$tzUTC);
        $this->zone = $zone;
        if (isset($attributes[3]) && is_numeric($attributes[3])) {
            $this->id = $attributes[3];
            $this->acknowledgable = true;
        }
    }

    public function __toString()
    {
        return $this->text;
    }

    /**
     * Acknowledges and dismisses an alert, if the alert is able to be acknowledged and dismissed.
     */
    public function acknowledge()
    {
        return $this->acknowledgeInternal(false);
    }

    /**
     * Acknowledges and dismisses an alert synchronously, if the alert is able to be acknowledged and dismissed.
     */
    public function acknowledgeSync()
    {
        return $this->acknowledgeInternal(true);
    }

    /**
     * @param bool $sync Whether the Guzzle call should be synchronous.
     * @return null|\Psr\Http\Message\ResponseInterface
     *
     * @throws Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function acknowledgeInternal($sync = false)
    {
        if (!$this->acknowledgable) {
            return null;
        }

        return $this->zone->context->request('POST', '/portal/Device/AcknowledgeAlert', [
            'form_params' => [
                'DeviceID' => $this->zone->id,
                'AlertID' => $this->id,
            ],
            'synchronous' => $sync,
        ], 0);
    }
}
