<?php
namespace Craft;

class WistiaVariable
{
	public function projects()
	{
		return craft()->wistia_apiConnect->getProjects();
	}

	public function videos()
	{
		return craft()->wistia_apiConnect->getVideos();
	}
}