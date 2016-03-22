<?php
namespace Craft;

class WistiaPlugin extends BasePlugin
{
	/**
	 * Name
	 *
	 * @return string
	 */
	public function getName()
	{
		return 'Wistia';
	}

	/**
	 * Description
	 *
	 * @return string
	 */
	public function getDescription()
	{
		return 'Manage videos and output data using the Wistia API.';
	}

	/**
	 * Version
	 *
	 * @return string
	 */
	public function getVersion()
	{
		return '0.2.0';
	}

	/**
	 * Schema version
	 *
	 * @return string
	 */
	public function getSchemaVersion()
	{
		return '1.0.0';
	}

	/**
	 * Developer
	 *
	 * @return string
	 */
	public function getDeveloper()
	{
		return 'Caddis';
	}

	/**
	 * Developer url
	 *
	 * @return string
	 */
	public function getDeveloperUrl()
	{
		return 'https://www.caddis.co';
	}

	/**
	 * Documentation url
	 *
	 * @return string
	 */
	public function getDocumentationUrl()
	{
		return 'https://www.caddis.co/software/craft/wistia';
	}

	/**
	 * Define plugin settings
	 *
	 * @return array
	 */
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

	/**
	 * Output settings into template
	 *
	 * @return mixed
	 */
	public function getSettingsHtml()
	{
		return craft()->templates->render('wistia/plugin/settings', array(
			'settings' => $this->getSettings()
		));
	}

	/**
	 * Clear plugin image cache
	 *
	 * @return array
	 */
	public function registerCachePaths()
	{
		return array(
			$_SERVER['DOCUMENT_ROOT'] . craft()->plugins
				->getPlugin('wistia')
				->getSettings()
				->thumbnailPath => Craft::t('Wistia preview images')
		);
	}
}