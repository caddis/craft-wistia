<?php
namespace Craft;

class Wistia_ThumbnailsModel extends BaseModel
{
	private $thumbnail;

	public function __construct($thumbnail) {
		$this->thumbnail = $thumbnail;
	}

	/**
	 * Get thumbnail url
	 *
	 * @param array $transform
	 * @return string
	 */
	public function getUrl($transform = array())
	{
		return craft()->wistia_thumbnails->getThumbnailUrl($this->thumbnail, $transform);
	}
}