<?php
namespace Craft;

class Wistia_VideosFieldType extends BaseOptionsFieldType
{
	private $apiKey;

	public function __construct()
	{
		$this->apiKey = craft()
			->plugins
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
		return Craft::t('Wistia Videos');
	}

	/**
	 * Modify stored data
	 *
	 * @var $value Wistia video id from db
	 * @return string
	 */
	public function prepValue($value)
	{
		return $value;
	}

	/**
	 * Render fieldtype
	 *
	 * @return string
	 */
	public function getInputHtml($name, $value)
	{
		$videos = craft()
			->wistia_apiConnect
			->getVideos($this->getSettings()->projects);

		if (is_array($videos)) {
			$results = 'wistia/fieldtype', [
				'name'  => $name,
				'value' => $value,
				'videos' => $videos
			]);
		} else {
			$results = 'wistia/fieldtype/errors', [
				'errors' => array(
					'error' => $videos
				)
			]);
		}

		return craft()->templates->render($results);
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
	 * Returns the label for the Options setting.
	 *
	 * @access protected
	 * @return string
	 */
	protected function getOptionsSettingsLabel()
	{
		return Craft::t('Wistia Video Options');
	}

	/**
	 * Define the settings
	 *
	 * @access protected
	 * @return array
	 */
	protected function defineSettings()
	{
		return array(
			'projects' => [
				AttributeType::Mixed,
				'default' => [
					'' => '--'
				]
			],
			'min' => [
				AttributeType::Number,
				'default' => 0
			],
			'max' => [
				AttributeType::Number
			]
		);
	}

	/**
	 * Display the field settings
	 *
	 * @return string
	 */
	public function getSettingsHtml()
	{
		$projects = craft()->wistia_apiConnect->getProjects();

		if (is_array($projects)) {
			$results = 'wistia/fieldtype/settings', [
				'settings' => $this->getSettings()
			]);
		} else {
			$results = 'wistia/fieldtype/errors', [
				'errors' => [
					'error' => $projects
				]
			]);
		}

		return craft()->templates->render($results);
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