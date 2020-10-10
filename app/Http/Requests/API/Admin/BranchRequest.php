<?php

namespace App\Http\Requests\API\Admin;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class BranchRequest extends FormRequest
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
                $rules = [
                    'branch_name'           => 'required',
                    'branch_email'          => 'required|unique:branches,branch_email',
                    'branch_contact_number' => 'required|unique:users,phone',
                    'address'               => 'required',
                    'customer_type'         => 'required|in:regular,composition'
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
