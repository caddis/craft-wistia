<?php
namespace Craft;

require_once(CRAFT_PLUGINS_PATH . '/wistia/helpers/WistiaHelper.php');

class Wistia_ThumbnailsService extends BaseApplicationComponent
{
	public function getThumbnail($thumbData, $dimensions = array())
	{
		// Cast dimensions as (int)
		$dimensions = array_map('intval', $dimensions);

		if (! $dimensions) {
			$dimensions = [
				'width' => 1280,
				'height' => 720
			];
		}

		// Set the base filename
		$filename = WistiaHelper::getValue('hashedId', $thumbData);

		// Extract the thumbnail URL from the video data
		$thumbnail = strtok(WistiaHelper::getValue('url', $thumbData), '?');

		// Get width parameter
		if (isset($dimensions['width'])) {
			$filename .= '_' . $dimensions['width'];
		}

		// Get height parameter
		if (isset($dimensions['height'])) {
			$filename .= '_' . $dimensions['height'];
		}

		$filename .= '.jpg';

		$cachePath = craft()->plugins->getPlugin('wistia')->getSettings()->thumbnailPath;
		$cacheFile = $_SERVER['DOCUMENT_ROOT'] . $cachePath . $filename;

		// Check for cached/current thumbnail before retrieving
		if (! file_exists($cacheFile) ||
			(filemtime($cacheFile) < (time() - (int) craft()->plugins->getPlugin('wistia')->getSettings()->cacheDuration * 3600)) ||
			! filesize($cacheFile))
		{
			if (isset($dimensions['height']) && isset($dimensions['width'])) {
				$thumbnail .= '?image_crop_resized=' . $dimensions['width'] . 'x' . $dimensions['height'];
			} else if (isset($dimensions['width'])) {
				$thumbnail .= '?image_resize=' . $dimensions['width'];
			}

			copy($thumbnail, $cacheFile);

			if (! filesize($cacheFile)) {
				unlink($cacheFile);
			}
		}

		// Return local path
		return $cachePath . $filename;
	}

	public function resizeThumbnail() {
		$imgClass = new Image;

		$imgClass->loadImage($cacheFile);

		$imgClass->resize(200, 300);

		$imgClass->saveAs($_SERVER['DOCUMENT_ROOT'] . craft()->plugins->getPlugin('wistia')->getSettings()->thumbnailPath);
	}
}