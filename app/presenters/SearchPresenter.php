<?php

class SearchPresenter extends BasePresenter
{	
	public function renderDefault($query)
	{
		$videos = $this->context->modelManager->connection->table('videos')
			->where('title LIKE ?', $query . '%');

		$this->template->videos = $videos;
	}
}