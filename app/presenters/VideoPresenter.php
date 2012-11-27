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

	public function handleEdit()
	{
		if(!$this->user->isAllowed($this->video, 'edit')) {
			throw new Nette\Application\ForbiddenRequestException;
		}

		$type = $this->getHttpRequest()->getPost('id');
		if($type == 'video-title') {
			$title = $this->getHttpRequest()->getPost('value');
			$this->video->update(array(
				'title' => $title
			));

			$this->payload->value = $this->template->escapeHtml($title);
			$this->payload->saved = TRUE;
		}
		elseif($type == 'video-description') {
			$description = $this->getHttpRequest()->getPost('value');
			$description = Nette\Utils\Strings::replace($description, '#<br[^>]*>#', '');

			$this->video->update(array(
				'description' => $description
			));

			$this->payload->value = $this->template->nl2br($this->template->escapeHtml($description));
			$this->payload->saved = TRUE;
		}
		elseif($type == 'video-tags') {
			$this->video->related('video_tags')
				->delete();

			$tags = $this->getHttpRequest()->getPost('value');
			if($tags) {
				$position = 0;
				foreach($tags as &$tag) {
					$tag = array(
						'tag' => trim($tag),
						'position' => $position++
					);
				}

				$this->video->related('video_tags')
					->insert($tags);
			}

			$this->payload->saved = TRUE;
		}
		else {
			throw new BadRequestException(Nette\Http\Request::S_400_BAD_REQUEST);
		}

		$this->terminate();
	}

	public function handleDelete($id)
	{
		if(!$this->user->isAllowed($this->video, 'delete')) {
			throw new Nette\Application\ForbiddenRequestException;
		}

		$this->videos->deleteVideo($this->video);

		$this->flashMessage('Video bylo smazáno.', 'success');
		$this->redirectHome();
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
					'user_nickname' => $this->user->id
				));
			}
		}
		else {
			$this->video->update(array('views' => new Nette\Database\SqlLiteral('views + 1')));
		}

		$videos = $this->videos->findAll()->limit(8)->order('RAND()');
		$this->template->videos = [];
		foreach($videos as $video) {
			$this->template->videos[] = $video;
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
		if(!$this->user->isLoggedIn()) {
			throw new Nette\Application\ForbiddenRequestException;
		}

		$video = array(
			'title' => $form['title']->value,
			'description' => $form['description']->value,
			'created' => new DateTime,
			'processed' => 0,
			'user_nickname' => $this->user->id
		);

		// Add video to process
		$video = $this->videos->addVideoToProcess($video, $form['video']->value);


		// Addd tags to video
		$tags = $this->getHttpRequest()->getPost('tags');
		if(is_array($tags)) {
			$position = 0;
			foreach($tags as &$tag) {
				$tag = [
					'tag' => trim($tag),
					'position' => $position++
				];
			}

			$video->related('video_tags')->insert($tags);
		}

		$this->flashMessage('Video bylo přidáno do fronty ke zpracování.', 'success');

		if($this->isAjax()) {
			$this->payload->videoUrl = $this->link('show', $video['id']);
			$this->terminate();
		}

		$this->redirect('show', $video['id']);
	}
}