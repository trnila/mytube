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

	public function actionEdit($nickname)
	{
		$user = $this->users->find($nickname);
		if(!$this->user->isAllowed($user, 'edit')) {
			throw new Nette\Application\ForbiddenRequestException;
		}

		$form = $this['userForm'];
		$form->setDefaults($user);
		$form['nickname']->setDisabled();
		$form['password']->setRequired(FALSE)->setDefaultValue('');
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
	}

	protected function createComponentUserForm()
	{
		$form = $this->createForm();

		$form->addText('nickname', 'Nickname');

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

		if($nickname = $this->getParameter('nickname')) {
			$user = $this->users->find($nickname);
			if(!$this->user->isAllowed($user, 'edit')) {
				throw new Nette\Application\ForbiddenRequestException;
			}

			if(isset($values['password'])) {
				$values['password'] = $this->users->hash($nickname, $values['password']);
			}

			unset($values['nickname']);

			$user->update($values);
			$this->flashMessage('Uživatel byl upraven.', 'success');
			$this->redirect('this');
		}
		else {
			$values['password'] = $this->users->hash($values['nickname'], $values['password']);
			$this->users->create($values);

			$this->flashMessage('Uživatel byl vytvořen.', 'success');
			$this->redirect('edit', $values['nickname']);
		}
	}
}