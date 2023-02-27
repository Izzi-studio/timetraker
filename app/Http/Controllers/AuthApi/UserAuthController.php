<?php

namespace App\Http\Controllers\AuthApi;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\CompanyResource;
use App\Http\Responses\ResponseResult;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UserAuthController extends Controller
{
    public function registerCompany(Request $request)
    {
        $dataInputs = $request->all();
        $rules = [
            'customer.name' => 'required|max:255',
            'customer.email' => 'email|unique:users,email',
            'customer.password' => 'required|confirmed',
            'company.company_name'=>'required',
            'company.company_email'=>'required',
        ];

        $validator = Validator::make($dataInputs,$rules);

        if($validator->fails()){
            return response()->json($validator->errors(),422);
        }

        $dataInputs['customer']['password'] = bcrypt($request->password);
        $dataInputs['company']['subdomain'] = Str::slug($dataInputs['company']['company_name']);

        $company = Company::create($dataInputs['company']);
        $dataInputs['customer']['company_id'] = $company->id;
        $user = User::create($dataInputs['customer']);
        $company->update(['owner_id'=>$user->id]);
        $token = $user->createToken('API Token')->accessToken;


        $data = [
            'user' => $user,
            'token' => $token,
            'company'=> new CompanyResource($company),
            'redirect'=>'to list users'
        ];

        $response = new ResponseResult();
        $response->setResult(true);
        $response->setMessage('Account company create');
        $response->setData($data);

        return response()->json($response->makeResponse());
    }

    public function registerCustomer(Request $request)
    {
        $dataInputs = $request->all();
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

        $dataInputs['password'] = bcrypt($request->password);


        $dataInputs['company_id'] = auth()->user()->company->id;
        $dataInputs['owner'] = false;
        $user = User::create($dataInputs);

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

    public function login(Request $request)
    {
        $response = new ResponseResult();

        $data = $request->validate([
            'email' => 'email|required',
            'password' => 'required'
        ]);

        if (!auth()->attempt($data)) {
            $response->setResult(false);
            $response->setMessage('Access denied');
            $response->setData($data);

            return response()->json($response->makeResponse());
        }

        $token = auth()->user()->createToken('API Token')->accessToken;

        $redirect = 'page statistic';

        if(auth()->user()->owner){
            $redirect = 'page list customers';
        }

        if(auth()->user()->is_admin){
            $redirect = 'page list companies';
        }

        $data = [
            'token' => $token,
            'redirect'=> $redirect
        ];


        $response->setResult(true);
        $response->setMessage('Your logged');
        $response->setData($data);

        return response()->json($response->makeResponse());

    }

    public function getMe(){

        $data['role']= 'customer';

        if(auth()->user()->owner){
            $data['role'] = 'owner';
            $data['active_company'] = (bool)auth()->user()->company->status;
        }

        if(auth()->user()->is_admin){
            $data['role']= 'admin';
        }



        $response = new ResponseResult();
        $response->setResult(true);
        $response->setMessage('Info about account');
        $response->setData($data);

        return response()->json($response->makeResponse());
    }
}
