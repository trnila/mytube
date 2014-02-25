<?php

class AccountPresenter extends BasePresenter
{
	/**
	 * @var Form\IChangePasswordFactory
	 * @inject
	*/
	public $changePasswordFactory;

	public function createComponentChangePassword()
	{
		return $this->changePasswordFactory->create();
	}
}
