<?php

namespace App\Models;

use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    use HasFactory, Filterable;
    public $filterFields  = ['name', 'value'];

    protected $guarded = [];
    protected $filterKeywords = ['name'];
    static $data;

    public static function getOptions($name = '', $default = array()) {
        if (!empty($name)) {
            if(!self::$data){
                self::$data = Config::all()->keyBy('name');
            }
            $result = self::$data[$name];
            if (!empty($result)) {
                $d = json_decode($result->value, true);
                if($d){
                    return $d;
                }else{
                    return $result;
                }
            }
        }
        return $default;
    }
}
