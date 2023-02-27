<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\CustomerCollection;
use App\Http\Resources\Admin\RequestChangeCompanyInfoResource;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
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
            'company_name' => $this->company_name,
            'company_phone' => $this->company_phone,
            'company_address' => $this->company_address,
            'company_email' => $this->company_email,
            'company_logo' => $this->company_logo,
            //'company_customers' => !$this->customers->isEmpty() ? new CustomerCollection($this->customers) : null,
            'company_request_update_info' => $this->requests->new()->first() ? new RequestChangeCompanyInfoResource($this->requests->new()->first()) : null,
        ];


        return $return;
    }

}
