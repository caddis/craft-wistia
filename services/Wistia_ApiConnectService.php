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

		// TODO: Add session caching with Craft
		// if (($projects = ee()->session->cache(__CLASS__, 'projects', false)) !== false) {
		// 	return $projects;
		// }

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
		$projects['--'] = '-- Any --';

		foreach ($data as $project) {
			$id = $this->getValue('id', $project);
			$name = $this->getValue('name', $project);

			$projects[$id] = $name;
		}

		// TODO: Add session caching with Craft
		// ee()->session->set_cache(__CLASS__, 'projects', $projects);

		return $projects;
	}

	/**
	 * Retrieve videos
	 *
	 * @return array
	 */
	public function getVideos()
	{
		$results = [];
		$rawVideos = json_decode($this->send('medias.json'));

		foreach ($rawVideos as $rawVideo) {
			$results[$rawVideo->id] = $rawVideo->name;
		}

		return $results;
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
	private function getApiData($endpoint, $params = array(), $page = false) {
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
		$json_data = $this->send($baseUrl);

		if ($json_data === false) {
			throw new Exception(lang('error_remote_file') . $baseUrl, 3);
		}

		$data = json_decode($json_data, true);

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