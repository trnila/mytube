<?php
class ProfilePresenter extends BasePresenter
{
	/**
	 * @var Model\Users
	 * @inject
	*/
	public $users;

	public function renderShow($username)
	{
		$user = $this->template->profile = $this->users->find($username);
		$this->template->myVideos = $user->related('videos');
		$this->template->likedVideos = $user->related('videos')->where(':ratings.user_id = ? AND :ratings.positive = 1', $this->user->id)->order('created DESC');

		$cache = new Nette\Caching\Cache($this->context->getByType('Nette\Caching\IStorage'), "Profiles.Gravatar");

		$this->template->info = $cache->load($username, function(&$cache) use($user) {
			$cache = array(
				Nette\Caching\Cache::EXPIRE => '+ 1hour'
			);

			$data = @file_get_contents('https://www.gravatar.com/' . md5($user->email). '.php');

			return $data ? unserialize($data)['entry'][0] : array();
		});
	}
}