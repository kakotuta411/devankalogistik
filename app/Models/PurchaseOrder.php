<?php

namespace App\Models;

use App\Scopes\BranchScope;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory, HasUuid;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $casts = [
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    protected $fillable = [
        'po_number', 'customer_id', 'total_amount', 'paid_amount', 'status', 'notes',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function shipments()
    {
        return Shipment::where('po_number', $this->po_number)->get();
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'po_id');
    }

    public function getRemainingAmount(): float
    {
        return (float)$this->total_amount - (float)$this->paid_amount;
    }

    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            'paid' => 'text-bg-success',
            'partial' => 'text-bg-warning',
            'cancelled' => 'text-bg-danger',
            'active' => 'text-bg-primary',
            'draft' => 'text-bg-secondary',
            default => 'text-bg-secondary',
        };
    }
}