<?php
namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory, HasUuid;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name', 'company_name', 'phone', 'email', 'address',
        'province_code', 'regency_code', 'district_code', 'village_code',
    ];

    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }
}
