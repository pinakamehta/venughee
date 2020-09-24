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

    public function getCustomers()
    {
        $customers = $this->customer->get([
            'id',
            'company_name',
            'first_name',
            'last_name',
            'phone',
            'address',
            'city',
            'state',
            'country',
            'pin_code',
            'gst_number',
            'email'
        ]);

        if (empty($customers)) {
            throw new Exception('There is no customer available try to add new customer');
        }

        $customer_data = [];

        foreach ($customers as $customer) {
            $customer_data[] = [
                'id'           => $customer->id,
                'company_name' => checkEmpty($customer, 'company_name', ''),
                'first_name'   => checkEmpty($customer, 'first_name', ''),
                'last_name'    => checkEmpty($customer, 'last_name', ''),
                'phone'        => checkEmpty($customer, 'phone', ''),
                'address'      => checkEmpty($customer, 'address', ''),
                'city'         => checkEmpty($customer, 'city', ''),
                'state'        => checkEmpty($customer, 'state', ''),
                'country'      => checkEmpty($customer, 'country', ''),
                'pin_code'     => checkEmpty($customer, 'pin_code', ''),
                'gst_number'   => checkEmpty($customer, 'gst_number', ''),
                'email'        => checkEmpty($customer, 'email', '')
            ];
        }

        return $customer_data;
    }

    public function createCustomer($data)
    {
        $customer = $this->customer->create([
            'company_name' => $data['company_name'],
            'first_name'   => $data['first_name'],
            'last_name'    => $data['last_name'],
            'phone'        => $data['phone'],
            'address'      => $data['address'],
            'city'         => $data['city'],
            'state'        => $data['state'],
            'country'      => $data['country'],
            'pin_code'     => $data['pin_code'],
            'gst_number'   => $data['gst_number'],
            'email'        => $data['email']
        ]);

        return [
            'id'           => $customer->id,
            'company_name' => $customer->company_name,
            'first_name'   => $customer->first_name,
            'last_name'    => $customer->last_name
        ];
    }
}
