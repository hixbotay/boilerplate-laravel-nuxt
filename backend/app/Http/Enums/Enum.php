<?php
namespace App\Http\Enums; 

abstract class Enum {
    public static function getAll() {
        $oClass = new \ReflectionClass(get_called_class());
        return $oClass->getConstants();
    }
    public static function getDisplay($value) {
        if (isset($value)){
            $oClass = new \ReflectionClass(get_called_class());
            $constants = $oClass->getConstants();
            foreach ($constants as $item) {
                if ($item['value'] == $value) return $item['display'];
            }
        }
        return false;
    }

    public static function getValueFromKey($key,$value){
		$oClass = new \ReflectionClass(get_called_class());
        $constants = $oClass->getConstants();
        foreach ($constants as $item) {
            if ($item[$key] == $value) return $item['value'];
        }
	}

    public static function getKeyFromKey($from_key, $from_value, $to_key){
		$oClass = new \ReflectionClass(get_called_class());
        $constants = $oClass->getConstants();
        foreach ($constants as $item) {
            if ($item[$from_key] == $from_value) return $item[$to_key];
        }
	}
	
	public static function getAllValue(){
		$all = self::getAll();
		
		return array_map ( function ($a) {
			return $a['value'];
		}, $all);
	}

    public static function getAllConstant(){
		$all = self::getAll();
		
        $array = [];

        foreach ($all as $key => $value) {
            array_push($array, $value);
        }

        return $array;
	}
}