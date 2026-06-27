<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    public function models()
    {
        return $this->hasMany(ClientModel::class);
    }
}
