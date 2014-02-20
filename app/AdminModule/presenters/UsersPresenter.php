<?php
namespace AdminModule;
use Model, Nette, Nette\Utils\Validators;

class UsersPresenter extends BasePresenter
{
	/**
	 * @var Model\Users
	 * @inject
	*/
	public $users;

	public function actionEdit($username)
	{
		$user = $this->users->find($username);
		if(!$this->user->isAllowed($user, 'edit')) {
			throw new Nette\Application\ForbiddenRequestException;
		}

		$form = $this['userForm'];
		$form->setDefaults($user);
		$form['username']->setDisabled();
		$form['password']->setRequired(FALSE)->setDefaultValue('');
	}

	public function handleDelete($username)
	{
		$user = $this->users->find($username);
		if(!$this->user->isAllowed($user, 'delete')) {
			throw new Nette\Application\ForbiddenRequestException;
		}

		$user->delete();
		$this->flashMessage('Uživatel byl smazán.', 'success');
		$this->redirect('this');
	}

	public function handleActivate($username, $activate = TRUE)
	{
		$user = $this->users->find($username);
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
	}

	protected function createComponentUserForm()
	{
		$form = $this->createForm();

		$form->addText('username', 'username');

		$form->addText('email', 'E-mail')
			->setRequired()
			->addRule($form::EMAIL);

		$form->addPassword('password', 'Heslo')
			->setRequired();


		$form->addPassword('passwordAgain', 'Heslo znovu')
			->addConditionOn($form['password'], $form::FILLED)
				->addRule($form::EQUAL, 'Hesla se musí shodovat', $form['password']);


		$form->addSelect('role', 'Role')
			->setRequired()
			->setPrompt('Vyberte roli')
			->setItems(array(
				'user' => 'user',
				'admin' => 'admin'
			));

		$form->addSubmit('submit', 'Upravit');


		$form->onSuccess[] = array($this, 'processUserForm');

		return $form;
	}

	public function processUserForm($form)
	{
		$values = $form->values;
		unset($values['passwordAgain']);

		if(empty($values['password'])) {
			unset($values['password']);
		}

		if($username = $this->getParameter('username')) {
			$user = $this->users->find($username);
			if(!$this->user->isAllowed($user, 'edit')) {
				throw new Nette\Application\ForbiddenRequestException;
			}

			if(isset($values['password'])) {
				$values['password'] = $this->users->hash($username, $values['password']);
			}

			unset($values['username']);

			$user->update($values);
			$this->flashMessage('Uživatel byl upraven.', 'success');
			$this->redirect('this');
		}
		else {
			$values['password'] = $this->users->hash($values['username'], $values['password']);
			$this->users->create($values);

			$this->flashMessage('Uživatel byl vytvořen.', 'success');
			$this->redirect('edit', $values['username']);
		}
	}
}