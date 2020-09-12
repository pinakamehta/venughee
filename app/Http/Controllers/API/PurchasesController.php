<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\PurchaseRequest;
use App\Repositories\API\PurchaseRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurchasesController extends Controller
{
    private $purchase_repository;

    public function __construct()
    {
        $this->purchase_repository = new PurchaseRepository();
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $purchases = $this->purchase_repository->getPurchases($request->all());

            if (empty($purchases)) {
                return prepare_response(200, false, 'There is no purchase available right now');
            }

            return prepare_response(200, true, 'Purchases have been retrieve successfully', $purchases);
        } catch (Exception $e) {
            Log::error('Exception in Get Purchases', [$e->getMessage() . " " . $e->getFile() . " " . $e->getLine()]);
            return prepare_response(500, false, 'Sorry Something was wrong.!');
        }
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param PurchaseRequest $request
     * @return JsonResponse
     */
    public function store(PurchaseRequest $request)
    {
        DB::beginTransaction();
        try {
            $this->purchase_repository->addPurchase($request->all());
            DB::commit();
            return prepare_response(200, true, 'Purchase has been added successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Exception in Add Purchase', [$e->getMessage() . " " . $e->getFile() . " " . $e->getLine()]);
            return prepare_response(500, false, 'Sorry Something was wrong.!');
        }
    }
}
