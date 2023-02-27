<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\CarbonInterval;
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
            'total_work_time'=> CarbonInterval::seconds($this->trackerWorkDays()->sum('total_work'))->cascade()->format('%H:%I'),
            'total_pause_time'=>  CarbonInterval::seconds($this->trackerWorkDays()->sum('pause'))->cascade()->format('%H:%I'),
            'total_work_days'=> $this->trackerWorkDays()->count(),
            'total_sick_days'=> $this->trackerSickDays()->count(),
            'total_vacation_days'=> $this->trackerVacationDays()->count(),
            ];


        return $return;
    }

}
