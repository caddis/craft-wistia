<?php
namespace Craft;

class Wistia_VideosFieldType extends BaseOptionsFieldType
{
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
	 * Defines the settings
	 *
	 * @access protected
	 * @return array
	 */
	protected function defineSettings()
	{
		return array(
			'projects' => array(
				AttributeType::Mixed,
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
		return craft()->templates->render('wistia/fieldtype/settings', array(
			'settings' => $this->getSettings(),
			'projectList' => craft()->wistia->getProjects()
		));
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