<?php
namespace Model\Entity;
use Nette;

class Thumbnail extends Nette\Object
{
	/**
	 * @var int
	 */
	public $id;

	/**
	 * @var int
	 */
	public $video_id;

	/**
	 * @var int
	 */
	public $number;

	public static function create($data)
	{
		$entity = new static;
		$entity->id = $data['id'];
		$entity->video_id = $data['video_id'];
		$entity->number = $data['number'];

		return $entity;
	}

	public function getLocation()
	{
		return '/thumbnails/' . $this->video_id . '-' . $this->number . '.png';
	}
}