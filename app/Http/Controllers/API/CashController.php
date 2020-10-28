<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\CashRequest;
use App\Repositories\API\CashRepository;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CashController extends Controller
{
    private $cashRepository;

    public function __construct()
    {
        $this->cashRepository = new CashRepository();
    }

    public function index(CashRequest $request)
    {
        try {
            $transactions = $this->cashRepository->cashPaymentTransactions($request->all());

            if (empty($transactions)) {
                return prepare_response(200, false, 'There is no cash transaction made by you');
            }

            return prepare_response(200, true, 'Here are your all cash transactions', $transactions);
        } catch (Exception $e) {
            report($e);
            return prepare_response(500, false, 'Sorry Something was wrong.!');
        }
    }

    public function store(CashRequest $request)
    {
        DB::beginTransaction();
        try {
            $this->cashRepository->addCashPayment($request->all());

            DB::commit();
            return prepare_response(200, true, 'Cash payment has been added');
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            Log::channel('slack')->critical($request->getRequestUri(), $request->all());
            return prepare_response(500, false, 'Sorry Something was wrong.!');
        }
    }

    public function show($transaction_id)
    {
        try {
            $transaction_detail = $this->cashRepository->cashPaymentDetail($transaction_id);
            return prepare_response(200, true, 'Cash payment detail has been retrieve', $transaction_detail);
        } catch (\Exception $e) {
            report($e);
            return prepare_response(500, false, 'Sorry Something was wrong.!');
        }
    }

    public function update(CashRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $this->cashRepository->updateTransaction($request->all(), $id);

            DB::commit();
            return prepare_response(200, true, 'Cash payment has been updated');
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            echo $e->getMessage() . " " . $e->getFile() . " " . $e->getLine();die;
            Log::channel('slack')->critical($request->getRequestUri(), $request->all());
            return prepare_response(500, false, 'Sorry Something was wrong.!');
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $this->cashRepository->deleteTransaction($id);

            DB::commit();
            return prepare_response(200, true, 'Cash payment has been deleted');
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return prepare_response(500, false, 'Sorry Something was wrong.!');
        }
    }
}
