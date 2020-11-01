<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Admin\ExpenseTypeRequest;
use App\Repositories\API\Admin\ExpenseTypesRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExpenseTypesController extends Controller
{
    private $expense_type_repository;

    public function __construct()
    {
        $this->expense_type_repository = new ExpenseTypesRepository();
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index()
    {
        try {
            $expense_types = $this->expense_type_repository->list();

            if (empty($expense_types)) {
                return prepare_response(200, false, 'There no expense type available right now');
            }
            return prepare_response(200, true, 'Expense types have been retrieve', $expense_types);
        } catch (Exception $e) {
            report($e);
            return prepare_response(500, false, $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ExpenseTypeRequest $request
     * @return JsonResponse
     */
    public function store(ExpenseTypeRequest $request)
    {
        DB::beginTransaction();
        try {
            $this->expense_type_repository->store($request->all());
            DB::commit();
            return prepare_response(200, true, 'Expense Type has been added successfully');
        } catch (Exception $e) {
            DB::rollBack();
            report($e);
            Log::channel('slack')->critical($request->getRequestUri(), $request->all());
            return prepare_response(500, false, $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($id)
    {
        try {
            $expense_type = $this->expense_type_repository->show($id);

            return prepare_response(200, true, 'Expense type details have been retrieve', $expense_type);
        } catch (Exception $e) {
            report($e);
            return prepare_response(500, false, $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param ExpenseTypeRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(ExpenseTypeRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $this->expense_type_repository->update($request->all(), $id);
            DB::commit();
            return prepare_response(200, true, 'Expense Type has been updated successfully');
        } catch (Exception $e) {
            DB::rollBack();
            report($e);
            Log::channel('slack')->critical($request->getRequestUri(), $request->all());
            return prepare_response(500, false, $e->getMessage());
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
            $this->expense_type_repository->delete($id);
            DB::commit();
            return prepare_response(200, true, 'Expense Type has been deleted successfully');
        } catch (Exception $e) {
            DB::rollBack();
            report($e);
            return prepare_response(500, false, $e->getMessage());
        }
    }
}
