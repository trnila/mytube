<?php
use Nette\Application\BadRequestException;

class VideoPresenter extends BasePresenter
{
	/**
	 * @var Model\Videos
	 * @inject
	 */
	public $videos;

	/**
	 * @var Model\Playlists
	 * @inject
	*/
	public $playlists;

	/**
	 * @var Component\Playlists\IFactory
	 * @inject
	 */
	public $playlistsComponentFactory;

	/**
	 * @var Component\Ratings\IFactory
	 * @inject
	 */
	public $ratingsComponentFactory;

	/**
	 * @var Component\Comments\IFactory
	 * @inject
	 */
	public $commentsComponentFactory;

	public function handleEdit()
	{
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
			$this->payload->error('Povinná hodnota, vyplňte ji prosím.');
			$this->terminate();
		}

		$video = $this->getVideo($id);

		// check if user has permission to eidt file
		if(!$this->user->isAllowed($video, 'edit')) {
			throw new Nette\Application\ForbiddenRequestException;
		}

		$this->videos->update($id, array($name => $value));
		$this->terminate();
	}

	public function handleDelete($id)
	{
		$video = $this->getVideo($id);

		if(!$this->user->isAllowed($video, 'delete')) {
			throw new Nette\Application\ForbiddenRequestException;
		}

		$this->videos->deleteVideo($video);

		$this->flashMessage('Video bylo smazáno.', 'success');
		$this->redirectHome();
	}

	public function actionShow($id, $playlist_id = NULL)
	{
		$video = $this->getVideo($id);
		$this->template->video = $video;

		// playlist
		if($playlist_id) {
			$playlist = $this->getPlaylist($playlist_id);

			$this->template->playlist = $this->playlists->getVideos($playlist);
		}


		$this['ratings']->setVideo($video);
		$this['comments']->setVideo($video);
		$this['playlists']->setVideo($video);

		$this->template->videos = $this->videos->getRelated($video->id, 8);
	}

	public function actionPlaylist($playlist_id)
	{
		$playlist = $this->getPlaylist($playlist_id);
		$nextVideo = $this->playlists->getNextVideo($playlist);

		if($nextVideo) {
			$this->redirect('show', $nextVideo->id, $playlist->id);
		}

		$this->terminate();
	}

	public function handleGetStatus($id)
	{
		$video = $this->getVideo($id);

		$client = new \GearmanClient;
		$client->addServer();

		$queueStatus = array();

		$status = $client->jobStatus($video->jobid);
		if($status && $status[0] && $status[1]) {
			switch($status[2]) {
				case 1:
					$queueStatus['message'] = 'Získávání metadat souboru';
					break;

				case 2:
					$queueStatus['message'] = 'Generování náhledu';
					break;

				case 3:
					$queueStatus['percentage'] = $status[3];
					break;
			}
		}

		$this->template->queueStatus = $queueStatus;

		$this->invalidateControl('video');
	}

	protected function createComponentPlaylists()
	{
		return $this->playlistsComponentFactory->create();
	}

	protected function createComponentRatings()
	{
		return $this->ratingsComponentFactory->create();
	}

	protected function createComponentComments()
	{
		return $this->commentsComponentFactory->create();
	}

	protected function createComponentAddVideo()
	{
		$form = $this->createForm();
		$form->addText('title', 'Titulek')
			->setRequired();

		$form->addTextArea('description', 'Popisek');

		$form->addText('tags', 'Tagy');

		$form->addUpload('file')
			->addRule(function($file) {
				return Nette\Utils\Strings::startsWith($file->value->contentType, 'video/') || Nette\Utils\Strings::startsWith($file->value->contentType, 'audio/');
			}, 'Soubor musí být video nebo audio!')
			->setRequired();

		$form->addSubmit('upload', 'Nahrát');
		$form->addProtection();
		$form->onSuccess[] = array($this, 'addVideo');
		$form->onError[] = function($form) {
			if($this->isAjax()) {
				$errors = $form->getErrors();
				$this->payload->error = $errors[0];
				$this->terminate();
			}
		};

		return $form;
	}

	public function addVideo($form)
	{
		if(!$this->user->isLoggedIn()) {
			throw new Nette\Application\ForbiddenRequestException;
		}

		$video = new Model\Entity\Video;
		$video->title = $form['title']->value;
		$video->description = $form['description']->value;
		$video->created = new DateTime;
		$video->user_id = $this->user->id;

		// Addd tags to video
		$tags = explode(",", $form['tags']->value);
		if(is_array($tags)) {
			$position = 0;
			foreach($tags as $tag) {
				$video->tags[] = array(
					'tag' => trim($tag),
					'position' => $position++
				);
			}
		}

		// Add video to process
		$video = $this->videos->addVideoToProcess($video, $form['file']->value);

		$this->flashMessage('Video bylo přidáno do fronty ke zpracování.', 'success');
		$this->redirect('show', $video->id);
	}

	/**
	 * @param $id string
	 * @return Model\Entity\Video
	 * @throws Nette\Application\BadRequestException
	 */
	public function getVideo($id)
	{
		$video = $this->videos->find($id);
		if(!$video) {
			throw new BadRequestException('Video not found.');
		}

		return $video;
	}

	public function getPlaylist($id)
	{
		$playlist = $this->playlists->find($id);

		if(!$playlist) {
			throw new Nette\Application\BadRequestException;
		}

		if(!$this->user->isAllowed($playlist, 'show')) {
			throw new Nette\Application\ForbiddenRequestException;
		}

		return $playlist;
	}
}