<?php

namespace App\Http\Enums;

class PayStatus extends Enum
{
    const AWAITING_PAYMENT = ['display' => 'Awaiting', 'value' => '1'];
    const PAID = ['display' => 'Paid', 'value' => '2'];
    const CANCELLED = ['display' => 'Cancelled', 'value' => '3'];
    const REFUNDED = ['display' => 'Refunded', 'value' => '4'];
    const PARTIALLY_REFUNDED = ['display' => 'Partially refunded', 'value' => '5'];
    const INCOMPLETE = ['display' => 'Incomplete', 'value' => '6'];
    const FAILED = ['display' => 'Failed', 'value' => '7'];
    const PARTIALLY_PAID = ['display' => 'Partially paid', 'value' => '8'];
}
