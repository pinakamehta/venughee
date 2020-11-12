<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Admin\HomeRequest;
use App\Models\Branch;
use App\Models\Invoice;
use App\Models\StockTransaction;
use Exception;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    private $branch, $invoice, $stock_transaction;

    public function __construct()
    {
        $this->branch            = new Branch();
        $this->invoice           = new Invoice();
        $this->stock_transaction = new StockTransaction();
    }

    public function homeData(HomeRequest $request)
    {
        try {
            $branches  = $this->branch->where('is_active', 1)->get();
            $from_date = $request->get('from_date');
            $to_date   = $request->get('to_date');
            if (empty($branches)) {
                return prepare_response(200, false, 'There is no branch available right now');
            }
            $response_data = [];
            foreach ($branches as $branch) {
                $invoices = $this->invoice
                    ->leftJoin("users", "users.id", "=", "invoices.added_by")
                    ->where('users.branch_id', $branch->id)
                    ->where('invoices.invoice_type', 'sales')
                    ->whereBetween('invoices.invoice_date', [$from_date, $to_date]);

                $invoice_ids = $invoices->pluck('invoices.id');

                $invoice_total = $invoices->select(DB::raw("(sum(invoices.grand_total)) as grand_total"))
                    ->first();


                $total_sell_items = 0;

                if (count($invoice_ids) > 0) {
                    $stock_transaction_obj = $this->stock_transaction->whereIN('invoice_id', $invoice_ids)
                        ->select(DB::raw("(sum(item_quantity)) as total_sell_items"))
                        ->first();

                    $total_sell_items = checkEmpty($stock_transaction_obj, 'total_sell_items', 0);
                }

                $response_data[] = [
                    'name'             => checkEmpty($branch, 'branch_name', ''),
                    'grand_total'      => checkEmpty($invoice_total, 'grand_total', 0),
                    'total_sell_items' => $total_sell_items
                ];
            }

            return prepare_response(200, true, 'Home screen data have been retrieved', $response_data);
        } catch (Exception $e) {
            report($e);
            return prepare_response(500, false, $e->getMessage());
        }
    }
}
