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
		$this->template->likedVideos = $user->related('videos')->where(':ratings.user_nickname = ? AND :ratings.positive = 1', $this->user->id)->order('created DESC');

		$this->template->info = $this->context->createServiceNette__Cache('Profiles.Gravatar')->load($nickname, function(&$cache) use($user) {
			$cache = array(
				Nette\Caching\Cache::EXPIRE => '+ 1hour'
			);

			$data = @file_get_contents('https://www.gravatar.com/' . md5($user->email). '.php');

			return $data ? unserialize($data)['entry'][0] : array();
		});
	}
}