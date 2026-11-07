<?php
namespace App\Models;

use App\Scopes\BranchScope;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory, HasUuid;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'packaging_cost' => 'decimal:2',
        'equipment_charge' => 'decimal:2',
        'ppftz03' => 'decimal:2',
        'ppn' => 'decimal:2',
        'pph' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'other_costs' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'total_koli' => 'decimal:2',
        'total_weight' => 'decimal:2',
    ];

    protected $fillable = [
        'invoice_no', 'branch_id', 'customer_id', 'po_id', 'parent_invoice_id', 'customer_name', 'customer_phone',
        'customer_address', 'customer_email',
        'customer_province_code', 'customer_regency_code', 'customer_district_code', 'customer_village_code',
        'subtotal', 'total_koli', 'total_weight', 'shipping_cost', 'packaging_cost',
        'equipment_charge', 'ppftz03', 'ppn', 'pph', 'discount_type', 'discount_value',
        'other_costs', 'discount', 'amount_paid', 'total_amount',
        'invoice_status', 'invoice_date', 'due_date', 'notes',
    ];

    public $branchColumn = 'branch_id';

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function po()
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_id');
    }

    public function parentInvoice()
    {
        return $this->belongsTo(Invoice::class, 'parent_invoice_id');
    }

    public function childInvoices()
    {
        return $this->hasMany(Invoice::class, 'parent_invoice_id');
    }


    public function payments()
    {
        return $this->hasMany(InvoicePayment::class);
    }

    protected static function booted()
    {
        static::addGlobalScope(new BranchScope());
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function shipments()
    {
        return $this->belongsToMany(Shipment::class, 'invoice_items')->using(InvoiceItem::class)->withPivot('amount');
    }

    public function grandTotal(): float
    {
        $totalWeight = (float) ($this->total_weight ?? 0);
        $totalLayanan = ((float)$this->shipping_cost * $totalWeight) + (float)$this->packaging_cost
            + (float)$this->equipment_charge + (float)$this->other_costs;
        $diskonPct = (float) ($this->discount_value ?? 0);
        $ppnPct = (float) ($this->ppn ?? 0);
        $pphPct = (float) ($this->pph ?? 0);
        $diskonAmt = $totalLayanan * ($diskonPct / 100);
        $ppnAmt = $totalLayanan * ($ppnPct / 100);
        $pphAmt = $totalLayanan * ($pphPct / 100);
        return $totalLayanan - $diskonAmt + (float)$this->ppftz03 + $ppnAmt + $pphAmt;
    }

    public function totalLayanan(): float
    {
        $totalWeight = (float) ($this->total_weight ?? 0);
        return ((float)$this->shipping_cost * $totalWeight) + (float)$this->packaging_cost
            + (float)$this->equipment_charge + (float)$this->other_costs;
    }

    public function diskonAmount(): float
    {
        return $this->totalLayanan() * ((float)($this->discount_value ?? 0) / 100);
    }

    public function ppnAmount(): float
    {
        return $this->totalLayanan() * ((float)($this->ppn ?? 0) / 100);
    }

    public function pphAmount(): float
    {
        return $this->totalLayanan() * ((float)($this->pph ?? 0) / 100);
    }

    public function getStatusBadgeClass(): string
    {
        return match ($this->invoice_status) {
            'Paid' => 'bg-green-100 text-green-800',
            'Cancelled' => 'bg-red-100 text-red-800',
            'Draft' => 'bg-gray-100 text-gray-800',
            default => 'bg-yellow-100 text-yellow-800',
        };
    }
}
