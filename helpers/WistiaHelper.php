<?php
namespace Craft;

class WistiaHelper
{
	/**
	 * Function to safely return the value of an array
	 *
	 * @param $needle
	 * @param $haystack
	 * @return bool
	 */
	public static function getValue($needle, $haystack)
	{
		return array_key_exists($needle, $haystack) ? $haystack[$needle] : false;
	}
}