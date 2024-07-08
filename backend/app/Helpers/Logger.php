<?php
namespace App\Helpers;

class Logger{
	public $logPath;

    function __construct($logPath){
		$this->logPath = $logPath;
	}

	function addPath($path){
		if(!in_array($path,$this->logPath)){
			$this->logPath[] = $path;
		}
	}

	function info($msg){
		foreach($this->logPath as $logPath){
			LogHelper::write_log($logPath,$msg);
		}
	}

	function error($msg){
		foreach($this->logPath as $logPath){
			LogHelper::write_log($logPath,$msg);
		}
	}
}