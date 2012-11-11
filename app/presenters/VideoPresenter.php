<?php
class VideoPresenter extends BasePresenter
{
	/** @persistent */
	public $id;

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

			if(!$this->video) {
				throw new Nette\Application\BadRequestException;
			}

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
					'user_email' => $this->user->id
				));
			}
		}
		else {
			$this->video->update(array('views' => new Nette\Database\SqlLiteral('views + 1')));
		}
	}

	protected function createComponentRatings()
	{
		$component = $this->context->createComponents__ratings();
		$component->setVideo($this->video);
		return $component;
	}

	protected function createComponentComments()
	{
		$component = $this->context->createComponents__comments();
		$component->setVideo($this->video);
		return $component;
	}

	protected function createComponentAddVideo()
	{
		$form = $this->createForm();
		$form->addText('title', 'Titulek')
			->setRequired();

		$form->addTextArea('description', 'Popisek');

		$form->addUpload('video');

		$form->addSubmit('upload', 'Nahrát');
		$form->addProtection();
		$form->onSuccess[] = array($this, 'addVideo');

		return $form;
	}

	public function addVideo($form)
	{
		$video = array(
			'title' => $form['title']->value,
			'description' => $form['description']->value,
			'created' => new DateTime,
			'processed' => 0,
			'user_email' => $this->user->id
		);

		$this->videos->addVideoToProcess($video, $form['video']->value);

		$this->flashMessage('Video bylo přidáno do fronty ke zpracování.', 'success');
		$this->redirectHome();
	}
}