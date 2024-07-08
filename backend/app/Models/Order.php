<?php

namespace App\Models;

use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    use HasFactory, Filterable;
    protected $guarded = [];
    protected $appends = [];

    public $filterKeywords = ['orders.email', 'orders.phone'];
    public $filterFields  = ['user_id', 'email', 'phone', 'payment_status'];
    // protected $appends = ['user'];
    
    
    public function scopeGetByUser($query, $user_id) {
        return $query->where('orders.user_id', $user_id);
    }
  
    public function scopeWithPayment($query){
        if($this->joinPayment)//de tranh phải join 2 lần
            return $query;
        $this->joinPayment= true;
        $query = $query->join('payment_transactions as r', 'orders.id', '=', 'r.order_id');
        $query->select('orders.*');
        return $query;
    }
    
    public function scopeGetByTxId($query, $txid, $condition = 'AND'){
        $query = $query->withPayment();
        if ($condition == 'AND') {
            $query->where('r.tx_id','like','%'.$txid.'%');
        }
        if ($condition == 'OR') {
            $query->orWhere('r.tx_id','like','%'.$txid.'%');
        }
        return $query;
    }
    public function delete() {
        return DB::transaction(function () {
            $orderProductItem = OrderProductItem::where('order_id', $this->id)->delete();
            $orderProduct = OrderProduct::where('order_id', $this->id)->delete();
            $order = Order::where('id', $this->id)->delete();
            return [
                'order_product_item' => $orderProductItem,
                'order_product' => $orderProduct,
                'order' => $order
            ];
        }, 5);
    }
    public function orderProductItem()
    {
        return $this->hasMany(\App\Models\OrderProductItem::class, 'order_id', 'id');
    }
    public function product()
    {
        return $this->belongsToMany(\App\Models\Product::class, 'order_products', 'order_id', 'product_id');
    }
    public function paymentTransaction()
    {
        return $this->hasOne(\App\Models\PaymentTransaction::class, 'order_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id', 'id');
    }
   
}
