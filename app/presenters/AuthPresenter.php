<?php
class SignPresenter extends BasePresenter
{
	/**
	 * @var Facebook
	 * @inject
	*/
	public $facebook;

	/**
	 * @var Form\ILoginFactory
	 * @inject
	*/
	public $loginFactory;

	/**
	 * @var Form\IRegistrationFactory
	 * @inject
	*/
	public $registrationFactory;

	/**
	 * @var Model\Users
	 * @inject
	*/
	public $users;

	/**
	 * @var Nette\Mail\IMailer
	 * @inject
	*/
	public $mailer;

	public function actionOut()
	{
		$this->user->logout(TRUE);
		$this->flashMessage('Byl jste úspěšně odhlášen.', $this::FLASH_SUCCESS);
		$this->redirectHome();
	}

	public function actionRecovery($email, $token)
	{
		$storage = $this->getPersistentReset();
		if(!isset($storage->{$email}) || $storage->{$email} != $token) {
			$this->flashMessage('Odkaz pro obnovu ztraceného hesla vypršel nebo je neplatný. Požadejte o obnovu znovu.', 'danger');
			$this->redirect('reset');
		}

		$user = $this->users->findByEmail($email);
		if(!$user) {
			$this->flashMessage('Uživatel již neexistuje', 'error');
			$this->redirect('Homepage:');
		}

		unset($storage->{$email});
		$this->users->removePassword($user->id);

		$this->user->login(new Nette\Security\Identity($user->id));
		$this->flashMessage('Nyní si můžete změnit své heslo.', 'success');
		$this->redirect('Account:changePassword');
	}

	public function renderIn()
	{
		$this->template->facebook = $this->facebook->getLoginUrl(array(
			'scope' => 'email',
			'redirect_uri' => $this->link('//facebook')
		));

		$this['login']['username']->setDefaultValue($this->getParameter('username'));
	}

	public function actionRegistration()
	{
		// Fill data from facebook
		if($facebook = $this->getPersistentRegistration()->facebook) {
			$form = $this->getComponent('registration');
			$form->addAdditionalData('fbId', $facebook['id']);

			if(isset($facebook['username'])) {
				$form['form']['username']->setDefaultValue($facebook['username']);
			}

			$form['form']['email']->setValue($facebook['email'])
				->getControlPrototype()
					->readonly(true);
		}
		elseif($openid = $this->getPersistentRegistration()->openid) {
			$form = $this->getComponent('registration');
			$form->addAdditionalData('identity', $openid['identity']);

			$form['form']['email']->setValue($openid['contact/email'])
				->getControlPrototype()
					->readonly(true);
		}
	}

	public function renderFacebook()
	{
		$me = $this->facebook->api('/me');

		try {
			$identity = $this->context->getService('facebookAuthenticator')->authenticate($me);
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

					$this->getPersistentLogin()->{$openid->getAttributes()['contact/email']} = $openid->identity;

					$this->redirect('in');
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

	protected function createComponentLogin()
	{
		return $this->loginFactory->create();
	}

	protected function createComponentRegistration()
	{
		return $this->registrationFactory->create();
	}

	protected function createComponentResetPassword()
	{
		$form = new Nette\Application\UI\Form;

		$form->addText('email', 'Email')
			->setRequired();

		$form->addSubmit('submit', 'Resetovat');
		$form->onSuccess[] = $this->processResetPassword;

		return $form;
	}

	public function processResetPassword($form)
	{
		$email = $form['email']->value;
		$user = $this->users->findByEmail($email);
		if(!$user) {
			$form['email']->addError('Uživatel s tímto emailem neexistuje.');
			return;
		}

		$storage = $this->getPersistentReset();
		if(isset($storage->{$email})) {
			$form['email']->addError('Již byl poslán email pro obnovu, zkontrolujte email.');
			return;
		}

		$hash = Nette\Utils\Strings::random(32);
		$storage->{$email} = $hash;

		$message = new Nette\Mail\Message;
		$message->addTo($email);
		$message->setSubject('Obnova ztraceného hesla');

		$template = $this->createTemplate();
		$template->setFile(__DIR__ . '/../emails/password-recovery.latte');
		$template->u = $user;
		$template->link = $this->link('//recovery', array('email' => $email, 'token' => $hash));

		$message->setHtmlBody($template);

		$this->mailer->send($message);


		$this->flashMessage('Na váš email byl odeslán email s informacemi jak postupovat při obnově hesla.', 'info');
		$this->redirect('this');
	}

	protected function getPersistentRegistration()
	{
		return $this->getSession('Registration')
			->setExpiration('+15 minutes');
	}

	public function getPersistentLogin()
	{
		return $this->getSession('Login')
			->setExpiration('+15 minutes');
	}

	public function getPersistentReset()
	{
		return $this->getSession('ResetPassword')
			->setExpiration('+15 minutes');
	}
}
