<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\CarbonInterval;
use Carbon\Carbon;

class TrackerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request The request
     *
     * @return array
     */
    public function toArray($request)
    {

        $status = array_search($this->current_status,config('statuses')) ? array_search($this->current_status,config('statuses')) : $this->current_status;
        $return = [];
        if(request()->get('month', null) || request()->owner || request()->admin) {

            $return = [
                'id' => $this->id,
                'comments' => $this->comments,
                'total_work' => convertMinutesToHumanTime($this->work),
                'total_pause' => convertMinutesToHumanTime($this->pause),
                'work_minutes' => $this->work,
                'pause_minutes' => $this->pause,
                'current_status' => $status,
                'date_start' => $this->date_start ? $this->date_start->format('H:i') : '00:00',
                'date_stop' => $this->date_stop ? $this->date_stop->format('H:i') : '00:00',
                'date' => Carbon::create($this->created_at)->shortDayName.','.Carbon::create($this->created_at)->format('d'),
            ];
            return $return;
        }

        $return = [
            'date' => Carbon::create($this->created_at)->shortMonthName,
            'total_sick_days' => $this->sick_days,
            'total_work_days' => $this->work_days,
            'total_vacation_days' => $this->vacation_days,
            'total_weekend_days' => $this->weekend_days,
            'sum_total_work' => convertMinutesToHumanTime($this->sum_total_work),
            'sum_total_pause' => convertMinutesToHumanTime($this->sum_total_pause)
        ];
        return $return;

    }


}
