<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\CustomerResource;
use App\Models\User;
class CustomersController extends Controller
{
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

}
