<?php
namespace Model\Entity;
use Nette;

class Video extends Nette\Object implements Nette\Security\IResource
{
	/**
	 * @var id
	 */
	public $id;

	/**
	 * @var string
	 */
	public $title;

	/**
	 * @var string
	 */
	public $description;

	/**
	 * @var DateTime
	 */
	public $created;

	/**
	 * @var int
	 */
	public $user_id;

	/**
	 * @var int
	 */
	public $duration;

	/**
	 * @var boolean
	 */
	public $enabled;

	/**
	 * @var boolean
	 */
	public $isvideo;

	/**
	 * @var string
	 */
	public $jobid;

	/**
	 * @var array
	 */
	public $tags = array();

	/**
	 * @var array
	 */
	public $thumbnails = array();

	/**
	 * @var OverallRating
	 */
	public $overallRating;

	public $user;


	public static function create($data)
	{
		$entity = new static;
		foreach(array('id', 'title', 'description', 'created', 'user_id', 'duration', 'enabled', 'isvideo', 'jobid') as $property) {
			$entity->$property = $data[$property];
		}

		// map tags
		foreach($data->related('tags')->order('position') as $tag) {
			$entity->tags[] = $tag;
		}

		// map rating
		$ratings = new OverallRating;
		$ratings->positive = $data->related('ratings')->where('positive', TRUE)->count('user_id');
		$ratings->negative = $data->related('ratings')->where('positive', FALSE)->count('user_id');
		$entity->overallRating = $ratings;

		// map thumbnails
		foreach($data->related('video_thumbnails') as $thumbnail) {
			$entity->thumbnails[] = Thumbnail::create($thumbnail);
		}

		// map user
		$entity->user = User::create($data->user);

		return $entity;
	}

	public function isConverted()
	{
		return $this->jobid === NULL;
	}

	public function getResourceId()
	{
		return 'video';
	}
}

class OverallRating extends Nette\Object
{
	/**
	 * @var int
	 */
	public $positive;

	/**
	 * @var int
	 */
	public $negative;

	public function getTotal()
	{
		return $this->positive + $this->negative;
	}
}