<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Repositories\API\TransactionsRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionsController extends Controller
{
    private $transaction_repository;

    public function __construct()
    {
        $this->transaction_repository = new TransactionsRepository();
    }

    public function index(Request $request)
    {
        try {
            $transactions = $this->transaction_repository->transactions($request->all());

            if (empty($transactions)) {
                return prepare_response(200, false, 'There is no transaction available right now');
            }
            return prepare_response(200, true, 'All transactions have been retrieve', $transactions);
        } catch (Exception $e) {
            report($e);
            return prepare_response(500, false, $e->getMessage());
        }
    }

    public function show($transaction)
    {
        try {
            $transactions = $this->transaction_repository->transactionDetail($transaction);

            return prepare_response(200, true, 'All transactions have been retrieve', $transactions);
        } catch (Exception $e) {
            report($e);
            return prepare_response(500, false, $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->all();
            $this->transaction_repository->transactionCreate($data);
            DB::commit();
            return prepare_response(200, true, 'Transaction has been created');
        } catch (Exception $e) {
            DB::rollBack();
            report($e);
            return prepare_response(500, false, $e->getMessage());
        }
    }

    public function update(Request $request, $transaction)
    {
        DB::beginTransaction();
        try {
            $data                   = $request->all();
            $data['transaction_id'] = $transaction;
            $this->transaction_repository->updateTransaction($data);
            DB::commit();
            return prepare_response(200, true, 'Transaction has been updated');
        } catch (Exception $e) {
            DB::rollBack();
            report($e);
            return prepare_response(500, false, $e->getMessage());
        }
    }

    public function destroy($transaction)
    {
        DB::beginTransaction();
        try {
            $this->transaction_repository->deleteTransaction($transaction);
            DB::commit();
            return prepare_response(200, true, 'Transaction has been deleted');
        } catch (Exception $e) {
            DB::rollBack();
            report($e);
            return prepare_response(500, false, $e->getMessage());
        }
    }
}
