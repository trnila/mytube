<?php
namespace Component;
use Nette;

class BaseControl extends Nette\Application\UI\Control
{
	public function createTemplate($class = NULL)
	{
		$template = parent::createTemplate($class);
		$template->registerHelperLoader('TemplateHelpers::loader');
		return $template;
	}

}