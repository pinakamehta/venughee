<?php

namespace App\Repositories\API\Admin;

use App\Models\ExpenseType;
use Exception;

class ExpenseTypesRepository
{
    private $expense_type;

    public function __construct()
    {
        $this->expense_type = new ExpenseType();
    }

    public function list()
    {
        $expense_types = $this->expense_type->get();

        $expense_type_data = [];

        if (!empty($expense_types)) {
            foreach ($expense_types as $expense_type) {
                $expense_type_data[] = [
                    'id'                => $expense_type->id,
                    'expense_type_name' => $expense_type->expense_type_name,
                ];
            }
        }


        return $expense_type_data;
    }

    public function show($type_id)
    {
        $expense_type = $this->expense_type->where('id', $type_id)
            ->first();

        if (empty($expense_type)) {
            throw new Exception("Expense type was not found for given id");
        }

        $expense_type_data = [];

        $expense_type_data[] = [
            'id'                => $expense_type->id,
            'expense_type_name' => $expense_type->expense_type_name,
        ];

        return $expense_type_data;
    }

    public function store($data)
    {
        $this->expense_type->create([
            'expense_type_name' => $data['expense_type_name']
        ]);
    }

    public function update($data, $type_id)
    {
        $expense_type        = $this->expense_type->where('id', $type_id);
        $expense_type_detail = $expense_type->first();

        if (empty($expense_type_detail)) {
            throw new Exception("Expense type was not found for given id");
        }

        $expense_type->update([
            'expense_type_name' => checkEmpty($data, 'expense_type_name', $expense_type_detail->expense_type_name)
        ]);

    }

    public function delete($type_id)
    {
        $expense_type = $this->expense_type->where('id', $type_id)->first();

        if (empty($expense_type)) {
            throw new Exception("Expense type was not found for given id");
        }
        $expense_type->delete();
    }
}
