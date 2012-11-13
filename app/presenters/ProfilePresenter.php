<?php
class ProfilePresenter extends BasePresenter
{
	/** @var Models\Users */
	protected $users;

	public function inject(Model\Users $users)
	{
		$this->users = $users;
	}

	public function renderShow($email)
	{
		$this->template->profile = $this->users->find($email);
	}
}