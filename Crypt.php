<?php

/**
 * Static functions to allow reversible encryption and decryption
 *
 * Requires the mcrypt PHP module to work!
 *
 * @author James Titcumb <hello@jamestitcumb.com>
 * @license https://github.com/Asgrim/MAL/raw/master/LICENSE The BSD License
 * @copyright Copyright (c) 2011, James Titcumb
 */
class MAL_Crypt
{
	/**
	 * Encrypt a string using AES 256 encryption
	 *
	 * @param string $data The data to encrypt
	 * @param string $key The key used to store the crypt
	 */
	public static function Encrypt($data, $key)
	{
		if($data != "")
		{
			$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);
			$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
			$key = hash('sha256', $key, true);
			return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $data, MCRYPT_MODE_ECB, $iv));
		}
		else
		{
			return "";
		}
	}

	/**
	 * Decrypt a string using AES 256 encryption
	 *
	 * @param string $data The encrypted data to decrypt
	 * @param string $key The key used to store the crypt
	 */
	public static function Decrypt($data, $key)
	{
		if($data != "")
		{
			$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
			$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
			$key = hash('sha256', $key, true);
			return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, base64_decode($data), MCRYPT_MODE_ECB, $iv));
		}
		else
		{
			return "";
		}
	}
}