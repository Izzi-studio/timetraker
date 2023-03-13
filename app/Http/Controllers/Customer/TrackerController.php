<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\TrackerCollection;
use App\Http\Resources\TrackerResource;
use App\Http\Responses\ResponseResult;
use App\Models\Tracker;
use App\Models\TrackerProcessing;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use DB;
class TrackerController extends Controller
{
    private $typeAction = [];

    public function __construct()
    {
        $this->typeAction = config('statuses');

      //  request()->timezone ? Carbon::now()->timezone(request()->timezone) : null;

    }

    public function index(){

        $response = new ResponseResult();
        $response->setResult(true);
        $response->setMessage('Available actions');
        $response->setData($this->getActions());

        return response()->json($response->makeResponse());

    }

    public function show(Tracker $tracker){
        if($tracker->customer_id == auth()->user()->id){
            return new TrackerResource($tracker);
        }
        $response = new ResponseResult();
        $response->setResult(false);
        $response->setMessage('Your not have access');

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
                ['current_status' => $this->typeAction['start_day'], 'date_start' => Carbon::now()->format('Y-m-d H:i')]
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
                ->whereRaw("date_format(created_at, '%Y-%m-%d') = '".Carbon::now()->format('Y-m-d')."'")
                ->whereNull('action_date_time_stop')
                ->first();

            if($trackerProcessing){
                $trackerProcessing->update(['action_date_time_stop' => Carbon::now()->format('Y-m-d H:i')]);
            }

            $trackerProcessing =  TrackerProcessing::whereStatus(3)
                ->whereCustomerId(auth()->user()->id)
                ->whereRaw("date_format(created_at, '%Y-%m-%d') = '".Carbon::now()->format('Y-m-d')."'")
                ->get();


            $pausedMinutes = 0;
            foreach ($trackerProcessing as $item) {
                $start = Carbon::parse($item['action_date_time_start']);
                $stop = Carbon::parse($item['action_date_time_stop']);
                $pausedMinutes += $stop->diffInMinutes($start);
            }

           // dd(CarbonInterval::seconds(8400)->cascade()->format('%H:%I'));

            $dateStop = Carbon::now()->format('Y-m-d H:i');
            $start = Carbon::parse($tracker['date_start']);
            $stop = Carbon::parse($dateStop);

            $workTimeMinutes = $stop->diffInMinutes($start) - $pausedMinutes;

             $tracker->update([
                 'current_status'=>$status,
                 'date_stop' => $dateStop,
                 'pause' => $pausedMinutes,
                 'work' => $workTimeMinutes
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
                'action_date_time_start' => Carbon::now()->format('Y-m-d H:i'),

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
                $trackerProcessing->update(['action_date_time_stop' => Carbon::now()->format('Y-m-d H:i')]);
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
            $status = isset($this->typeAction[request()->get('current_status')]) ? $this->typeAction[request()->get('current_status')] : $tracker->current_status;
            $comments = request()->get('comments', $tracker->comments);
            $tracker->update([
                //'current_status' => $status,
                'comments'=>$comments
            ]);

            $response = new ResponseResult();
            $response->setResult(true);
            $response->setMessage('Updated tracker row');
            return response()->json($response->makeResponse());
        }

        $response = new ResponseResult();
        $response->setResult(true);
        $response->setMessage('Your not have access');

        return response()->json($response->makeResponse());
    }

    public function tableStatistic(){

        $year = request()->get('year', null);
        $month = request()->get('month', null);

        $trackers = auth()->user()->tracker();

        if($year && $month) {
            $trackers = $trackers->whereRaw("date_format(created_at, '%Y-%m') = '" . $year . '-' . $month . "'");
        }else{

            $trackers = $trackers->select(
                'created_at',
                DB::raw("date_format(created_at, '%Y-%m') as month"),
                DB::raw("sum(work) as total_work"),
                DB::raw("sum(pause) as total_pause"),
                DB::raw("(select count(id) from tracker where current_status = ".config('statuses.sick_day')." and date_format(created_at, '%Y-%m') = month AND customer_id = ".auth()->user()->id." ) as sick_days"),
                DB::raw("(select count(id) from tracker where current_status = ".config('statuses.stop_day')." and date_format(created_at, '%Y-%m') = month  AND customer_id = ".auth()->user()->id.") as work_days"),
                DB::raw("(select count(id) from tracker where current_status = ".config('statuses.vacation_day')." and date_format(created_at, '%Y-%m') = month  AND customer_id = ".auth()->user()->id.") as vacation_days"),
                DB::raw("(select count(id) from tracker where current_status = ".config('statuses.weekend_day')." and date_format(created_at, '%Y-%m') = month  AND customer_id = ".auth()->user()->id.") as weekend_days")
            );
            $trackers = $trackers->whereRaw("date_format(created_at, '%Y') = '" . $year . "'")->groupBy("month");

        }

        $trackers = $trackers->orderBy('created_at','desc')->get();

        return new TrackerCollection($trackers);

    }

    public function getActions()
    {
        $tracker = Tracker::whereRaw("date_format(created_at, '%Y-%m-%d') = '" . Carbon::now()->format('Y-m-d') . "'")
            ->whereCustomerId(auth()->user()->id)
            ->first();

        $data['current_day'] = [
            'id'=>$tracker->id,
            'date_start'=>$tracker->date_start ? $tracker->date_start->format('H:i'): null,
            'date_stop'=>$tracker->date_stop ? $tracker->date_stop->format('H:i') : null,
            'comments'=>$tracker->comments,

        ];
        if ($tracker) {
            if ($tracker->current_status == 0) {
                $data['actions'] = ['start_day'];
            }

            if ($tracker->current_status == 1) {
                $data['actions'] = ['stop_day', 'pause'];
            }

            if ($tracker->current_status == 3) {
                $data['actions'] = ['stop_day','unpause'];
            }


        }
        return $data;
    }

}
