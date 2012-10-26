<?php
namespace ActiveRow;
use Nette;

class Comment extends ActiveRow implements Nette\Security\IResource
{
	public function getResourceId()
	{
		return 'comment';
	}
}