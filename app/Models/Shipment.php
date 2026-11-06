<?php
namespace App\Models;

use App\Scopes\BranchScope;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    use HasFactory, HasUuid;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'resi_no', 'package_id', 'customer_id', 'po_number', 'origin_branch_id', 'destination_branch_id',
        'sender_name', 'sender_phone', 'sender_address',
        'sender_province_code', 'sender_regency_code', 'sender_district_code', 'sender_village_code',
        'receiver_name', 'receiver_phone', 'receiver_address',
        'receiver_province_code', 'receiver_regency_code', 'receiver_district_code', 'receiver_village_code',
        'total_koli', 'total_weight', 'service_type', 'payment_type', 'package_description', 'shipment_status',
    ];

    public $branchColumn = 'origin_branch_id';
    public $destinationBranchColumn = 'destination_branch_id';

    protected static function booted()
    {
        static::addGlobalScope(new BranchScope());
    }

    public function originBranch()
    {
        return $this->belongsTo(Branch::class, 'origin_branch_id');
    }

    public function destinationBranch()
    {
        return $this->belongsTo(Branch::class, 'destination_branch_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function trackingEvents()
    {
        return $this->hasMany(TrackingEvent::class);
    }

    public function manifests()
    {
        return $this->belongsToMany(Manifest::class, 'manifest_items');
    }

    public function invoices()
    {
        return $this->belongsToMany(Invoice::class, 'invoice_items');
    }

    public function manifestItems()
    {
        return $this->hasMany(ManifestItem::class);
    }
}
