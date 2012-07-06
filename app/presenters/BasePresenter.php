<?php

abstract class BasePresenter extends Nette\Application\UI\Presenter
{
	const FLASH_SUCCESS = 'success';
	const FLASH_ERROR = 'error';

	public function redirectHome()
	{
		$this->redirect('Homepage:');
	}

	protected function createComponent($name)
	{
		$component = parent::createComponent($name);
		if($component) {
			return $component;
		}

		return $this->context->{'createComponents__' . $name}();
	}

	protected function createForm()
	{
		return new Form\BaseForm;
	}
}
