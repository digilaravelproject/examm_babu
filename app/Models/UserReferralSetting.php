<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserReferralSetting extends Model
{
    protected $fillable = [
        'user_id',
        'commission_percentage',
        'recurring_commission_percentage',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
