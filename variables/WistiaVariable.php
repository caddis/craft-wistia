<?php
namespace Craft;

class WistiaVariable
{
	public function videos($params = [])
	{
		return craft()->wistia_videos->getVideosByHashedId($params);
	}
}