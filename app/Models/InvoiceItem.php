<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'description',
        'quantity',
        'unit_price',
        'tax_rate',
        'amount',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'amount' => 'decimal:2',
        ];
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    protected static function booted(): void
    {
        static::creating(function (InvoiceItem $item) {
            $item->amount = $item->quantity * $item->unit_price;
        });

        static::updating(function (InvoiceItem $item) {
            $item->amount = $item->quantity * $item->unit_price;
        });
    }

    public function getTaxAmountAttribute(): float
    {
        return $this->amount * ($this->tax_rate / 100);
    }

    public function getTotalAttribute(): float
    {
        return $this->amount + $this->tax_amount;
    }
}
