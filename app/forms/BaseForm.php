<?php
namespace Form;
use Nette;

class BaseForm extends Nette\Application\UI\Form
{
	public function addPrimary($name, $caption = NULL)
	{
		$control = new Nette\Forms\Controls\SubmitButton($caption);
		$control->getControlPrototype()->addClass('btn btn-primary');
		return $this[$name] = $control;
	}	
}
