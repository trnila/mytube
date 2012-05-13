<?php

abstract class BasePresenter extends Nette\Application\UI\Presenter
{
	const FLASH_SUCCESS = 'success';
	const FLASH_ERROR = 'error';

	public function redirectHome()
	{
		$this->redirect('Homepage:');
	}

	protected function createComponent($name) {
		return $this->context->{'createComponents__' . $name}();
	}
}
