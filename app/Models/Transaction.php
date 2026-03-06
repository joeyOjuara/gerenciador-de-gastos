<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\Category;
use App\Models\Payment;

class Transaction extends Model
{
    protected $fillable = [
        'amount',
        'description',
        'date',
        'category_id',
        'user_id',
        'payment_id'
    ];

    public function category() : HasOne
    {
        return $this->hasOne(Category::class, 'id', 'category_id');
    }

    public function payment() : HasOne
    {
        return $this->hasOne(Payment::class, 'id', 'payment_id');
    }
}
