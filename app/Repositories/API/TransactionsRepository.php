<?php

namespace App\Repositories\API;

use App\Models\Customer;
use App\Models\Transaction;
use Exception;

class TransactionsRepository
{
    private $transaction, $customer;

    public function __construct()
    {
        $this->transaction = new Transaction();
        $this->customer    = new Customer();
    }

    public function transactions($data)
    {
        $bank_id = checkEmpty($data, 'bank_id', 0);
        $page    = checkEmpty($data, 'page', 1);
        $limit   = checkEmpty($data, 'limit', 25);

        $offset = ($page - 1) * $limit;

        $transactions = $this->transaction->with([
            'bank',
            'expense_type',
            'invoice',
            'invoice.customer',
            'invoice.branch'
        ])
            ->where('bank_id', $bank_id)
            ->where('created_by', $data['user_id'])
            ->orderBy('transaction_date', 'DESC')
            ->limit($limit)
            ->offset($offset)
            ->get();

        $transaction_data = [];

        if (!empty($transactions)) {
            foreach ($transactions as $transaction) {
                $title = "Petty Cash";

                if (!empty($transaction->expense_type)) {
                    $title = $transaction->expense_type->expense_type_name;
                } else if (!empty($transaction->invoice)) {
                    if (!empty($transaction->invoice->customer)) {
                        $title = $transaction->invoice->customer->customer_name;
                    } else if (!empty($transaction->invoice->branch)) {
                        $title = $transaction->invoice->branch->branch_name;
                    }
                }

                $transaction_data[] = [
                    'transaction_id'   => $transaction->id,
                    'transaction_date' => $transaction->transaction_date,
                    'title'            => $title,
                    'notes'            => checkEmpty($transaction, 'notes', ''),
                    'credit'           => $transaction->credit,
                    'debit'            => $transaction->debit,
                    'type'             => $transaction->type,
                ];
            }
        }

        return $transaction_data;
    }

    public function transactionCreate($data)
    {
        $transaction_type = "cash";
        if (!empty($data['customer_id']) && $data['customer_id'] > 0) {
            $transaction_type = "invoice";

            $customer = $this->customer->where('id', $data['customer_id'])->first();

            if (empty($customer)) {
                throw new Exception("Customer not found for given id");
            }

            $customer->total_debit -= $data['amount'];
            $customer->save();
        }
        $this->transaction->create([
            'transaction_date' => $data['transaction_date'],
            'bank_id'          => checkEmpty($data, 'bank_id', 0),
            'credit'           => $data['amount'],
            'notes'            => $data['notes'],
            'type'             => $transaction_type,
            'created_by'       => $data['user_id']
        ]);

    }

    public function transactionDetail($transaction_id)
    {
        $transaction = $this->transaction->with([
            'bank',
            'expense_type',
            'invoice',
            'invoice.customer',
            'invoice.branch'
        ])
            ->where('id', $transaction_id)
            ->first();

        if (empty($transaction)) {
            throw new Exception("Transaction is not found for given id");
        }


        $title = $bank_name = "Petty Cash";

        if (!empty($transaction->bank)) {
            $bank_name = $transaction->bank->bank_name;
        }

        if (!empty($transaction->expense_type)) {
            $title = $transaction->expense_type->expense_type_name;
        } else if (!empty($transaction->invoice)) {
            if (!empty($transaction->invoice->customer)) {
                $title = $transaction->invoice->customer->customer_name;
            } else if (!empty($transaction->invoice->branch)) {
                $title = $transaction->invoice->branch->branch_name;
            }
        }

        return [
            'transaction_id'   => $transaction->id,
            'transaction_date' => $transaction->transaction_date,
            'title'            => $title,
            'bank_name'        => $bank_name,
            'notes'            => checkEmpty($transaction, 'notes', ''),
            'credit'           => $transaction->credit,
            'debit'            => $transaction->debit,
            'type'             => $transaction->type,
        ];
    }

    public function updateTransaction($data)
    {
        $transaction = $this->transaction->where('id', $data['transaction_id'])
            ->first();

        if (empty($transaction)) {
            throw new Exception("Transaction is not found for given id");
        }

        if ($transaction->type != "cash") {
            throw new Exception("This transaction can not be edit");
        }

        $transaction->credit = (!empty($data['amount'])) ? $data['amount'] : $transaction->credit;
        $transaction->notes  = (!empty($data['notes'])) ? $data['notes'] : $transaction->notes;
        $transaction->save();
    }

    public function deleteTransaction($transaction_id)
    {
        $transaction = $this->transaction->where('id', $transaction_id);

        if (empty($transaction->first())) {
            throw new Exception("Transaction is not found for given id");
        }
        $transaction_obj = $transaction->first();
        if ($transaction_obj->type != "cash") {
            throw new Exception("This transaction can not be delete");
        }

        $transaction->delete();
    }
}
