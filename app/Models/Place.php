<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Place extends Model
{
    use HasFactory;
    
    protected $fillable = ['type', 'name', 'parent_id'];
    
    const TYPE_PROVINCE = 'province';
    const TYPE_MUNICIPALITY = 'municipality';
    const TYPE_OFFICE = 'office';
    
    // Relationships
    public function parent()
    {
        return $this->belongsTo(Place::class, 'parent_id');
    }
    
    public function children()
    {
        return $this->hasMany(Place::class, 'parent_id');
    }
    
    // Helper methods
    public static function getProvinces()
    {
        return self::where('type', self::TYPE_PROVINCE)
            ->whereNull('parent_id')
            ->orderBy('name')
            ->get();
    }
    
    public static function getMunicipalities($provinceId = null)
    {
        $query = self::where('type', self::TYPE_MUNICIPALITY);
        
        if ($provinceId) {
            $query->where('parent_id', $provinceId);
        }
        
        return $query->orderBy('name')->get();
    }
    
    public static function getOffices($municipalityId = null)
    {
        $query = self::where('type', self::TYPE_OFFICE);
        
        if ($municipalityId) {
            $query->where('parent_id', $municipalityId);
        }
        
        return $query->orderBy('name')->get();
    }
}
