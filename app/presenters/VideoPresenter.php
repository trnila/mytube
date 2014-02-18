<?php
use Nette\Application\BadRequestException;

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
		/*if(!$this->user->isAllowed($this->video, 'edit')) {
			throw new Nette\Application\ForbiddenRequestException;
		}*/

		$id = $this->getHttpRequest()->getPost('pk');
		$name = $this->getHttpRequest()->getPost('name');
		$value = $this->getHttpRequest()->getPost('value');

		if(!$id) {
			throw new BadRequestException('POST id not provided', Nette\Http\Response::S400_BAD_REQUEST);
		}

		if(!$name) {
			throw new BadRequestException('POST name not provided', Nette\Http\Response::S400_BAD_REQUEST);
		}

		if(!$value) {
			throw new BadRequestException('POST value not provided', Nette\Http\Response::S400_BAD_REQUEST);
		}

		$this->videos->update($id, array($name => $value));

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

		/* TODO
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
		*/

		$videos = $this->videos->findAll()->limit(8)->order('RAND()');
		$this->template->videos = [];
		foreach($videos as $video) {
			$this->template->videos[] = $video;
		}
	}

	protected function createComponentRatings()
	{
		$component = $this->context->createServiceComponents__ratings();
		$component->setVideo($this->video);
		return $component;
	}

	protected function createComponentComments()
	{
		$component = $this->context->createServiceComponents__comments();
		$component->setVideo($this->video);
		return $component;
	}

	protected function createComponentAddVideo()
	{
		$form = $this->createForm();
		$form->addText('title', 'Titulek')
			->setRequired();

		$form->addTextArea('description', 'Popisek');

		$form->addText('tags', 'Tagy');

		$form->addUpload('file')
			->setRequired();

		$form->addSubmit('upload', 'Nahrát');
		$form->addProtection();
		$form->onSuccess[] = array($this, 'addVideo');

		return $form;
	}


	public function renderStatus($handle)
	{
			$client = new \GearmanClient;
			$client->addServer();

			dump($client->jobStatus($handle));
		exit;
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
			'user_id' => $this->user->id
		);

		// Add video to process
		$video = $this->videos->addVideoToProcess($video, $form['file']->value);

		// Addd tags to video
		$tags = explode(",", $form['tags']->value);
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

		$this->redirect('show', $video['id']);
	}
}