<?php

namespace App\Http\Controllers;

use App\Services\AirConditioner\AirConditionerDeviceService;

class AirConditionerController extends Controller
{
    public function turnOn(Request $request)
    {
        $airConditioner = new AirConditionerDeviceService(uuid: $request->get('deviceId'));
        $airConditioner->turnOn();
    }

    public function turnOff(Request $request)
    {
        $airConditioner = new AirConditionerDeviceService(uuid: $request->get('deviceId'));
        $airConditioner->turnOff();
    }
}