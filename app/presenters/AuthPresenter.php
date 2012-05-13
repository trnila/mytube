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

	public function actionRegistration()
	{
		$user = $this->getPersistentRegistration()->user;

		// Fill data from facebook
		if($user) {
			$form = $this->getComponent('registration');
			$form->addAdditionalData('fbId', $user['id']);

			$form['email']->setValue($user['email'])
				->getControlPrototype()
					->readonly(true);
		}
	}

	public function renderFacebook()
	{
		$facebook = $this->context->facebook;

		$me = $facebook->api('/me');

		try {
			$identity = $this->context->facebookAuthenticator->authenticate($me);
			$this->user->login($identity);

			$this->flashMessage('Úspěšně přihlášeno.', $this::FLASH_SUCCESS);
			$this->redirectHome();
		}
		catch(\Model\Security\Authenticator\RegisterException $e) {
			$storage = $this->getPersistentRegistration();
			$storage->user = $me;

			$this->redirect('registration');
		}
	}

	protected function getPersistentRegistration()
	{
		return $this->getSession('Registration');
	}
}
