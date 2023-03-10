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
        $dataInputs['owner']['position'] = env('DEFAULT_OWNER_POSITION');
        $user = User::create($dataInputs['owner']);
        $company->update(['owner_id'=>$user->id]);
        $token = $user->createToken('API Token')->accessToken;


        $data = [
            'user' => $user,
            'token' => $token,
            'company'=> new CompanyResource($company)
        ];

        $response = new ResponseResult();
        $response->setResult(true);
        $response->setMessage('Account company create');
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
        auth()->user()->timezone =  request()->hasHeader('time-zone') ? request()->header('time-zone') : env('DEFAULT_TIMEZONE');
        auth()->user()->save();

        $role = 'customer';

        $data = [];

        if(auth()->user()->owner){
            $role = 'owner';
        }

        if(!auth()->user()->is_admin){
            $status = 'inactiv';

            if (auth()->user()->company->status == 1) {
                $status = 'active';
            }

            if (auth()->user()->company->status == 2) {
                $status = 'blocked';
            }
            $data['active_company'] = $status;
        }

        if(auth()->user()->is_admin){
            $role = 'admin';
        }


        $data += [
            'token' => $token,
            'role'=> $role
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
