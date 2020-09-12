<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\SalesRequest;
use App\Repositories\API\SaleRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SalesController extends Controller
{
    private $sales_repository;

    public function __construct()
    {
        $this->sales_repository = new SaleRepository();
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $sales = $this->sales_repository->getSales($request->all());

            if (empty($sales)) {
                return prepare_response(200, false, 'There is no sales available right now');
            }

            return prepare_response(200, true, 'Sales have been retrieve successfully', $sales);
        } catch (Exception $e) {
            Log::error('Exception in Get Sales', [$e->getMessage() . " " . $e->getFile() . " " . $e->getLine()]);
            return prepare_response(500, false, 'Sorry Something was wrong.!');
        }
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param SalesRequest $request
     * @return JsonResponse
     */
    public function store(SalesRequest $request)
    {
        DB::beginTransaction();
        try {
            $this->sales_repository->addSales($request->all());
            DB::commit();
            return prepare_response(200, true, 'Sales has been added successfully');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Exception in Add Sales', [$e->getMessage() . " " . $e->getFile() . " " . $e->getLine()]);
            return prepare_response(500, false, 'Sorry Something was wrong.!');
        }
    }
}
