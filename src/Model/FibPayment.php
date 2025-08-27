<?php

namespace FirstIraqiBank\FIBPaymentSDK\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FibPayment extends Model
{
    use HasFactory;

    const PENDING = 'PENDING';

    const UNPAID = 'UNPAID';

    const PAID = 'PAID';

    const DECLINED = 'DECLINED';

    const REFUNDED = 'REFUNDED';

    const CANCELED = 'CANCELED';

    protected $fillable = [
        'fib_payment_id',
        'readable_code',
        'personal_app_link',
        'status',
        'amount',
        'valid_until',
    ];

    protected $casts = [
        'valid_until' => 'datetime',
    ];

    public function refund(): HasOne
    {
        return $this->hasOne(FibRefund::class, 'payment_id');
    }
}
