<?php

namespace App\Services\AirConditioner;

use App\Job;
use App\Order;
use App\Jobs\TurnOnAirConditionerByOrder;
use App\Jobs\TurnOffAirConditioner;
use App\Services\NotifyService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Http;

class MiezoDevice extends Device
{
    private $params;

    public function __construct($uuid)
    {
        $this->uuid = $uuid;
        $this->params = ['QRID' => $uuid, 'KEY' => config('miezo.key')];
    }

    public function turnOn()
    {
        $this->params['ACTION'] = 'ON';
        $response = Http::get(config('miezo.url') . 'aircontrol', $this->params);

        $errorMessage = '';
        if($response->status() != 200) {
            $errorMessage = '開啟冷氣API錯誤：'. $response->status();
        } else {
            switch($response->body()) {
                case 'errKEY':
                    $errorMessage = '開啟冷氣金鑰錯誤';
                    break;
                case 'errQR':
                    $errorMessage = '開啟冷氣 UUID 錯誤：'.$this->uuid;
                    break;
            }
        }

        if($errorMessage != '') {
            $notifyService = app()->make(NotifyService::class);
            $notifyService->setMessage($errorMessage)->chatNotification();
            return false;
        }

        return true;
    }

    public function turnOff()
    {
        $this->params['ACTION'] = 'OFF';
        $response = Http::get(config('miezo.url') . 'aircontrol', $this->params);

        $errorMessage = '';
        if($response->status() != 200) {
            $errorMessage = '關閉冷氣API錯誤：'. $response->status();
        } else {
            switch($response->body()) {
                case 'errKEY':
                    $errorMessage = '關閉冷氣金鑰錯誤';
                    break;
                case 'errQR':
                    $errorMessage = '關閉冷氣 UUID 錯誤：'.$this->uuid;
                    break;
            }
        }

        if($errorMessage != '') {
            $notifyService = app()->make(NotifyService::class);
            $notifyService->setMessage($errorMessage)->chatNotification();
            return false;
        }

        return true;
    }

    public function turnOnByOrder($order, $tomorrow00 = false)
    {
        if(!$tomorrow00) {
            $date = Carbon::today()->format('Y-m-d');
            $times = $order->times->where('date', $date);
            $start = strtotime($date . " " . $times->min('start'));
        } else {
            $date = Carbon::tomorrow()->format('Y-m-d');
            $times = $order->times->where('date', $date)->where('start', '00:00:00');
            $start = strtotime($date . " 00:00:00");
        }

        $end = strtotime($date . " " . $times->max('end'));
        $nowTimeStamp = Carbon::now()->timestamp;

        if($end <= $nowTimeStamp) {
            // 訂單已經過期，不用開
            return true;
        }

        if ($start <= $nowTimeStamp) {
            // 訂單時間已經開始了，直接開冷氣
            TurnOnAirConditionerByOrder::dispatch($this->uuid, $order->id, $end);
        } else {
            // 訂單時間還沒開始，設定開冷氣排程
            TurnOnAirConditionerByOrder::dispatch($this->uuid, $order->id, $end)->delay($start - $nowTimeStamp);
            $job = Job::where('payload', 'LIKE', '%TurnOnAirConditionerByOrder%')->select('id')->latest('id')->first();
            $redisKey = 'air_on_job_'.$order->id;
            Redis::set($redisKey, $job->id);
            Redis::expireAt($redisKey, $start);
        }

        // 設定要關冷氣的時間
        TurnOffAirConditioner::dispatch($this->uuid, $order->id)->delay($end - $nowTimeStamp);
        $job = Job::where('payload', 'LIKE', '%TurnOffAirConditioner%')->select('id')->latest('id')->first();
        $redisKey = 'air_off_job_'.$order->id;
        Redis::set($redisKey, $job->id);
        Redis::expireAt($redisKey, $end);

        return true;
    }

    public function turnOffByOrder($order)
    {
        // 把正開著的冷氣關掉
        $uuidKey = 'air_'.$this->uuid;
        if(Redis::exists($uuidKey) && Redis::get($uuidKey) == $order->id) {
            $this->turnOff();
            Redis::del($uuidKey);
        }

        // 把開啟排程刪掉
        $jobKey = 'air_on_job_'.$order->id;
        if(Redis::exists($jobKey)) {
            $job = Job::find(Redis::get($jobKey));
            if(!is_null($job)) {
                $job->delete();
            }
        }

        // 把關閉排程刪掉
        $jobKey = 'air_off_job_'.$order->id;
        if(Redis::exists($jobKey)) {
            $job = Job::find(Redis::get($jobKey));
            if(!is_null($job)) {
                $job->delete();
            }
        }
    }
}
