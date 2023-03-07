<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
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

        $return = [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'position' => $this->position,
            'owner' => (bool)$this->owner,
            ];


        if(request()->filter){
            $return +=  [
                'work_time'=>  convertMinutesToHumanTime($this->sum_total_work),
                'pause_time'=> convertMinutesToHumanTime($this->sum_total_pause),
                'work_days'=> $this->work_days_count,
                'sick_days'=> $this->sick_days_count,
                'vacation_days'=> $this->vacation_days_count,
                'weekend_days'=> $this->weekend_days_count,
            ];
        }


        return $return;
    }

}
