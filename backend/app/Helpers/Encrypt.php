<?php
namespace App\Helpers;

class Encrypt{

    static $secret_key = 'AEC2657E0C322C82D47B233AAC8B8CC1';
    
	static function encrypt($plainText, $key = null)
	{
		if(!$key){
			$key = self::$secret_key;
		}

		$key = self::hextobin(md5($key));
		$iv = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
        $encryptedMessage = openssl_encrypt($plainText, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
		return bin2hex($encryptedMessage);
		//old version
	
		$secretKey = self::hextobin(md5($key));

		$initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);

		$openMode = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', 'cbc', '');

		$blockSize = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, 'cbc');

		$plainPad = self::pkcs5_pad($plainText, $blockSize);



		if (mcrypt_generic_init($openMode, $secretKey, $initVector) != -1) {

			$encryptedText = mcrypt_generic($openMode, $plainPad);

			mcrypt_generic_deinit($openMode);
		}

		return bin2hex($encryptedText);
	}

	static function decrypt($encryptedText, $key = null)
	{
		if(!$key){
			$key = self::$secret_key;
		}
		$key = self::hextobin(md5($key));
		$encryptedText = hex2bin($encryptedText);
		$iv = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
		$text = openssl_decrypt ( $encryptedText , 'AES-128-CBC' , $key ,OPENSSL_RAW_DATA,$iv);
		return $text;
		$secretKey = self::hextobin(md5($key));

		$initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);

		$encryptedText = self::hextobin($encryptedText);

		$openMode = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', 'cbc', '');

		mcrypt_generic_init($openMode, $secretKey, $initVector);

		$decryptedText = mdecrypt_generic($openMode, $encryptedText);

		$decryptedText = rtrim($decryptedText, "\0");

		mcrypt_generic_deinit($openMode);

		return $decryptedText;
	}



	// *********** Padding Function *********************

	function pkcs5_pad($plainText, $blockSize)
	{

		$pad = $blockSize - (strlen($plainText) % $blockSize);

		return $plainText . str_repeat(chr($pad), $pad);
	}



	// ********** Hexadecimal to Binary function for php 4.0 version ********

	function hextobin($hexString)
	{

		$length = strlen($hexString);

		$binString = "";

		$count = 0;

		while ($count < $length) {

			$subString = substr($hexString, $count, 2);

			$packedString = pack("H*", $subString);

			if ($count == 0) {

				$binString = $packedString;
			} else {

				$binString .= $packedString;
			}

			$count += 2;
		}

		return $binString;
	}
}