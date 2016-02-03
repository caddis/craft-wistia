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
	 * @var $value Wistia video ids from db
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
	 * Save videos to wistia_videos db
	 *
	 * @return array
	 */
	public function onAfterElementSave()
	{
		// TODO: add session caching
		// if (($data = ee()->session->cache('wisteea', $this->name(), false)) !== false) {
		// } elseif (isset($this->settings['entry_id'])) {
		// 	$entry_id = $this->settings['entry_id'];

		// 	ee()->wisteea_lib->remove_videos($entry_id, $field_id, $row_id, $col_id);
		// }

		$field = $this->model;
		$element = $this->element;

		return craft()->wistia_apiConnect->saveVideos($field, $element);
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