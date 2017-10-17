<?php
namespace Craft;

class WistiaPlugin extends BasePlugin
{
	private $name = 'Wistia';
	private $version = '0.4.2';
	private $description = 'Powerful fieldtype and template tags for Wistia videos.';

	public function getName()
	{
		return $this->name;
	}

	public function getVersion()
	{
		return $this->version;
	}

	public function getSchemaVersion()
	{
		return '1.0.0';
	}

	public function getDescription()
	{
		return Craft::t($this->description);
	}

	public function getDeveloper()
	{
		return 'Caddis';
	}

	public function getDeveloperUrl()
	{
		return 'https://www.caddis.co';
	}

	public function getDocumentationUrl()
	{
		return 'https://github.com/caddis/craft-wistia';
	}

	public function getReleaseFeedUrl()
	{
		return 'https://raw.githubusercontent.com/caddis/craft-wistia/master/releases.json';
	}

	protected function defineSettings()
	{
		return array(
			'apiKey' => AttributeType::String,
			'cacheDuration' => array(
				AttributeType::Number,
				'default' => 24
			),
			'thumbnailPath' => array(
				AttributeType::String,
				'default' => '/images/videos/'
			)
		);
	}

	public function getSettingsHtml()
	{
		return craft()->templates->render('wistia/plugin/settings', array(
			'settings' => $this->getSettings()
		));
	}

	public function registerCachePaths()
	{
		$cachePath = $_SERVER['DOCUMENT_ROOT'] .
			$this->getSettings()->thumbnailPath;

		return array(
			$cachePath => Craft::t('Wistia preview images')
		);
	}

	/**
	 * Modify data just before importing
	 * https://github.com/boboldehampsink/import#modifyimportrow
	 *
	 * @param BaseElementModel $element The current element receiving import data.
	 * @param array $map Mapping of data between CSV -> Craft fields.
	 * @param array $data Raw data provided for this row.
	 */
	public function modifyImportRow($element, $map, $data)
	{
		$content = [];
		$videos = [];

		// Build video reference
		foreach ($element->getFieldLayout()->getFields() as $field) {
			$fieldModel = $field->getField();
			$attributes = $fieldModel->getAttributes();

			if ($fieldModel->type === 'Wistia_Videos') {
				$videos[$attributes['handle']] = $attributes['settings']['projects'];
			}
		}

		$contentModel = $element->getContent();

		foreach (array_combine($map, $data) as $handle => $value) {
			if (isset($videos[$handle])) {
				if (! empty(trim($value))) {
					$videoTitles = explode(',', $value);
					$value = [];

					foreach($videoTitles as $videoTitle) {
						$value[] = array_search(
							$videoTitle,
							craft()->wistia_video->getVideosByProjectId($videos[$handle])
						);
					}
				} else {
					// Set the value to empty string so Wistia does not throw error
					$value = '';
				}
			} else {
				$value = $contentModel->getAttribute($handle);
			}

			$content[$handle] = $value;
		}

		// Set modified content
		$element->setContentFromPost($content);
	}
}