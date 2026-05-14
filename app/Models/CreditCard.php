<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditCard extends Model
{
    protected $fillable = ['name', 'limit_amount', 'closing_day', 'due_day', 'user_id'];

    protected $casts = [
        'limit_amount' => 'decimal:2',
        'closing_day' => 'integer',
        'due_day' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function invoices()
    {
        return $this->hasMany(CreditCardInvoice::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
