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
		$videos = craft()->wistia_videos
			->getVideos($this->getSettings()->projects);

		return craft()->templates->render('wistia/fieldtype', [
			'settings' => $this->getSettings(),
			'name'  => $name,
			'value' => $value,
			'videos' => $videos
		]);
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
		return craft()->templates->render('wistia/fieldtype/settings', [
			'settings' => $this->getSettings(),
			'projects' => craft()->wistia_videos->getProjects()
		]);
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