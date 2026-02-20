<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillables = [
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
        'acs_front_image',
        'acs_back_image',
        'acs_left_image',
        'acs_right_image',
        'acs_overall_image',
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
