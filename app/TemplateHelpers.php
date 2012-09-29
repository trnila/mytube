<?php
class TemplateHelpers extends Nette\Object
{
	public static function loader($helper)
	{
		if (method_exists(__CLASS__, $helper)) {
			return callback(__CLASS__, $helper);
		}
		return NULL;
	}
}