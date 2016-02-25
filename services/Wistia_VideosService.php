<?php
namespace Craft;

use \Guzzle\Http\Client;

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
	 * Get videos from model to output on front end and in cp
	 *
	 * @param array $value
	 */
	public function getVideos($value)
	{
		return new Wistia_VideosModel($value);
	}

	/**
	 * Get videos from API or cache
	 *
	 * @param array $hashedIds
	 * @param array $params
	 * @return array
	 */
	public function getVideosByHashedId($hashedIds, $params = [])
	{
		if (! $hashedIds) {
			return false;
		}

		$offset = isset($params['offset']) ? $params['offset'] : '';

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
					->cacheDuration * 3600;

				craft()->cache->set($cacheKey, $video, $duration);
			}

			// Add embed and thumbnail after caching video data
			$video['embed'] = $embed;
			$video['preview'] = $this->getThumbnail($video);

			$videos[] = $video;
		}

		if ($offset) {
			$videos = array_slice($videos, $offset);
		}

		if (isset($params['limit'])) {
			$videos = array_slice($videos, 0, $params['limit']);
		} else {
			$videos = array_slice($videos, 0, '150');
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
	public function getVideosByProjectId($projects)
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

	public function getThumbnail($video, $dimensions = array())
	{
		// Cast dimensions as (int)
		$dimensions = array_map('intval', $dimensions);

		if (! $dimensions) {
			$dimensions = [
				'width' => 1280,
				'height' => 720
			];
		}

		// Set the base filename
		$filename = $this->getValue('hashed_id', $video);

		// Extract the thumbnail URL from the video data
		$thumbnail = strtok($this->getValue('url', $this->getValue('thumbnail', $video)), '?');

		// Get width parameter
		if (isset($dimensions['width'])) {
			$filename .= '_' . $dimensions['width'];
		}

		// Get height parameter
		if (isset($dimensions['height'])) {
			$filename .= '_' . $dimensions['height'];
		}

		$filename .= '.jpg';

		$cachePath = craft()->plugins->getPlugin('wistia')->getSettings()->thumbnailPath;
		$cacheFile = $_SERVER['DOCUMENT_ROOT'] . $cachePath . $filename;

		// Check for cached/current thumbnail before retrieving
		if (! file_exists($cacheFile) ||
			(filemtime($cacheFile) < (time() - (int) craft()->plugins->getPlugin('wistia')->getSettings()->cacheDuration * 3600)) ||
			! filesize($cacheFile))
		{
			if (isset($dimensions['height']) && isset($dimensions['width'])) {
				$thumbnail .= '?image_crop_resized=' . $dimensions['width'] . 'x' . $dimensions['height'];
			} else if (isset($dimensions['width'])) {
				$thumbnail .= '?image_resize=' . $dimensions['width'];
			}

			copy($thumbnail, $cacheFile);

			if (! filesize($cacheFile)) {
				unlink($cacheFile);
			}
		}

		// Return local path
		return $cachePath . $filename;
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

		$data = $this->send($baseUrl);

		if ($data === false) {
			throw new Exception(lang('error_remote_file') . $baseUrl, 3);
		}

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

		$client = new Client();

		$data = $client->get($url)
			->setAuth('api', $this->apiKey)
			->setHeader('Accept', 'application/json')
			->send()
			->json();

		return $data;
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