<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\CustomerRequest;
use App\Repositories\API\CustomerRepository;
use Exception;
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
            $customers = $this->customer_repository->getCustomers($request->all());

            if (empty($customers)) {
                return prepare_response(200, false, 'There is no customer available right now');
            }

            return prepare_response(200, true, 'Customers have been retrieve successfully', $customers);
        } catch (Exception $e) {
            report($e);
            Log::channel('slack')->critical($request->getRequestUri(), $request->all());
            return prepare_response(500, false, $e->getMessage());
        }
    }

    public function store(CustomerRequest $request)
    {
        DB::beginTransaction();
        try {
            $customer = $this->customer_repository->createCustomer($request->all());
            DB::commit();
            return prepare_response(200, true, 'Customer has been created', $customer);
        } catch (Exception $e) {
            DB::rollBack();
            report($e);
            Log::channel('slack')->critical($request->getRequestUri(), $request->all());
            return prepare_response(500, false, $e->getMessage());
        }
    }

    public function customerInvoices(CustomerRequest $request)
    {
        try {
            $customer_invoices = $this->customer_repository->getCustomerInvoices($request->all());

            if (empty($customer_invoices)) {
                return prepare_response(200, false, 'There is no invoice available for this customer');
            }

            return prepare_response(200, true, 'Customer Invoices have been retrieve', $customer_invoices);
        } catch (Exception $e) {
            report($e);
            Log::channel('slack')->critical($request->getRequestUri(), $request->all());
            return prepare_response(500, false, $e->getMessage());
        }
    }
}
