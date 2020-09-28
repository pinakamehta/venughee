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
            regenerate:
            $prefix              = substr($last_invoice_number, 0, 2);
            $invoice_digits      = substr($last_invoice_number, 2);
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
            'invoice_type'   => $data['type']
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
        $invoices = $this->invoice;

        if (!empty($data['invoice_date'])) {
            $invoices = $invoices->where('invoice_date', $data['invoice_date']);
        }

        $invoices = $invoices->orderBy('id', 'DESC')
            ->get();

        $invoice_data = [];

        if (!empty($invoices)) {
            foreach ($invoices as $invoice) {
                $invoice_data[] = [
                    'id'              => $invoice->id,
                    'invoice_number'  => !empty($invoice->custom_invoice_number) ? $invoice->custom_invoice_number : $invoice->invoice_number,
                    'invoice_type'    => $invoice->invoice_type,
                    'invoice_date'    => $invoice->invoice_date,
                    'items'           => checkEmpty($invoice, 'items', ''),
                    'tax_amount'      => $invoice->tax_amount,
                    'sub_total'       => $invoice->sub_total,
                    'grand_total'     => $invoice->grand_total,
                    'terms_condition' => checkEmpty($invoice, 'terms_condition', ''),
                ];
            }
        }
        return $invoice_data;
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

        $invoice->invoice_date = $data['invoice_date'];
        $invoice->tax_amount   = checkEmpty($data, 'tax_amount', 0);
        $invoice->sub_total    = checkEmpty($data, 'sub_total', 0);
        $invoice->grand_total  = checkEmpty($data, 'grand_total', 0);
        $invoice->save();
    }

    public function getInvoiceData ($invoice_number)
    {
        $invoice = $this->invoice->where('id', $invoice_number)->first();

        if (empty($invoice)) {
            return [];
        }

        return [
            'id'              => $invoice->id,
            'invoice_number'  => !empty($invoice->custom_invoice_number) ? $invoice->custom_invoice_number : $invoice->invoice_number,
            'invoice_type'    => $invoice->invoice_type,
            'invoice_date'    => $invoice->invoice_date,
            'items'           => checkEmpty($invoice, 'items', ''),
            'tax_amount'      => $invoice->tax_amount,
            'sub_total'       => $invoice->sub_total,
            'grand_total'     => $invoice->grand_total,
            'terms_condition' => checkEmpty($invoice, 'terms_condition', ''),
        ];
    }

    public function deleteInvoice($invoice_number)
    {
        $this->invoice->where('id', $invoice_number)->delete();
    }
}
