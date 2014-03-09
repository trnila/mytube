<?php
namespace Form;
use Model;
use Nette\Utils\Strings;
use Nette;

class Registration extends Nette\Application\UI\Control
{
	/**
	 * @var Model\Users
	 * @inject
	 */
	public $users;

	/**
	 * @var Nette\Http\Request
	 * @inject
	*/
	public $httpRequest;

	/**
	 * Additional data about user, from facebook for example.
	 * @var array
	 */
	protected $additionalData = array();

	public function createComponentForm()
	{
		$form = new Nette\Application\UI\Form;

		$form->addText('email', 'E-Mail')
				->setRequired()
				->setType('email')
				->addRule($form::EMAIL);

		$form->addText('firstname', 'Jméno');
		$form->addText('lastname', 'Příjmení');

		$form->addText('username', 'Username')
			->setRequired();

		$form->addPassword('password', 'Heslo')
				->setRequired()
				->addRule($form::MIN_LENGTH, 'Minimální delka hesla je %d znaků', 5);

		$form->addPassword('passwordCheck', 'Heslo znovu')
				->setRequired()
				->addConditionOn($form['password'], $form::VALID)
					->addRule($form::EQUAL, 'Hesla se neshodují.', $form['password']);

		$form->addSubmit('register', 'Registrovat');
		$form->onSuccess[] = array($this, 'register');

		return $form;
	}

	public function register($form)
	{
		if(isset($this->additionalData['identity'])) {
			$identity = $this->additionalData['identity'];
			unset($this->additionalData['identity']);
		}

		$values = array_merge($this->additionalData, (array) $form->values);

		try {
			$user = $this->users->register($values);
		}
		catch(Model\DuplicateException $e) {
			if($e->getKey() == 'username') {
				$form['username']->addError('Uživatelské jméno již existuje.');
			}
			else {
				// could be risky?
				$form->addError($e->getMessage());
			}
			return;
		}

		// Add openid identity if any
		if(isset($identity)) {
			$this->users->addIdentity($user->id, array('type' => 'openid', 'identity' => $identity));
		}

		$identity = new \Nette\Security\Identity($user->id, NULL, $user);
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

	public function handleGravatar()
	{
		$email = $this->httpRequest->getPost('email');
		if(!$email) {
			throw new Nette\Application\BadRequestException('Missing email', 400);
		}

		$opts = array(
			'http' => array(
				'timeout' => 3
			)
		);

		$userData = array();
		$response = file_get_contents("https://www.gravatar.com/" . md5(strtolower($email)) . ".php", false, stream_context_create($opts));
		if($response !== FALSE) {
			$data = unserialize($response);
			if($data && isset($data['entry'], $data['entry'][0])) {
				$identity = $data['entry'][0];

				if(isset($identity['preferredUsername'])) {
					$userData['username'] = $identity['preferredUsername'];
				}

				if(isset($identity['name'], $identity['name']['givenName'])) {
					$userData['firstname'] = $identity['name']['givenName'];
				}

				if(isset($identity['name'], $identity['name']['familyName'])) {
					$userData['lastname'] = $identity['name']['familyName'];
				}

			}
		}

		$this->presenter->sendJson($userData);
	}

	public function render()
	{
		$template = $this->createTemplate();
		$template->setFile(__DIR__ . '/registration.latte');

		echo $template;
	}
}

interface IRegistrationFactory
{
	/**
	 * @return Registration
	 */
	public function create();
}