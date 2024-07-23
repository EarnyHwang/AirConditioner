<?php

namespace App\Jobs;

use App\Job;
use App\Order;
use App\Services\AirConditioner\AirConditionerDeviceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Redis;

class TurnOnAirConditionerByOrder implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $uuid;
    protected $orderId;
    protected $end;

    public function __construct($uuid, $orderId, $end)
    {
        $this->uuid = $uuid;
        $this->orderId = $orderId;
        $this->end = $end;
    }

    public function handle()
    {
        $order = Order::find($this->orderId);
        if($order->status != Order::STATUS_PAID) {
            return;
        }

        $airConditioner = new AirConditionerDeviceService(deviceId: $this->uuid);
        $airConditioner->turnOn();

        $redisKey = 'air_'.$this->uuid;
        if(Redis::exists($redisKey)) {
            // 冷氣已經有別的訂單打開了，還沒關掉
            $oldOlderId = Redis::get($redisKey);

            // 把那筆訂單的關冷氣排程刪除
            $jobRedisKey = 'air_off_job_'.$oldOlderId;
            $offJobId = Redis::get($jobRedisKey);
            $job = Job::find($offJobId);
            if(!is_null($job)) {
                $job->delete();
            }
        }

        Redis::set($redisKey, $this->orderId);
        Redis::expireAt($redisKey, $this->end);
    }
}
