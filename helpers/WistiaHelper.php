<?php
namespace Craft;

class WistiaHelper
{
	/**
	 * Function to safely return the value of an array
	 *
	 * @param string $needle   The value to look for.
	 * @param array  $haystack The array to search in.
	 *
	 * @return mixed False on failure, or the array at position $needle.
	 */
	public static function getValue($needle, $haystack)
	{
		return array_key_exists($needle, $haystack) ? $haystack[$needle] : false;
	}
}