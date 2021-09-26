<?php

namespace App\Repositories\API;

use App\Models\Customer;
use App\Models\Invoice;
use Exception;

class CustomerRepository
{
    private $customer, $invoice;

    public function __construct()
    {
        $this->customer = new Customer();
        $this->invoice  = new Invoice();
    }

    public function getCustomers($data)
    {
        $customers = $this->customer
            ->where('type', $data['type'])
            ->where('added_by', $data['user_id'])
            ->get([
                'id',
                'customer_name',
                'type',
                'total_debit',
                'phone',
                'address',
                'city',
                'state',
                'country',
                'pin_code',
                'gst_number',
                'gst_treatment',
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
                'type'          => $customer->type,
                'total_debit'   => $customer->total_debit,
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
//            'company_name'  => $data['company_name'],
            'customer_name' => $data['customer_name'],
            'type'          => checkEmpty($data, 'type', 'customer'),
            'phone'         => $data['phone'],
            'address'       => $data['address'],
            'city'          => $data['city'],
            'state'         => $data['state'],
            'country'       => $data['country'],
            'pin_code'      => $data['pin_code'],
            'gst_number'    => $data['gst_number'],
            'gst_treatment' => $data['gst_treatment'],
            'email'         => checkEmpty($data, 'email', ''),
            'added_by'      => $data['user_id']
        ]);

        return [
            'id'            => $customer->id,
            'company_name'  => $customer->company_name,
            'customer_name' => $customer->customer_name,
        ];
    }

    public function getCustomerInvoices($data)
    {
        $page  = checkEmpty($data, 'page', 1);
        $limit = checkEmpty($data, 'limit', 25);

        $offset = ($page - 1) * $limit;

        $invoices = $this->invoice->where('invoice_type', 'sales')
            ->where('added_by', $data['user_id'])
            ->where('customer_id', $data['customer_id'])
            ->orderBy('id', 'DESC')
            ->limit($limit)
            ->offset($offset)
            ->get();

        $invoice_data = [];

        if (!empty($invoices)) {
            foreach ($invoices as $invoice) {
                $invoice_data[ $invoice->invoice_date ][] = [
                    'id'              => $invoice->id,
                    'invoice_number'  => !empty($invoice->custom_invoice_number) ? $invoice->custom_invoice_number : $invoice->invoice_number,
                    'invoice_type'    => $invoice->invoice_type,
                    'payment_mode'    => $invoice->payment_mode,
                    'items'           => checkEmpty($invoice, 'items', ''),
                    'tax_amount'      => $invoice->tax_amount,
                    'sub_total'       => $invoice->sub_total,
                    'grand_total'     => $invoice->grand_total,
                    'terms_condition' => checkEmpty($invoice, 'terms_condition', ''),
                ];
            }
        }

        $prepare_invoice_data = [];

        if (!empty($invoice_data)) {
            foreach ($invoice_data as $invoice_date => $invoice) {
                $prepare_invoice_data[] = [
                    'invoice_date' => $invoice_date,
                    'count'        => count($invoice_data[ $invoice_date ]),
                    'invoice_data' => $invoice_data[ $invoice_date ]
                ];
            }
        }
        $customer_detail = $this->customer->where('id', $data['customer_id'])->first(['total_debit']);

        return [
            'customer_pending_balance' => checkEmpty($customer_detail, 'total_debit', 0),
            'invoices'                 => $prepare_invoice_data
        ];
    }
}
