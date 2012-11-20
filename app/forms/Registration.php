<?php
namespace Form;
use Model;
use \Nette\Utils\Strings;

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

	public function __construct(Model\Users $users)
	{
		parent::__construct();
		$this->users = $users;

		$this->addText('email', 'E-Mail')
				->setRequired()
				->setType('email')
				->addRule($this::EMAIL);

		$this->addText('nickname', 'Nickname')
			->setRequired();

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
		if(isset($this->additionalData['identity'])) {
			$identity = $this->additionalData['identity'];
			unset($this->additionalData['identity']);
		}

		$values = array_merge($this->additionalData, (array) $form->values);
		unset($values['passwordCheck']);

		try {
			$user = $this->users->register($values);
		}
		catch(Model\DuplicateException $e) {
			$form->addError($e->getMessage());
			return;
		}

		// Add openid identity if any
		if(isset($identity)) {
			$user->related('identities')->insert(array('identity' => $identity));
		}

		$identity = new \Nette\Security\Identity($user['nickname'], 'user', $user);
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
