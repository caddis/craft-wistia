<?php
namespace Craft;

require_once(CRAFT_PLUGINS_PATH . '/wistia/helpers/WistiaHelper.php');

class Wistia_ThumbnailService extends BaseApplicationComponent
{
	private $absoluteCachePath;
	private $cacheDuration;
	private $relativeCachePath;
	private $defaultWidth = '1280';
	private	$defaultHeight = '720';

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

		$this->absoluteCachePath = $_SERVER['DOCUMENT_ROOT'] .
			$this->relativeCachePath;
	}

	/**
	 * Get default sized external thumbnail url.
	 *
	 * @param string $thumbnailUrl
	 * @return string
	 */
	public function getExternalThumbnailUrl($thumbnailUrl)
	{
		return strtok($thumbnailUrl, '?') . '?image_crop_resized=' . $this->defaultWidth . 'x' . $this->defaultHeight;
	}

	/**
	 * Download video screenshot and return local URL
	 *
	 * @param array $video
	 * @param array $transform
	 * @return string
	 */
	public function getPreviewUrl($video, $transform)
	{
		// Set the base filename
		$hashedId = WistiaHelper::getValue('hashedId', $video);
		$filename = $hashedId;

		// Update filename with default width and height
		$filename .= '-' . $this->defaultWidth . '-' . $this->defaultHeight . '.jpg';
		$cachedFile = $this->absoluteCachePath . $filename;

		// Check whether thumbnail exists and/or has not expired
		if (! DateTimeHelper::wasWithinLast($this->cacheDuration, IOHelper::getLastTimeModified($cachedFile))) {
			copy($this->getExternalThumbnailUrl($video['thumbnail']['url']), $cachedFile);
		}

		return WistiaHelper::getValue('width', $transform) ?
			$this->transformThumbnail($cachedFile, $hashedId, $transform) :
			$this->relativeCachePath . $filename;
	}

	/**
	 * Transform the thumbnail and return new URL
	 *
	 * @param string $originalCachedFile
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
		if (IOHelper::fileExists($originalCachedFile) && ! DateTimeHelper::wasWithinLast(
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