<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\ItemRequest;
use App\Repositories\API\ItemRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ItemsController extends Controller
{
    private $item_repository;

    public function __construct()
    {
        $this->item_repository = new ItemRepository();
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $items = $this->item_repository->getItems($request->all());

            if (empty($items)) {
                return prepare_response(200, false, 'There is no item available right now');
            }

            return prepare_response(200, true, 'Items have been retrieve successfully', $items);
        } catch (Exception $e) {
            report($e);
            return prepare_response(500, false, 'Sorry Something was wrong.!');
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ItemRequest $request
     * @return JsonResponse
     */
    public function store(ItemRequest $request)
    {
        DB::beginTransaction();
        try {
            $item = $this->item_repository->addItem($request->all());
            DB::commit();
            return prepare_response(200, true, 'Item has been added successfully', $item);
        } catch (Exception $e) {
            DB::rollBack();
            report($e);
            Log::channel('slack')->critical($request->all());
            return prepare_response(500, false, 'Sorry Something was wrong.!');
        }
    }

    public function update(ItemRequest $request, $item_id)
    {
        DB::beginTransaction();
        try {
            $this->item_repository->updateItem($request->all(), $item_id);
            DB::commit();
            return prepare_response(200, true, 'Item has been updated');
        } catch (Exception $e) {
            DB::rollBack();
            report($e);
            Log::channel('slack')->critical($request->all());
            return prepare_response(500, false, 'Sorry Something was wrong.!');
        }
    }

    public function destroy($id)
    {
        try {
            $this->item_repository->deleteItem($id);

            return prepare_response(200, true, 'Item has been deleted');
        } catch (Exception $e) {
            report($e);
            Log::channel('slack')->critical($request->all());
            return prepare_response(500, false, 'Sorry Something was wrong.!');
        }
    }
}
