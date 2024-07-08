<?php

namespace App\Models;

use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory, Filterable;
    public $filterFields  = ['name', 'mobile_code', 'code', 'flag'];

    public $timestamps = false;

    protected $table = 'countries';

    protected $guarded = [];

    protected $text_fields = ['name', 'code', 'mobile_code'];

    protected $appends = [];

    /**
     * get state count
     */
    // public function getStateCountAttribute()
    // {
    //     return CountryStates::where('country_id', $this->id)->count();
    // }
}
