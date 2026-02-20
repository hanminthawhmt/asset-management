<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Defect extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['asset_id', 'name'];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

}
