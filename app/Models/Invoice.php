<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Invoice extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function (Invoice $invoice) {
            if (empty($invoice->public_uuid)) {
                $invoice->public_uuid = Str::uuid();
            }
        });
    }

    protected $fillable = [
        'tenant_id',
        'client_id',
        'invoice_number',
        'status',
        'issue_date',
        'due_date',
        'subtotal',
        'tax_amount',
        'total',
        'notes',
        'terms',
        'public_uuid',
        'stripe_checkout_session_id',
        'stripe_payment_intent_id',
        'payment_method',
        'amount_paid',
        'sent_at',
        'email_sent_at',
        'email_sent_count',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => InvoiceStatus::class,
            'issue_date' => 'date',
            'due_date' => 'date',
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'sent_at' => 'datetime',
            'email_sent_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function calculateTotals(): void
    {
        $subtotal = $this->items->sum(fn ($item) => $item->quantity * $item->unit_price);
        $taxAmount = $this->items->sum(fn ($item) => $item->quantity * $item->unit_price * ($item->tax_rate / 100));

        $this->subtotal = $subtotal;
        $this->tax_amount = $taxAmount;
        $this->total = $subtotal + $taxAmount;
    }

    public static function generateNumber(int $tenantId): string
    {
        $lastInvoice = static::withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->orderByRaw('CAST(SUBSTRING(invoice_number, 5) AS UNSIGNED) DESC')
            ->first();

        $nextNumber = 1;
        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->invoice_number, 4);
            $nextNumber = $lastNumber + 1;
        }

        return 'INV-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status !== InvoiceStatus::Paid
            && $this->status !== InvoiceStatus::Cancelled
            && $this->due_date < now()->startOfDay();
    }

    public function markAsSent(): void
    {
        $this->update([
            'status' => InvoiceStatus::Sent,
            'sent_at' => now(),
        ]);
    }

    public function markAsEmailSent(): void
    {
        $this->update([
            'status' => InvoiceStatus::Sent,
            'sent_at' => $this->sent_at ?? now(),
            'email_sent_at' => now(),
            'email_sent_count' => $this->email_sent_count + 1,
        ]);
    }

    public function markAsPaid(): void
    {
        $this->update([
            'status' => InvoiceStatus::Paid,
            'paid_at' => now(),
        ]);
    }

    public function markAsCancelled(): void
    {
        $this->update([
            'status' => InvoiceStatus::Cancelled,
        ]);
    }
}
