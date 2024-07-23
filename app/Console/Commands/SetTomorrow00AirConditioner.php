<?php

namespace App\Console\Commands;

use App\AirConditioner;
use App\Repositories\OrderRepository;
use App\Services\AirConditioner\AirConditionerDeviceService;
use Illuminate\Console\Command;

class SetTomorrow00AirConditioner extends Command
{
    protected $signature = 'set-tomorrow00-air-conditioner';

    protected $description = 'Set air conditioner for orders in tomorrow.';

    public function handle(): mixed
    {
        echo "\n";
        echo '[' . date('Y-m-d H:i:s') . ']' . "\t";
        echo 'COMMAND: SetTomorrow00AirConditioner' . "\t" . 'STATUS: start task...' . "\n";

        $orders = OrderRepository::getOrdersAtTomorrow00();

        foreach ($orders as $order) {
            $airConditionerDevice = new AirConditionerDeviceService(spaceId: $order->space_id);
            $airConditionerDevice->turnOnByOrder($order, true);
        }

        echo '[' . date('Y-m-d H:i:s') . ']' . "\t";
        echo 'COMMAND: SetTomorrow00AirConditioner' . "\t" . 'STATUS: finish task...' . "\n";
        echo "\n";
    }
}
