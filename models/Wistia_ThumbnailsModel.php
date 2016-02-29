<?php
namespace Craft;

class Wistia_ThumbnailsModel extends BaseModel
{
	public $_thumbnail;

	public function __construct($thumbnail) {
		$this->_thumbnail = $thumbnail;
	}

	public function getUrl($transform = array())
	{
		return craft()->wistia_thumbnails->getThumbnailUrl($this->_thumbnail, $transform);
	}
}