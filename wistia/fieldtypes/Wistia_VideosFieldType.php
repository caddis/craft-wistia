<?php
namespace Craft;

use Guzzle\Http\Client;

class Wistia_VideosFieldType extends BaseFieldType
{
	private $apiKey;

	public function __construct()
	{
		$this->apiKey = craft()->plugins
			->getPlugin('wistia')
			->getSettings()
			->apiKey;
	}

	/**
	 * Return fieldtype name
	 *
	 * @return string
	 */
	public function getName()
	{
		return 'Wistia';
	}

	/**
	 * Define database column
	 *
	 * @return array
	 */
	public function defineContentAttribute()
	{
		return array(AttributeType::String);
	}

	/**
	 * Modify data before it's stored
	 *
	 * @param array|string $hashedIds
	 * @return string
	 */
	public function prepValueFromPost($hashedIds)
	{
		if (is_array($hashedIds)) {
			foreach(craft()->wistia_video->getVideosByHashedId($hashedIds) as $video) {
				$client = new Client(craft()->wistia_thumbnail->getExternalThumbnailUrl($video['thumbnail']['url']));
				$code = $client->get()
					->send()
					->getStatusCode();

				if ($code !== 200) {
					WistiaPlugin::log('Wistia video thumbnail with a hashed id of ' . $video['hashedId']. ' failed to load.', LogLevel::Warning);
				}
			}
		}

		return json_encode($hashedIds);
	}

	/**
	 * Modify stored data for output
	 *
	 * @param string $value
	 * @return object
	 */
	public function prepValue($value)
	{
		$value = json_decode($value);

		return $value ? craft()->wistia_video->getVideos($value) : $value;
	}

	/**
	 * Render fieldtype
	 *
	 * @param string $name
	 * @param object $value
	 * @return string
	 */
	public function getInputHtml($name, $value)
	{
		$params = array();

		if ($this->apiKey) {
			craft()->templates->includeJsResource('wistia/js/input.min.js');

			$template = 'wistia/fieldtype/input';

			$params = array(
				'name'  => $name,
				'id' => craft()->templates->formatInputId($name),
				'settings' => $this->getSettings(),
				'videos' => $value ? $value->getVideos() : null
			);
		} else {
			$template = 'wistia/fieldtype/errors';
		}

		return craft()->templates->render($template, $params);
	}

	/**
	 * Render field settings
	 *
	 * @return array
	 */
	public function getSettingsHtml()
	{
		$params = array();

		if ($this->apiKey) {
			$template = 'wistia/fieldtype/settings';

			$params = array(
				'settings' => $this->getSettings(),
				'projects' => craft()->wistia_video->getProjects()
			);
		} else {
			$template = 'wistia/fieldtype/errors';
		}

		return craft()->templates->render($template, $params);
	}

	/**
	 * Sanitize field settings
	 *
	 * @param array $settings
	 * @return array
	 */
	public function prepSettings($settings)
	{
		return $settings;
	}

	/**
	 * Returns the label for the options setting
	 *
	 * @return string
	 */
	protected function getOptionsSettingsLabel()
	{
		return 'Wistia ' . Craft::t('Options');
	}

	/**
	 * Define field settings
	 *
	 * @return array
	 */
	protected function defineSettings()
	{
		return array(
			'projects' => array(
				AttributeType::Mixed,
				'default' => '*'
			),
			'min' => array(
				AttributeType::Number,
				'default' => 0
			),
			'max' => AttributeType::Number
		);
	}
}