<?php

namespace App\Models;

use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permissions extends Model
{
    use HasFactory, Filterable;
    protected $table = 'permissions';
    public $filterFields  = ['name', 'resource', 'action', 'owner'];

    protected $guarded = [];
}
