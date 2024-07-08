<?php

namespace App\Models;

use App\Http\Enums\ProductItemStatus;
use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OrderProductItem extends Model
{
    use HasFactory, Filterable;
    protected $guarded = [];
    public $timestamps = false;
    public $filterFields  = ['order_id', 'product_id', 'product_item_id', 'name', 'description'];
    public $filterKeywords = [];
    public static function createOrderProductItem($product_id, $count, $order_id) {
        $listProductItem = ProductItem::getByProduct($product_id)->isActive()->limit($count)->get();
        $dataOrderProductItemArr = array();
        $listProductItemId = array();
        foreach ($listProductItem as $key => $item) {
            $dataOrderProductItemArr[] = array(
                'order_id' => $order_id,
                'product_id' => $product_id,
                'product_item_id' => $item->id,
                'name' => $item->name,
                'description' => $item->description,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            );
            $listProductItemId[] = $item->id;
        }
        ProductItem::getListProductItem($listProductItemId)->delete();
        $resultOrder = OrderProductItem::insert($dataOrderProductItemArr);
      
        return $listProductItem;
    }
}
