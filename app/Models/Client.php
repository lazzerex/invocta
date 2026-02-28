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

    public function getTotalBilledAttribute()
    {
        return 0;
    }

    public function getTotalPaidAttribute()
    {
        return 0;
    }

    public function getTotalOutstandingAttribute()
    {
        return 0;
    }
}
