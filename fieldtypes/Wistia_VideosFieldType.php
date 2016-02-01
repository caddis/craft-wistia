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

		return craft()->templates->render('wistia/fieldtype', [
			'settings' => $this->getSettings(),
			'name'  => $name,
			'value' => $value,
			'videos' => $videos
		]);
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
			'max' => AttributeType::Number
		);
	}

	/**
	 * Display the field settings
	 *
	 * @return string
	 */
	public function getSettingsHtml()
	{
		return craft()->templates->render('wistia/fieldtype/settings', [
			'settings' => $this->getSettings(),
			'projects' => craft()->wistia_apiConnect->getProjects()
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