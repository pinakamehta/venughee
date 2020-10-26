<?php

namespace App\Repositories\API;

use App\Models\Bank;
use App\Models\Transaction;
use Exception;
use Illuminate\Support\Facades\DB;

class BankRepository
{
    private $bank, $transaction;

    public function __construct()
    {
        $this->bank        = new Bank();
        $this->transaction = new Transaction();
    }

    public function getBanks($data)
    {
        $banks = $this->bank->where('added_by', $data['user_id'])
            ->get();

        $bank_data = [];

        $cash_transactions = $this->transaction->where('bank_id', 0)
            ->where('expense_type_id', 0)
            ->where('credit', '>', 0)
            ->where('debit', 0)
            ->where('created_by', $data['user_id'])
            ->select(DB::raw(DB::raw("(sum(credit) - sum(debit)) as total")))
            ->first();

        $bank_data[] = [
            'id'             => 0,
            'account_name'   => 'Petty Cash',
            'account_code'   => '',
            'account_number' => '',
            'bank_name'      => '',
            'description'    => '',
            'balance'        => checkEmpty($cash_transactions, 'total', 0)
        ];

        if (!empty($banks->toArray())) {
            foreach ($banks as $key => $bank) {
                $bank_data[] = [
                    'id'             => $bank->id,
                    'account_name'   => $bank->account_name,
                    'account_code'   => $bank->account_code,
                    'account_number' => $bank->account_number,
                    'bank_name'      => $bank->bank_name,
                    'description'    => $bank->description,
                    'balance'        => bank_balance($bank->id)
                ];
            }
        }

        return $bank_data;
    }

    public function getBankDetail($data, $bank_id)
    {
        $bank = $this->bank->where('added_by', $data['user_id'])
            ->where('id', $bank_id)
            ->first();

        $bank_data = [];

        if (!empty($bank->toArray())) {
            $bank_data = [
                'id'             => $bank->id,
                'account_name'   => $bank->account_name,
                'account_code'   => $bank->account_code,
                'account_number' => $bank->account_number,
                'bank_name'      => $bank->bank_name,
                'description'    => $bank->description,
                'balance'        => bank_balance($bank->id)
            ];
        }

        return $bank_data;
    }

    public function addNewBank($data)
    {
        $this->bank->create([
            'account_name'   => $data['account_name'],
            'account_code'   => $data['account_code'],
            'account_number' => $data['account_number'],
            'bank_name'      => $data['bank_name'],
            'description'    => $data['description'],
            'added_by'       => $data['user_id']
        ]);
    }

    public function updateBank($data, $bank_id)
    {
        $bank_obj = $this->bank->where('added_by', $data['user_id'])
            ->where('id', $bank_id);

        $bank = $bank_obj->first();

        if (empty($bank->toArray())) {
            throw new Exception('Bank id was invalid');
        }

        $bank_obj->update([
            'account_name'   => checkEmpty($data, 'account_name', $bank->account_name),
            'account_code'   => checkEmpty($data, 'account_code', $bank->account_code),
            'account_number' => checkEmpty($data, 'account_number', $bank->account_number),
            'bank_name'      => checkEmpty($data, 'bank_name', $bank->bank_name),
            'description'    => checkEmpty($data, 'description', $bank->description),
        ]);
    }

    public function deleteBank($data, $bank_id)
    {
        $bank_obj = $this->bank->where('added_by', $data['user_id'])
            ->where('id', $bank_id);

        $bank = $bank_obj->first();

        if (empty($bank->toArray())) {
            throw new Exception('Bank id was invalid');
        }

        $bank_obj->delete();
    }
}
