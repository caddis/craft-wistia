<?php
namespace Craft;

class Wistia_ThumbnailsService extends BaseApplicationComponent
{
	public function resizeThumbnail() {
		$imgClass = new Image;

		$imgClass->loadImage($cacheFile);

		$imgClass->resize(200, 300);

		$imgClass->saveAs($_SERVER['DOCUMENT_ROOT'] . craft()->plugins->getPlugin('wistia')->getSettings()->thumbnailPath);
	}
}