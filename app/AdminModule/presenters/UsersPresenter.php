<?php
namespace AdminModule;
use Model, Nette, Nette\Utils\Validators;

class UsersPresenter extends BasePresenter
{
	/** @var Model\Users */
	protected $users;

	public function inject(Model\Users $users)
	{
		$this->users = $users;
	}

	public function handleDelete($nickname)
	{
		$user = $this->users->find($nickname);
		if(!$this->user->isAllowed($user, 'delete')) {
			throw new Nette\Application\ForbiddenRequestException;
		}

		$user->delete();
		$this->flashMessage('Uživatel byl smazán.', 'success');
		$this->redirect('this');
	}

	public function handleEdit()
	{
		$post = $this->getHttpRequest()->getPost();
		if(!isset($post['id']) || !($user = $this->users->find($post['id']))) {
			throw new Nette\Application\BadRequestException;
		}

		// Check if we can edit this user
		if(!$this->user->isAllowed($user, 'edit')) {
			throw new Nette\Application\ForbiddenRequestException;
		}

		if(isset($post['email'])) {
			if(!Validators::isEmail($post['email'])) {
				$this->payload->error = 'Zadejte email ve správném formátu.';
				$this->payload->original = $post['email'];
				$this->terminate();
			}

			$user->update(array(
				'email' => $post['email']
			));

			$this->payload->value = $post['email'];
		}
		elseif(isset($post['role']) && in_array($post['role'], array_keys($this->getRoles()))) {
			$user->update(array(
				'role' => $post['role']
			));

			$this->payload->value = $this->getRoles()[$post['role']];
		}
		else {
			throw new Nette\Application\BadRequestException('', Nette\Http\Response::S400_BAD_REQUEST);
		}


		$this->terminate();
	}

	public function handleActivate($nickname, $activate = TRUE)
	{
		$user = $this->users->find($nickname);
		if(!$user) {
			throw new Nette\Application\BadRequestException;
		}

		// Check if we can edit this user
		if(!$this->user->isAllowed($user, 'activation')) {
			throw new Nette\Application\ForbiddenRequestException;
		}

		$user->update(array(
			'active' => $activate
		));

		$this->flashMessage('Uživatel byl ' . ($activate ? 'povolen.' : 'zakázán.'), 'success');
		$this->redirect('this');
	}

	public function renderList()
	{
		if(!$this->user->isAllowed('user', 'list')) {
			throw new Nette\Application\ForbiddenRequestException;
		}

		$users = $this->users->findAll();
		$this->template->users = $users;
		$this->template->roles = $this->getRoles();
	}

	protected function getRoles()
	{
		return array(
			'user' => 'Uživatel',
			'admin' => 'Administrator'
		);
	}
}