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

	public function actionEdit($id)
	{
		$user = $this->users->find($id);
		if(!$user) {
			throw new Nette\Application\BadRequestException;
		}

		if(!$this->user->isAllowed($user, 'edit')) {
			throw new Nette\Application\ForbiddenRequestException;
		}

		$form = $this['userForm'];

		$defaults = (array) $user;
		unset($defaults['password']);

		$form->setDefaults($defaults);
	}

	public function handleDelete($id)
	{
		$user = $this->users->find($id);
		if(!$this->user->isAllowed($user, 'delete')) {
			throw new Nette\Application\ForbiddenRequestException;
		}

		$this->users->delete($user->id);

		$this->flashMessage('Uživatel byl smazán.', 'success');
		$this->redirect('this');
	}

	public function handleActivate($id, $activate = TRUE)
	{
		$user = $this->users->find($id);
		if(!$user) {
			throw new Nette\Application\BadRequestException;
		}

		// Check if we can edit this user
		if(!$this->user->isAllowed($user, 'activation')) {
			throw new Nette\Application\ForbiddenRequestException;
		}

		$this->users->update($user, array(
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

		$users = iterator_to_array($this->users->findAll());
		foreach($users as &$user) {
			$user = Model\Entity\User::create($user);
		}



		$this->template->users = $users;
	}

	protected function createComponentUserForm()
	{
		$form = $this->createForm();

		$form->addText('username', 'Username')
			->setDisabled();

		$form->addText('email', 'E-mail')
			->setRequired()
			->addRule($form::EMAIL);

		$form->addPassword('password', 'Heslo');

		$form->addPassword('passwordAgain', 'Heslo znovu')
			->addConditionOn($form['password'], $form::FILLED)
				->addRule($form::EQUAL, 'Hesla se musí shodovat', $form['password']);

		$form->addCheckbox('admin', 'Admin');

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

		$user = $this->users->find($this->getParameter('id'));
		if(!$user) {
			throw new Nette\Application\BadRequestException;
		}

		if(!$this->user->isAllowed($user, 'edit')) {
			throw new Nette\Application\ForbiddenRequestException;
		}

		if(isset($values['password'])) {
			$values['password'] = Nette\Security\Passwords::hash($values['password']);
		}

		unset($values['username']);

		$this->users->update($user, (array) $values);
		$this->flashMessage('Uživatel byl upraven.', 'success');
		$this->redirect('this');
	}
}