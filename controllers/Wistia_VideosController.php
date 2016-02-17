<?php
namespace Craft;

class Wistia_VideosController extends BaseController
{
	public function actionGetModal($projectIds = '*')
	{
		$this->requireAjaxRequest();

		return $this->renderTemplate('wistia/fieldtype/modal', [
			'videos' => craft()->wistia_videos->getVideos(explode(',', $projectIds))
		]);
	}
}