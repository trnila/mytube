<?php
class ProfilePresenter extends BasePresenter
{
	/**
	 * @var Model\Users
	 * @inject
	*/
	public $users;

	/**
	 * @var Model\Playlists
	 * @inject
	 */
	public $playlists;

	/**
	 * @var Model\Videos
	 * @inject
	*/
	public $videos;

	/**
	 * @var Component\Videos\IFactory
	 * @inject
	*/
	public $videosFactory;

	public function renderShow($username)
	{
		$user = $this->template->profile = $this->users->find($username);


		$ownedVideos = $this['ownedVideos'];
		$myVideos = $this->videos->getUserVideos($user->id, $ownedVideos['paginator']->paginator->page, $ownedVideos['paginator']->paginator->itemsPerPage);
		$ownedVideos['paginator']->paginator->itemCount = $myVideos->total;
		$ownedVideos->videos = $myVideos->videos;

		$this->template->likedVideos = $this->videos->getRatedVideos($user->id);

		$this->template->playlists = $this->playlists->getPublic($user->id);
	}

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

		if($name == 'email' && empty($value)) {
			$this->payload->error = 'Email je povinny!';
			$this->terminate();
		}

		$user = $this->users->find($id);
		if(!$user) {
			throw new Nette\Application\BadRequestException;
		}

		if(!$this->user->isAllowed($user, 'edit')) {
			throw new Nette\Application\ForbiddenRequestException;
		}

		$this->users->update($user, array(
			$name => trim($value)
		));

		$this->terminate();
	}

	protected function createComponentOwnedVideos()
	{
		$component = $this->videosFactory->create();
		$component['paginator']->paginator->itemsPerPage = 12;

		return $component;
	}
}