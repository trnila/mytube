<?php
class SignPresenter extends BasePresenter
{
	/** @persistent */
	public $identity;

	public function actionOut()
	{
		$this->user->logout(TRUE);
		$this->flashMessage('Byl jste úspěšně odhlášen.', $this::FLASH_SUCCESS);
		$this->redirectHome();
	}

	public function renderIn()
	{
		$this['login']['nickname']->value = $this->getParameter('nickname');
	}

	protected function getPersistentRegistration()
	{
		return $this->getSession('Registration')
			->setExpiration('+15 minutes');
	}
}
