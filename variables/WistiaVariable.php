<?php
namespace Craft;

class WistiaVariable
{
	public function videos($params = [])
	{
		return craft()->wistia_videos->getVideosByHashedId($params);
	}

	public function thumbnail($hashedId, $width = '', $height = '')
	{
		// TODO: just doing some initial testing
		return $hashedId;
	}
}