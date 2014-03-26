<?php

class HomepagePresenter extends BasePresenter
{
	/**
	 * @var Model\Videos
	 * @inject
	*/
	public $videos;

	/**
	 * @var Component\Videos\IFactory
	 * @inject
	*/
	public $videosFactory;

	public function renderDefault()
	{
		$component = $this['videos'];
		$paginator = $component['paginator']->paginator;
		$videos = $this->videos->getLastVideos($paginator->page, $paginator->itemsPerPage);
		$paginator->itemCount = $videos->total;
		$component->videos = $videos->videos;
	}


	protected function createComponentVideos()
	{
		$component = $this->videosFactory->create();
		$component['paginator']->paginator->itemsPerPage = 12;

		return $component;
	}
}
