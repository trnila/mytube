<?php
class ProfilePresenter extends BasePresenter
{
	/** @var Models\Users */
	protected $users;

	public function inject(Model\Users $users)
	{
		$this->users = $users;
	}

	public function renderShow($nickname)
	{
		$user = $this->template->profile = $this->users->find($nickname);
		$this->template->myVideos = $user->related('videos');
	}
}