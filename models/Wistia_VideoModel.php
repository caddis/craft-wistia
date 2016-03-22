<?php
namespace Craft;

class Wistia_VideoModel extends BaseModel
{
	private $value;

	public function __construct($value) {
		$this->value = $value;
	}

	/**
	 * Get video data
	 *
	 * @param array $params (optional)
	 * @return array
	 */
	public function getVideos($params = array())
	{
		return craft()->wistia_video->getVideosByHashedId($this->value, $params);
	}
}