<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Admin\BranchRequest;
use App\Repositories\API\Admin\BranchRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BranchesController extends Controller
{
    private $branch_repository;

    public function __construct()
    {
        $this->branch_repository = new BranchRepository();
    }

    /**
     * Display a listing of the resource.
     *
     * @param BranchRequest $request
     * @return JsonResponse
     */
    public function index(BranchRequest $request)
    {
        try {
            $branches = $this->branch_repository->branches($request->all());

            return prepare_response(200, true, "Branches have been retrieved successfully", $branches);
        } catch (Exception $e) {
            report($e);
            return prepare_response(500, false, $e->getMessage());
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return JsonResponse
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param BranchRequest $request
     * @return JsonResponse
     */
    public function store(BranchRequest $request)
    {
        DB::beginTransaction();
        try {
            $this->branch_repository->addBranch($request->all());
            DB::commit();
            return prepare_response(200, true, 'Branch added successfully');
        } catch (Exception $e) {
            DB::rollBack();
            report($e);
            Log::channel('slack')->critical($request->getRequestUri(), $request->all());
            return prepare_response(500, false, $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function edit($id)
    {

    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(BranchRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $this->branch_repository->editBranch($id, $request->all());
            DB::commit();
            return prepare_response(200, true, 'Branch updated successfully');
        } catch (Exception $e) {
            DB::rollBack();
            report($e);
            Log::channel('slack')->critical($request->getRequestUri(), $request->all());
            return prepare_response(500, false, $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        //
    }
}
