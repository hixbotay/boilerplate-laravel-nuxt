<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserKycInformation extends Model
{
    use HasFactory;

    protected $table = 'user_kyc_informations';

    protected $guarded = [];


}
