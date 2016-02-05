<?php
namespace Craft;

class WistiaVariable
{
	public function videos($wistiaIds, $params = [])
	{
		return craft()->wistia_videos->getVideosByHashedId($wistiaIds, $params);
	}
}