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
        $status = 'inactiv';
        if($this->status == 1){
            $status = 'active';
        }
        if($this->status == 2){
            $status = 'blocked';
        }
        $return = [
            'id' => $this->id,
            'company_name' => $this->company_name,
            'company_phone' => $this->company_phone,
            'company_address' => $this->company_address,
            'company_email' => $this->company_email,
            'company_logo' => $this->company_logo,
            'active_company' => $status,
            'created_at' => $this->created_at->format('Y-m-d'),
            'customers_count' => $this->customers->count(),
            //'company_customers' => !$this->customers->isEmpty() ? new CustomerCollection($this->customers) : null,
            'company_request_update_info' => $this->requests ? new RequestChangeCompanyInfoResource($this->requests) : null,
        ];


        return $return;
    }

}
