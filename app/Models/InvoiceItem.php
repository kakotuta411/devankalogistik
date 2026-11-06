<?php
namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Relations\Pivot;

class InvoiceItem extends Pivot
{
    use HasUuid;

    protected $table = 'invoice_items';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['invoice_id', 'shipment_id', 'amount'];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }
}
