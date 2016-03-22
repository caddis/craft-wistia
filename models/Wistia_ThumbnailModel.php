<?php
namespace Craft;

class Wistia_ThumbnailModel extends BaseModel
{
	private $thumbnail;

	public function __construct($thumbnail) {
		$this->thumbnail = $thumbnail;
	}

	/**
	 * Get thumbnail url
	 *
	 * @param array $transform (optional)
	 * @return string
	 */
	public function getUrl($transform = array())
	{
		return craft()->wistia_thumbnails->getThumbnailUrl($this->thumbnail, $transform);
	}
}