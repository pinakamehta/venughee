<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    protected $guarded = ['id'];

    public function item()
    {
        return $this->hasOne(Item::class, 'id', 'item_id');
    }
}
