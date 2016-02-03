<?php
namespace Craft;

class Wistia_ApiConnectService extends BaseApplicationComponent
{
	private $apiKey;

	const WISTIA_API_URL = 'https://api.wistia.com/v1/';

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
	 * @param $hashedIds
	 * @return array
	 */
	public function getVideosByHashedIds($hashedIds)
	{
		if (! $hashedIds) {
			return false;
		}

		$hashedIds = json_decode($hashedIds);

		$videos = [];

		foreach ($hashedIds as $hashedId) {
			$cachedVideo = craft()->cache->get($hashedId);

			if ($cachedVideo) {
				$video = $cachedVideo;
			} else {
				$params = [
					'hashed_id' => $hashedId
				];

				$video = $this->getApiData('medias.json', $params)[0];

				$duration = (int) craft()->plugins->getPlugin('wistia')->getSettings()->cacheDuration * 60 * 60;

				craft()->cache->set($hashedId, $video, $duration);
			}

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
	 * @access private
	 * @return array
	 */
	public function getVideos($projects)
	{
		$projects = is_array($projects) ? $projects : [];

		$cacheString = implode('_', $projects);

		if (($videos = craft()->httpSession->get('project_videos' . $cacheString, false)) !== false) {
			return $videos;
		}

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

		// Add videos from each project
		$videos = [];

		foreach ($projects as $project) {
			$params = [
				'sort_by' => 'name'
			];

			if ($project !== '*') {
				$params['project_id'] = $project;
			}

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

			// Add each video
			foreach ($data as $video) {
				$hashedId = $this->getValue('hashed_id', $video);
				$name = $this->getValue('name', $video);
				$section = $this->getValue('section', $video); // TODO: Not sure why this is here. Copied from original.

				$videos[$hashedId] = $name;
			}

			if ($project === '*') {
				break;
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
	public function getProjects() {
		// Fail if no API key defined
		if ($this->apiKey === false) {
			throw new Exception(lang('error_no_api_key'), 0);
		}

		if (($projects = craft()->httpSession->get('projects', false)) !== false) {
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
			$id = $this->getValue('id', $project);
			$name = $this->getValue('name', $project);

			$projects[$id] = $name;
		}

		craft()->httpSession->add('projects', $projects);

		return $projects;
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
	private function getApiData($endpoint, $params = [], $page = false) {
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

		// TODO: Not sure why this is needed. Throwing a Craft error.
		// if ($page) {
		// 	foreach ($data as $val) {
		// 		$this->data[] = $val;
		// 	}
		// } else {
		// 	$this->data = $data;
		// 	$page = 1;
		// }

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
		if (! is_array($haystack) || ! array_key_exists($needle, $haystack)) {
			return false;
		}

		return $haystack[$needle];
	}
}