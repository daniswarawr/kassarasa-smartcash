<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    protected $fillable = [
        'user_id',
        'transaction_code',
        'customer_name',
        'total_price',
        'cash_received',
        'change_amount',
        'payment_method',
        'status',
        'kitchen_status',
        'transaction_date',
    ];

    protected $casts = [
        'total_price'      => 'decimal:2',
        'cash_received'    => 'decimal:2',
        'change_amount'    => 'decimal:2',
        'transaction_date' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(TransactionDetail::class);
    }

    public function transactionDetails(): HasMany
    {
        return $this->hasMany(TransactionDetail::class);
    }

    public function getWhatsappUrlAttribute(): string
    {
        $lines = [
            "*STRUK PEMBAYARAN - KASSARASA (SMARTCASH)*",
            "Kode: {$this->transaction_code}",
            "Tanggal: " . ($this->transaction_date?->format('d/m/Y H:i') ?? now()->format('d/m/Y H:i')),
            "--------------------------------",
        ];

        foreach ($this->transactionDetails as $detail) {
            $name = $detail->product->name ?? 'Menu';
            $sub  = number_format((float) $detail->subtotal, 0, ',', '.');
            $lines[] = "{$name} x{$detail->quantity} = Rp {$sub}";
        }

        $total = number_format((float) $this->total_price, 0, ',', '.');
        $lines[] = "--------------------------------";
        $lines[] = "*TOTAL: Rp {$total}*";
        $lines[] = "Metode: " . strtoupper($this->payment_method);
        $lines[] = "Terima kasih sudah berbelanja!";

        return 'https://wa.me/?text=' . urlencode(implode("\n", $lines));
    }

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
