<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

class LogHelper
{
	static function write_log($log_file, $error, $store = true)
	{

		if (!$log_file) return false;
		date_default_timezone_set('Asia/Calcutta');
		$date = date('d/m/Y H:i:s');
		$error = $date . ": " . $error . PHP_EOL . '--------------------------------' . PHP_EOL;
		$path = storage_path("logs/");
		if (!is_dir($path)) {
			mkdir($path, 0755);
		}
		//create dir if not exist
		$dir = explode('/', $log_file);
		if (count($dir) > 1) {
			$count = count($dir);
			$dir_path = $path;
			for ($i = 0; $i < $count - 1; $i++) {
				$dir_path .= $dir[$i] . '/';
				if (!is_dir($dir_path)) {
					mkdir($dir_path);
				}
			}
		}
		$log_file = $path . $log_file;

		if (!file_exists($log_file) || filesize($log_file) > 2048576) {
			if ($store && file_exists($log_file)) {
				$file_info = pathinfo($log_file);
				$file_path = $file_info['dirname'] . '/' . $file_info['filename'] . '-backup.' . $file_info['extension'];
				if (file_exists($file_path)) {
					unlink($file_path);
				}
				rename($log_file, $file_path);
			}
			$fh = fopen($log_file, 'w');
			chmod($log_file, 0777);
		} else {
			//echo "Append log to log file ".$log_file;
			chmod($log_file, 0774);
			$fh = fopen($log_file, 'a');
		}

		fwrite($fh, $error);
		fclose($fh);
	}

	static function error($error)
	{
		return self::write_log("error.log", $error);
	}


	static function info(string $filename, string $content, bool $autoBackup = true)
	{
		$filePath = storage_path('logs/') . $filename;

		if (file_exists($filePath)) {
			if (filesize($filePath) > 2048576 && $autoBackup) {
				$fileInfo = pathinfo($filePath);

				$backupPath = storage_path('logs/') . $fileInfo['filename'] . '-backup.' . $fileInfo['extension'];
				if (file_exists($backupPath)) {
					unlink($backupPath);
				}
				rename($filePath, $backupPath);
			}
		}

		Log::build([
			'driver' => 'single',
			'path' => storage_path('logs/' . $filename),
		])->info($content);
	}
}
