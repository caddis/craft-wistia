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

		return new Wistia_VideosModel($value);
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
			$template = 'wistia/fieldtype/input';

			$videos = [];

			$selVideos = craft()->wistia_videos->getVideosByHashedId($value['value']);

			if ($selVideos) {
				foreach ($selVideos as $selVideo) {
					$videos[] = [
						'id' => $selVideo['hashed_id'],
						'title' => $selVideo['name']
					];
				}
			}

			$params = [
				'settings' => $this->getSettings(),
				'id' => craft()->templates->formatInputId($name),
				'name'  => $name,
				'value' => $value,
				'videos' => $videos,
				'projects' => $this->getSettings()->projects
			];
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
		return Craft::t('Wistia Options');
	}

	/**
	 * Define field settings
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
	 * Render field settings
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