<?php
namespace Craft;

class Wistia_ThumbnailsModel extends BaseModel
{
	public $_thumbnail;

	public function __construct($thumbnail) {
		$this->_thumbnail = $thumbnail;
	}

	public function getUrl()
	{
		return craft()->wistia_thumbnails->getThumbnail($this->_thumbnail);
	}
}