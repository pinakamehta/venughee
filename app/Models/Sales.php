<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sales extends Model
{
    public $table = 'sales';
    protected $guarded = ['id'];

    public function item()
    {
        return $this->hasOne(Item::class, 'id', 'item_id');
    }
}
