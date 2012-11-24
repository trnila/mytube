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
		$query .= '*'; // to enable searching of non-completely typed words

		$result = $this->context->modelManager->connection->table('videoSearch')
			->where("MATCH(title, description, tags) AGAINST (? IN BOOLEAN MODE)", $query, $query, $query, $query)
			->order("5 * MATCH(title) AGAINST (?) + MATCH(tags) AGAINST (?) + 2 * MATCH(description) AGAINST (?) DESC");

		$ids = array();
		foreach($result as $row) {
			$ids[] = $row->id;
		}

		$this->template->videos = $this->videos->findBy(array('id' => $ids));
		$this->invalidateControl();
	}
}