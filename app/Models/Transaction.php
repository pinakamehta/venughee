<?php

namespace App\Models\API;

use App\Models\Bank;
use App\Models\ExpenseType;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $guarded = ['id'];

    public function bank()
    {
        return $this->hasOne(Bank::class, 'id', 'bank_id');
    }

    public function expense_type()
    {
        return $this->hasOne(ExpenseType::class, 'id', 'expense_type_id');
    }
}
