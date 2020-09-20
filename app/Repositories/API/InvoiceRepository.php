<?php

namespace App\Repositories\API;

use App\Models\Invoice;

class InvoiceRepository
{
    private $invoice;

    public function __construct()
    {
        $this->invoice = new Invoice();
    }

    public function getNextInvoiceId($data)
    {
        $last_invoice = $this->invoice->where('invoice_type', $data['type'])
            ->orderBy('id', 'DESC')
            ->first([
                'invoice_number'
            ]);

        $next_invoice_number = ($data['type'] == 'purchase') ? 'PI00001' : 'SI00001';

        if (!empty($last_invoice)) {
            $prefix              = substr($last_invoice->invoice_number, 0, 2);
            $invoice_digits      = substr($last_invoice->invoice_number, 2);
            $next_invoice_number = $prefix . str_pad($invoice_digits + 1, 5, '0', STR_PAD_LEFT);
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
        $invoice_obj = $this->invoice->where('invoice_number', $invoice_id)->first();

        if (empty($invoice_obj)) {
            return true;
        }

        return false;
    }
}
