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

        $transactions = $this->transaction->where('bank_id', $bank_id)
            ->where('created_by', $data['user_id'])
            ->orderBy('transaction_date', 'DESC')
            ->limit($limit)
            ->offset($offset)
            ->get();

        $transaction_data = [];

        if (!empty($transactions)) {
            foreach ($transactions as $transaction) {
                $transaction_data[] = [
                    'transaction_id'   => $transaction->id,
                    'transaction_date' => $transaction->transaction_date,
                    'notes'            => $transaction->notes,
                    'credit'           => $transaction->credit,
                    'debit'            => $transaction->debit,
                ];
            }
        }

        return $transaction_data;
    }
}
