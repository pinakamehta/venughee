<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\ExpenseRequest;
use App\Repositories\API\ExpenseRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExpensesController extends Controller
{
    private $expense_repository;

    public function __construct()
    {
        $this->expense_repository = new ExpenseRepository();
    }

    /**
     * Display a listing of the resource.
     *
     * @param ExpenseRequest $request
     * @return JsonResponse
     */
    public function index(ExpenseRequest $request)
    {
        try {
            $transactions = $this->expense_repository->expenseList($request->all());

            if (empty($transactions)) {
                return prepare_response(200, false, 'There is no expense available right now');
            }
            return prepare_response(200, true, 'All expenses have been retrieve successfully', $transactions);
        } catch (Exception $e) {
            report($e);
            return prepare_response(500, false, 'Sorry Something was wrong.!');
        }
    }

    public function show($id)
    {
        try {
            $transaction = $this->expense_repository->expenseDetail($id);

            return prepare_response(200, true, 'Expense details have been retrieve successfully', $transaction);
        } catch (Exception $e) {
            report($e);
            return prepare_response(500, false, 'Sorry Something was wrong.!');
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ExpenseRequest $request
     * @return JsonResponse
     */
    public function store(ExpenseRequest $request)
    {
        DB::beginTransaction();
        try {
            $this->expense_repository->addExpense($request->all());
            DB::commit();
            return prepare_response(200, true, 'New expense has been added');
        } catch (Exception $e) {
            DB::rollBack();
            echo $e->getMessage() . " " . $e->getFile() . " " . $e->getLine();die;
            report($e);
            Log::channel('slack')->critical($request->getRequestUri(), $request->all());
            return prepare_response(500, false, 'Sorry Something was wrong.!');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param ExpenseRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(ExpenseRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $this->expense_repository->updateExpense($request->all(), $id);
            DB::commit();
            return prepare_response(200, true, 'Expense has been updated');
        } catch (Exception $e) {
            DB::rollBack();
            report($e);
            Log::channel('slack')->critical($request->getRequestUri(), $request->all());
            return prepare_response(500, false, 'Sorry Something was wrong.!');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $this->expense_repository->deleteExpense($id);
            DB::commit();
            return prepare_response(200, true, 'Expense has been deleted');
        } catch (Exception $e) {
            DB::rollBack();
            report($e);
            Log::channel('slack')->critical($request->getRequestUri(), $request->all());
            return prepare_response(500, false, 'Sorry Something was wrong.!');
        }
    }
}
