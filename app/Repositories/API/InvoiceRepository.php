<?php

namespace App\Repositories\API;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\StockTransaction;
use App\Models\Transaction;
use Carbon\Carbon;
use Exception;

class InvoiceRepository
{
    private $invoice, $transaction, $customer, $stock_transaction, $item;

    public function __construct()
    {
        $this->invoice           = new Invoice();
        $this->transaction       = new Transaction();
        $this->customer          = new Customer();
        $this->item              = new Item();
        $this->stock_transaction = new StockTransaction();
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

        $invoices = $this->invoice->with(['customer', 'branch'])
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
                    ],
                    'branch'          => [
                        'branch_id'   => !empty($invoice->branch) ? $invoice->branch->id : 0,
                        'branch_name' => !empty($invoice->branch) ? checkEmpty($invoice->branch, 'branch_name', '') : '',
                        'address'     => !empty($invoice->branch) ? checkEmpty($invoice->branch, 'address', '') : '',
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

            $item_data = json_decode($data['items']);
            if ($invoice->invoice_type == 'sales') {
                foreach ($item_data as $item) {
                    if (!empty($item->quantity)) {
                        $itemObj = $this->item->where('id', $item->id)->first();

                        if (empty($itemObj)) {
                            throw new Exception('Invalid item id');
                        }

                        if ($itemObj->stock < $item->quantity) {
                            throw new Exception("You have not sufficient stock for " . strtoupper($item->item_name));
                        }

                        if (empty($item->is_update) || $item->is_update == 0) {
                            $itemObj->stock = $itemObj->stock - $item->quantity;
                            $itemObj->save();

                            $this->stock_transaction->create([
                                'invoice_id'    => $invoice_id,
                                'item_id'       => $item->id,
                                'item_quantity' => $item->quantity,
                                'notes'         => "Item deduct from stock for Invoice #" . $data['invoice_number']
                            ]);
                        } else {
                            $stock_transaction = $this->stock_transaction->where("invoice_id", $invoice_id)
                                ->where("item_id", $item->id)
                                ->first();

                            $stock_item_quantity = $stock_transaction->item_quantity;
                            $difference          = $stock_item_quantity - $item->qantity;

                            $itemObj->stock = $itemObj->stock + $difference;
                            $itemObj->save();

                            $stock_transaction->item_quantity = $item->quantity;
                            $stock_transaction->save();
                        }
                    }
                }
            }
        }

        if (!empty($data['terms_condition'])) {
            $invoice->terms_condition = $data['terms_condition'];
        }

        if ($data['invoice_for'] == 'customer') {
            $invoice->customer_id = $data['consumer_id'];
        } else {
            $invoice->branch_id = $data['consumer_id'];
        }
        $invoice->bank_id      = checkEmpty($data, 'bank_id', 0);
        $invoice->invoice_date = $data['invoice_date'];
        $invoice->payment_mode = checkEmpty($data, 'payment_mode', 'Cash');
        $invoice->tax_amount   = checkEmpty($data, 'tax_amount', 0);
        $invoice->sub_total    = checkEmpty($data, 'sub_total', 0);
        $invoice->grand_total  = checkEmpty($data, 'grand_total', 0);
        $invoice->save();

        $transaction = $this->transaction->where('invoice_id', $invoice->id)->first();

        if ($invoice->invoice_type == "purchase") {
            if (empty($transaction)) {
                $this->transaction->create([
                    'transaction_date' => Carbon::now()->format('Y-m-d'),
                    'debit'            => $invoice->grand_total,
                    'invoice_id'       => $invoice->id,
                    'notes'            => "Payment given for Invoice #" . $data['invoice_number'],
                    'created_by'       => $data['user_id']
                ]);
            } else {
                $transaction->debit = $transaction->debit + $data['differentiate_amount'];
                $transaction->save();
            }
        } else {
            if ($invoice->payment_mode == "Cash") {
                if (empty($transaction)) {
                    $this->transaction->create([
                        'bank_id'          => checkEmpty($data, 'bank_id', 0),
                        'transaction_date' => $invoice->invoice_date,
                        'invoice_id'       => $invoice->id,
                        'credit'           => $invoice->grand_total,
                        'notes'            => "Payment received for Invoice #" . $data['invoice_number'],
                        'created_by'       => $data['user_id']
                    ]);
                } else {
                    $transaction->credit = $transaction->credit + $data['differentiate_amount'];
                    $transaction->save();
                }
            } else {
                if ($data['invoice_for'] == 'customer') {
                    $customer_obj = $this->customer->where('id', $data['consumer_id'])->first();

                    if (empty($customer_obj)) {
                        throw new Exception("Customer not found for given id");
                    }

                    $customer_obj->total_debit = $customer_obj->total_debit + $data['differentiate_amount'];
                    $customer_obj->save();
                }
            }
        }

    }

    public function getInvoiceData($invoice_number)
    {
        $invoice = $this->invoice->with(['customer', 'branch'])->where('id', $invoice_number)->first();

        if (empty($invoice)) {
            return [];
        }

        return [
            'id'              => $invoice->id,
            'invoice_number'  => !empty($invoice->custom_invoice_number) ? $invoice->custom_invoice_number : $invoice->invoice_number,
            'invoice_type'    => $invoice->invoice_type,
            'payment_mode'    => $invoice->payment_mode,
            'bank_id'         => $invoice->bank_id,
            'invoice_date'    => $invoice->invoice_date,
            'items'           => checkEmpty($invoice, 'items', ''),
            'tax_amount'      => $invoice->tax_amount,
            'sub_total'       => $invoice->sub_total,
            'grand_total'     => $invoice->grand_total,
            'terms_condition' => checkEmpty($invoice, 'terms_condition', ''),
            'customer'        => [
                'id'            => !empty($invoice->customer) ? $invoice->customer->id : 0,
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
            ],
            'branch'          => [
                'branch_id'   => !empty($invoice->branch) ? $invoice->branch->id : 0,
                'branch_name' => !empty($invoice->branch) ? checkEmpty($invoice->branch, 'branch_name', '') : '',
                'address'     => !empty($invoice->branch) ? checkEmpty($invoice->branch, 'address', '') : '',
            ]
        ];
    }

    public function deleteInvoice($invoice_number)
    {
        $this->invoice->where('id', $invoice_number)->delete();
    }
}
