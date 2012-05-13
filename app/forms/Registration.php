<?php
namespace Form;
use Model;


class Registration extends BaseForm
{
	/**
	 * @var Models\Repository\Users
	 */
	protected $users;

	/**
	 * Additional data about user, from facebook for example.
	 * @var array
	 */
	protected $additionalData = array();

	public function __construct(Model\Repository\Users $users)
	{
		parent::__construct();
		$this->users = $users;

		$this->addText('email', 'E-Mail')
				->setRequired()
				->setType('email')
				->addRule($this::EMAIL);

		$this->addPassword('password', 'Heslo')
				->setRequired()
				->addRule($this::MIN_LENGTH, 'Minimální delka hesla je %d znaků', 5);

		$this->addPassword('passwordCheck', 'Heslo znovu')
				->setRequired()
				->addConditionOn($this['password'], $this::VALID)
					->addRule($this::EQUAL, 'Hesla se neshodují.', $this['password']);

		$this->addPrimary('register', 'Registrovat');
		$this->onSuccess[] = array($this, 'register');
	}

	public function register($form)
	{
		$values = array_merge($this->additionalData, (array) $form->values);
		unset($values['passwordCheck']);

		$user = $this->users->register($values);

		$identity = new \Nette\Security\Identity($user['email'], NULL, $user);
		$this->presenter->user->login($identity);

		$this->presenter->flashMessage('Registrace dokončena.', 'success');
		$this->presenter->redirectHome();
	}

	public function addAdditionalData($key, $data = NULL)
	{
		if($data == NULL) {
			foreach($key as $k => $value) {
				$this->additionalData[$k] = $value;
			}
		}
		else {
			$this->additionalData[$key] = $data;
		}
	}
}
