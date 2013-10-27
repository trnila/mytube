<?php
namespace Form;
use Nette;

class ChangeDetails extends BaseForm
{
	/**
	 * @var \Model\Repository\Users
	 */
	protected $users;

	public function __construct(\Model\Users $users, Nette\Security\User $user)
	{
		parent::__construct();
		$this->users = $users;

		$userRow = $users->find($user->identity->id);

		$this->addText('email', 'Email')
			->setRequired()
			->addRule($this::EMAIL)
			->setDefaultValue($userRow->email);

		$this->addPrimary('change', 'Upravit');
		$this->onSuccess[] = callback($this, 'changeDetails');
	}

	public function changeDetails($form)
	{
		$values = $form->values;
		try {
			$this->users->changeDetails($this->presenter->user->id, $values);
			$this->presenter->flashMessage('Detaily byly změněny.', 'success');
			$this->presenter->redirect('this');
		}
		catch(\Model\Exception $e) {
			$this->addError($e->getMessage());
		}
	}
}
