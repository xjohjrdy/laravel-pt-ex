<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class BaseRequest extends FormRequest
{
    public function failedValidation($validator)
    {
        $error = $validator->errors()->all();

        throw  new HttpResponseException(response()->json(['code' => 1000, 'msg' => $error[0]]));
    }
}