<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $guarded = ['id'];

    public function customer() {
        return $this->hasOne(Customer::class, 'id', 'customer_id');
    }
}
