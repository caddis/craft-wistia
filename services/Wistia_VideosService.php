<?php
namespace Craft;

class Wistia_VideosService extends BaseApplicationComponent
{
	private $apiKey;

	const WISTIA_API_URL = 'https://api.wistia.com/v1/';
	const WISTIA_EMBED_URL = 'https://fast.wistia.com/assets/external/E-v1.js';

	public function __construct()
	{
		// Set the API key from the global settings
		$this->apiKey = craft()
			->plugins
			->getPlugin('wistia')
			->getSettings()
			->apiKey;
	}

	/**
	 * Get videos from API or cache
	 *
	 * @param string $hashedIds
	 * @param array $params
	 * @return array
	 */
	public function getVideosByHashedId($params)
	{
		if (! isset($params['hashedIds'])) {
			return false;
		}

		$hashedIds = json_decode($params['hashedIds']);

		// Remove hashed ids from params array
		unset($params['hashedIds']);

		// Determine if video should be responsive
		if (isset($params['responsive'])) {
			$responsive = $params['responsive'];
		} else if (isset($params['width'])) {
			$responsive = 'default';
		} else {
			$responsive = 'true';
		}

		// Set default parameters
		$defaultParams = [
			'autoPlay' => 'default',
			'controlsVisibleOnLoad' => 'true',
			'email' => 'default',
			'endVideoBehavior' => 'pause',
			'fullscreenButton' => 'true',
			'height' => 360,
			'playbar' => 'true',
			'playButton' => 'true',
			'playerColor' => craft()->config->get('wistiaPlayerColor') != null ? craft()->config->get('wistiaPlayerColor') : 'default',
			'smallPlayButton' => 'true',
			'stillUrl' => 'default',
			'time' => 'default',
			'volumeControl' => 'true',
			'width' => 640
		];

		// Merge defaults with input parameters
		$params = array_merge($defaultParams, $params);
		$params['videoFoam'] = $responsive;

		$videos = [];

		foreach ($hashedIds as $hashedId) {
			$cacheKey = 'wistia_video_' . $hashedId;

			// Get embed code
			$embed = $this->getSuperEmbed($hashedId, $params);

			$cachedVideo = craft()->cache->get($cacheKey);

			// Cache Wistia API data
			if ($cachedVideo) {
				$video = $cachedVideo;
			} else {
				$video = current($this->getApiData('medias.json', [
						'hashed_id' => $hashedId
					])
				);

				$video['name'] = htmlspecialchars_decode($video['name']);

				$duration = (int) craft()
					->plugins
					->getPlugin('wistia')
					->getSettings()
					->cacheDuration * 60 * 60;

				craft()->cache->set($cacheKey, $video, $duration);
			}

			// Add embed after caching video data
			$video['embed'] = $embed;

			$videos[] = $video;
		}

		return $videos;
	}

	/**
	 * Function to get an array of available videos given API key and project list.
	 *
	 * @throws Exception if unable to get a list of projects from the API.
	 * @throws Exception if unable to get a list of videos for a project.
	 *
	 * @param array $projects
	 * @return array
	 */
	public function getVideos($projects)
	{
		$cacheString = is_array($projects) ? implode('_', $projects) : '_' . $projects;

		if (($videos = craft()->httpSession->get('project_videos' . $cacheString, false)) !== false) {
			return $videos;
		}

		$videos = [];

		// Add videos from each project
		if (is_array($projects)) {
			// Try to get project names
			try {
				$projectNames = $this->getProjects();
			} catch (Exception $e) {
				throw new Exception(lang('error_no_projects'), 1, $e);
			}

			// If no defined projects, fail out
			if (! is_array($projects) || ! is_array($projectNames)) {
				return false;
			}

			$data = [];

			foreach ($projects as $project) {
				$params = [
					'sort_by' => 'name',
					'project_id' => $project
				];

				// Try to get a list of videos for this project
				try {
					$data = $this->getApiData('medias.json', $params);
				} catch (Exception $e) {
					throw new Exception(lang('error_no_video_list') . $project, 5, $e);
				}

				// Skip empty datasets
				if (! is_array($data)) {
					continue;
				}

				foreach ($data as $video) {
					$hashedId = $this->getValue('hashed_id', $video);
					$name = htmlspecialchars_decode($this->getValue('name', $video));

					$videos[$hashedId] = $name;
				}
			}
		} else {
			$data = $this->getApiData('medias.json', []);

			foreach ($data as $video) {
				$hashedId = $this->getValue('hashed_id', $video);
				$name = htmlspecialchars_decode($this->getValue('name', $video));

				$videos[$hashedId] = $name;
			}
		}

		ksort($videos);

		craft()->httpSession->add('project_videos' . $cacheString, $videos);

		return $videos;
	}

