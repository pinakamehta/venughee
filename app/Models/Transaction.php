<?php

namespace App\Models;

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

    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'id', 'invoice_id');
    }
}
