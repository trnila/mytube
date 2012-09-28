<?php
class VideoPresenter extends BasePresenter
{
	/**
	 * @var Model\Videos
	 */
	protected $videos;

	protected $video;

	public function inject(Model\Videos $videos) 
	{
		$this->videos = $videos;
	}

	public function startup()
	{
		parent::startup();

		if($this->getParameter('id')) {
			$this->video = $this->videos->find($this->getParameter('id'))->fetch();
		}
	}

	public function renderShow($id)
	{
		$this->template->video = $this->video;
	}

	public function createComponentRatings()
	{
		$component = $this->context->createComponents__ratings();
		$component->setVideo($this->video);
		return $component;
	}

	public function createComponentComments()
	{
		$component = $this->context->createComponents__comments();
		$component->setVideo($this->video);
		return $component;
	}
}