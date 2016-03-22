<?php
namespace Craft;

require_once(CRAFT_PLUGINS_PATH . '/wistia/helpers/WistiaHelper.php');

class Wistia_ThumbnailService extends BaseApplicationComponent
{
	private $cacheDuration;
	private $relativeCachePath;
	private $absoluteCachePath;

	public function __construct()
	{
		$this->cacheDuration = craft()->plugins
			->getPlugin('wistia')
			->getSettings()
			->cacheDuration . 'hours';

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
	 * @return array
	 */
	public function getThumbnail($thumbData)
	{
		return new Wistia_ThumbnailModel($thumbData);
	}

	/**
	 * Get the new downloaded thumbnail url
	 *
	 * @param array $thumbData
	 * @param array $transform
	 * @return string
	 */
	public function getThumbnailUrl($thumbData, $transform)
	{
		$defaultWidth = '1280';
		$defaultHeight = '720';

		// Set the base filename
		$hashedId = WistiaHelper::getValue('hashedId', $thumbData);
		$filename = $hashedId;

		// Extract the thumbnail URL from the video data
		$thumbnail = strtok(WistiaHelper::getValue('url', $thumbData), '?') .
			'?image_crop_resized=' . $defaultWidth . 'x' . $defaultHeight;

		// Update filename with default width and height
		$filename .= '-' . $defaultWidth . '-' . $defaultHeight . '.jpg';

		$cachedFile = $this->absoluteCachePath . $filename;

		// Check whether thumbnail exists and/or has not expired
		if (! DateTimeHelper::wasWithinLast(
			$this->cacheDuration,
			IOHelper::getLastTimeModified($cachedFile))
		) {
			copy($thumbnail, $cachedFile);
		}

		// Check if transform is defined
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
	 * @return string
	 */
	public function transformThumbnail($originalCachedFile, $hashedId, $transform) {
		$width = WistiaHelper::getValue('width', $transform);
		$height = WistiaHelper::getValue('height', $transform);

		$filename = $hashedId . '-' . $width . '-' . $height . '.jpg';
		$cachedFile = $this->absoluteCachePath . $filename;

		// Check whether thumbnail exists and/or has not expired
		if (! DateTimeHelper::wasWithinLast(
				$this->cacheDuration,
				IOHelper::getLastTimeModified($cachedFile))
			) {
			craft()->images
				->loadImage($originalCachedFile)
				->scaleAndCrop($width, $height)
				->saveAs($cachedFile);
		}

		return $this->relativeCachePath . $filename;
	}
}