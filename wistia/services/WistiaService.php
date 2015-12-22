<?php
namespace Craft;

class WistiaService extends BaseApplicationComponent
{
	/**
	 * Fetches a list of Wistia Projects associated with an Account.
	 *
	 * TODO: Actually fetch the list of projects from Wistia. Take it one
	 * method at a time.
	 *
	 * @return array A list of projects
	 */
	public function getProjects() {
		$videos = array(
			'project-1' => 'Project 1',
			'project-2' => 'Project 2',
		);
		return $videos;
	}
}