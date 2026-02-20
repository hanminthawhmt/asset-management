<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['name', 'code', 'asset_type_id', 'brand', 'model', 'color', 'status'];

    public function assetType()
    {
        return $this->belongsTo(AssetType::class);
    }

    public function accessories()
    {
        return $this->hasMany(Accessory::class);
    }

    public function defects()
    {
        return $this->hasMany(Defect::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
