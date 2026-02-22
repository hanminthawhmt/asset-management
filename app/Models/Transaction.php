<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'asset_id',
        'approver_id',
        'borrower_name',
        'recorded_date',
        'borrow_date',
        'return_date',
        'property_front_image',
        'property_back_image',
        'property_left_image',
        'property_right_image',
        'property_overall_image',
        'acc_front_image',
        'acc_back_image',
        'acc_left_image',
        'acc_right_image',
        'acc_overall_image',
        'approval_status',
    ];

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function approver()
    {
        return $this->belongsTo(Approver::class);
    }

}
