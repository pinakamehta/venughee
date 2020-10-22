<?php

namespace App\Models;

use App\Models\API\Transaction;
use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    protected $guarded = ['id'];

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'bank_id', 'id');
    }
}
