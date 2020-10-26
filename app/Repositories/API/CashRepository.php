<?php

namespace App\Repositories\API;

use App\Models\Transaction;
use Exception;

class CashRepository
{
    private $transaction;

    public function __construct()
    {
        $this->transaction = new Transaction();
    }

    public function cashPaymentTransactions($data)
    {
        $transactions = $this->transaction->where('bank_id', 0)
            ->where('expense_type_id', 0)
            ->where('credit', '>', 0)
            ->where('debit', 0)
            ->where('created_by', $data['user_id'])
            ->get();

        $transaction_info = [];

        if (!empty($transactions->toArray())) {
            foreach ($transactions as $transaction) {
                $transaction_info[ $transaction->transaction_date ][] = [
                    'transaction_id'   => $transaction->id,
                    'transaction_date' => $transaction->transaction_date,
                    'amount'           => $transaction->credit,
                    'notes'            => checkEmpty($transaction, 'notes', '')
                ];
            }
        }

        $transaction_data = [];

        if (!empty($transaction_info)) {
            foreach ($transaction_info as $transaction_date => $transaction) {
                $transaction_data[] = [
                    'transaction_date' => $transaction_date,
                    'count'            => count($transaction_info[ $transaction_date ]),
                    'transaction_data' => $transaction_info[ $transaction_date ]
                ];
            }
        }

        return $transaction_data;
    }

    public function addCashPayment($data)
    {
        $this->transaction->create([
            'transaction_date' => $data['transaction_date'],
            'credit'           => $data['amount'],
            'notes'            => $data['notes'],
            'created_by'       => $data['user_id']
        ]);
    }

    public function updateTransaction($data, $transaction_id)
    {
        $transaction = $this->transaction->where('id', $transaction_id);

        $transaction_detail = $transaction->first();
        if (empty($transaction_detail)) {
            throw new Exception("Invalid transaction id");
        }

        $transaction->update([
            'transaction_date' => $data['transaction_date'],
            'credit'           => $data['amount'],
            'notes'            => checkEmpty($data, 'notes', $transaction_detail->notes),
        ]);
    }

    public function deleteTransaction($transaction_id)
    {
        $this->transaction->where('id', $transaction_id)->delete();
    }

}
