<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Approver extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['name', 'approval_code'];

    protected $hidden = [
        'approval_code'
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}