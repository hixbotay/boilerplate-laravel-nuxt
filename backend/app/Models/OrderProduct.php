<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderProduct extends Model
{
    use HasFactory;
    protected $guarded = [];
    public $timestamps = false;
    public $filterFields  = ['order_id', 'product_id', 'quantity'];

    
}
