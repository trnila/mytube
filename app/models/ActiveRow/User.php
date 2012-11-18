<?php
namespace ActiveRow;

class User extends ActiveRow implements \Nette\Security\IResource
{
	public function getResourceId()
	{
		return 'user';
	}
}