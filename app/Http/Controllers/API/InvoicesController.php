<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\InvoiceRequest;
use App\Repositories\API\InvoiceRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvoicesController extends Controller
{
    private $invoice_repository;
    public function __construct()
    {
        $this->invoice_repository = new InvoiceRepository();
    }

    public function getNextInvoiceId(InvoiceRequest $request)
    {
        DB::beginTransaction();
        try {
            $response = $this->invoice_repository->getNextInvoiceId($request->all());
            DB::commit();

            return prepare_response(200, true, 'Here is your next invoice number', $response);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Exception in getNextInvoiceId', [$e->getMessage() . " " . $e->getFile() . " " . $e->getLine()]);
            return prepare_response(500, false, 'Sorry Something was wrong.!');
        }
    }

    public function validateInvoiceId($invoice_id)
    {
        try {
            $response = $this->invoice_repository->validateInvoiceNumber($invoice_id);

            if (!$response) {
                return prepare_response(200, false, 'This invoice number has been taken, Please try another');
            }

            return prepare_response(200, true, 'This invoice number you can use');

        } catch (\Exception $e) {
            Log::error('Exception in validateInvoiceId', [$e->getMessage() . " " . $e->getFile() . " " . $e->getLine()]);
            return prepare_response(500, false, 'Sorry Something was wrong.!');
        }
    }
}
