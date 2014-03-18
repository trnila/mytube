<?php
namespace Component\Playlists;
use Nette;
use DateTime;
use Model;
use Component;

class Playlists extends Component\BaseControl
{
	/**
	 * @var Model\Playlists
	 * @inject
	 */
	public $playlists;

	/**
	 * @var Model\Entity\Video
	*/
	protected $video;

	public function setVideo(Model\Entity\Video $video)
	{
		$this->video = $video;
	}

	public function handleAdd($playlist_id, $add = TRUE)
	{
		$playlist = $this->playlists->find($playlist_id);
		if(!$playlist) {
			throw new Nette\Application\BadRequestException;
		}

		if(!$this->presenter->user->isAllowed($playlist, 'manage')) {
			throw new Nette\Application\ForbiddenRequestException;
		}

		if($add) {
			$this->playlists->addVideo($playlist->id, $this->video->id);
		} else {
			$this->playlists->removeVideo($playlist->id, $this->video->id);
		}

		if($this->presenter->isAjax()) {
			$this->invalidateControl('list');
		} else {
			$this->redirect('this');
		}
	}

	public function handleDelete($playlist_id)
	{
		$playlist = $this->playlists->find($playlist_id);
		if(!$playlist) {
			throw new Nette\Application\BadRequestException;
		}

		if(!$this->presenter->user->isAllowed($playlist, 'delete')) {
			throw new Nette\Application\ForbiddenRequestException;
		}

		$this->playlists->delete($playlist_id);

		if($this->presenter->isAjax()) {
			$this->invalidateControl('list');
		} else {
			$this->redirect('this');
		}
	}

	public function handleSetPrivate($playlist_id, $private = TRUE)
	{
		$playlist = $this->playlists->find($playlist_id);
		if(!$playlist) {
			throw new Nette\Application\BadRequestException;
		}

		if(!$this->presenter->user->isAllowed($playlist, 'manage')) {
			throw new Nette\Application\ForbiddenRequestException;
		}

		$this->playlists->setPrivate($playlist->id, $private);

		if($this->presenter->isAjax()) {
			$this->invalidateControl('list');
		} else {
			$this->redirect('this');
		}
	}

	public function render()
	{
		$template = $this->createTemplate();
		$template->setFile(__DIR__ . '/playlists.latte');

		$template->playlists = $this->playlists->getAll($this->presenter->user->id, $this->video->id);

		echo $template;
	}

	protected function createComponentPlaylist()
	{
		$form = new Nette\Application\UI\Form;

		$form->addText('name', 'Název')
			->setRequired();

		$form->addSelect('type')
			->setItems(array(
				1 => 'Privátni',
				0 => 'Veřejný'
			));


		$form->addSubmit('send');
		$form->onSuccess[] = $this->processPlaylist;

		return $form;
	}

	public function processPlaylist($form)
	{
		$playlist = new Model\Entity\Playlist;
		$playlist->name = $form['name']->value;
		$playlist->private = $form['type']->value;
		$playlist->user_id = $this->presenter->user->id;
		$playlist->created = new DateTime;

		$this->playlists->create($playlist);

		if($this->presenter->isAjax()) {
			$form['name']->value = '';
			$this->invalidateControl('list');
			$this->invalidateControl('form');
		} else {
			$this->redirect('this');
		}
	}
}