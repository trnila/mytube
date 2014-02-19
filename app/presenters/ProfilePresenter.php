<?php
class ProfilePresenter extends BasePresenter
{
	/**
	 * @var Model\Users
	 * @inject
	*/
	public $users;

	public function renderShow($username)
	{
		$user = $this->template->profile = $this->users->find($username);

		$this->template->myVideos = [];//$user->related('videos');
		$this->template->likedVideos = [];//$user->related('videos')->where(':ratings.user_id = ? AND :ratings.positive = 1', $this->user->id)->order('created DESC');
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
}