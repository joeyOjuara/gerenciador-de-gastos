<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Account;
use App\Models\Category;
use App\Models\CreditCard;
use App\Models\CreditCardInvoice;
use App\Models\Payment;

class Transaction extends Model
{
    protected $fillable = [
        'amount',
        'description',
        'date',
        'type',
        'payment_method',
        'category_id',
        'user_id',
        'payment_id',
        'account_id',
        'credit_card_id',
        'invoice_id',
        'installment_number',
        'installments_total',
        'parent_transaction_id',
        'is_invoice_payment',
    ];

    protected $casts = [
        'is_invoice_payment' => 'boolean',
    ];

    public function category(): HasOne
    {
        return $this->hasOne(Category::class, 'id', 'category_id');
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class, 'id', 'payment_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function creditCard(): BelongsTo
    {
        return $this->belongsTo(CreditCard::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(CreditCardInvoice::class, 'invoice_id');
    }
}
