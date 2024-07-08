<?php

namespace App\Models;

use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    use HasFactory, Filterable;

    protected $primaryKey = 'id';

    protected $guarded = [];

    protected $filterTextFields = ['name', 'sku', 'subname', 'description'];
    private $joinCategory = false;
    public $filterKeywords = ['name'];
    public $filterFields  = ['name', 'sku', 'price', 'price_sale', 'quantity', 'subname', 'description', 'excerpt', 'raw_data'];

    public function categories()
    {
        return $this->belongsToMany(\App\Models\ProductCategory::class, 'product_categories_relation', 'product_id', 'category_id');
    }

    public static function updateQuantity($product_id) {
        $count = ProductItem::getByProduct($product_id)->isActive()->count();
        Product::find($product_id)->update(['quantity'=>$count]);
        return $count;
    }

    public static function getTotalPrice($product_id, $count) {
        $product = is_object($product_id)?$product_id:Product::find($product_id);
        if ($product->price_sale > 0) {
            return $product->price_sale * $count;
        } else {
            return $product->price * $count;
        }
    }

    public function deleteCategory(){ 
        return DB::table('product_categories_relation')->where('product_id', '=', $this->id)
        ->delete();
    }

    public function insertCategory($ids){ 
        $data = [];
        foreach($ids as $id){
            $data[$id] = [
                'product_id' => $this->id,
                'category_id' => $id,
            ];
        }
        return DB::table('product_categories_relation')->insert($data);
    }

    public function scopeIsQuantity($query)
    {
        return $query->where('quantity', '>',0);
    }
    public function scopeJoinCategory($query)
    {
        if($this->joinCategory){
            return $query;
        }
        $query->leftJoin('product_categories_relation as r', function ($join) {
            $join->on('products.id', '=', 'r.product_id');
        });
        $query->select('products.*');
        $query->groupBy('products.id');
        $this->joinCategory = true;
		return $query;
    }

    public function scopeGetCategory($query, $cat_id) {
        $query->joinCategory();
        return $query->where('r.category_id', $cat_id);
    }
}
