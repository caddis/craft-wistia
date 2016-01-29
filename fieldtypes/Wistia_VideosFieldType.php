<?php
namespace Craft;

class Wistia_VideosFieldType extends BaseOptionsFieldType
{
	private $apiKey;

	public function __construct()
	{
		$this->apiKey = craft()->plugins->getPlugin('wistia')->getSettings()->apiKey;
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
		$videos = craft()->wistia_apiConnect->getVideos($this->getSettings()->projects);

		if (is_array($videos)) {
			$results = craft()->templates->render('wistia/fieldtype', array(
				'name'  => $name,
				'value' => $value,
				'videos' => $videos
			));
		} else {
			$results = craft()->templates->render(
				'wistia/fieldtype/errors', array(
					'errors' => array(
						'error' => $videos
					)
				)
			);
		}

		return $results;
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
			'projects' => array(
				AttributeType::Mixed,
				'default' => array(
					'' => '--'
				)
			),
			'min' => array(
				AttributeType::Number,
				'default' => 0
			),
			'max' => array(
				AttributeType::Number
			)
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
			$results = craft()->templates->render(
				'wistia/fieldtype/settings', array(
					'settings' => $this->getSettings()
				)
			);
		} else {
			$results = craft()->templates->render(
				'wistia/fieldtype/errors', array(
					'errors' => array(
						'error' => $projects
					)
				)
			);
		}

		return $results;
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