<?php
namespace Model\Entity;
use Nette;

class Rating extends Nette\Object
{
 	/**
 	 * @var int
 	 */
 	public $video_id;

 	/**
 	 * @var int
 	 */
 	public $user_id;

 	/**
 	 * @var string
 	 */
 	public $positive;

 	/**
 	 * @var DateTime
 	 */
 	public $created;

 	public static function create($row)
 	{
 		$rating = new static;
 		$rating->user_id = $row->user_id;
 		$rating->video_id = $row->video_id;
 		$rating->positive = $row->positive;
 		$rating->created = $row->created;

 		return $rating;
 	}
}