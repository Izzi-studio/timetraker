<?php

namespace App\Http\Controllers\AuthApi;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\CompanyResource;
use App\Http\Responses\ResponseResult;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserAuthController extends Controller
{

    public function registerCompany(Request $request)
    {
        $dataInputs = $request->all();
        $rules = [
            'owner.name' => 'required|max:255',
            'owner.email' => 'email|unique:users,email',
            'owner.password' => 'required|confirmed',
            'company.company_name'=>'required',
            'company.company_email'=>'required',
        ];

        $validator = Validator::make($dataInputs,$rules);

        if($validator->fails()){
            return response()->json($validator->errors(),422);
        }

        $dataInputs['owner']['password'] = bcrypt($dataInputs['owner']['password']);

        $company = Company::create($dataInputs['company']);
        $dataInputs['owner']['company_id'] = $company->id;
        $user = User::create($dataInputs['owner']);
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

    public function logout(){
        $user = auth()->user()->token();
        $user->revoke();
        $response = new ResponseResult();
        $response->setResult(true);
        $response->setMessage('Logout');

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
        $role = 'customer';

        if(auth()->user()->owner){
            $redirect = 'page list customers';
            $role = 'owner';
        }

        if(auth()->user()->is_admin){
            $redirect = 'page list companies';
            $role = 'admin';
        }

        $status = 'inactiv';
        if(auth()->user()->company->status == 1){
            $status = 'active';
        }
        if(auth()->user()->company->status == 2){
            $status = 'blocked';
        }

        $data = [
            'token' => $token,
            'redirect'=> $redirect,
            'role'=> $role,
            'active_company'=> $status
        ];


        $response->setResult(true);
        $response->setMessage('Your logged');
        $response->setData($data);

        return response()->json($response->makeResponse());

    }

    public function getMe(){

        $data['role']= 'customer';
        $status = 'inactiv';
        if(auth()->user()->company->status == 1){
            $status = 'active';
        }
        if(auth()->user()->company->status == 2){
            $status = 'blocked';
        }
        $data['active_company'] = $status;

        if(auth()->user()->owner){
            $data['role'] = 'owner';

            $status = 'inactiv';
            if(auth()->user()->company->status == 1){
                $status = 'active';
            }
            if(auth()->user()->company->status == 2){
                $status = 'blocked';
            }
            $data['active_company'] = $status;
        }

        if(auth()->user()->is_admin){
            $data['role']= 'admin';
            unset($data['active_company']);
        }



        $response = new ResponseResult();
        $response->setResult(true);
        $response->setMessage('Info about account');
        $response->setData($data);

        return response()->json($response->makeResponse());
    }
}
