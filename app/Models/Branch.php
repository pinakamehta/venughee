<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $guarded = ['id'];

    public function branchUser()
    {
        return $this->hasOne(User::class, 'branch_id', 'id');
    }
}
