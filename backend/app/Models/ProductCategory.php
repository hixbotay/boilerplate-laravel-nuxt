<?php

namespace App\Models;

use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    use HasFactory, Filterable;

    protected $primaryKey = 'id';

    protected $guarded = [];

    public $filterFields  = ['user_id', 'parent_id', 'name', 'description', 'status'];

    public function scopeGetChildren($query, $parent_id) {
        return $query->where('parent_id', $parent_id);
    }

    public function scopeGetListId($query, $listId) {
        return $query->whereIn('id', $listId);
    }
}
