<?php

namespace App\Http\Requests\API;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class BankRequest extends FormRequest
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

        switch ($this->route()->getActionMethod()) {
            case 'store':
            case 'update':
                $rules = [
                    'account_name'   => 'required',
                    'account_code'   => 'required',
                    'account_number' => 'required|unique:banks,account_number,' . $this->route('bank'),
                    'bank_name'      => 'required',
                    'description'    => 'required',
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
