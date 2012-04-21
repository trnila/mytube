<?php

class HomepagePresenter extends BasePresenter
{
	public function renderDefault()
	{
		$user = $this->context->repository->users->getTable();
		/*foreach($user as $u) {
			dump($u->toArray());
		}
		echo 'done';*/
	}
}
