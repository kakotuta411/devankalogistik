<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    protected $primaryKey = 'code';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['code', 'name', 'level', 'parent_code'];

    public function parent()
    {
        return $this->belongsTo(Region::class, 'parent_code', 'code');
    }

    public function children()
    {
        return $this->hasMany(Region::class, 'parent_code', 'code');
    }

    public function scopeProvinces($query)
    {
        return $query->where('level', 'province');
    }

    public function scopeRegencies($query, $parentCode)
    {
        return $query->where('level', 'regency')->where('parent_code', $parentCode);
    }

    public function scopeDistricts($query, $parentCode)
    {
        return $query->where('level', 'district')->where('parent_code', $parentCode);
    }

    public function scopeVillages($query, $parentCode)
    {
        return $query->where('level', 'village')->where('parent_code', $parentCode);
    }
}
