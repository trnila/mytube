<?php

class HomepagePresenter extends BasePresenter
{


	public function renderDefault()
	{
		if($this->isAjax()) {
			$this->invalidateControl('title');
			$this->invalidateControl('content');
		}

		$this->template->videos = $this->context->getService('model__videos')->findAll()->order('created DESC');
	}
}
