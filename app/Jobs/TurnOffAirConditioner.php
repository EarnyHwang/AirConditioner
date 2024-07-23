<?php

namespace App\Jobs;

use App\Services\AirConditioner\AirConditionerDeviceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;

class TurnOffAirConditioner implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $uuid;

    public function __construct($uuid)
    {
        $this->uuid = $uuid;
    }

    public function handle()
    {
        $airConditioner = new AirConditionerDeviceService(deviceId: $this->uuid);
        $airConditioner->turnOff();
    }
}
