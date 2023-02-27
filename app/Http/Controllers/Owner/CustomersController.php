<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomerCollection;
use App\Http\Responses\ResponseResult;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\CustomerResource;
use App\Models\User;
use Carbon\Carbon;
//use Carbon\CarbonInterval;
class CustomersController extends Controller
{
    public function index(){


        $order = request()->get('order', 'total_work');
        $sort = request()->get('sort', 'desc');
        $range = request()->get('range', 'month');
        $year = request()->get('year', Carbon::now()->format('Y'));
        $month = request()->get('month', Carbon::now()->format('m'));

        $customers = auth()->user()->company->customers();

        if($range == 'year'){
            $customers = $customers->whereRaw("date_format(created_at, '%Y') = '".$year."'");
        }else{
            $customers = $customers->whereRaw("date_format(created_at, '%m') = '".$month."'");
        }

        if($order == 'total_work_time'){
            $customers = $customers->withSum('tracker','total_work')->orderBy('tracker_sum_total_work', $sort);
        }

        if($order == 'total_pause_time'){
            $customers = $customers->withSum('tracker','pause')->orderBy('tracker_sum_pause', $sort);
        }

        if($order == 'total_work_days'){
            $customers = $customers->withCount('trackerWorkDays')->orderBy('tracker_work_days_count', $sort);
        }

        if($order == 'total_sick_days'){
            $customers = $customers->withCount('trackerSickDays')->orderBy('tracker_sick_days_count', $sort);
        }

        if($order == 'total_vacation_days'){
            $customers = $customers->withCount('trackerVacationDays')->orderBy('tracker_vacation_days_count', $sort);
        }


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
