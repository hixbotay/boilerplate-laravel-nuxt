<?php
namespace App\Helpers;

use App\Models\Config as ModelsConfig;

class ConfigHeper{

	static $config;

    static function get($key, $default=''){
		if(!isset(self::$config)){
			$config = ModelsConfig::all(['name','value']);
			foreach($config as $v){
				$arr = json_decode($v->value,true);
				if($arr){
					$v->value = $arr;
				}
				self::$config[$v->name] = $v->value;
			}
		}
		if(isset(self::$config[$key])){
			return self::$config[$key];
		}
		return $default;
	}

	static function set($key, $value){
		$config = self::get($key,null);
		self::$config[$key] = $value;
		if(is_array($value) || is_object($value)){
			$value = json_encode($value);
		}
		if($config === null){
			return ModelsConfig::create([
				'name' => $key,
				'value' => $value
			]);
		}else{
			return ModelsConfig::where(['name' => $key])->update(['value' => $value]);
		}

	}
}
