<?php
class SignPresenter extends BasePresenter
{
	public function actionOut()
	{
		$this->user->logout(TRUE);
		$this->flashMessage('Byl jste úspěšně odhlášen.', $this::FLASH_SUCCESS);
		$this->redirectHome();
	}

	public function renderIn()
	{
		$facebook = $this->context->facebook;
		$user = $facebook->getUser();
		$this->template->facebook = $this->context->facebook->getLoginUrl(array(
			'scope' => 'email',
			'redirect_uri' => $this->link('//facebook')
		));
	}

	public function renderFacebook()
	{
		$facebook = $this->context->facebook;

		($me = $facebook->api('/me'));

		$identity = $this->context->facebookAuthenticator->authenticate($me);
		$this->user->login($identity);


		$this->flashMessage('Byl jste uspefadfadf', $this::FLASH_SUCCESS);
		$this->redirectHome();
	}
}
