<?php
namespace Craft;

class WistiaVariable
{
	public function videos($wistiaIds)
	{
		return craft()->wistia_apiConnect->getVideosByHashedIds($wistiaIds);
	}
}