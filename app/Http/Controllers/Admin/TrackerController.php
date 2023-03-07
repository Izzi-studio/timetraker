<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\TrackerCollection;
use App\Http\Resources\TrackerResource;
use App\Http\Responses\ResponseResult;
use App\Models\Tracker;
use App\Models\TrackerProcessing;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use DB;
use App\Models\User;
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

    public function show(Tracker $tracker){
        request()->admin = true;
        return new TrackerResource($tracker);
    }


    public function update(Tracker $tracker){

        $start = Carbon::parse(request()->get('date_start',$tracker->date_start));
        $stop = Carbon::parse(request()->get('date_stop',$tracker->date_stop));
        $pause = request()->get('pause', CarbonInterval::minutes($tracker->pause)->cascade()->format('%H:%I'));

        $intervalue = CarbonInterval::createFromFormat('H:i', $pause);

        $pausedMinutes = $intervalue->totalMinutes;

        $data['date_start'] = request()->get('date_start', $tracker->date_start);
        $data['date_stop'] = request()->get('date_stop', $tracker->date_stop);
        $data['pause'] = $pausedMinutes;
        $data['work'] = $stop->diffInMinutes($start) - $pausedMinutes;

        $status = isset($this->typeAction[request()->get('current_status')]) ? $this->typeAction[request()->get('current_status')] : $tracker->current_status;

        $data['current_status'] = $status;

        $tracker->update($data);

        $response = new ResponseResult();
        $response->setResult(true);
        $response->setMessage('Updated tracker row');
        return response()->json($response->makeResponse());

    }

    public function tableStatistic(User $customer){

        $year = request()->get('year', null);
        $month = request()->get('month', null);

        $trackers = $customer->tracker();
        if ($year && $month) {
            $trackers = $trackers->whereRaw("date_format(created_at, '%Y-%m') = '" . $year . '-' . $month . "'");
        } else {
            $trackers = $trackers->select(
                'created_at',
                DB::raw("date_format(created_at, '%Y-%m') as month"),
                DB::raw("sum(work) as total_work"),
                DB::raw("sum(pause) as total_pause"),
                DB::raw("(select count(id) from tracker where current_status = ".config('statuses.sick_day')." and date_format(created_at, '%Y-%m') = month AND customer_id = ".$customer->id." ) as sick_days"),
                DB::raw("(select count(id) from tracker where current_status = ".config('statuses.stop_day')." and date_format(created_at, '%Y-%m') = month  AND customer_id = ".$customer->id.") as work_days"),
                DB::raw("(select count(id) from tracker where current_status = ".config('statuses.vacation_day')." and date_format(created_at, '%Y-%m') = month  AND customer_id = ".$customer->id.") as vacation_days"),
                DB::raw("(select count(id) from tracker where current_status = ".config('statuses.weekend_day')." and date_format(created_at, '%Y-%m') = month  AND customer_id = ".$customer->id.") as weekend_days")
            );
            $trackers = $trackers->whereRaw("date_format(created_at, '%Y') = '" . $year . "'")->groupBy("month");
        }

        $trackers = $trackers->orderBy('created_at', 'asc')->get();

        return new TrackerCollection($trackers);


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
