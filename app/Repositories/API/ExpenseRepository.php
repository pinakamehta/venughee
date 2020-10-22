<?php

namespace App\Repositories\API;

use App\Models\API\Transaction;
use Exception;

class ExpenseRepository
{
    private $transaction;

    public function __construct()
    {
        $this->transaction = new Transaction();
    }

    public function expenseList($data)
    {
        $page         = checkEmpty($data, 'page', 1);
        $limit        = checkEmpty($data, 'limit', 25);
        $offset       = ($page - 1) * $limit;
        $transactions = $this->transaction->with(['bank', 'expense_type'])
            ->where('created_by', $data['user_id'])
            ->where('expense_type_id', '>', 0);

        if (!empty($data['transaction_date'])) {
            $transactions = $transactions->where('transaction_date', $data['transaction_date']);
        }

        $transactions = $transactions->orderBy('transaction_date')
            ->limit($limit)
            ->offset($offset)
            ->get();

        $transaction_info = [];

        if (!empty($transactions->toArray())) {
            foreach ($transactions as $transaction) {
                $transaction_info[ $transaction->transaction_date ][] = [
                    'transaction_id'   => $transaction->id,
                    'payment_through'  => ($transaction->bank_id == 0) ? 'Cash' : 'Bank',
                    'bank_id'          => $transaction->bank_id,
                    'bank_name'        => (!empty($transaction->bank)) ? $transaction->bank->bank_name : '',
                    'expense_type_id'  => $transaction->expense_type_id,
                    'expense_type'     => (!empty($transaction->expense_type)) ? $transaction->expense_type->expense_type_name : '',
                    'transaction_date' => $transaction->transaction_date,
                    'amount'           => $transaction->debit,
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

    public function addExpense($data)
    {
        $this->transaction->create([
            'bank_id'          => $data['bank_id'],
            'expense_type_id'  => $data['expense_type'],
            'transaction_date' => $data['transaction_date'],
            'debit'            => $data['amount'],
            'notes'            => $data['notes'],
            'created_by'       => $data['user_id'],
        ]);
    }

    public function expenseDetail($transaction_id)
    {
        $transaction = $this->transaction->where('id', $transaction_id)->first();

        if (empty($transaction)) {
            throw new Exception('Expense details not found for given date');
        }

        return [
            'transaction_id'   => $transaction->id,
            'payment_through'  => ($transaction->bank_id == 0) ? 'Cash' : 'Bank',
            'bank_id'          => $transaction->bank_id,
            'bank_name'        => (!empty($transaction->bank)) ? $transaction->bank->bank_name : '',
            'expense_type_id'  => $transaction->expense_type_id,
            'expense_type'     => (!empty($transaction->expense_type)) ? $transaction->expense_type->expense_type_name : '',
            'transaction_date' => $transaction->transaction_date,
            'amount'           => $transaction->debit,
            'notes'            => checkEmpty($transaction, 'notes', '')
        ];
    }

    public function updateExpense($data, $transaction_id)
    {
        $this->transaction->where('id', $transaction_id)->update([
            'bank_id'          => $data['bank_id'],
            'expense_type_id'  => $data['expense_type'],
            'transaction_date' => $data['transaction_date'],
            'debit'            => $data['amount'],
            'notes'            => $data['notes']
        ]);
    }

    public function deleteExpense($transaction_id)
    {
        $this->transaction->where('id', $transaction_id)->delete();
    }
}
