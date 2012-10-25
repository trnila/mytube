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
			$this->video = $this->videos->find($this->getParameter('id'));

			if(!$this->user->isAllowed($this->video, 'show')) {
				throw new Nette\Application\ForbiddenRequestException;
			}
		}
	}

	public function renderShow($id)
	{
		$this->template->video = $this->video;

		if($this->user->isLoggedIn()) {
			// increment views count only if last video is not same as actual
			$lastVideo = $this->video->related('history')->order('created DESC')->fetch();
			if(!$lastVideo || $lastVideo->video_id != $id) {
				$this->video->update(array('views' => new Nette\Database\SqlLiteral('views + 1')));
				$this->video->related('history')->insert(array(
					'created' => new DateTime,
					'user_id' => $this->user->id
				));
			}
		}
		else {
			$this->video->update(array('views' => new Nette\Database\SqlLiteral('views + 1')));
		}
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