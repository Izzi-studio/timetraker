<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\CompanyResource;
use App\Http\Resources\Admin\CustomerCollection;
use App\Http\Resources\Admin\CustomerResource;
use App\Http\Responses\ResponseResult;
use App\Models\RequestChangeCompanyInfo;

class CompaniesController extends Controller
{
    public function index(){
        return new CompanyResource(auth()->user()->company);
    }

    public function store(){

        $request = RequestChangeCompanyInfo::whereCompanyId(auth()->user()->company->id)->whereApproved(0)->count();
        if($request > 0){
            $response = new ResponseResult();
            $response->setResult(true);
            $response->setMessage('Request in processed review');
            return response()->json($response->makeResponse());
        }

        $data['company_id'] = auth()->user()->company->id;
        $data['request_data_update'] = request()->all();

        RequestChangeCompanyInfo::create($data);

        $response = new ResponseResult();
        $response->setResult(true);
        $response->setMessage('Request updated company info');
        return response()->json($response->makeResponse());
    }

}
