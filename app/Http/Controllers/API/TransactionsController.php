<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Repositories\API\TransactionsRepository;
use Exception;
use Illuminate\Http\Request;

class TransactionsController extends Controller
{
    private $transaction_repository;

    public function __construct()
    {
        $this->transaction_repository = new TransactionsRepository();
    }

    public function index(Request $request)
    {
        try {
            $transactions = $this->transaction_repository->transaction($request->all());

            if (empty($transactions)) {
                return prepare_response(200, false, 'There is no transaction available right now');
            }
            return prepare_response(200, true, 'All transactions have been retrieve', $transactions);
        } catch (Exception $e) {
            echo $e->getMessage() . " " . $e->getFile() . " " . $e->getLine();die;
            report($e);
            return prepare_response(500, false, 'Sorry Something was wrong.!');
        }
    }
}
