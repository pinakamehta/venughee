<?php

namespace App\Repositories\API;

use App\Models\Invoice;
use Exception;

class InvoiceRepository
{
    private $invoice;

    public function __construct()
    {
        $this->invoice = new Invoice();
    }

    public function getNextInvoiceId($data)
    {
        $last_invoice        = $this->invoice->where('invoice_type', $data['type'])
            ->orderBy('id', 'DESC')
            ->first([
                'invoice_number'
            ]);
        $last_invoice_number = !empty($last_invoice) ? $last_invoice->invoice_number : '';
        $next_invoice_number = ($data['type'] == 'purchase') ? 'PI00001' : 'SI00001';

        if (!empty($last_invoice)) {
            $next_invoice_number = $last_invoice_number;
            regenerate:
            $prefix              = substr($next_invoice_number, 0, 2);
            $invoice_digits      = substr($next_invoice_number, 2);
            $next_invoice_number = $prefix . str_pad($invoice_digits + 1, 5, '0', STR_PAD_LEFT);

            $invoice_number_detail = $this->invoice->where('invoice_number', $next_invoice_number)
                ->orWhere('custom_invoice_number', $next_invoice_number)
                ->first();

            if (!empty($invoice_number_detail)) {
                goto regenerate;
            }
        }

        $next_invoice = $this->invoice->create([
            'invoice_number' => $next_invoice_number,
            'invoice_type'   => $data['type'],
            'added_by'       => $data['user_id']
        ]);

        return [
            'id'             => $next_invoice->id,
            'invoice_number' => $next_invoice_number,
        ];
    }

    public function validateInvoiceNumber($invoice_id)
    {
        $invoice_obj = $this->invoice->where('invoice_number', $invoice_id)
            ->orWhere('custom_invoice_number', $invoice_id)
            ->first();

        if (empty($invoice_obj)) {
            return true;
        }

        return false;
    }

    public function getInvoices($data)
    {
        $page         = checkEmpty($data, 'page', 1);
        $limit        = checkEmpty($data, 'limit', 25);
        $invoice_type = checkEmpty($data, 'invoice_type', 'sales');

        $offset = ($page - 1) * $limit;

        $invoices = $this->invoice->with(['customer'])
            ->where('invoice_type', $invoice_type)
            ->where('added_by', $data['user_id'])
            ->where(function ($query) {
                $query->where('customer_id', '>', 0)
                    ->orWhere('branch_id', '>', 0);
            });

        if (!empty($data['invoice_date'])) {
            $invoices = $invoices->where('invoice_date', $data['invoice_date']);
        }

        $invoices = $invoices->orderBy('id', 'DESC')
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
                    'customer'        => [
                        'customer_id'   => !empty($invoice->customer) ? $invoice->customer->id : 0,
                        'company_name'  => !empty($invoice->customer) ? checkEmpty($invoice->customer, 'company_name', '') : '',
                        'customer_name' => !empty($invoice->customer) ? checkEmpty($invoice->customer, 'customer_name', '') : '',
                    ]
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

        return $prepare_invoice_data;
    }

    public function editInvoice($data, $invoice_id)
    {
        $invoice = $this->invoice->where('id', $invoice_id)->first();

        if (empty($invoice)) {
            throw new Exception('Invoice number is invalid try again');
        }

        if ($invoice->invoice_number != $data['invoice_number']) {
            $invoice->custom_invoice_number = $data['invoice_number'];
        }

        if (!empty($data['items'])) {
            $invoice->items = $data['items'];
        }

        if (!empty($data['terms_condition'])) {
            $invoice->terms_condition = $data['terms_condition'];
        }

        if ($data['invoice_for'] == 'customer') {
            $invoice->customer_id = $data['consumer_id'];
        } else {
            $invoice->branch_id = $data['consumer_id'];
        }

        $invoice->invoice_date = $data['invoice_date'];
        $invoice->payment_mode = checkEmpty($data, 'payment_mode', 'cash');
        $invoice->tax_amount   = checkEmpty($data, 'tax_amount', 0);
        $invoice->sub_total    = checkEmpty($data, 'sub_total', 0);
        $invoice->grand_total  = checkEmpty($data, 'grand_total', 0);
        $invoice->save();
    }

    public function getInvoiceData($invoice_number)
    {
        $invoice = $this->invoice->with(['customer'])->where('id', $invoice_number)->first();

        if (empty($invoice)) {
            return [];
        }

        return [
            'id'              => $invoice->id,
            'invoice_number'  => !empty($invoice->custom_invoice_number) ? $invoice->custom_invoice_number : $invoice->invoice_number,
            'invoice_type'    => $invoice->invoice_type,
            'payment_mode'    => $invoice->payment_mode,
            'invoice_date'    => $invoice->invoice_date,
            'items'           => checkEmpty($invoice, 'items', ''),
            'tax_amount'      => $invoice->tax_amount,
            'sub_total'       => $invoice->sub_total,
            'grand_total'     => $invoice->grand_total,
            'terms_condition' => checkEmpty($invoice, 'terms_condition', ''),
            'customer'        => [
                'id'            => !empty($invoice->customer) ? $invoice->customer->id : '',
                'company_name'  => !empty($invoice->customer) ? checkEmpty($invoice->customer, 'company_name', '') : '',
                'customer_name' => !empty($invoice->customer) ? checkEmpty($invoice->customer, 'customer_name', '') : '',
                'phone'         => !empty($invoice->customer) ? checkEmpty($invoice->customer, 'phone', '') : '',
                'email'         => !empty($invoice->customer) ? checkEmpty($invoice->customer, 'email', '') : '',
                'gst_treatment' => !empty($invoice->customer) ? checkEmpty($invoice->customer, 'gst_treatment', '') : '',
                'gst_number'    => !empty($invoice->customer) ? checkEmpty($invoice->customer, 'gst_number', '') : '',
                'address'       => !empty($invoice->customer) ? checkEmpty($invoice->customer, 'address', '') : '',
                'city'          => !empty($invoice->customer) ? checkEmpty($invoice->customer, 'city', '') : '',
                'state'         => !empty($invoice->customer) ? checkEmpty($invoice->customer, 'state', '') : '',
                'country'       => !empty($invoice->customer) ? checkEmpty($invoice->customer, 'country', '') : '',
                'pin_code'      => !empty($invoice->customer) ? checkEmpty($invoice->customer, 'pin_code', '') : '',
            ]
        ];
    }

    public function deleteInvoice($invoice_number)
    {
        $this->invoice->where('id', $invoice_number)->delete();
    }
}
