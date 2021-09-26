<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $guarded = ['id'];

    public function branch()
    {
        return $this->hasOne(Branch::class, 'id', 'branch_id');
    }
}
