<?php

class SearchPresenter extends BasePresenter
{
	/**
	 * @var Model\Videos
	 * @inject
	*/
	public $videos;

	public function renderDefault($query)
	{
		$result = $this->videos->search($query . '*');

		$this->template->videos = $result->videos;
		$this->template->total = $result->total;
		$this->template->query = $query;
		$this->invalidateControl();

		$this->payload->url = $this->link('this');
	}
}