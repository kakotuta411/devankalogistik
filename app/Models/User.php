<?php

namespace App\Models;

use App\Scopes\BranchScope;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, HasUuid, HasApiTokens, Notifiable;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['name', 'email', 'password', 'phone_number', 'role', 'branch_id'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'Admin';
    }

    public function canWrite(): bool
    {
        return !in_array($this->role, ['Management_View'], true);
    }

    public function canManageShipments(): bool
    {
        return in_array($this->role, ['Admin', 'Staff_Shipment'], true);
    }

    public function canManageManifests(): bool
    {
        return in_array($this->role, ['Admin', 'Staff_Shipment'], true);
    }

    public function canManageInvoices(): bool
    {
        return in_array($this->role, ['Admin', 'Staff_Invoice'], true);
    }

    public function canManageCustomers(): bool
    {
        return in_array($this->role, ['Admin', 'Staff_Invoice'], true);
    }
}
