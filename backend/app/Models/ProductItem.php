<?php

namespace App\Models;

use App\Http\Enums\ProductItemStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Filterable;

class ProductItem extends Model
{
    use HasFactory, Filterable;

    protected $primaryKey = 'id';

    protected $guarded = [];

    public $filterFields  = ['product_id', 'status', 'name', 'description'];


    public function product()
    {
        return $this->belongsTo(\App\Models\Product::class, 'product_id', 'id');
    }

    public function scopeGetByProduct($query, $product_id) {
        return $query->where('product_id', $product_id);
    }
    public function scopeIsActive($query) {
        return $query->where('status', ProductItemStatus::ACTIVE['value']);
    }
    public function scopeGetListProductItem($query, $product_item_ids) {
        return $query->whereIn('id', $product_item_ids);
    }
    public static function updateStatus($product_item_ids, $status) {
        if (is_array($product_item_ids)) {
            ProductItem::whereIn('id', $product_item_ids)->update(['status' => $status]);
        } else {
            $productItem = ProductItem::find($product_item_ids);
            $productItem->update(['status' => $status]);
        }
    }

}
