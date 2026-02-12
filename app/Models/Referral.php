<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Referral extends Model
{
    // Mass assignment allow karne ke liye
    protected $guarded = [];

    /**
     * Wo user jisne link share kiya (Commission earner)
     */
    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    /**
     * Wo user jisne register kiya/khareeda
     */
    public function referee()
    {
        return $this->belongsTo(User::class, 'referee_id');
    }

    /**
     * Kis payment ke against ye commission mila
     */
    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }
}
