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
		$ratings->total = $ratings->positive + $ratings->negative;
		$entity->overallRating = $ratings;

		// map thumbnails
		foreach($data->related('video_thumbnails') as $thumbnail) {
			$entity->thumbnails[] = Thumbnail::create($thumbnail);
		}

		// map user
		$entity->user = 'daniel';

		return $entity;
	}

	public function getCoverThumbnail()
	{
		$thumbnail = $this->related('video_thumbnails')->fetch();
		return $thumbnail ? $thumbnail : NULL;
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

	/**
	 * @var int
	 */
	public $total;
}