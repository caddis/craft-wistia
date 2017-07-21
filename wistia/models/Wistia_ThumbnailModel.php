<?php
namespace Craft;

class Wistia_ThumbnailModel extends BaseModel
{
	private $thumbnail;

	public function __construct($thumbnail) {
		parent::__construct();

		$this->thumbnail = $thumbnail;
	}

	/**
	 * Get thumbnail URL
	 *
	 * @param array $transform (optional)
	 * @return string
	 */
	public function getUrl($transform = array())
	{
		return craft()->wistia_thumbnail
			->getPreviewUrl($this->thumbnail, $transform);
	}
}