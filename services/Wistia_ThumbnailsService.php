<?php
namespace Craft;

require_once(CRAFT_PLUGINS_PATH . '/wistia/helpers/WistiaHelper.php');

class Wistia_ThumbnailsService extends BaseApplicationComponent
{
	public $relativeCachePath;
	public $absoluteCachePath;

	public function __construct()
	{
		$this->relativeCachePath = craft()->plugins
			->getPlugin('wistia')
			->getSettings()
			->thumbnailPath;

		$this->absoluteCachePath = $_SERVER['DOCUMENT_ROOT'] . $this->relativeCachePath;
	}

	/**
	 * Pass the thumbnail data to the model
	 *
	 * @param array $thumbData
	 */
	public function getThumbnail($thumbData)
	{
		return new Wistia_ThumbnailsModel($thumbData);
	}

	/**
	 * Get the new downloaded thumbnail url
	 *
	 * @param array $thumbData
	 * @param array $transform
	 */
	public function getThumbnailUrl($thumbData, $transform)
	{
		// Set default size
		$defaultWidth = '1280';
		$defaultHeight = '720';

		// Set the base filename
		$hashedId = WistiaHelper::getValue('hashedId', $thumbData);
		$filename = $hashedId;

		// Extract the thumbnail URL from the video data
		$thumbnail = strtok(WistiaHelper::getValue('url', $thumbData), '?');

		// Update filename with default width and height
		$filename .= '_' . $defaultWidth . '_' . $defaultHeight . '.jpg';

		$cachedFile = $this->absoluteCachePath . $filename;

		// Check for cached/current thumbnail before retrieving
		if (! file_exists($cachedFile) ||
			(filemtime($cachedFile) < (time() - (int) craft()->plugins->getPlugin('wistia')->getSettings()->cacheDuration * 3600)) ||
			! filesize($cachedFile))
		{
			$thumbnail .= '?image_crop_resized=' . $defaultWidth . 'x' . $defaultHeight;

			copy($thumbnail, $cachedFile);

			if (! filesize($cachedFile)) {
				unlink($cachedFile);
			}
		}

		// Apply transform if one is defined
		if (WistiaHelper::getValue('width', $transform)) {
			$url = $this->transformThumbnail($cachedFile, $hashedId, $transform);
		} else {
			$url = $this->relativeCachePath . $filename;
		}

		return $url;
	}

	/**
	 * Transform the thumbnail
	 *
	 * @param string $cachedFile
	 * @param string $hashedId
	 * @param array $transform
	 */
	public function transformThumbnail($cachedFile, $hashedId, $transform) {
		$width = WistiaHelper::getValue('width', $transform);
		$height = WistiaHelper::getValue('height', $transform);

		// Get the image
		$image = craft()->images->loadImage($cachedFile);

		// Transform the image
		$image->scaleAndCrop($width, $height);

		// Rename the image
		$newName = $hashedId . '_' . $width . '_' . $height . '.jpg';
		$newFullCachePath = $this->absoluteCachePath . $newName;

		// Save the image
		$image->saveAs($newFullCachePath);

		return $this->relativeCachePath . $newName;
	}
}