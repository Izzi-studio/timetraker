<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\CustomerCollection;
use Illuminate\Http\Resources\Json\JsonResource;

class RequestChangeCompanyInfoResource extends JsonResource
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
            'request_data_update' => $this->request_data_update,
            //'current_info' => new CompanyResource($this->company),
        ];


        return $return;
    }

}
