<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\CompanyCollection;
use App\Http\Resources\Admin\CompanyResource;
use App\Http\Resources\Admin\CustomerCollection;
use App\Http\Resources\Admin\CustomerResource;
use App\Models\Company;

class CompaniesController extends Controller
{
    public function index(){
        $status = request()->get('status',null);
        $perPage = request()->query('perPage', 20);
        $orderBy = request()->query('orderBy', 'customers_count');
        $sort = request()->query('sort', 'desc');
        $query = new Company();

        if($status === 0){
            $query = $query->noActive();
        }

        if($status == 1){
            $query = $query->active();
        }

        if($status == 2){
            $query = $query->suspended();
        }

        if($orderBy == 'customers_count'){
            $query =  $query->withCount('customers')->orderBy('customers_count', $sort);
        }

        if($orderBy == 'created_at'){
            $query =  $query->orderBy('created_at', $sort);
        }

        $query =  $query->withCount('requests')->orderBy('requests_count', $sort);


        $companies = $query->paginate($perPage)->withQueryString();

        return new CompanyCollection($companies);

    }

    public function show(Company $company){
        return new CompanyResource($company);
    }
}
