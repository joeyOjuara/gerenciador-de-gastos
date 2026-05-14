<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditCardInvoice extends Model
{
    protected $fillable = [
        'user_id',
        'credit_card_id',
        'reference_month',
        'reference_year',
        'closing_date',
        'due_date',
        'status',
        'paid_at',
        'paid_from_account_id',
        'payment_transaction_id',
    ];

    protected $casts = [
        'reference_month' => 'integer',
        'reference_year' => 'integer',
        'closing_date' => 'date',
        'due_date' => 'date',
        'paid_at' => 'datetime',
    ];

    public function creditCard()
    {
        return $this->belongsTo(CreditCard::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'invoice_id');
    }

    public function paidFromAccount()
    {
        return $this->belongsTo(Account::class, 'paid_from_account_id');
    }
}
