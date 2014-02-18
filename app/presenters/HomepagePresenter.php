<?php

class HomepagePresenter extends BasePresenter
{
	/**
	 * @var Model\Videos
	 * @inject
	*/
	public $videos;

	public function renderDefault()
	{
		$this->template->videos = $this->videos->getLastVideos();
	}
}
