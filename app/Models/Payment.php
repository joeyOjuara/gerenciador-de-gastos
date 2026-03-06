<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Transaction;

class Payment extends Model
{
    protected $fillable = ['name'];

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'category_id', 'id');
    }
}
