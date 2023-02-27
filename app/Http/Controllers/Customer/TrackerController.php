<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Responses\ResponseResult;
use App\Models\Tracker;
use App\Models\TrackerProcessing;
use Carbon\Carbon;
use Carbon\CarbonInterval;
class TrackerController extends Controller
{
    private $typeAction = [];

    public function __construct()
    {
        $this->typeAction = config('statuses');

    }

    public function index(){

        $response = new ResponseResult();
        $response->setResult(true);
        $response->setMessage('Available actions');
        $response->setData($this->getActions());

        return response()->json($response->makeResponse());

    }

    public function store(){


        $status = isset($this->typeAction[request()->get('typeAction')]) ? $this->typeAction[request()->get('typeAction')] : null;
        if(!$status){
            $response = new ResponseResult();
            $response->setResult(false);
            $response->setMessage('Undefined Type Action');

            return response()->json($response->makeResponse());
        }

        $tracker = Tracker::whereRaw("date_format(created_at, '%Y-%m-%d') = '".Carbon::now()
            ->format('Y-m-d')."'")
            ->whereCurrentStatus($this->typeAction['stop_day'])
            ->whereCustomerId(auth()->user()->id)
            ->count();

        if($tracker > 0){
            $response = new ResponseResult();
            $response->setResult(false);
            $response->setMessage('Current day is closed');
            $response->setData($this->getActions());
            return response()->json($response->makeResponse());
        }

        $tracker = Tracker::whereRaw("date_format(created_at, '%Y-%m-%d') = '".Carbon::now()
            ->format('Y-m-d')."'")
            ->whereCurrentStatus($status)
            ->whereCustomerId(auth()->user()->id)
            ->count();

        if($tracker > 0){
            $response = new ResponseResult();
            $response->setResult(false);
            $response->setMessage('Action '.array_search($status, $this->typeAction).' in progress');
            $response->setData($this->getActions());
            return response()->json($response->makeResponse());
        }

        $tracker = Tracker::whereRaw("date_format(created_at, '%Y-%m-%d') = '".Carbon::now()
            ->format('Y-m-d')."'")
            ->whereCustomerId(auth()->user()->id)->first();

        if($tracker && $status == 1) {
            $tracker->update(
                ['current_status' => $this->typeAction['start_day'], 'date_start' => Carbon::now()->format('Y-m-d h:i')]
            );

            $response = new ResponseResult();
            $response->setResult(true);
            $response->setMessage('Started');
            $response->setData($this->getActions());
            return response()->json($response->makeResponse());
        }



        if($tracker && $status == 2){

            $trackerProcessing = TrackerProcessing::whereStatus(3)
                ->whereCustomerId(auth()->user()->id)
                ->whereNull('action_date_time_stop')
                ->first();

            if($trackerProcessing){
                $trackerProcessing->update(['action_date_time_stop' => Carbon::now()->format('Y-m-d h:i')]);
            }

            $trackerProcessing =  TrackerProcessing::whereStatus(3)
                ->whereCustomerId(auth()->user()->id)
                ->get();


            $pausedSeconds = 0;
            foreach ($trackerProcessing as $item) {
                $start = Carbon::parse($item['action_date_time_start']);
                $stop = Carbon::parse($item['action_date_time_stop']);
                $pausedSeconds += $stop->diffInSeconds($start);
            }

           // dd(CarbonInterval::seconds(8400)->cascade()->format('%H:%I'));

            $dateStop = Carbon::now()->format('Y-m-d h:i');
            $start = Carbon::parse($tracker['date_start']);
            $stop = Carbon::parse($dateStop);

            $workTimeSeconds = $stop->diffInSeconds($start) - $pausedSeconds;

             $tracker->update([
                 'current_status'=>$status,
                 'date_stop' => $dateStop,
                 'pause' => $pausedSeconds,
                 'total_work' => $workTimeSeconds
              ]);

            $response = new ResponseResult();
            $response->setResult(true);
            $response->setMessage('Stopped');
            $response->setData($this->getActions());
            return response()->json($response->makeResponse());
        }

        if($tracker && $status == 3){

            $trackerProcessing = TrackerProcessing::whereStatus(3)
                ->whereCustomerId(auth()->user()->id)
                ->whereNull('action_date_time_stop')
                ->count();

            if($trackerProcessing > 0){
                $response = new ResponseResult();
                $response->setResult(false);
                $response->setMessage('You time is paused');
                $response->setData($this->getActions());
                return response()->json($response->makeResponse());
            }

            $dataInsert = [
                'customer_id' => auth()->user()->id,
                'tracker_id' => $tracker->id,
                'status' => $status,
                'action_date_time_start' => Carbon::now()->format('Y-m-d h:i'),

            ];
            TrackerProcessing::create($dataInsert);
            $tracker->update(['current_status'=>$status]);

            $response = new ResponseResult();
            $response->setResult(true);
            $response->setMessage('Paused');
            $response->setData($this->getActions());
            return response()->json($response->makeResponse());
        }

        if($tracker && $status == 4){
            $trackerProcessing = TrackerProcessing::whereStatus(3)
                ->whereCustomerId(auth()->user()->id)
                ->whereNull('action_date_time_stop')
                ->first();

            if($trackerProcessing){
                $trackerProcessing->update(['action_date_time_stop' => Carbon::now()->format('Y-m-d h:i')]);
                $tracker->update(['current_status' => $this->typeAction['start_day']]);
            }

            $response = new ResponseResult();
            $response->setResult(true);
            $response->setMessage('Resume');
            $response->setData($this->getActions());
            return response()->json($response->makeResponse());
        }

    }

    public function update(Tracker $tracker){
        if($tracker->customer_id == auth()->user()->id) {
            $tracker->update(['comments' => request()->comments]);
        }

        $response = new ResponseResult();
        $response->setResult(true);
        $response->setMessage('Comment Added');

        return response()->json($response->makeResponse());
    }

    public function getActions()
    {
        $tracker = Tracker::whereRaw("date_format(created_at, '%Y-%m-%d') = '" . Carbon::now()->format('Y-m-d') . "'")
            ->whereCustomerId(auth()->user()->id)
            ->first();

        $data = [];
        if ($tracker) {
            if ($tracker->current_status == 0) {
                $data['actions'] = ['start_day'];
            }

            if ($tracker->current_status == 1) {
                $data['actions'] = ['stop_day', 'pause'];
            }

            if ($tracker->current_status == 3) {
                $data['actions'] = ['unpause'];
            }


        }
        return $data;
    }

}
