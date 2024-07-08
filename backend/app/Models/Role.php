<?php

namespace App\Models;

use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    use Filterable;

    protected $table = 'roles';

    protected $guarded = [];

    protected $casts = [
        'permission' => 'array'
    ];

    protected $text_fields = ['name'];

    public $filterFields  = ['name', 'type'];


    public function scopeGetByType($query,$type){
        return $query->where('type',$type);
    }
}
