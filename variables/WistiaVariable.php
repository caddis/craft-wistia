<?php
namespace Craft;

class WistiaVariable
{
	public function videos($wistiaIds, $params = [])
	{
		$videos = [];

		$wistiaIds = json_decode($wistiaIds);

		foreach ($wistiaIds as $wistiaId) {
			$videos[] = craft()->wistia_videos->getVideoByHashedId($wistiaId, $params);
		}

		return $videos;
	}
}