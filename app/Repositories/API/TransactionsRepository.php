<?php

namespace App\Repositories\API;

use App\Models\Transaction;

class TransactionsRepository
{
    private $transaction;

    public function __construct()
    {
        $this->transaction = new Transaction();
    }

    public function transaction($data)
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

    public function updateTransaction($data)
    {

    }
}
