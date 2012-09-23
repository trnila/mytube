<?php
class VideoPresenter extends BasePresenter
{
	/**
	 * @var Model\Videos
	 */
	protected $videos;

	public function inject(Model\Videos $videos) 
	{
		$this->videos = $videos;
	}

	public function renderShow($id)
	{
		$this->template->video = $this->videos->find($id)->fetch();
	}
}