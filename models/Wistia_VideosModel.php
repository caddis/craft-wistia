<?php
namespace Craft;

class Wistia_VideosModel extends BaseModel
{
	public $value;

	public function __construct($value) {
		$this->value = $value;
	}

	public function getVideos($params = array())
	{
		return craft()->wistia_videos->getVideosByHashedId($this->value, $params);
	}
}