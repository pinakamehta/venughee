<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\CustomerRequest;
use App\Repositories\API\CustomerRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CustomersController extends Controller
{
    private $customer_repository;

    public function __construct()
    {
        $this->customer_repository = new CustomerRepository();
    }

    public function index(CustomerRequest $request)
    {
        try {
            $customers = $this->customer_repository->getCustomers();

            return prepare_response(200, true, 'Customers have been retrieve successfully', $customers);
        } catch (\Exception $e) {
            Log::error('Exception in Customer list', [$e->getMessage() . " " . $e->getFile() . " " . $e->getLine()]);
            return prepare_response(500, false, 'Sorry Something was wrong.!');
        }
    }

    public function store(CustomerRequest $request)
    {
        DB::beginTransaction();
        try {
            $customer = $this->customer_repository->createCustomer($request->all());
            DB::commit();
            return prepare_response(200, true, 'Customer has been created', $customer);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Exception in Customer list', [$e->getMessage() . " " . $e->getFile() . " " . $e->getLine()]);
            return prepare_response(500, false, 'Sorry Something was wrong.!');
        }
    }
}
