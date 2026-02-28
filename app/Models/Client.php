<?php

namespace App\Models;

use App\Models\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::addGlobalScope(new TenantScope);
    }

    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'phone',
        'address',
        'tax_id',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function getTotalBilledAttribute(): float
    {
        return (float) $this->invoices()
            ->whereNotIn('status', ['draft', 'cancelled'])
            ->sum('total');
    }

    public function getTotalPaidAttribute(): float
    {
        return (float) $this->invoices()
            ->where('status', 'paid')
            ->sum('total');
    }

    public function getTotalOutstandingAttribute(): float
    {
        return (float) $this->invoices()
            ->whereIn('status', ['sent', 'overdue'])
            ->sum('total');
    }
}
