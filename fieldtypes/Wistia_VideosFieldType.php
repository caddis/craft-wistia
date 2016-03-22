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
	 * Modify data before it's stored
	 *
	 * @param array $value
	 * @return string
	 */
	public function prepValueFromPost($value)
	{
		return json_encode($value);
	}

	/**
	 * Modify stored data for output
	 *
	 * @param string $value
	 * @return object
	 */
	public function prepValue($value)
	{
		return craft()->wistia_video->getVideos(json_decode($value));
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
		if ($this->apiKey) {
			craft()->templates->includeJsResource('wistia/js/input.min.js');

			$template = 'wistia/fieldtype/input';

			$params = array(
				'id' => craft()->templates->formatInputId($name),
				'name'  => $name,
				'settings' => $this->getSettings(),
				'videos' => $value ? $value->getVideos() : null
			);
		} else {
			$template = 'wistia/fieldtype/errors';
		}

		return craft()->templates->render($template, $params);
	}

	/**
	 * Returns the label for the options setting.
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
}