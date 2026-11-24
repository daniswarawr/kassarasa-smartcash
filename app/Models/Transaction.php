<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    protected $fillable = [
        'transaction_code',
        'total_price',
        'payment_method',
        'status',
        'transaction_date',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'transaction_date' => 'datetime',
    ];

    public function details(): HasMany
    {
        return $this->hasMany(TransactionDetail::class);
    }

    public function transactionDetails(): HasMany
    {
        return $this->hasMany(TransactionDetail::class);
    }

    // Auto-generate transaction code
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->transaction_code)) {
                $model->transaction_code = 'TRX-' . strtoupper(uniqid());
            }
        });
    }
}
