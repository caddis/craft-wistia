<?php
namespace Craft;

class WistiaPlugin extends BasePlugin
{
	private $name = 'Wistia';
	private $version = '0.3.5';
	private $description = 'Powerful fieldtype and template tags for Wistia videos.';

	public function getName()
	{
		return $this->name;
	}

	public function getVersion()
	{
		return $this->version;
	}

	public function getSchemaVersion()
	{
		return '1.0.0';
	}

	public function getDescription()
	{
		return Craft::t($this->description);
	}

	public function getDeveloper()
	{
		return 'Caddis';
	}

	public function getDeveloperUrl()
	{
		return 'https://www.caddis.co';
	}

	public function getDocumentationUrl()
	{
		return 'https://github.com/caddis/craft-wistia';
	}

	public function getReleaseFeedUrl()
	{
		return 'https://raw.githubusercontent.com/caddis/craft-wistia/master/releases.json';
	}

	protected function defineSettings()
	{
		return array(
			'apiKey' => AttributeType::String,
			'cacheDuration' => array(
				AttributeType::Number,
				'default' => 24
			),
			'thumbnailPath' => array(
				AttributeType::String,
				'default' => '/images/videos/'
			)
		);
	}

	public function getSettingsHtml()
	{
		return craft()->templates->render('wistia/plugin/settings', array(
			'settings' => $this->getSettings()
		));
	}

	public function registerCachePaths()
	{
		$cachePath = $_SERVER['DOCUMENT_ROOT'] .
			$this->getSettings()->thumbnailPath;

		return array(
			$cachePath => Craft::t('Wistia preview images')
		);
	}
}