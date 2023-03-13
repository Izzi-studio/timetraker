<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\CompanyResource;
use App\Models\RequestChangeCompanyInfo;

class RequestsChangeCompanyInfoController extends Controller
{
    public function update(RequestChangeCompanyInfo $changeInfoCompany){
        if(request()->approved == 1){
            $updateData = $changeInfoCompany->request_data_update;

            if ($changeInfoCompany->company->update($updateData)){
                $changeInfoCompany->update(['approved'=>request()->approved]);
            }
        }
        if(request()->approved == 2){
             $changeInfoCompany->update(['approved'=>request()->approved]);
        }
        return new CompanyResource($changeInfoCompany->company);
    }



}
