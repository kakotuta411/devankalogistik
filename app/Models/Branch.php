<?php
namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory, HasUuid;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['branch_code', 'name', 'city', 'address', 'province_code', 'regency_code', 'district_code', 'village_code'];

    public $branchColumn = 'id';

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function originShipments()
    {
        return $this->hasMany(Shipment::class, 'origin_branch_id');
    }

    public function destinationShipments()
    {
        return $this->hasMany(Shipment::class, 'destination_branch_id');
    }

    public function originManifests()
    {
        return $this->hasMany(Manifest::class, 'origin_branch_id');
    }

    public function destinationManifests()
    {
        return $this->hasMany(Manifest::class, 'destination_branch_id');
    }
}
