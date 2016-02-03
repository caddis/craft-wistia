<?php
namespace Craft;

class WistiaVariable
{
	public function videos($wistiaIds, $params = [])
	{
		return craft()->wistia_apiConnect->getVideosByHashedIds($wistiaIds, $params);
	}
}