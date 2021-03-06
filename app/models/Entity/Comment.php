<?php
namespace Model\Entity;
use Nette;

class Comment extends Nette\Object implements Nette\Security\IResource
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
 	public $text;

 	/**
 	 * @var DateTime
 	 */
 	public $created;

 	/**
 	 * @var User
 	 */
 	public $user;

 	public static function create($row)
 	{
 		$comment = new static;
 		$comment->id = $row->id;
 		$comment->user_id = $row->user_id;
 		$comment->text = $row->text;
 		$comment->created = $row->created;

 		$comment->user = User::create($row->user);

 		return $comment;
 	}

	public function getResourceId()
	{
		return 'comment';
	}
}