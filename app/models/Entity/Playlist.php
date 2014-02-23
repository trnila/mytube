<?php
namespace Model\Entity;
use Nette;

class Playlist extends Nette\Object implements Nette\Security\IResource
{
	/**
	 * @var int
	 */
	public $id;

	/**
	 * @var int
	 */
	public $user_id;

	/**
	 * @var string
	 */

	public $name;

	/**
	 * @var DateTime
	 */
	public $created;

	/**
	 * @var boolean
	 */
	public $private;

	public function getResourceId()
	{
		return 'playlist';
	}

	public static function create($row)
	{
		$entity = new static;
		$entity->id = $row['id'];
		$entity->user_id = $row['user_id'];
		$entity->name = $row['name'];
		$entity->created = $row['created'];
		$entity->private = $row['private'];

		return $entity;
	}
}