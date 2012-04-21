<?php
class SignPresenter extends BasePresenter
{
	public function actionOut()
	{
		$this->user->logout(TRUE);
		$this->flashMessage('Byl jste úspěšně odhlášen.', $this::FLASH_SUCCESS);
		$this->redirectHome();
	}

	protected function createComponentLogin()
	{
		return new Form\LoginForm;
	}
}
