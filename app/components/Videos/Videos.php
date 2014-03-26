<?php
namespace Component\Videos;
use Nette;
use Nette\Application\ForbiddenRequestException;
use Model;
use Component;

class Videos extends Component\BaseControl
{
	protected $videos;

	public function setVideos($videos)
	{
		$this->videos = $videos;
	}

	public function render()
	{
		$template = $this->createTemplate();
		$template->setFile(__DIR__ . '/videos.latte');
		$template->videos = $this->videos;

		echo $template;
	}

	protected function createComponentPaginator()
	{
		$paginator = new Component\VisualPaginator\VisualPaginator;
		$paginator->paginator->itemsPerPage = 1;

		return $paginator;
	}
}