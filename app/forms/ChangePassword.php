<?php
namespace Form;
use Nette;
use Model;

class ChangePassword extends BaseForm
{
	/**
	 * @var Nette\Security\User
	 * @inject
	*/
	public $user;

	/**
	 * @var Model\Users
	 * @inject
	 */
	public $users;

	public function init()
	{
		$user = $this->users->find($this->user->id);

		if($user->password) {
			$this->addPassword('oldPassword', 'Staré heslo')
				->setRequired();
		}

		$this->addPassword('password', 'Nové heslo')
			->setRequired()
			->addRule($this::MIN_LENGTH, NULL, 5);

		$this->addPassword('passwordCheck', 'Nové heslo pro kontrolu')
			->setRequired()
			->addConditionOn($this['password'], $this::VALID)
				->addRule($this::EQUAL, 'Hesla se neshodují.', $this['password']);

		$this->addPrimary('change', 'Změnit heslo');
		$this->onSuccess[] = callback($this, 'changePassword');
	}

	public function changePassword($form)
	{
		$values = $form->values;
		try {
			$this->users->changePassword($this->user->id, isset($values->oldPassword) ? $values->oldPassword : NULL, $values->password);
			$this->presenter->flashMessage('Heslo bylo změněno.', 'success');
			$this->presenter->redirectHome();
		}
		catch(\Model\Exception $e) {
			$this->addError($e->getMessage());
		}
	}
}

interface IChangePasswordFactory
{
	/**
	 * @return ChangePassword
	*/
	public function create();
}