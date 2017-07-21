<?php
namespace Craft;

use Guzzle\Http\Client;

require_once(CRAFT_PLUGINS_PATH . '/wistia/helpers/WistiaHelper.php');

class Wistia_VideoService extends BaseApplicationComponent
{
	private $apiKey;
	private $apiUrl;
	private $embedUrl;

	public function __construct()
	{
		$this->apiKey = craft()->plugins
			->getPlugin('wistia')
			->getSettings()
			->apiKey;

		$this->apiUrl = 'https://api.wistia.com/v1/';
		$this->embedUrl = 'https://fast.wistia.com/assets/external/E-v1.js';
	}

	/**
	 * Pass video data to the model
	 *
	 * @param array $value
	 * @return Wistia_VideoModel
	 */
	public function getVideos($value)
	{
		return new Wistia_VideoModel($value);
	}

	/**
	 * Get videos by hashed ID
	 *
	 * @param array $hashedIds
	 * @param array $params (optional)
	 * @return array|bool
	 * @throws Exception
	 */
	public function getVideosByHashedId($hashedIds, $params = array())
	{
		if (! $hashedIds) {
			return false;
		}

		$offset = WistiaHelper::getValue('offset', $params);
		$limit = WistiaHelper::getValue('limit', $params);

		// Set default parameters
		$defaultParams = array(
			'autoPlay' => 'default',
			'controlsVisibleOnLoad' => 'false',
			'email' => 'default',
			'endVideoBehavior' => 'pause',
			'fullscreenButton' => 'true',
			'height' => 360,
			'playbar' => 'true',
			'playButton' => 'true',
			'playerColor' => craft()->config->get('playerColor', 'wistia'),
			'smallPlayButton' => 'true',
			'stillUrl' => 'default',
			'time' => 'default',
			'volumeControl' => 'true',
			'width' => 640
		);

		// Remove parameters not specific to the embed
		unset($params['offset']);
		unset($params['limit']);

		// Merge defaults with input parameters
		$params = array_merge($defaultParams, $params);
		$params['videoFoam'] = isset($params['responsive']) ? $params['responsive'] : true;

		$videos = array();

		foreach ($hashedIds as $hashedId) {
			$cacheKey = 'wistia-video-' . $hashedId;

			$embed = $this->getEmbed($hashedId, $params);

			$cachedVideo = craft()->cache->get($cacheKey);

			// Cache Wistia API data
			if ($cachedVideo) {
				$video = $cachedVideo;
			} else {
				$video = current($this->getApiData('medias.json', array(
					'hashed_id' => $hashedId
				)));

				$video['hashedId'] = $hashedId;

				// Remove old embed code
				unset($video['embedCode'], $video['hashed_id']);

				foreach ($video['assets'] as $asset) {
					$type = $asset['type'];
					$url = str_replace('.bin', '/' . $hashedId . '.mp4', $asset['url']);
					$width = $asset['width'];
					$height = $asset['height'];
					$filesize = $asset['fileSize'];

					if ($type === 'OriginalFile') {
						$video['original'] = array(
							'url' => $url,
							'width' => $width,
							'height' => $height,
							'filesize' => $filesize
						);
					} elseif ($type === 'HdMp4VideoFile') {
						$video['high'] = array(
							'url' => $url,
							'width' => $width,
							'height' => $height,
							'filesize' => $filesize
						);
					} elseif ($type === 'IphoneVideoFile') {
						$video['low'] = array(
							'url' => $url,
							'width' => $width,
							'height' => $height,
							'filesize' => $filesize
						);
					}
				}

				$video['name'] = htmlspecialchars_decode($video['name']);

				craft()->cache
					->set($cacheKey, $video, (int) craft()->plugins
						->getPlugin('wistia')
						->getSettings()
						->cacheDuration * 3600
					);
			}

			$video['preview'] = new Wistia_ThumbnailModel($video);
			$video['embed'] = $embed;

			// Reset project hashed ID
			$projectId = $video['project']['hashed_id'];
			$video['project']['hashedId'] = $projectId;
			unset($video['project']['hashed_id']);

			$videos[] = $video;
		}

		if ($offset) {
			$videos = array_slice($videos, $offset);
		}

		if ($limit) {
			$videos = array_slice($videos, 0, $limit);
		}

		return $videos;
	}

