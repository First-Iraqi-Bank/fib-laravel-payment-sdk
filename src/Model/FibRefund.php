<?php

namespace FirstIraqiBank\FIBPaymentSDK\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FibRefund extends Model
{
    use HasFactory;

    const PENDING = 'PENDING';

    const SUCCESS = 'SUCCESS';

    const FAILED = 'FAILED';

    protected $fillable = [
        'payment_id',
        'fib_trace_id',
        'status',
        'refund_details',
        'refund_failure_reason',
    ];

    /**
     * Get the payment associated with the refund.
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(FibPayment::class, 'payment_id');
    }
}
