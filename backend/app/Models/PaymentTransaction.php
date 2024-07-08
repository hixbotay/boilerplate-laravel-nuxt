<?php

namespace App\Models;

use App\Http\Enums\PaymentStatus;
use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model
{
    use HasFactory, Filterable;
    public $filterFields  = ['order_id', 'status', 'user_id', 'tx_id', 'data'];

    protected $table = 'payment_transactions';

    protected $guarded = [];

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function order() {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }
    public static function getTxId() {
        $listTransaction = PaymentTransaction::select('tx_id')->get();
        while (true) {
            $tx_td = mt_rand(1000000, 999999999);
            $status = $listTransaction->contains(function($value, $key) use ($tx_td) {
                return $value->tx_id == $tx_td;
            });
            if (!$status) {
                return $tx_td;
            }
        }
    }

    public static function getOrderId() {
        while (true) {
            $order_id = mt_rand(1000000, 999999999);
            $status = PaymentTransaction::where('order_id', $order_id)->first();
            if (empty($status)) {
                return $order_id;
            }
        }
    }

    public function scopeGetByUser($query, $user_id) {
        return $query->where('user_id', $user_id);
    }
    public function scopeGetByTxid($query, $txid) {
        return $query->where('tx_id', $txid);
    }
    public function scopeGetByOrder($query, $order_id) {
        return $query->where('order_id', $order_id);
    }
    public function scopeGetTransactionId($query, $transaction_id) {
        return $query->where('id', $transaction_id);
    }
    public function scopeIsPurchase($query) {
        return $query->where('order_id', 0);
    }
    public function scopeIsBuy($query) {
        return $query->where('order_id', '>', 0);
    }
    public function scopeIsStatusApproved($query) {
        return $query->where('status', PaymentStatus::APPROVED['value']);
    }
    public function scopeGetByDay($query, $day) {
        return $query->whereDay('updated_at', $day);
    }
}
