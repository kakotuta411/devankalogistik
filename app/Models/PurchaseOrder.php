<?php
namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory, HasUuid;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'po_number', 'customer_id', 'total_amount', 'paid_amount', 'status', 'notes',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function shipments()
    {
        return $this->hasMany(Shipment::class, 'po_number', 'po_number');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'po_id');
    }
}
