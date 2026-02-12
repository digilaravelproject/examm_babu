<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletTransaction extends Model
{
    protected $guarded = [];

    /**
     * Relationship: Get the user who owns the transaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: Link to Withdrawal Request.
     * We must specify 'reference_id' because that is the name of your column in the database.
     */
    public function withdrawalRequest(): BelongsTo
    {
        // 2nd argument is the foreign key in 'wallet_transactions' table
        // 3rd argument is the owner key in 'withdrawal_requests' table (usually 'id')
        return $this->belongsTo(WithdrawalRequest::class, 'reference_id', 'id');
    }

    /**
     * (Optional) Relationship: Link to Payment (for Referral Rewards).
     * Useful if you want to show details of the purchase that generated the commission.
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'reference_id', 'id');
    }
}
