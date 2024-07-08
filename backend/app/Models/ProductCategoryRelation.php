<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCategoryRelation extends Model
{
    use HasFactory;
    protected $table = 'product_categories_relation';

    public function scopeGetListCategoryId($query, $listCatId) {
        return $query->whereIn('category_id', $listCatId);
    }
    public function scopeGetByProduct($query, $product_id) {
        return $query->where('product_id', $product_id);
    }
}
