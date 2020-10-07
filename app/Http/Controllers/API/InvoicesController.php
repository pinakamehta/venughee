<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\InvoiceRequest;
use App\Repositories\API\InvoiceRepository;
use Exception;
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
        } catch (Exception $e) {
            DB::rollBack();
            report($e);
            Log::channel('slack')->critical($request->all());
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

        } catch (Exception $e) {
            report($e);
            return prepare_response(500, false, 'Sorry Something was wrong.!');
        }
    }

    public function index(InvoiceRequest $request)
    {
        try {
            $invoices = $this->invoice_repository->getInvoices($request->all());

            if (empty($invoices)) {
                return prepare_response(200, false, 'There is no invoice available right now');
            }

            return prepare_response(200, true, 'Invoice list have been retrieve', $invoices);
        } catch (Exception $e) {
            report($e);
            return prepare_response(500, false, 'Sorry Something was wrong.!');
        }
    }

    public function show($id)
    {
        try {
            $invoice = $this->invoice_repository->getInvoiceData($id);

            if (empty($invoice)) {
                return prepare_response(200, false, 'Invoice number is invalid try again');
            }

            return prepare_response(200, true, 'Invoice details have been retrieve', $invoice);
        } catch (Exception $e) {
            report($e);
            return prepare_response(500, false, 'Sorry Something was wrong.!');
        }
    }

    public function update(InvoiceRequest $request, $id)
    {
        try {
            $this->invoice_repository->editInvoice($request->all(), $id);
            return prepare_response(200, true, 'Invoice successfully saved');
        } catch (Exception $e) {
            report($e);
            Log::channel('slack')->critical($request->all());
            return prepare_response(500, false, 'Sorry Something was wrong.!');
        }
    }

    public function destroy($id)
    {
        try {
            $this->invoice_repository->deleteInvoice($id);
            return prepare_response(200, true, 'Invoice successfully deleted');
        } catch (Exception $e) {
            report($e);
            Log::channel('slack')->critical($request->all());
            return prepare_response(500, false, 'Sorry Something was wrong.!');
        }
    }
}
