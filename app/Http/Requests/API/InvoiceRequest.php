<?php

namespace App\Http\Requests\API;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class InvoiceRequest extends FormRequest
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
            case 'getNextInvoiceId':
                $rules = [
                    'type' => 'required|in:sales,purchase'
                ];
                break;
            case 'update':
                $rules = [
                    'invoice_number' => 'required',
                    'invoice_date'   => 'required',
                    'consumer_id'    => 'required',
                    'invoice_for'    => 'required|in:customer,branch'
                ];
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