	/**
	 * Function to get an array of available projects given an API key.
	 *
	 * @throws Exception If unable to retrieve a list of projects from the API.
	 *
	 * @access private
	 * @return array
	 */
	public function getProjects()
	{
		// Fail if no API key defined
		if ($this->apiKey === false) {
			throw new Exception(lang('error_no_api_key'), 0);
		}

		if ($projects = craft()->httpSession->get('projects', false)) {
			return $projects;
		}

		$projects = [];
		$params = [
			'sort_by' => 'name'
		];

		try {
			$data = $this->getApiData('projects', $params);
		} catch (Exception $e) {
			throw new Exception(lang('error_no_projects'), 1, $e);
		}

		// Add each project
		foreach ($data as $project) {
			$projects[$this->getValue('id', $project)] = $this->getValue('name', $project);
		}

		craft()->httpSession->add('projects', $projects);

		return $projects;
	}

	/**
	 * Embeds the video as a JS API embed
	 *
	 * @param string $hashedId
	 * @param array $params
	 *
	 * @access private
	 * @return string
	 */
	private function getSuperEmbed($hashedId, $params)
	{
		$params = array_filter($params, function($val) {
			return $val !== 'default';
		});

		$settings = http_build_query($params, '', ' ');

		$oldPath = craft()->path->getTemplatesPath();

		$newPath = craft()->path->getPluginsPath().'wistia/templates';

		craft()->path->setTemplatesPath($newPath);

		$html = craft()->templates->render('fieldtype/embed', [
			'embedUrl' => self::WISTIA_EMBED_URL,
			'settings' => $settings,
			'hashedId' => $hashedId,
			'width' => $params['width'],
			'height' => $params['height']
		]);

		craft()->path->setTemplatesPath($oldPath);

		return TemplateHelper::getRaw($html);
	}

	/**
	 * Function to return an API URL
	 *
	 * @param string $endpoint	 The Wistia API endpoint to query.
	 * @param array  $params Additional parameters to append to the request.
	 *
	 * @throws Exception If no API key is defined.
	 * @throws Exception If video data is requested with an id that is blank or 0.
	 * @throws Exception If unable to download the JSON data from the API provider.
	 *
	 * @access private
	 * @return string The formatted URL.
	 */
	private function getApiData($endpoint, $params = [], $page = false)
	{
		// Set the base URL from the global settings
		$baseUrl = self::WISTIA_API_URL;

		$apiParams = array(
			'per_page=100'
		);

		if ($page) {
			$apiParams[] = '&page=' . $page;
		}

		foreach ($params as $key => $value) {
			$apiParams[] = "$key=$value";
		}

		$url_params = '?' . implode('&', $apiParams);

		$baseUrl .= $endpoint . $url_params;

		// Return JSON-decoded stream
		$jsonData = $this->send($baseUrl);

		if ($jsonData === false) {
			throw new Exception(lang('error_remote_file') . $baseUrl, 3);
		}

		$data = json_decode($jsonData, true);

		if (count($data) === 100) {
			$this->getApiData($endpoint, $params, $page + 1);
		}

		return $data;
	}

	private function send($url)
	{
		// Fail if no API key defined
		if ($this->apiKey === false) {
			throw new Exception(lang('error_no_api_key'), 0);
		}

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_USERPWD, 'api:' . $this->apiKey);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);

		$result = curl_exec($ch);

		curl_close($ch);

		return $result;
	}

	/**
	 * Function to safely return the value of an array
	 *
	 * @param string $needle   The value to look for.
	 * @param array  $haystack The array to search in.
	 *
	 * @return mixed False on failure, or the array at position $needle.
	 */
	private function getValue($needle, $haystack)
	{
		return array_key_exists($needle, $haystack) ? $haystack[$needle] : false;
	}
}