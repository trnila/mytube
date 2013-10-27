<?php
namespace ActiveRow;
use Fabik\Database, Nette;

class Video extends Database\ActiveRow implements Nette\Security\IResource
{
	public function getCoverThumbnail()
	{
		$thumbnail = $this->related('thumbnails')->fetch();
		return $thumbnail ? $thumbnail : NULL;
	}

	public function getResourceId()
	{
		return 'video';
	}
}