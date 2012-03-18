<?php

abstract class BasePresenter extends Nette\Application\UI\Presenter
{
	protected function createForm()
	{
		$form = new Nette\Application\UI\Form;
		$form->addProtection();
		return $form;
	}
}
