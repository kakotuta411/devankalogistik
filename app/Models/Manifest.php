<?php
namespace App\Models;

use App\Scopes\BranchScope;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Manifest extends Model
{
    use HasFactory, HasUuid;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'manifest_no', 'origin_branch_id', 'destination_branch_id',
        'vehicle_no', 'driver_name', 'manifest_status',
        'dispatch_time', 'receive_time',
    ];

    protected $casts = [
        'dispatch_time' => 'datetime',
        'receive_time' => 'datetime',
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

    public function items()
    {
        return $this->hasMany(ManifestItem::class);
    }

    public function shipments()
    {
        return $this->belongsToMany(Shipment::class, 'manifest_items')
            ->using(ManifestItem::class)
            ->withPivot('id');
    }
}
