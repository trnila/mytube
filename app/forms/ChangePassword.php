<?php
namespace Form;
use Nette;

class ChangePassword extends BaseForm
{
	/**
	 * @var \Model\Repository\Users
	 */
	protected $users;

	public function __construct(\Model\Users $users)
	{
		parent::__construct();
		$this->users = $users;

		$this->addPassword('oldPassword', 'Staré heslo');

		$this->addPassword('password', 'Nové heslo')
				->addRule($this::MIN_LENGTH, NULL, 5);

		$this->addPassword('passwordCheck', 'Nové heslo pro kontrolu')
				->addConditionOn($this['oldPassword'], $this::VALID)
					->addRule($this::EQUAL, NULL, $this['password']);

		$this->addPrimary('change', 'Změnit heslo');
		$this->onSuccess[] = callback($this, 'changePassword');
	}

	public function changePassword($form)
	{
		$values = $form->values;
		$this->users->changePassword($this->presenter->user->id, $values->oldPassword, $values->password);
		$this->presenter->flashMessage('Heslo bylo změněno.', 'success');
		$this->presenter->redirectHome();
	}
}
