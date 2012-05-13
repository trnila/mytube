<?php
namespace Form;
use Nette;

class ChangePassword extends BaseForm
{
	public function __construct()
	{
		parent::__construct();
		$this->addPassword('oldPassword', 'Staré heslo');

		$this->addPassword('password', 'Nové heslo')
				->addRule($this::MIN_LENGTH, NULL, 5);

		$this->addPassword('passwordCheck', 'Nové heslo pro kontrolu')
				->addConditionOn($this['oldPassword'], $this::VALID)
					->addRule($this::EQUAL, NULL, $this['password']);

		$this->addSubmit('change', 'Změnit heslo');
		$this->onSuccess[] = callback($this, 'changePassword');
	}

	public function changePassword($form)
	{
		
	}
}
