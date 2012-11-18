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
		$data = file_get_contents('http://www.gravatar.com/205e460b479e2e5b48aec07710c08d50.php');
		$data = unserialize($data);
		$this->template->info = $data['entry'][0];


		$user = $this->template->profile = $this->users->find($nickname);
		$this->template->myVideos = $user->related('videos');
	}
}