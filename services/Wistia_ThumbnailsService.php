<?php
namespace Craft;

require_once(CRAFT_PLUGINS_PATH . '/wistia/helpers/WistiaHelper.php');

class Wistia_ThumbnailsService extends BaseApplicationComponent
{
	public $relativeCachePath;

	public function __construct()
	{
		$this->relativeCachePath = craft()->plugins
			->getPlugin('wistia')
			->getSettings()
			->thumbnailPath;
	}

	public function getThumbnail($thumbData, $transform)
	{
		// Set default size
		$defaultWidth = '1280';
		$defaultHeight = '720';

		// Set the base filename
		$hashedId = WistiaHelper::getValue('hashedId', $thumbData);
		$filename = $hashedId;

		// Extract the thumbnail URL from the video data
		$thumbnail = strtok(WistiaHelper::getValue('url', $thumbData), '?');

		$filename .= '_' . $defaultWidth . '_' . $defaultHeight . '.jpg';

		$fullCachePath = $_SERVER['DOCUMENT_ROOT'] . $this->relativeCachePath;
		$cacheFile = $fullCachePath . $filename;

		// Check for cached/current thumbnail before retrieving
		if (! file_exists($cacheFile) ||
			(filemtime($cacheFile) < (time() - (int) craft()->plugins->getPlugin('wistia')->getSettings()->cacheDuration * 3600)) ||
			! filesize($cacheFile))
		{
			$thumbnail .= '?image_crop_resized=' . $defaultWidth . 'x' . $defaultHeight;

			copy($thumbnail, $cacheFile);

			if (! filesize($cacheFile)) {
				unlink($cacheFile);
			}
		}

		if (WistiaHelper::getValue('width', $transform)) {
			$url = $this->transformThumbnail($fullCachePath, $cacheFile, $hashedId, $transform);
		} else {
			$url = $this->relativeCachePath . $filename;
		}

		return $url;
	}

	public function transformThumbnail($fullCachePath, $cacheFile, $hashedId, $transform) {
		$width = WistiaHelper::getValue('width', $transform);
		$height = WistiaHelper::getValue('height', $transform);

		// Get the image
		$image = craft()->images->loadImage($cacheFile);

		// Transform the image
		$image->scaleAndCrop($width, $height);

		// Rename the image
		$newName = $hashedId . '_' . $width . '_' . $height . '.jpg';
		$newFullCachePath = $fullCachePath . $newName;

		// Save the image
		$image->saveAs($newFullCachePath);

		return $this->relativeCachePath . $newName;
	}
}