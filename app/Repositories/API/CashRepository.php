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
        $page   = checkEmpty($data, 'page', 1);
        $limit  = checkEmpty($data, 'limit', 25);
        $offset = ($page - 1) * $limit;

        $transactions = $this->transaction->where('bank_id', 0)
            ->where('expense_type_id', 0)
            ->where('credit', '>', 0)
            ->where('debit', 0);

        if (!empty($data['transaction_date'])) {
            $transactions = $transactions->where('transaction_date', $data['transaction_date']);
        }

        $transactions = $transactions->where('created_by', $data['user_id'])
            ->orderBy('transaction_date')
            ->limit($limit)
            ->offset($offset)
            ->get();

        $transaction_data = [];

        if (!empty($transactions->toArray())) {
            foreach ($transactions as $transaction) {
                $transaction_data[] = [
                    'transaction_id'   => $transaction->id,
                    'transaction_date' => $transaction->transaction_date,
                    'amount'           => $transaction->credit,
                    'notes'            => checkEmpty($transaction, 'notes', '')
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
            'type'             => 'cash',
            'created_by'       => $data['user_id']
        ]);
    }

    public function cashPaymentDetail($transaction_id)
    {
        $transaction = $this->transaction->where('id', $transaction_id)->first();

        if (empty($transaction)) {
            throw new Exception('Cash transaction detail not found for given date');
        }

        return [
            'transaction_id'   => $transaction->id,
            'payment_through'  => 'Cash',
            'transaction_date' => $transaction->transaction_date,
            'amount'           => $transaction->credit,
            'notes'            => checkEmpty($transaction, 'notes', '')
        ];
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
