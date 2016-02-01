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
		$params = [];
		$videos = craft()
			->wistia_apiConnect
			->getVideos($this->getSettings()->projects);

		if (is_array($videos)) {
			$template = 'wistia/fieldtype';
			$params = [
				'name'  => $name,
				'value' => $value,
				'videos' => $videos
			];
		} else {
			$template = 'wistia/fieldtype/errors';
			$params = [
				'errors' => [
					'error' => $videos
				]
			];
		}

		return craft()->templates->render($template, $params);
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
		$params = [];
		$projects = craft()->wistia_apiConnect->getProjects();

		if (is_array($projects)) {
			$template = 'wistia/fieldtype/settings';
			$params = [
				'settings' => $this->getSettings()
			];
		} else {
			$template = 'wistia/fieldtype/errors';
			$params = [
				'errors' => [
					'error' => $projects
				]
			];
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