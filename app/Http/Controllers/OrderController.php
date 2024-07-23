<?php

namespace App\Http\Controllers;

use App\Services\AirConditioner\AirConditionerDeviceService;
use App\Repositories\OrderRepository;

class OrderController extends Controller
{
    public function orderProcess(Request $request)
    {
        $order = OrderRepository::create(['spaceId' => $request->get('spaceId')]);

        $airConditioner = new AirConditionerDeviceService(spaceId: $order->space_id);
        $airConditioner->turnOnByOrder($order);
    }

    public function refundProcess(Request $request)
    {
        $order = OrderRepository::get($request->get('orderId'));

        $airConditioner = new AirConditionerDeviceService(spaceId: $order->space_id);
        $airConditioner->turnOffByOrder($order);
    }
}