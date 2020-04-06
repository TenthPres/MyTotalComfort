<?php

namespace Tenth\MyTotalComfort;

use Tenth\MyTotalComfort;

class Alert
{
    protected $clearable = false; // should be treated as immutable.
    protected $dateTime;
    protected $text;
    protected $alertClearRef;


    /**
     * @param $jsonString
     * @return Alert[]
     */
    public static function fromJsonString($jsonString)
    {

        preg_match_all(
            '/<div class="alert-content">[\s\S]+<span>([\s\S]+(\d{1,2}\/\d{1,2}\/\d{4} ' .
                '\d{1,2}:\d{2}:\d{2} [AP]M)[\s\S]+)[\s\.]+<\/span>[\s\S]+(<div class="clear"><\/div>):?/Um',
            $jsonString,
            $matches,
            PREG_SET_ORDER
        );

        $alerts = [];
        foreach ($matches as $alert => $content) {
            $alerts[] = new Alert($content);
        }
        return $alerts;
    }

    /**
     * Alert constructor.
     * @param string[] $attributes Matches from the JSON status response
     */
    protected function __construct($attributes)
    {
        $this->text = $attributes[1];
        $this->dateTime = \DateTime::createFromFormat("n/j/Y g:i:s A", $attributes[2], MyTotalComfort::$tzUTC);
        $this->alertClearRef = $attributes[3]; // TODO parse clearability, and provide callback for clearing.
    }

    /**
     * Clears an alert, if the alert is clearable.
     * Not implemented yet.  Potentially should be split into a synchronous and asynchronous version because
     * Resideo servers take a while to respond to these messages.
     */
    public function clear()
    {
        // TODO
    }
}
