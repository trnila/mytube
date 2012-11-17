<?php

class SearchPresenter extends BasePresenter
{	
	public function renderDefault($query)
	{
		$videos = $this->context->modelManager->connection->table('videoSearch')
			->where("MATCH(title, description) AGAINST (? IN BOOLEAN MODE)", $query, $query, $query)
			->order("5 * MATCH(title) AGAINST (?) + MATCH(description) AGAINST (?) DESC");

		$this->template->videos = $videos;
	}
}