<?php

namespace App\Services\AirConditioner;

use App;
use App\AirConditioner;

class AirConditionerDeviceService
{
    private ?Device $device = null;

    public function __construct($deviceId = null, $spaceId = null, $dbId = null)
    {
        $airConditioner = null;

        if (!is_null($dbId)) {
            $airConditioner = AirConditioner::find($dbId);
        } elseif (!is_null($spaceId)) {
            $airConditioner = AirConditioner::where('space_id', $spaceId)->first();
        } elseif (!is_null($deviceId)) {
            $airConditioner = AirConditioner::where('uuid', $deviceId)->first();
        }

        if (is_null($airConditioner)) {
            return;
        }

        switch ($airConditioner->type){
            case AirConditioner::TYPE_MIEZO:
                $this->device = new MiezoDevice($airConditioner->uuid);
                break;
            default:
                break;
        }
    }

    public function turnOn()
    {
        if (is_null($this->device)) {
            return true;
        }
        return $this->device->turnOn();
    }

    public function turnOff()
    {
        if (is_null($this->device)) {
            return true;
        }
        return $this->device->turnOff();
    }

    public function turnOnByOrder($order, $tomorrow00 = false)
    {
        if (is_null($this->device)) {
            return true;
        }
        return $this->device->turnOnByOrder($order, $tomorrow00);
    }

    public function turnOffByOrder($order)
    {
        if (is_null($this->device)) {
            return true;
        }
        return $this->device->turnOffByOrder($order);
    }
}
