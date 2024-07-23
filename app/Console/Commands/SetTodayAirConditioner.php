<?php

namespace App\Console\Commands;

use App\AirConditioner;
use Illuminate\Console\Command;
use App\Repositories\OrderRepository;
use App\Services\AirConditioner\AirConditionerDeviceService;

class SetTodayAirConditioner extends Command
{
    protected $signature = 'set-today-air-conditioner';

    protected $description = 'Set air conditioner for orders in today.';

    public function handle(): mixed
    {
        echo "\n";
        echo '[' . date('Y-m-d H:i:s') . ']' . "\t";
        echo 'COMMAND: SetTodayAirConditioner' . "\t" . 'STATUS: start task...' . "\n";

        $orders = OrderRepository::getOrdersBetweenStartToEndDate(date("Y-m-d", strtotime("today")));

        foreach ($orders as $order) {
            $airConditionerDevice = new AirConditionerDeviceService(spaceId : $order->space_id);
            $airConditionerDevice->turnOnByOrder($order);
        }

        echo '[' . date('Y-m-d H:i:s') . ']' . "\t";
        echo 'COMMAND: SetTodayAirConditioner' . "\t" . 'STATUS: finish task...' . "\n";
        echo "\n";
    }
}
