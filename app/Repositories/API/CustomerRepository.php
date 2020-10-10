<?php

namespace App\Repositories\API;

use App\Models\Customer;
use Exception;

class CustomerRepository
{
    private $customer;

    public function __construct()
    {
        $this->customer = new Customer();
    }

    public function getCustomers($data)
    {
        $customers = $this->customer
            ->where('added_by', $data['user_id'])
            ->get([
                'id',
                'company_name',
                'customer_name',
                'phone',
                'address',
                'city',
                'state',
                'country',
                'pin_code',
                'gst_number',
                'gst_treatment',
                'email'
            ]);

        if (empty($customers)) {
            throw new Exception('There is no customer available try to add new customer');
        }

        $customer_data = [];

        foreach ($customers as $customer) {
            $customer_data[] = [
                'id'            => $customer->id,
                'company_name'  => checkEmpty($customer, 'company_name', ''),
                'customer_name' => checkEmpty($customer, 'customer_name', ''),
                'phone'         => checkEmpty($customer, 'phone', ''),
                'address'       => checkEmpty($customer, 'address', ''),
                'city'          => checkEmpty($customer, 'city', ''),
                'state'         => checkEmpty($customer, 'state', ''),
                'country'       => checkEmpty($customer, 'country', ''),
                'pin_code'      => checkEmpty($customer, 'pin_code', ''),
                'gst_number'    => checkEmpty($customer, 'gst_number', ''),
                'gst_treatment' => checkEmpty($customer, 'gst_treatment', ''),
                'email'         => checkEmpty($customer, 'email', '')
            ];
        }

        return $customer_data;
    }

    public function createCustomer($data)
    {
        $customer = $this->customer->create([
            'company_name'  => $data['company_name'],
            'customer_name' => $data['customer_name'],
            'phone'         => $data['phone'],
            'address'       => $data['address'],
            'city'          => $data['city'],
            'state'         => $data['state'],
            'country'       => $data['country'],
            'pin_code'      => $data['pin_code'],
            'gst_number'    => $data['gst_number'],
            'gst_treatment' => $data['gst_treatment'],
            'email'         => $data['email'],
            'added_by'      => $data['user_id']
        ]);

        return [
            'id'            => $customer->id,
            'company_name'  => $customer->company_name,
            'customer_name' => $customer->customer_name,
        ];
    }
}
