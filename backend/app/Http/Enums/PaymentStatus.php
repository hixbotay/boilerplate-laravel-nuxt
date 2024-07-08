<?php

namespace App\Http\Enums;

class PaymentStatus extends Enum
{
    const WAITING = ['display' => 'Đang chờ giao dịch', 'value' => 1];
    const APPROVED = ['display' => 'Đã duyệt', 'value' => 2];
    const CANCEL = ['display' => 'Hủy bỏ', 'value' => 3];
}
