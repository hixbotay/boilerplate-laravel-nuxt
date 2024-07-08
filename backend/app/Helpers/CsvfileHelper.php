<?php
namespace App\Helpers;

class CsvfileHelper{
	
	static function read($filepath){
		$data = array();
		$start_row = 0;
		$file = fopen($filepath,"r");
		while (($readData = fgetcsv($file, 1000, ',')) != false ) {
			$column_count = count($readData);
			for ($i=0; $i < $column_count; $i++) { 
				$data[$start_row]['col-'.($i + 1)] = $readData[$i];
			}
			$start_row++;
			
		}
		
		fclose($file);
		return $data;
	}

	static function write($filepath, array $fields, $header = true, $append = false, $utf8 = true){
		if($append){
			$fh = fopen($filepath, 'a');
		}else{
			$fh = fopen($filepath, 'w') ;
		}
		if($utf8){
			fwrite($fh, "\xEF\xBB\xBF");
		}
		if ( $fh ) {
			if($header){
				
				fputcsv($fh, array_keys((array)reset($fields)));
			}
			foreach ($fields as $data) {
			    fputcsv($fh, (array)$data);
			}				
			fclose($fh);
			return true;
		}
		return false;
	}
	
	static function download($datas,$name,$header = true,$utf8 = true) {	
		try{				
			// Open the output stream
			$fh = fopen('php://output', 'w');
// 			fputs($fh, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
// 			fputs($fh, pack("CCC",0xef,0xbb,0xbf)); 
// 			fputs($fh, chr(255) . chr(254)); 
			
			// Start output buffering (to capture stream contents)
			ob_start();
			if($header){
				fputcsv($fh, array_keys((array)reset($datas)));
			}
			if($utf8){
				fwrite($fh, "\xEF\xBB\xBF");
			}
			foreach ($datas as $data) {			
				fputcsv($fh, (array)$data);
			}
// 			fputs( $fh, "\xEF\xBB\xBF" );
// 			fputs($fh, pack("CCC",0xef,0xbb,0xbf)); 
			$string = ob_get_clean();
			
			header('Content-Encoding: UTF-8');
			header('Content-Type: application/octet-stream charset=UTF-8');
			header('Content-Disposition: attachment; filename="' . $name . '.csv";');
			header('Content-Transfer-Encoding: binary');
			header('Pragma: public');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Cache-Control: private', false);
			//stream_encoding($fh, 'UTF-8'); 
			// Stream the CSV data
			echo $string;
			//echo chr(255) . chr(254) . mb_convert_encoding($string, 'UTF-16LE', 'UTF-8');
			exit;
		}
		catch( \Exception $e ) {
			LogHelper::info('download csv failed');
			return false;
		}
		
	}
	
}