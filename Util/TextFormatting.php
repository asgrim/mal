<?php

	/**
	 * Various useful text formatting functions
	 * I guess generally these will be static functions
	 *
	 * @author James Titcumb <hello@jamestitcumb.com>,
	 *         Jon Wigham <jon.wigham@gmail.com>
	 * @license https://github.com/Asgrim/MAL/raw/master/LICENSE The BSD License
	 * @copyright Copyright (c) 2011, James Titcumb
	 */
	class MAL_Util_TextFormatting
	{
		/**
		 * Generate a slug based on arbitrary text
		 *
		 * @author Jon Wigham <jon.wigham@gmail.com>
		 *
		 * @param string $value The arbitrary text to slug-ify
		 * @return string
		 */
		public static function MakeSlug($value)
		{
			$slug = strtolower($value);
			$slug = preg_replace("/[^a-zA-Z0-9\s]/", "", $slug);
			$slug = trim($slug);
			$slug = preg_replace("/[\s+^$]/", "-", $slug);

			return $slug;
		}
		
		/**
		 * Reverse of self::MakeSlug 
		 * 
		 * @param string $value 
		 * @return string
		 */
		public static function UnmakeSlug($value)
		{
			$plain_text = ucfirst($value);
			$plain_text = preg_replace("/[-]/", " ", $plain_text);
			
			return $plain_text;
		}
	}
