<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\CompanyCollection;
use App\Http\Resources\Admin\CompanyResource;
use App\Http\Resources\Admin\CustomerCollection;
use App\Http\Resources\Admin\CustomerResource;
use App\Http\Responses\ResponseResult;
use App\Models\Company;

class CompaniesController extends Controller
{
    public function index(){
        $status = request()->get('status',null);
        $perPage = request()->query('perPage', 20);
        $orderBy = request()->query('order', 'customers_count');
        $sort = request()->query('sort', 'desc');
        $query = new Company();

        if($status == 'inactiv'){
            $query = $query->noActive();
        }

        if($status == 'active'){
            $query = $query->active();
        }

        if($status == 'blocked'){
            $query = $query->suspended();
        }

        if($orderBy == 'customers_count'){
            $query =  $query->withCount('customers')->orderBy('customers_count', $sort);
        }

        if($orderBy == 'created_at'){
            $query =  $query->orderBy('created_at', $sort);
        }

      //  $query =  $query->withCount('requests')->orderBy('requests_count', $sort);

        $queryStr = request()->get('search',null);

        if ($queryStr){
            $query = $query->where('company_name','LIKE','%'.$queryStr.'%')
                ->orWhere('id',$queryStr)
                ->orWhere('company_email','LIKE','%'.$queryStr.'%');
        }

        $companies = $query->paginate($perPage)->withQueryString();

        return new CompanyCollection($companies);

    }

    public function show(Company $company){
        return new CompanyResource($company);
    }
    public function update(Company $company){
        $updateData = request()->all();
        $status = 2;

        if(request()->get('status', null) == 'inactiv'){
            $status = 0;
        }
        if(request()->get('status', null) == 'active'){
            $status = 1;
        }

        $updateData['status'] = $status;
        $company->update($updateData);
        return new CompanyResource($company);
    }

    public function destroy(Company $company){
        foreach ($company->customers as $customer) {
            $customer->tracker()->delete();
            $customer->trackerProcessing()->delete();
            $customer->delete();
        }
        $company->requests()->delete();
        $company->delete();

        $response = new ResponseResult();
        $response->setResult(true);
        $response->setMessage('Company and other data deleted');

        return response()->json($response->makeResponse());
    }
}
