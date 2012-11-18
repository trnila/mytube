<?php

class SearchPresenter extends BasePresenter
{	
	/* @var Model\Videos */
	protected $videos;

	public function inject(Model\Videos $videos)
	{
		$this->videos = $videos;
	}

	public function renderDefault($query)
	{
		$this->invalidateControl();

		$result = $this->context->modelManager->connection->table('videoSearch')
			->where("MATCH(title, description) AGAINST (? IN BOOLEAN MODE)", $query, $query, $query)
			->order("5 * MATCH(title) AGAINST (?) + MATCH(description) AGAINST (?) DESC");

		$ids = array();
		foreach($result as $row) {
			$ids[] = $row->id;
		}

		$this->template->videos = $this->videos->findBy(array('id' => $ids));
	}
}