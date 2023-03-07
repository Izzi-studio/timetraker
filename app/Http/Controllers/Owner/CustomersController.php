<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomerCollection;
use App\Http\Responses\ResponseResult;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\CustomerResource;
use App\Models\User;
use Carbon\Carbon;
use App\Models\Tracker;
use DB;

class CustomersController extends Controller
{
    public function index(){
        request()->filter = true;

        $order = request()->get('order', 'sum_total_work');
        $sort = request()->get('sort', 'desc');

        $year = request()->get('year', null);
        $month = request()->get('month', null);

        if(!$year && !$month){
            $response = new ResponseResult();
            $response->setResult(false);
            $response->setMessage('Send year and year and month');
            return response()->json($response->makeResponse());
        }

        if ($year && !$month){
            $template = '%Y';
            $value = $year;
        }else{
            $template = '%Y-%m';
            $value = $year.'-'.$month;
        }

        $subQuery = "date_format(created_at, '".$template."') = '".$value."'";

        $customers = auth()->user()->company->customers();

        $customers = $customers->withSum(['tracker as sum_total_work' => function($query) use ($subQuery){
            $query->whereRaw($subQuery);
        }],'work')
          ->withSum(['tracker as sum_total_pause' => function($query) use ($subQuery){
            $query->whereRaw($subQuery);
        }],'pause')
          ->withCount(['tracker as weekend_days_count'=>function($query) use ($subQuery){
              $query->whereCurrentStatus(config('statuses.weekend_day'))->whereRaw($subQuery);
        }])
          ->withCount(['tracker as work_days_count'=>function($query) use ($subQuery){
              $query->whereCurrentStatus(config('statuses.stop_day'))->whereRaw($subQuery);
        }])
          ->withCount(['tracker as sick_days_count'=>function($query) use ($subQuery){
              $query->whereCurrentStatus(config('statuses.sick_day'))->whereRaw($subQuery);
        }])
          ->withCount(['tracker as vacation_days_count'=>function($query) use ($subQuery){
              $query->whereCurrentStatus(config('statuses.vacation_day'))->whereRaw($subQuery);
        }]);


        $customers = $customers->orderBy($order, $sort);

        return new CustomerCollection($customers->get());
    }

    public function show(User $customer){
        if($customer->company_id == auth()->user()->company->id) {
            return new CustomerResource($customer);
        }

        $response = new ResponseResult();
        $response->setResult(false);
        $response->setMessage('Your action blocked');

        return response()->json($response->makeResponse());
    }

    public function update(User $customer){

        if($customer->company_id == auth()->user()->company->id) {
            $dataInputs = request()->all();

            $rules = [
                'name' => 'required|max:255',
                'email' => 'email|unique:users,email,' . $customer->id,
                'position' => 'required'
            ];

            if (request()->get('password',null)) {
                $rules['password'] = 'required|confirmed';
            }

            $validator = Validator::make($dataInputs, $rules);

            if (request()->get('password',null)) {
                $dataInputs['password'] = bcrypt($dataInputs['password']);
            }

            if ($validator->fails()) {
                return response()->json($validator->errors(), 422);
            }

            $customer->update($dataInputs);

            return new CustomerResource($customer);
        }

        $response = new ResponseResult();
        $response->setResult(false);
        $response->setMessage('Your action blocked');

        return response()->json($response->makeResponse());
    }

}
