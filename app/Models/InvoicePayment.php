<?php
namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoicePayment extends Model
{
    use HasFactory, HasUuid;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'invoice_id', 'amount', 'payment_date', 'payment_method', 'notes',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