	/**
	 * Get videos by project id
	 *
	 * @param array $projects
	 * @return array|bool
	 * @throws Exception
	 */
	public function getVideosByProjectId($projects)
	{
		$cacheString = is_array($projects) ? implode('-', $projects) : '-' . $projects;

		if (($videos = craft()->httpSession->get('wistiaProjectVideos' . $cacheString, false)) !== false) {
			return $videos;
		}

		$videos = array();

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

			foreach ($projects as $project) {
				$params = array(
					'sort_by' => 'name',
					'project_id' => $project
				);

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
					$hashedId = WistiaHelper::getValue('hashed_id', $video);
					$name = htmlspecialchars_decode(WistiaHelper::getValue('name', $video));

					$videos[$hashedId] = $name;
				}
			}
		} else {
			foreach ($this->getApiData('medias.json') as $video) {
				$hashedId = WistiaHelper::getValue('hashed_id', $video);
				$name = htmlspecialchars_decode(WistiaHelper::getValue('name', $video));

				$videos[$hashedId] = $name;
			}
		}

		asort($videos);

		craft()->httpSession->add('wistiaProjectVideos' . $cacheString, $videos);

		return $videos;
	}

	/**
	 * Get all project names
	 *
	 * @return array
	 * @throws Exception
	 */
	public function getProjects()
	{
		if ($this->apiKey === false) {
			throw new Exception(lang('error_no_api_key'), 0);
		}

		if ($projects = craft()->httpSession->get('wistiaProjects', false)) {
			return $projects;
		}

		$projects = array();
		$params = array(
			'sort_by' => 'name'
		);

		try {
			$data = $this->getApiData('projects', $params);
		} catch (Exception $e) {
			throw new Exception(lang('error_no_projects'), 1, $e);
		}

		// Add each project
		foreach ($data as $project) {
			$projects[WistiaHelper::getValue('id', $project)] = WistiaHelper::getValue('name', $project);
		}

		craft()->httpSession->add('projects', $projects);

		return $projects;
	}

	/**
	 * Get js video embed
	 *
	 * @param string $hashedId
	 * @param array $params
	 * @return string
	 */
	private function getEmbed($hashedId, $params)
	{
		$params = array_filter($params, function($val) {
			return $val !== 'default';
		});

		$settings = http_build_query($params, '', ' ');

		// Use plugin template path
		$oldPath = craft()->templates->getTemplatesPath();
		craft()->templates->setTemplatesPath(
			craft()->path->getPluginsPath() . 'wistia/templates'
		);

		$html = craft()->templates->render('fieldtype/embed', array(
			'embedUrl' => $this->embedUrl,
			'hashedId' => $hashedId,
			'height' => $params['height'],
			'settings' => $settings,
			'width' => $params['width']
		));

		// Revert to site template path
		craft()->templates->setTemplatesPath($oldPath);

		return TemplateHelper::getRaw($html);
	}

	/**
	 * Get api data
	 *
	 * @param $endpoint
	 * @param array $params
	 * @param int $page
	 * @return array
	 * @throws Exception
	 */
	private function getApiData($endpoint, $params = array(), $page = 1)
	{
		$apiUrl = $this->apiUrl;

		$perPageDefault = 100;

		$apiParams = array(
			'per_page=' . $perPageDefault
		);

		if ($page) {
			$apiParams[] = 'page=' . $page;
		}

		foreach ($params as $key => $value) {
			$apiParams[] = "$key=$value";
		}

		$urlParams = '?' . implode('&', $apiParams);

		$apiUrl .= $endpoint . $urlParams;

		$data = $this->send($apiUrl);

		if ($data === false) {
			throw new Exception(lang('error_remote_file') . $apiUrl, 3);
		}

		if (count($data) === $perPageDefault) {
			$data = array_merge($data, $this->getApiData($endpoint, $params, $page + 1));
		}

		return $data;
	}

	/**
	 * Send data request to Wistia endpoint
	 *
	 * @param string $url
	 * @return array
	 * @throws Exception
	 */
	private function send($url)
	{
		if ($this->apiKey === false) {
			throw new Exception(lang('error_no_api_key'), 0);
		}

		$client = new Client();

		return $client->get($url)
			->setAuth('api', $this->apiKey)
			->setHeader('Accept', 'application/json')
			->send()
			->json();
	}
}