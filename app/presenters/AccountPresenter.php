<?php

class AccountPresenter extends BasePresenter
{
	/**
	 * @var Model\Users
	 * @inject
	*/
	public $users;

	/**
	 * @var Form\IChangePasswordFactory
	 * @inject
	*/
	public $changePasswordFactory;

	public function createComponentChangePassword()
	{
		return $this->changePasswordFactory->create();
	}

	public function createComponentChangeAvatar()
	{
		$form = new Nette\Application\UI\Form;

		$form->addUpload('image', 'Avatar')
			->addCondition($form::FILLED)
				->addRule($form::IMAGE);

		$form->addSubmit('submit', 'Změnit');
		$form->onSuccess[] = $this->processAvatar;

		return $form;
	}

	public function processAvatar($form)
	{
		$this->users->changeAvatar($this->user->id, $form['image']->isFilled() ? $form['image']->value->toImage() : NULL);
		$this->flashMessage('Avatar byl změněn.', 'success');
		$this->redirect('Profile:show', $this->user->id);
	}
}
