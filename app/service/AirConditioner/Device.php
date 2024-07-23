<?php

namespace App\Services\AirConditioner;

use App\Order;

abstract class Device
{
    public $uuid;

    abstract public function turnOn();

    abstract public function turnOff();

    abstract public function turnOnByOrder(Order $order, $tomorrow00 = false);

    abstract public function turnOffByOrder(Order $order);
}
