<?php

namespace App\Http\Requests\API;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class AuthRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [];

        switch ($this->route()->uri) {
            case 'api/login':
                $rules = [
                    'phone'    => 'required',
                    'password' => 'required'
                ];
                break;

            case 'api/register':
                $rules = [
                    'first_name' => 'required',
                    'last_name'  => 'required',
                    'phone'      => 'required|unique:customers',
                    'email'      => 'required|unique:customers|email',
                    'password'   => 'required',
                ];
                break;
        }
        return $rules;
    }

    protected function failedValidation(Validator $validator)
    {
        $response = response()->json([
            'success' => false,
            'message' => $validator->errors()->first(),
            'data'    => [],
        ], 422);
        throw new ValidationException($validator, $response);
    }
}

