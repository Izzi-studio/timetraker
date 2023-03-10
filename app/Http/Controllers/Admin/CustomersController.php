<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomerCollection;
use App\Http\Responses\ResponseResult;
use App\Models\Company;
use App\Models\Tracker;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Http\Resources\CustomerResource;
use App\Models\User;

class CustomersController extends Controller
{
    public function index(){
        request()->filter = true;
        $companyId = request()->get('company_id', null);
        $order = request()->get('order', 'sum_total_work');
        $sort = request()->get('sort', 'desc');

        $year = request()->get('year', null);
        $month = request()->get('month', null);

        if(!$year && !$month){
            $response = new ResponseResult();
            $response->setResult(false);
            $response->setMessage('Send year or year and month');
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

        $company = Company::find($companyId);
        $customers = $company->customers();

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

    public function store()
    {
        $dataInputs = request()->all();
        $rules = [
            'name' => 'required|max:255',
            'email' => 'email|unique:users,email',
            'password' => 'required|confirmed',
            'position' => 'required'
        ];

        $validator = Validator::make($dataInputs,$rules);

        if($validator->fails()){
            return response()->json($validator->errors(),422);
        }

        $dataInputs['password'] = bcrypt(request()->password);
        $dataInputs['company_id'] = request()->company_id;
        $dataInputs['owner'] = false;
        $user = User::create($dataInputs);
        $dataInsert = [
            'current_status'=>0,
            'customer_id' => $user->id
        ];

        Tracker::create($dataInsert);
        $data = [
            'user' => $user,
            'redirect'=>'to list users'
        ];

        $response = new ResponseResult();
        $response->setResult(true);
        $response->setMessage('Customer created');
        $response->setData($data);

        return response()->json($response->makeResponse());
    }

    public function show(User $customer){
        return new CustomerResource($customer);
    }

    public function update(User $customer){
        $dataInputs = request()->all();
        $rules = [
            'name' => 'required|max:255',
            'email' => 'email|unique:users,email,' . $customer->id,
            'position' => 'required'
        ];

        if (request()->get('password',null) != '') {
            $rules['password'] = 'required|confirmed';
            $dataInputs['password'] = bcrypt($dataInputs['password']);
        }else{
            unset($dataInputs['password']);
            unset($dataInputs['password_confirmation']);
        }
        $validator = Validator::make(request()->all(), $rules);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $customer->update($dataInputs);

        return new CustomerResource($customer);
    }

    public function destroy(User $customer){
        if($customer->owner){
            $response = new ResponseResult();
            $response->setResult(false);
            $response->setMessage('Customer is owner');

            return response()->json($response->makeResponse());
        }
        $customer->tracker()->delete();
        $customer->trackerProcessing()->delete();
        $customer->delete();

        $response = new ResponseResult();
        $response->setResult(true);
        $response->setMessage('Customer deleted');

        return response()->json($response->makeResponse());
    }

}
