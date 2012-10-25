<?php
namespace ActiveRow;
use Database, Nette;

class Video extends Database\ActiveRow implements Nette\Security\IResource
{
	public function getResourceId()
	{
		return 'video';
	}
}