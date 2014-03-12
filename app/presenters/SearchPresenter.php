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
		$query .= '*'; // to enable searching of non-completely typed words



		$result = $this->context->getByType('Nette\Database\Context')->table('videoSearch')
			->where("MATCH(title, description, tags) AGAINST (? IN BOOLEAN MODE)", $query)
			->order("5 * MATCH(title) AGAINST (?) + MATCH(tags) AGAINST (?) + 2 * MATCH(description) AGAINST (?) DESC", $query, $query, $query);

		$ids = array();
		foreach($result as $row) {
			$ids[] = $row->id;
		}

		$this->template->videos = $this->videos->search($query);
		$this->invalidateControl();
	}
}