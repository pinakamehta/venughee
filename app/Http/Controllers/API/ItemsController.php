<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\ItemRequest;
use App\Repositories\API\ItemRepository;
use Exception;
use Illuminate\Http\JsonResponse;
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
    public function index()
    {
        try {
            $items = $this->item_repository->getItems();

            if (empty($items)) {
                return prepare_response(200, false, 'There is no item available right now');
            }

            return prepare_response(200, true, 'Items have been retrieve successfully', $items);
        } catch (Exception $e) {
            Log::error('Exception in Get Items', [$e->getMessage() . " " . $e->getFile() . " " . $e->getLine()]);
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

        }
    }

    public function destroy($id)
    {
        try {
            $this->item_repository->deleteItem($id);

            return prepare_response(200, true, 'Item has been deleted');
        } catch (Exception $e) {
            Log::error('Exception in Delete Item', [$e->getMessage() . " " . $e->getFile() . " " . $e->getLine()]);
            return prepare_response(500, false, 'Sorry Something was wrong.!');
        }
    }
}
