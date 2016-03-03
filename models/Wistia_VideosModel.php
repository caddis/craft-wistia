<?php
namespace Craft;

class Wistia_VideosModel extends BaseModel
{
	public $ids;

	public function __construct($value) {
		$this->ids = $value;
	}

	public function getVideos($params = array())
	{
		return craft()->wistia_videos->getVideosByHashedId($this->ids, $params);
	}
}