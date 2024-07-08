<?php

namespace App\Models;

use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory, Filterable;

    public $timestamps = false;

    protected $guarded = [];

    protected $text_fields = ['name', 'symbol', 'code'];

    public $filterFields  = ['name', 'thousand', 'symbol', 'exchange_rate', 'display_type', 'code'];

}
