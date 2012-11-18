<?php
namespace AdminModule;
use Model, Nette;

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


	public function renderList()
	{
		$users = $this->users->findAll();

		$this->template->users = $users;
	}
}