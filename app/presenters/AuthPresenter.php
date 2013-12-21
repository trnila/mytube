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
		$facebook = $this->context->getService('facebook');
		$user = $facebook->getUser();
		$this->template->facebook = $this->context->getService('facebook')->getLoginUrl(array(
			'scope' => 'email',
			'redirect_uri' => $this->link('//facebook')
		));

		$this['login']['nickname']->value = $this->getParameter('nickname');
	}

	public function actionRegistration()
	{
		// Fill data from facebook
		if($facebook = $this->getPersistentRegistration()->facebook) {
			$form = $this->getComponent('registration');
			$form->addAdditionalData('fbId', $facebook['id']);

			if(isset($facebook['username'])) {
				$form['nickname']->setDefaultValue($facebook['username']);
			}

			$form['email']->setValue($facebook['email'])
				->getControlPrototype()
					->readonly(true);
		}
		elseif($openid = $this->getPersistentRegistration()->openid) {
			$form = $this->getComponent('registration');
			$form->addAdditionalData('identity', $openid['identity']);

			$form['email']->setValue($openid['contact/email'])
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
			unset($storage->openid);
			$storage->facebook = $me;

			$this->redirect('registration');
		}
	}

	public function actionOpenID($identity)
	{
		$openid = $this->context->getService('openid');
		if(!$openid->mode) {
			$openid->identity = $identity;
			$openid->required = array('contact/email');
			$this->redirectUrl($openid->authUrl());
		}

		else {
			if($openid->validate()) {
				try {
					$identity = $this->context->getService('openIDAuthenticator')->authenticate(array($openid->identity, $openid->getAttributes()));
					$this->user->login($identity);

					$this->redirectHome();
				}
				catch(\Model\Security\Authenticator\NeedLoginException $e) {
					$this->flashMessage('Účet s tímto emailem už existuje. Pro spárování účtu se přihlašte.');
					$this->redirect('in', array(
						'identity' => $openid->identity,
						'email' => $openid->getAttributes()['contact/email']
					));
				}
				catch(\Model\Security\Authenticator\RegisterException $e) {
					$storage = $this->getPersistentRegistration();
					unset($storage->facebook);
					$storage->openid = $openid->getAttributes();
					$storage->openid['identity'] = $openid->identity;

					$this->redirect('registration');
				}
			}
			else {
				echo 'Error';
			}
		}
	}

	protected function getPersistentRegistration()
	{
		return $this->getSession('Registration')
			->setExpiration('+15 minutes');
	}
}
