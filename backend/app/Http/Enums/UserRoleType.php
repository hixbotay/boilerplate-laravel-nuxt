<?php

namespace App\Http\Enums;

class UserRoleType extends Enum
{
    const ADMIN = ['display' => 'Admin', 'value' => 1];
    const MANAGER = ['display' => 'Quản lí', 'value' => 2];
    const MERCHANT = ['display' => 'Thành viên đăng kí', 'value' => 3];
    const PARTNER = ['display' => 'Cộng tác viên', 'value' => 4];
}
