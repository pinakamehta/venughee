<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\BankRequest;
use App\Repositories\API\BankRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Class BanksController
 * @package App\Http\Controllers\API
 */
class BanksController extends Controller
{
    /**
     * @var BankRepository
     */
    private $bank_repository;

    /**
     * BanksController constructor.
     */
    public function __construct()
    {
        $this->bank_repository = new BankRepository();
    }

    /**
     * Display a listing of the resource.
     *
     * @param BankRequest $request
     * @return JsonResponse
     */
    public function index(BankRequest $request)
    {
        try {
            $banks = $this->bank_repository->getBanks($request->all());

            if (empty($banks)) {
                return prepare_response(200, false, 'There is no bank created by you');
            }
            return prepare_response(200, true, 'Bank list have been retrieve', $banks);
        } catch (Exception $e) {
            report($e);
            Log::channel('slack')->critical($request->getRequestUri(), $request->all());
            return prepare_response(500, false, 'Sorry Something was wrong.!');
        }
    }

    /**
     * @param BankRequest $request
     * @return JsonResponse
     */
    public function store(BankRequest $request)
    {
        DB::beginTransaction();
        try {
            $this->bank_repository->addNewBank($request->all());
            DB::commit();
            return prepare_response(200, true, 'Bank has been created');
        } catch (Exception $e) {
            DB::rollBack();
            report($e);
            Log::channel('slack')->critical($request->getRequestUri(), $request->all());
            return prepare_response(500, false, 'Sorry Something was wrong.!');
        }
    }

    /**
     * @param BankRequest $request
     * @param $bank_id
     * @return JsonResponse
     */
    public function show(BankRequest $request, $bank_id)
    {
        try {
            $bank_detail = $this->bank_repository->getBankDetail($request->all(), $bank_id);

            if (empty($bank_detail)) {
                return prepare_response(200, false, 'Bank detail not found');
            }
                return prepare_response(200, true, 'Bank details have been retrieve', $bank_detail);
        } catch (Exception $e) {
            report($e);
            Log::channel('slack')->critical($request->getRequestUri(), $request->all());
            return prepare_response(500, false, 'Sorry Something was wrong.!');
        }
    }

    /**
     * @param BankRequest $request
     * @param $bank_id
     * @return JsonResponse
     */
    public function update(BankRequest $request, $bank_id)
    {
        DB::beginTransaction();
        try {
            $this->bank_repository->updateBank($request->all(), $bank_id);
            DB::commit();
            return prepare_response(200, true, 'Bank details have been updated');
        } catch (Exception $e) {
            DB::rollBack();
            report($e);
            Log::channel('slack')->critical($request->getRequestUri(), $request->all());
            return prepare_response(500, false, 'Sorry Something was wrong.!');
        }
    }

    /**
     * @param BankRequest $request
     * @param $bank_id
     * @return JsonResponse
     */
    public function destroy(BankRequest $request, $bank_id)
    {
        DB::beginTransaction();
        try {
            $this->bank_repository->deleteBank($request->all(), $bank_id);
            DB::commit();
            return prepare_response(200, true, 'Bank has been deleted');
        } catch (Exception $e) {
            DB::rollBack();
            report($e);
            Log::channel('slack')->critical($request->getRequestUri(), $request->all());
            return prepare_response(500, false, 'Sorry Something was wrong.!');
        }
    }
}
