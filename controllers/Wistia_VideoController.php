<?php
namespace Craft;

class Wistia_VideoController extends BaseController
{
	/**
	 * Output video data to separate template for use in ajax request
	 *
	 * @param string $projectIds
	 * @return mixed
	 */
	public function actionGetModal($projectIds = '*')
	{
		$this->requireAjaxRequest();

		if ($projectIds != '*') {
			$projectIds = explode(',', $projectIds);
		}

		return $this->renderTemplate('wistia/fieldtype/modal', array(
			'videos' => craft()->wistia_videos->getVideosByProjectId($projectIds)
		));
	}
}