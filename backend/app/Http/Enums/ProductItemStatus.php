<?php

namespace App\Http\Enums;

class ProductItemStatus extends Enum
{
    const ACTIVE = ['display' => 'Hoạt động', 'value' => 1];
    const DEACTIVE = ['display' => 'Đã khóa', 'value' => 2];
    const SELL = ['display' => 'Đã bán', 'value' => 3];
}
