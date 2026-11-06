<?php
namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory, HasUuid;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'shipment_id', 'package_code', 'package_sequence', 'koli',
        'weight', 'length', 'width', 'height', 'qr_tag',
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }
}
