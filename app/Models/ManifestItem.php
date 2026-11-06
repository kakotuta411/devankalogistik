<?php
namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ManifestItem extends Pivot
{
    use HasFactory, HasUuid;

    protected $table = 'manifest_items';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['manifest_id', 'shipment_id'];

    public function manifest()
    {
        return $this->belongsTo(Manifest::class);
    }

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }
}
