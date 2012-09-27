<?php

abstract class BasePresenter extends Nette\Application\UI\Presenter
{
	const FLASH_SUCCESS = 'success';
	const FLASH_ERROR = 'error';

	public function redirectHome()
	{
		$this->redirect('Homepage:');
	}

	public function templatePrepareFilters($template)
	{
		parent::templatePrepareFilters($template);

		if($this->context->parameters['productionMode']) {
			$template->registerFilter('Nette\Templating\Helpers::strip');
		}
	}

	protected function createComponent($name)
	{
		$component = parent::createComponent($name);
		if($component) {
			return $component;
		}

		return $this->context->{'createComponents__' . $name}();
	}

	public function createForm()
	{
		return new Form\BaseForm;
	}

	public function allowed($resource, $action = NULL)
	{
		if(!$this->user->isAllowed($resource, $action)) {
			throw new \Nette\Application\ForbiddenRequestException;
		}
	}
}
