<?php
namespace Craft;

class Wistia_VideosFieldType extends BaseOptionsFieldType
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
	 * Fieldtype name
	 *
	 * @return string
	 */
	public function getName()
	{
		return Craft::t('Wistia');
	}

	/**
	 * Define database column
	 *
	 * @return AttributeType::String
	 */
	public function defineContentAttribute()
	{
		return array(AttributeType::String);
	}

	/**
	 * Modify stored data
	 *
	 * @param array $value
	 * @return string
	 */
	public function prepValue($value)
	{
		return $value;
	}

	/**
	 * Render fieldtype
	 *
	 * @param string $name
	 * @param string $value
	 * @return string
	 */
	public function getInputHtml($name, $value)
	{
		$params = [];

		if ($this->apiKey) {
    		craft()->templates->includeJsResource('wistia/js/input.js');

			$template = 'wistia/fieldtype/input';

			$values = craft()->wistia_videos->getVideosByHashedId($value);

			$selVideos = [];

			if ($values) {
				foreach ($values as $selVideo) {
					$selVideos[] = $selVideo[0];
				}
			}

			$params = [
				'settings' => $this->getSettings(),
				'name'  => $name,
				'selectedVideos' => $selVideos,
				'videos' => craft()->wistia_videos->getVideos($this->getSettings()->projects),
				'selectionLabel' => 'Add a video'
			];
		} else {
			$template = 'wistia/fieldtype/errors';
		}

		return craft()->templates->render($template, $params);
	}

	/**
	 * Returns the label for the Options setting.
	 *
	 * @access protected
	 * @return string
	 */
	protected function getOptionsSettingsLabel()
	{
		return Craft::t('Wistia Options');
	}

	/**
	 * Define the settings
	 *
	 * @access protected
	 * @return array
	 */
	protected function defineSettings()
	{
		return [
			'projects' => [
				AttributeType::Mixed,
				'default' => '*'
			],
			'min' => [
				AttributeType::Number,
				'default' => 0
			],
			'max' => AttributeType::Number
		];
	}

	/**
	 * Display the field settings
	 *
	 * @return array
	 */
	public function getSettingsHtml()
	{
		$params = [];

		if ($this->apiKey) {
			$template = 'wistia/fieldtype/settings';

			$params = [
				'settings' => $this->getSettings(),
				'projects' => craft()->wistia_videos->getProjects()
			];
		} else {
			$template = 'wistia/fieldtype/errors';
		}

		return craft()->templates->render($template, $params);
	}

	/**
	 * Sanitize the settings
	 *
	 * @param array $settings
	 * @return array
	 */
	public function prepSettings($settings)
	{
		return $settings;
	}
}