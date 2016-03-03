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
	 * Modify data before it is stored
	 *
	 * @param array $value
	 * @return string
	 */
	public function prepValueFromPost($value)
	{
		$value = json_encode($value);

		return $value;
	}

	/**
	 * Modify stored data for output
	 *
	 * @param array $value
	 * @return string
	 */
	public function prepValue($value)
	{
		$value = json_decode($value);

		return craft()->wistia_videos->getVideos($value);
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
		if ($this->apiKey) {
			// Include JavaScript
			craft()->templates->includeJsResource('wistia/js/input.min.js');

			$template = 'wistia/fieldtype/input';

			$videos = array();

			// Build selected video output
			$selVideos = craft()->wistia_videos
				->getVideosByHashedId($value['value']);

			if ($selVideos) {
				foreach ($selVideos as $selVideo) {
					$videos[] = array(
						'id' => $selVideo['hashed_id'],
						'title' => $selVideo['name']
					);
				}
			}

			$params = array(
				'id' => craft()->templates->formatInputId($name),
				'name'  => $name,
				'projects' => $this->getSettings()->projects,
				'settings' => $this->getSettings(),
				'value' => $value,
				'videos' => $videos
			);
		} else {
			$template = 'wistia/fieldtype/errors';
		}

		return craft()->templates->render($template, $params);
	}

	/**
	 * Returns the label for the Options setting
	 *
	 * @access protected
	 * @return string
	 */
	protected function getOptionsSettingsLabel()
	{
		return 'Wistia ' . Craft::t('Options');
	}

	/**
	 * Define field settings
	 *
	 * @access protected
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

	/**
	 * Render field settings
	 *
	 * @return array
	 */
	public function getSettingsHtml()
	{
		if ($this->apiKey) {
			$template = 'wistia/fieldtype/settings';

			$params = array(
				'settings' => $this->getSettings(),
				'projects' => craft()->wistia_videos->getProjects()
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
}